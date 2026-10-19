<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    /**
     * Keys that should never be stored in plaintext in audit logs.
     */
    protected static array $sensitiveKeys = [
        'password',
        'password_confirmation',
        'token',
        'plainTextToken',
        'access_token',
        'secret',
        'code',
        'otp',
        'remember_token',
    ];

    /**
     * Record an audit event.
     */
    public static function log(
        string $action,
        ?Model $entity = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?User $actor = null
    ): AuditLog {
        $actorUser = $actor ?? auth()->user();
        $ip = request()->ip();
        $userAgent = request()->userAgent();

        return AuditLog::create([
            'user_id' => $actorUser?->id,
            'action' => $action,
            'entity_type' => $entity ? get_class($entity) : null,
            'entity_id' => $entity?->getKey(),
            'old_values' => $oldValues ? self::sanitize($oldValues) : null,
            'new_values' => $newValues ? self::sanitize($newValues) : null,
            'ip_address' => $ip,
            'user_agent' => $userAgent ? substr($userAgent, 0, 255) : null,
            'created_at' => now(),
        ]);
    }

    /**
     * Sanitize array to strip or mask secret information.
     */
    public static function sanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            $lowerKey = strtolower($key);

            $isSensitive = false;
            foreach (self::$sensitiveKeys as $sensitive) {
                if (str_contains($lowerKey, strtolower($sensitive))) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $sanitized[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $sanitized[$key] = self::sanitize($value);
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }
}
