<?php

namespace App\Support;

/**
 * One place that decides what an Uzbek phone number looks like,
 * so registration, login, profile and checkout all accept and store it the same way.
 */
class Phone
{
    /** Stored form: +998 followed by 9 digits, e.g. +998901234567 */
    public const PATTERN = '/^\+998\d{9}$/';

    public const MESSAGE = "Telefon raqamini +998901234567 ko'rinishida kiriting.";

    /**
     * "90 123 45 67", "998901234567", "+998 (90) 123-45-67" -> "+998901234567".
     * Anything that is not a phone number is returned trimmed, so validation can reject it.
     */
    public static function normalize(?string $value): string
    {
        $raw = trim((string) $value);
        $digits = preg_replace('/\D+/', '', $raw);

        if ($digits === '' || preg_match('/[a-zA-Z@]/', $raw)) {
            return $raw;
        }
        if (strlen($digits) === 9) {
            return '+998' . $digits;
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '998')) {
            return '+' . $digits;
        }

        return $raw;
    }

    public static function looksLikePhone(?string $value): bool
    {
        return (bool) preg_match(self::PATTERN, self::normalize($value));
    }
}
