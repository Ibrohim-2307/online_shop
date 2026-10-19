<?php

namespace App\Services\Auth;

use App\Models\OtpCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DemoOtpService implements OtpServiceInterface
{
    /**
     * Send or generate an OTP code.
     */
    public function send(string $phone, string $type = 'login'): array
    {
        // In local or test environments, default deterministic code can be '123456' or random
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
        $otp = OtpCode::where('phone', $phone)
            ->where('type', $type)
            ->whereNull('used_at')
            ->orderByDesc('id')
            ->first();

        if (!$otp) {
            return false;
        }

        // Check expiration
        if ($otp->isExpired()) {
            return false;
        }

        // Check brute force attempts (max 5)
        if ($otp->attempts >= 5) {
            return false;
        }

        $otp->increment('attempts');

        if ($otp->code !== $code) {
            return false;
        }

        // Mark as used
        $otp->update(['used_at' => Carbon::now()]);

        return true;
    }
}
