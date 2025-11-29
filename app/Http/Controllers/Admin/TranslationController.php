<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TranslationKey;
use App\Models\TranslationValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TranslationController extends Controller
{
    /**
     * Display translation management interface
     */
    public function index(Request $request)
    {
        $group = $request->get('group');
        $locale = $request->get('locale', 'en');
        $search = $request->get('search');

        $query = TranslationKey::with([
            'translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }
        ]);

        // Filter by group
        if ($group) {
            $query->where('group', $group);
        }

        // Search
        if ($search) {
            $query->search($search);
        }

        $keys = $query->orderBy('group')->orderBy('key')->paginate(50);

        // Get all groups
        $groups = TranslationKey::distinct()->pluck('group')->sort();

        // Available locales
        $locales = [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
            'ja' => 'Japanese',
            'zh' => 'Chinese',
            'ar' => 'Arabic',
            'hi' => 'Hindi',
        ];

        return view('admin.translations.index', compact('keys', 'groups', 'locales', 'locale', 'group', 'search'));
    }

    /**
     * Update a single translation
     */
    public function update(Request $request, TranslationKey $translationKey)
    {
        $validated = $request->validate([
            'locale' => 'required|string|max:5',
            'value' => 'required|string',
        ]);

        TranslationValue::updateOrCreate(
            [
                'translation_key_id' => $translationKey->id,
                'locale' => $validated['locale'],
            ],
            [
                'value' => $validated['value'],
            ]
        );

        // Clear cache
        clear_translation_cache($validated['locale']);

        return back()->with('success', 'Translation updated successfully!');
    }

    /**
     * Bulk update translations
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|string|max:5',
            'translations' => 'required|array',
            'translations.*.key_id' => 'required|exists:translation_keys,id',
            'translations.*.value' => 'required|string',
        ]);

        foreach ($validated['translations'] as $translation) {
            TranslationValue::updateOrCreate(
                [
                    'translation_key_id' => $translation['key_id'],
                    'locale' => $validated['locale'],
                ],
                [
                    'value' => $translation['value'],
                ]
            );
        }

        clear_translation_cache($validated['locale']);

        return back()->with('success', count($validated['translations']) . ' translations updated successfully!');
    }

    /**
     * Export translations as JSON
     */
    public function export(Request $request)
    {
        $locale = $request->get('locale', 'en');

        $translations = TranslationKey::with([
            'translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }
        ])->get();

        $data = [];
        foreach ($translations as $key) {
            $translation = $key->translations->first();
            if (!isset($data[$key->group])) {
                $data[$key->group] = [];
            }
            $data[$key->group][$key->key] = $translation ? $translation->value : '';
        }

        $filename = "translations_{$locale}_" . date('Y-m-d') . ".json";

        return response()->json($data, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ], JSON_PRETTY_PRINT);
    }

    /**
     * Import translations from JSON
     */
    public function import(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|string|max:5',
            'file' => 'required|file|mimes:json',
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($content, true);

        if (!$data) {
            return back()->with('error', 'Invalid JSON file!');
        }

        $count = 0;
        foreach ($data as $group => $translations) {
            foreach ($translations as $key => $value) {
                $translationKey = TranslationKey::firstOrCreate(
                    ['key' => $key],
                    ['group' => $group]
                );

                TranslationValue::updateOrCreate(
                    [
                        'translation_key_id' => $translationKey->id,
                        'locale' => $validated['locale'],
                    ],
                    [
                        'value' => $value,
                    ]
                );

                $count++;
            }
        }

        clear_translation_cache($validated['locale']);

        return back()->with('success', "{$count} translations imported successfully!");
    }

    /**
     * Clear all translation caches
     */
    public function clearCache()
    {
        clear_translation_cache();

        return back()->with('success', 'Translation cache cleared successfully!');
    }
}
