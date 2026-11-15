<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    use HasFactory;

    protected $table = 'platform_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
        'description',
    ];

    /**
     * Get a setting by key with a default value fallback.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set or update a setting by key.
     */
    public static function set(string $key, mixed $value, ?string $group = null, ?string $description = null): static
    {
        $payload = ['value' => (string) $value];
        if ($group !== null) {
            $payload['group'] = $group;
        }
        if ($description !== null) {
            $payload['description'] = $description;
        }

        return static::updateOrCreate(['key' => $key], $payload);
    }
}
