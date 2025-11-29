<?php

namespace App\Services;

use Illuminate\Translation\FileLoader;
use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

class DatabaseTranslationLoader extends FileLoader
{
    /**
     * Load the messages for the given locale.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string|null  $namespace
     * @return array
     */
    public function load($locale, $group, $namespace = null)
    {
        // First, load lines from files
        $fileLines = parent::load($locale, $group, $namespace);

        // If we are looking for a namespaced group, we just return the file lines
        // as we currently don't support namespaced translations in the DB.
        if ($namespace !== null && $namespace !== '*') {
            return $fileLines;
        }

        // Cache the database results to avoid hitting the DB on every request
        $cacheKey = "translations.{$locale}.{$group}";

        // Use a try-catch block to handle cases where the DB might not be ready (e.g. during migration)
        try {
            $dbLines = Cache::rememberForever($cacheKey, function () use ($locale, $group) {
                return Translation::whereHas('language', function ($query) use ($locale) {
                    $query->where('code', $locale);
                })
                    ->where('group', $group)
                    ->pluck('value', 'key')
                    ->toArray();
            });
        } catch (\Exception $e) {
            // Fallback to just file lines if DB fails
            return $fileLines;
        }

        // Merge file lines with database lines (DB takes precedence)
        return array_replace_recursive($fileLines, $dbLines);
    }
}
