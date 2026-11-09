<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Audit\AuditService;
use App\Services\Auth\OtpServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /** SMS cost money and a stranger can type anyone's number, so each phone gets a limited number per hour. */
    private const MAX_CODES_PER_PHONE_PER_HOUR = 3;

    public function __construct(
        protected OtpServiceInterface $otpService
    ) {}

    public function showRequestForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('profile');
        }

        return view('auth.forgot_password', ['smsAvailable' => $this->smsAvailable()]);
    }

    /**
     * Text a reset code. The answer is the same for unknown numbers, so this page
     * cannot be used to find out who has an account.
     */
    public function sendCode(Request $request): RedirectResponse
    {
        if (!$this->smsAvailable()) {
            return redirect()->route('password.request');
        }

        $request->merge(['phone' => preg_replace('/[^\d+]/', '', (string) $request->input('phone'))]);

        $validated = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?998\d{9}$/'],
        ], [
            'phone.required' => 'Telefon raqamini kiriting.',
            'phone.regex' => "Telefon raqami +998XXXXXXXXX formatida bo'lishi kerak.",
        ]);

        $phone = '+' . ltrim($validated['phone'], '+');
        $user = $this->findUser($phone);
        $demoCode = null;

        if ($user && $user->is_active && $this->withinPhoneLimit($phone)) {
            try {
                $result = $this->otpService->send($user->phone, 'password_reset');
                $demoCode = $result['demo_code'] ?? null;
            } catch (\Throwable $e) {
                report($e);

                return back()->withInput()->withErrors(['phone' => "SMS yuborib bo'lmadi. Birozdan keyin qayta urinib ko'ring."]);
            }

            AuditService::log('password.forgot_requested', $user);
        }

        $request->session()->put('password_reset_phone', $phone);

        return redirect()->route('password.reset')->with('demo_code', $demoCode);
    }

    public function showResetForm(Request $request): View|RedirectResponse
    {
        if (!$this->smsAvailable() || !$request->session()->has('password_reset_phone')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset_password', ['phone' => $request->session()->get('password_reset_phone')]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $phone = $request->session()->get('password_reset_phone');
        if (!$this->smsAvailable() || !$phone) {
            return redirect()->route('password.request');
        }

        // Checked before the code, so a mistyped password does not burn the SMS code
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'code.required' => 'SMS kodini kiriting.',
            'code.digits' => "SMS kodi 6 ta raqamdan iborat.",
            'password.required' => 'Yangi parolni kiriting.',
            'password.min' => "Parol kamida 8 ta belgidan iborat bo'lishi kerak.",
            'password.confirmed' => 'Parollar mos kelmadi.',
        ]);

        $user = $this->findUser($phone);

        if (!$user || !$user->is_active || !$this->otpService->verify($user->phone, $validated['code'], 'password_reset')) {
            return back()->withErrors(['code' => "Kod noto'g'ri yoki muddati tugagan."]);
        }

        DB::transaction(function () use ($user, $validated) {
            $user->password = $validated['password'];
            $user->setRememberToken(Str::random(60));
            $user->save();

            // Whoever may have got into the account is thrown out everywhere
            $user->tokens()->delete();
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->where('user_id', $user->id)->delete();
            }

            AuditService::log('password.reset_completed', $user, null, null, $user);
        });

        $request->session()->forget('password_reset_phone');

        return redirect()->route('login')->with('success', 'Parolingiz yangilandi. Yangi parol bilan kiring.');
    }

    /**
     * On the live site a code can only reach the customer through a connected SMS provider.
     */
    protected function smsAvailable(): bool
    {
        if (!app()->environment('production')) {
            return true;
        }

        return config('services.sms.driver') === 'eskiz'
            && filled(config('services.sms.eskiz.email'))
            && filled(config('services.sms.eskiz.password'));
    }

    protected function findUser(string $phone): ?User
    {
        return User::whereIn('phone', [$phone, ltrim($phone, '+')])->first();
    }

    protected function withinPhoneLimit(string $phone): bool
    {
        $key = 'password-reset-sms:' . $phone;

        if (RateLimiter::tooManyAttempts($key, self::MAX_CODES_PER_PHONE_PER_HOUR)) {
            return false;
        }

        RateLimiter::hit($key, 3600);

        return true;
    }
}
