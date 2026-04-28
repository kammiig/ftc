<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    public static function setValue(string $key, mixed $value, string $type = 'text'): void
    {
        self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => is_array($value) ? implode(',', $value) : $value, 'type' => $type]
        );

        Cache::forget("setting.{$key}");
    }
}
