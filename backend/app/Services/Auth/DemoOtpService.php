<?php

namespace App\Services\Auth;

use App\Models\OtpCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class DemoOtpService implements OtpServiceInterface
{
    /**
     * Send or generate an OTP code.
     */
    public function send(string $phone, string $type = 'login'): array
    {
        // Demo OTP service must NEVER be used in production environment
        if (app()->environment('production')) {
            throw new RuntimeException("Xavfsizlik xatosi: Demo OTP servisi production muhitida ishlatilishi qat'iyan taqiqlangan. Haqiqiy SMS gateway provayderini sozlang.");
        }

        // In local or test environments, default deterministic code is '123456'
        $isTestOrLocal = app()->environment('local', 'testing');
        $code = $isTestOrLocal ? '123456' : (string) random_int(100000, 999999);

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

        Log::info("OTP generated for {$phone}: {$code} (type: {$type})");

        return [
            'success' => true,
            'message' => 'Tasdiqlash kodi yuborildi',
            'demo_code' => $isTestOrLocal ? $code : null,
        ];
    }

    /**
     * Verify the OTP code.
     */
    public function verify(string $phone, string $code, string $type = 'login'): bool
    {
        if (app()->environment('production')) {
            throw new RuntimeException("Xavfsizlik xatosi: Demo OTP servisi production muhitida ishlatilishi qat'iyan taqiqlangan.");
        }

        $otp = OtpCode::where('phone', $phone)
            ->where('type', $type)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();

        // Reject if no unused code found (also prevents replay of used codes)
        if (!$otp) {
            return false;
        }

        // Reject expired codes
        if ($otp->isExpired()) {
            return false;
        }

        // Reject brute-force attempts if threshold exceeded (max 5)
        if ($otp->attempts >= 5) {
            return false;
        }

        $otp->increment('attempts');

        if ($otp->code !== $code) {
            return false;
        }

        // Mark as used immediately to prevent replay
        $otp->update(['used_at' => Carbon::now()]);

        return true;
    }
}
