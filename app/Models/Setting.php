<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * In-memory cache for all settings during the lifecycle of the request
     *
     * @var array<string, ?string>|null
     */
    protected static ?array $runtimeCache = null;

    public static function get(string $key, $default = null): ?string
    {
        if (self::$runtimeCache === null) {
            try {
                self::$runtimeCache = self::pluck('value', 'key')->toArray();
            } catch (\Throwable) {
                return $default;
            }
        }

        return self::$runtimeCache[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        self::$runtimeCache = null;

        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
