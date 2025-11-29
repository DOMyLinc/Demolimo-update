<?php

namespace App\Services;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    /**
     * Auto-translate all missing translations for a language
     */
    public function autoTranslateLanguage(Language $language)
    {
        // Get default language
        $defaultLanguage = Language::where('is_default', true)->first();

        if (!$defaultLanguage || $defaultLanguage->id === $language->id) {
            return;
        }

        // Get all translations from default language
        $defaultTranslations = Translation::where('language_id', $defaultLanguage->id)->get();

        foreach ($defaultTranslations as $defaultTranslation) {
            // Check if translation already exists
            $exists = Translation::where('language_id', $language->id)
                ->where('group', $defaultTranslation->group)
                ->where('key', $defaultTranslation->key)
                ->exists();

            if (!$exists) {
                // Translate
                $translatedValue = $this->translate(
                    $defaultTranslation->value,
                    $defaultLanguage->code,
                    $language->code
                );

                Translation::create([
                    'language_id' => $language->id,
                    'group' => $defaultTranslation->group,
                    'key' => $defaultTranslation->key,
                    'value' => $translatedValue,
                    'is_auto_translated' => true,
                ]);
            }
        }
    }

    /**
     * Translate text using Google Translate API (free alternative: LibreTranslate)
     */
    public function translate($text, $fromLang, $toLang)
    {
        // Use cache to avoid repeated translations
        $cacheKey = "translation.{$fromLang}.{$toLang}." . md5($text);

        return Cache::remember($cacheKey, 86400, function () use ($text, $fromLang, $toLang) {
            try {
                // Option 1: Use LibreTranslate (free, open-source)
                // Install: https://github.com/LibreTranslate/LibreTranslate
                $response = Http::post('https://libretranslate.com/translate', [
                    'q' => $text,
                    'source' => $fromLang,
                    'target' => $toLang,
                    'format' => 'text',
                ]);

                if ($response->successful()) {
                    return $response->json()['translatedText'];
                }

                // Option 2: Use Google Translate (requires API key)
                // Uncomment if you have Google Translate API key
                /*
                $response = Http::get('https://translation.googleapis.com/language/translate/v2', [
                    'key' => config('services.google_translate.key'),
                    'q' => $text,
                    'source' => $fromLang,
                    'target' => $toLang,
                ]);

                if ($response->successful()) {
                    return $response->json()['data']['translations'][0]['translatedText'];
                }
                */

                // Fallback: Return original text
                return $text;
            } catch (\Exception $e) {
                \Log::error('Translation failed: ' . $e->getMessage());
                return $text;
            }
        });
    }

    /**
     * Get translation
     */
    public function get($key, $group = 'general', $languageCode = null)
    {
        if (!$languageCode) {
            $languageCode = app()->getLocale();
        }

        $language = Language::where('code', $languageCode)->first();

        if (!$language) {
            return $key;
        }

        $translation = Translation::where('language_id', $language->id)
            ->where('group', $group)
            ->where('key', $key)
            ->first();

        return $translation ? $translation->value : $key;
    }

    /**
     * Set translation
     */
    public function set($key, $value, $group = 'general', $languageCode = null)
    {
        if (!$languageCode) {
            $languageCode = app()->getLocale();
        }

        $language = Language::where('code', $languageCode)->first();

        if (!$language) {
            return false;
        }

        Translation::updateOrCreate(
            [
                'language_id' => $language->id,
                'group' => $group,
                'key' => $key,
            ],
            [
                'value' => $value,
                'is_auto_translated' => false,
            ]
        );

        return true;
    }

    /**
     * Get all translations for a group
     */
    public function getGroup($group, $languageCode = null)
    {
        if (!$languageCode) {
            $languageCode = app()->getLocale();
        }

        $language = Language::where('code', $languageCode)->first();

        if (!$language) {
            return [];
        }

        return Translation::where('language_id', $language->id)
            ->where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }
}
