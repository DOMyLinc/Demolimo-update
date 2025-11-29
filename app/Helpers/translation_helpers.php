<?php

use App\Models\TranslationKey;
use App\Models\TranslationValue;
use Illuminate\Support\Facades\Cache;

if (!function_exists('t')) {
    /**
     * Get translated text by key
     * 
     * @param string $key Translation key (e.g., 'profile', 'dashboard')
     * @param string|null $locale Locale (defaults to current)
     * @param string|null $default Default value if not found
     * @return string
     */
    function t(string $key, ?string $locale = null, ?string $default = null): string
    {
        static $cache = [];

        $locale = $locale ?? app()->getLocale();
        $cacheKey = "{$key}:{$locale}";

        // Check static cache
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        // Get from Laravel cache
        $translations = Cache::remember("translations:{$locale}", 3600, function () use ($locale) {
            return TranslationValue::where('locale', $locale)
                ->with('translationKey')
                ->get()
                ->mapWithKeys(function ($translation) {
                    return [$translation->translationKey->key => $translation->value];
                })
                ->toArray();
        });

        // Get value
        $value = $translations[$key] ?? $default ?? $key;
        $cache[$cacheKey] = $value;

        return $value;
    }
}

if (!function_exists('trans_group')) {
    /**
     * Get all translations for a group
     * 
     * @param string $group Group name
     * @param string|null $locale
     * @return array
     */
    function trans_group(string $group, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return Cache::remember("translations:{$locale}:{$group}", 3600, function () use ($group, $locale) {
            return TranslationKey::where('group', $group)
                ->with([
                    'translations' => function ($query) use ($locale) {
                        $query->where('locale', $locale);
                    }
                ])
                ->get()
                ->mapWithKeys(function ($key) {
                    $translation = $key->translations->first();
                    return [$key->key => $translation ? $translation->value : $key->key];
                })
                ->toArray();
        });
    }
}

if (!function_exists('clear_translation_cache')) {
    /**
     * Clear translation cache for a specific locale or all locales
     * 
     * @param string|null $locale
     * @return void
     */
    function clear_translation_cache(?string $locale = null): void
    {
        if ($locale) {
            Cache::forget("translations:{$locale}");

            // Clear group caches
            $groups = TranslationKey::distinct()->pluck('group');
            foreach ($groups as $group) {
                Cache::forget("translations:{$locale}:{$group}");
            }
        } else {
            // Clear all translation caches
            $locales = ['en', 'es', 'fr', 'de', 'it', 'pt', 'ja', 'zh', 'ar', 'hi'];
            foreach ($locales as $loc) {
                clear_translation_cache($loc);
            }
        }
    }
}
