<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::withCount('translations')->get();
        return view('admin.languages.index', compact('languages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'code' => 'required|string|max:5|unique:languages',
            'flag' => 'nullable|string|max:10',
            'is_rtl' => 'boolean',
        ]);

        $language = Language::create($validated);

        // Auto-populate translations from default language (English)
        $defaultLang = Language::where('is_default', true)->first();
        if ($defaultLang && $defaultLang->id !== $language->id) {
            $translations = $defaultLang->translations;
            foreach ($translations as $translation) {
                Translation::create([
                    'language_id' => $language->id,
                    'group' => $translation->group,
                    'key' => $translation->key,
                    'value' => $translation->value, // Start with English value
                ]);
            }
        }

        return back()->with('success', 'Language created successfully!');
    }

    public function translations(Language $language)
    {
        $translations = $language->translations()->orderBy('group')->orderBy('key')->paginate(50);
        return view('admin.languages.translations', compact('language', 'translations'));
    }

    public function updateTranslation(Request $request, Translation $translation)
    {
        $request->validate(['value' => 'required|string']);
        $translation->update(['value' => $request->value]);

        // Clear translation cache
        Cache::forget("translations.{$translation->language->code}");

        return response()->json(['success' => true]);
    }

    public function setDefault(Language $language)
    {
        Language::where('is_default', true)->update(['is_default' => false]);
        $language->update(['is_default' => true]);
        return back()->with('success', 'Default language updated!');
    }

    public function toggleActive(Language $language)
    {
        if ($language->is_default) {
            return back()->with('error', 'Cannot deactivate default language.');
        }
        $language->update(['is_active' => !$language->is_active]);
        return back()->with('success', 'Language status updated!');
    }
}
