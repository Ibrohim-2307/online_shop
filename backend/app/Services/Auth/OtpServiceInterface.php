<?php

namespace App\Services\Auth;

interface OtpServiceInterface
{
    /**
     * Send or generate an OTP code for the given phone number.
     *
     * @param string $phone
     * @param string $type ('login', 'register', 'password_reset')
     * @return array{success: bool, message: string, demo_code?: string}
     */
    public function send(string $phone, string $type = 'login'): array;

    /**
     * Verify the OTP code for the given phone number.
     *
     * @param string $phone
     * @param string $code
     * @param string $type
     * @return bool
     */
    public function verify(string $phone, string $code, string $type = 'login'): bool;
}
