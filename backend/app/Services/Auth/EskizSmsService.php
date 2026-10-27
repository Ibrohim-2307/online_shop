<?php

namespace App\Services\Auth;

use App\Models\OtpCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class EskizSmsService implements OtpServiceInterface
{
    protected ?string $email;
    protected ?string $password;
    protected string $from;

    public function __construct()
    {
        $this->email = config('services.sms.eskiz.email');
        $this->password = config('services.sms.eskiz.password');
        $this->from = config('services.sms.eskiz.from', '4546');
    }

    public function isConfigured(): bool
    {
        return !empty($this->email) && !empty($this->password);
    }

    /**
     * Send OTP via official Eskiz.uz SMS gateway.
     */
    public function send(string $phone, string $type = 'login'): array
    {
        $normalizedPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($normalizedPhone, '8') && strlen($normalizedPhone) === 11) {
            $normalizedPhone = '998' . substr($normalizedPhone, 1);
        }

        // Generate cryptographically secure 6-digit OTP
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Invalidate older unused codes for this phone & type
        OtpCode::where('phone', $phone)
            ->where('type', $type)
            ->whereNull('used_at')
            ->delete();

        // Save new OTP record with 5-minute TTL
        OtpCode::create([
            'phone' => $phone,
            'code' => $code,
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(5),
            'attempts' => 0,
        ]);

        if (!$this->isConfigured()) {
            if (app()->environment('production')) {
                throw new RuntimeException("Eskiz SMS provayderi sozlanmagan (ESKIZ_EMAIL va ESKIZ_PASSWORD talab qilinadi).");
            }
            // In local/test mode without credentials, log sanitized info
            Log::info("Eskiz SMS (Demo rejimida emulyatsiya): {$normalizedPhone} raqamiga OTP yuborildi.");
            return [
                'success' => true,
                'message' => 'Tasdiqlash kodi yuborildi (Test rejimida)',
                'demo_code' => app()->environment('local', 'testing') ? $code : null,
            ];
        }

        $token = $this->getAuthToken();
        $message = "Sizning tasdiqlash kodingiz: {$code}. Hech kimga bermang!";

        $response = Http::withToken($token)
            ->timeout(10)
            ->post('https://notify.eskiz.uz/api/message/sms/send', [
                'mobile_phone' => $normalizedPhone,
                'message' => $message,
                'from' => $this->from,
            ]);

        if (!$response->successful()) {
            Log::error("Eskiz SMS yuborishda xato: " . $response->body());
            throw new RuntimeException("SMS yuborishda xatolik yuz berdi. Iltimos, qaytadan urinib ko'ring.");
        }

        return [
            'success' => true,
            'message' => 'Tasdiqlash kodi telefoningizga yuborildi',
        ];
    }

    /**
     * Verify the OTP code.
     */
    public function verify(string $phone, string $code, string $type = 'login'): bool
    {
        $otp = OtpCode::where('phone', $phone)
            ->where('type', $type)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();

        if (!$otp) {
            return false;
        }

        if ($otp->isExpired()) {
            return false;
        }

        if ($otp->attempts >= 5) {
            return false;
        }

        $otp->increment('attempts');

        if (!hash_equals((string) $otp->code, (string) $code)) {
            return false;
        }

        $otp->update(['used_at' => Carbon::now()]);

        return true;
    }

    /**
     * Retrieve or refresh Eskiz API JWT token.
     */
    protected function getAuthToken(): string
    {
        return Cache::remember('eskiz_sms_jwt_token', 60 * 60 * 24 * 25, function () {
            $response = Http::timeout(10)->post('https://notify.eskiz.uz/api/auth/login', [
                'email' => $this->email,
                'password' => $this->password,
            ]);

            if (!$response->successful()) {
                throw new RuntimeException("Eskiz SMS tizimiga ulanib bo'lmadi: " . $response->body());
            }

            $data = $response->json();
            return $data['data']['token'] ?? throw new RuntimeException("Eskiz token topilmadi.");
        });
    }
}
