<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Read a setting value by key, or $default if it isn't set (or is empty).
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = static::where('key', $key)->value('value');

        return $value !== null && $value !== '' ? $value : $default;
    }

    /**
     * Create or update a setting value by key.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
