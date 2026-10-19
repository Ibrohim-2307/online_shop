<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerMember;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Auth\OtpServiceInterface;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected OtpServiceInterface $otpService
    ) {}

    /**
     * User registration (Customer, Wholesale Customer, or Seller).
     * Strictly immune to mass assignment or role elevation attacks.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required_without:phone', 'nullable', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required_without:email', 'nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8'],
            'account_type' => ['required', 'string', Rule::in(['customer', 'wholesale_customer', 'seller'])],
            // Seller-specific fields
            'store_name' => ['required_if:account_type,seller', 'nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'legal_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? ($validated['phone'] . '@placeholder.local'),
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'is_active' => true,
        ]);

        $accountType = $validated['account_type'];

        if ($accountType === 'seller') {
            $storeName = $validated['store_name'];
            $slug = Str::slug($storeName) . '-' . Str::random(5);

            $seller = Seller::create([
                'name' => $storeName,
                'slug' => $slug,
                'legal_name' => $validated['legal_name'] ?? null,
                'tax_id' => $validated['tax_id'] ?? null,
                'status' => 'pending', // Requires admin approval
                'phone' => $user->phone,
                'email' => $user->email,
            ]);

            SellerMember::create([
                'seller_id' => $seller->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'permissions' => ['*'],
                'is_active' => true,
            ]);

            $user->assignRole('seller_owner');
        } elseif ($accountType === 'wholesale_customer') {
            $user->assignRole('wholesale_customer');
        } else {
            $user->assignRole('customer');
        }

        AuditService::log('auth.registered', $user, null, ['account_type' => $accountType]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::created([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUserPayload($user),
        ], "Muvaffaqiyatli ro'yxatdan o'tdingiz");
    }

    /**
     * User login with email/phone and password.
     * Enforces Admin MFA if user holds the admin role.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $validated['login'];

        $user = User::where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ["Login yoki parol noto'g'ri."],
            ]);
        }

        if (!$user->is_active) {
            return ApiResponse::forbidden("Ushbu hisob faolsizlantirilgan. Administrator bilan bog'laning.", 'ACCOUNT_DEACTIVATED');
        }

        $user->update(['last_login_at' => Carbon::now()]);

        // Security requirement: Admin accounts require Multi-Factor Authentication (MFA)
        if ($user->hasRole('admin')) {
            $mfaToken = Str::random(40);
            $challengeKey = "admin_mfa_{$mfaToken}";

            Cache::put($challengeKey, [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ], now()->addMinutes(5));

            // Generate and send MFA OTP
            $otpTarget = $user->phone ?: $user->email;
            $mfaResult = $this->otpService->send($otpTarget, 'admin_mfa');

            AuditService::log('auth.admin_mfa_challenged', $user);

            return ApiResponse::success([
                'mfa_required' => true,
                'mfa_token' => $mfaToken,
                'message' => 'Administrator uchun ikki bosqichli tasdiqlash (MFA) kodi talab qilinadi',
                'demo_code' => $mfaResult['demo_code'] ?? null,
            ]);
        }

        AuditService::log('auth.login', $user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUserPayload($user),
        ], "Tizimga muvaffaqiyatli kirildi");
    }

    /**
     * Verify Admin MFA challenge and issue administrative token.
     */
    public function verifyAdminMfa(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mfa_token' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);

        $challengeKey = "admin_mfa_{$validated['mfa_token']}";
        $challenge = Cache::get($challengeKey);

        if (!$challenge) {
            return ApiResponse::error(
                message: "MFA seansi muddati tugagan yoki token yaroqsiz",
                code: 'EXPIRED_MFA_CHALLENGE',
                status: 422
            );
        }

        $user = User::find($challenge['user_id']);

        if (!$user || !$user->is_active || !$user->hasRole('admin')) {
            Cache::forget($challengeKey);
            return ApiResponse::forbidden("MFA tekshiruvi rad etildi");
        }

        $otpTarget = $user->phone ?: $user->email;
        $isValid = $this->otpService->verify($otpTarget, $validated['code'], 'admin_mfa');

        if (!$isValid) {
            return ApiResponse::error(
                message: "MFA tasdiqlash kodi noto'g'ri yoki uning muddati tugagan",
                code: 'INVALID_MFA_CODE',
                status: 422
            );
        }

        // Successfully validated: clear MFA challenge
        Cache::forget($challengeKey);

        AuditService::log('auth.admin_mfa_success', $user);

        $token = $user->createToken('admin_auth_token', ['*'])->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUserPayload($user),
        ], "Ikki bosqichli tasdiqlash muvaffaqiyatli yakunlandi");
    }

    /**
     * Send OTP code to phone number.
     */
    public function otpSend(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'type' => ['nullable', 'string', Rule::in(['login', 'register', 'password_reset'])],
        ]);

        $type = $validated['type'] ?? 'login';
        $result = $this->otpService->send($validated['phone'], $type);

        return ApiResponse::success($result, $result['message']);
    }

    /**
     * Verify OTP code and authenticate/register user.
     */
    public function otpVerify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'code' => ['required', 'string', 'max:10'],
            'type' => ['nullable', 'string', Rule::in(['login', 'register', 'password_reset'])],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $type = $validated['type'] ?? 'login';

        $isValid = $this->otpService->verify($validated['phone'], $validated['code'], $type);

        if (!$isValid) {
            return ApiResponse::error(
                message: "Tasdiqlash kodi noto'g'ri yoki uning amal qilish muddati tugagan",
                code: 'INVALID_OTP',
                status: 422
            );
        }

        // Find or create user
        $user = User::where('phone', $validated['phone'])->first();

        if (!$user) {
            $user = User::create([
                'name' => $validated['name'] ?? ('Foydalanuvchi ' . substr($validated['phone'], -4)),
                'phone' => $validated['phone'],
                'email' => $validated['phone'] . '@placeholder.local',
                'password' => Str::random(16),
                'is_active' => true,
            ]);
            $user->assignRole('customer');
        }

        if (!$user->is_active) {
            return ApiResponse::forbidden("Ushbu hisob faolsizlantirilgan.", 'ACCOUNT_DEACTIVATED');
        }

        $user->update(['last_login_at' => Carbon::now()]);
        AuditService::log('auth.otp_login', $user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUserPayload($user),
        ], "Telefon raqami muvaffaqiyatli tasdiqlandi");
    }

    /**
     * Request a time-limited single-use password reset token.
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $email = $validated['email'];
        $user = User::where('email', $email)->first();

        $token = Str::random(64);

        // Save token with 15-minute validity
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        AuditService::log('password.forgot_requested', $user);

        $isLocalOrTesting = app()->environment('local', 'testing');

        return ApiResponse::success([
            'email' => $email,
            'expires_in_minutes' => 15,
            'reset_token' => $isLocalOrTesting ? $token : null,
        ], "Parolni tiklash havolasi yuborildi");
    }

    /**
     * Reset password using a single-use token; rejects replay.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = $validated['email'];
        $token = $validated['token'];

        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return ApiResponse::error(
                message: "Tiklash belgisi topilmadi yoki u allaqachon ishlatilgan",
                code: 'INVALID_TOKEN',
                status: 422
            );
        }

        // Check 15-minute expiration
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return ApiResponse::error(
                message: "Tiklash belgisining amal qilish muddati tugagan",
                code: 'EXPIRED_TOKEN',
                status: 422
            );
        }

        // Check token hash
        if (!Hash::check($token, $record->token)) {
            return ApiResponse::error(
                message: "Tiklash belgisi noto'g'ri",
                code: 'INVALID_TOKEN',
                status: 422
            );
        }

        $user = User::where('email', $email)->firstOrFail();
        $user->update(['password' => $validated['password']]);

        // CRUCIAL: Immediately delete token from database to prevent re-use / replay
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Revoke all previous API tokens for this user for security
        $user->tokens()->delete();

        AuditService::log('password.reset_completed', $user);

        return ApiResponse::success(null, "Parol muvaffaqiyatli yangilandi. Yangi parol bilan tizimga kiring");
    }

    /**
     * Return authenticated user profile.
     */
    public function me(Request $request): JsonResponse
    {
        return ApiResponse::success($this->formatUserPayload($request->user()));
    }

    /**
     * Revoke current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        AuditService::log('auth.logout', $request->user());

        return ApiResponse::success(null, "Tizimdan muvaffaqiyatli chiqildi");
    }

    /**
     * Helper to format standardized user response payload.
     */
    protected function formatUserPayload(User $user): array
    {
        $user->loadMissing(['roles.permissions', 'sellerMemberships.seller']);

        $roles = $user->roles->pluck('name')->toArray();
        $permissions = $user->roles->flatMap->permissions->pluck('name')->unique()->values()->toArray();

        $seller = $user->activeSeller();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_active' => $user->is_active,
            'roles' => $roles,
            'permissions' => $permissions,
            'seller' => $seller ? [
                'id' => $seller->id,
                'name' => $seller->name,
                'slug' => $seller->slug,
                'status' => $seller->status,
                'role' => $user->sellerMember($seller->id)?->role,
            ] : null,
            'last_login_at' => $user->last_login_at?->toIso8601String(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
