<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }

    public static function values(): array
    {
        return Cache::rememberForever('settings.all', function () {
            return static::query()->get()->mapWithKeys(function ($s) {
                return [$s->key => static::castValue($s->value, $s->type)];
            })->toArray();
        });
    }

    public static function get(string $key, $default = null)
    {
        return static::values()[$key] ?? $default;
    }

    public static function set(string $key, $value, string $type = 'string', string $group = 'general'): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? json_encode($value) : $value, 'type' => $type, 'group' => $group]
        );
    }

    protected static function castValue($value, string $type)
    {
        return match ($type) {
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'int' => (int) $value,
            'json' => json_decode((string) $value, true),
            default => $value,
        };
    }
}
