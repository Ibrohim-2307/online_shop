<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerMember;
use App\Models\User;
use App\Services\Auth\OtpServiceInterface;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::created([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUserPayload($user),
        ], "Muvaffaqiyatli ro'yxatdan o'tdingiz");
    }

    /**
     * User login with email/phone and password.
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
            return ApiResponse::forbidden("Ushbu hisob faolsizlantirilgan. Administrator bilan bog'laning.");
        }

        $user->update(['last_login_at' => Carbon::now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUserPayload($user),
        ], "Tizimga muvaffaqiyatli kirildi");
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
            return ApiResponse::forbidden("Ushbu hisob faolsizlantirilgan.");
        }

        $user->update(['last_login_at' => Carbon::now()]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $this->formatUserPayload($user),
        ], "Telefon raqami muvaffaqiyatli tasdiqlandi");
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
