<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_enabled',
        'category',
        'config',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'config' => 'array',
    ];

    // Feature categories
    const CATEGORY_CORE = 'core';
    const CATEGORY_SOCIAL = 'social';
    const CATEGORY_MONETIZATION = 'monetization';
    const CATEGORY_CONTENT = 'content';
    const CATEGORY_ANALYTICS = 'analytics';
    const CATEGORY_MARKETING = 'marketing';

    public static function isEnabled($key)
    {
        $feature = static::where('key', $key)->first();
        return $feature ? $feature->is_enabled : false;
    }

    public static function enable($key)
    {
        return static::where('key', $key)->update(['is_enabled' => true]);
    }

    public static function disable($key)
    {
        return static::where('key', $key)->update(['is_enabled' => false]);
    }

    public static function getByCategory($category)
    {
        return static::where('category', $category)->get();
    }
}
