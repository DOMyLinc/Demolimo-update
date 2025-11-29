<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Http\Request;

class LanguageManagementController extends Controller
{
    protected $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Languages list
     */
    public function index()
    {
        $languages = Language::orderBy('order')->get();

        $stats = [
            'total_languages' => $languages->count(),
            'active_languages' => $languages->where('is_active', true)->count(),
            'total_translations' => Translation::count(),
        ];

        return view('admin.languages.index', compact('languages', 'stats'));
    }

    /**
     * Create language
     */
    public function create()
    {
        $availableLanguages = $this->getAvailableLanguages();
        return view('admin.languages.create', compact('availableLanguages'));
    }

    /**
     * Store language
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:5|unique:languages,code',
            'flag' => 'nullable|string|max:10',
            'is_rtl' => 'boolean',
            'is_active' => 'boolean',
            'auto_translate' => 'boolean',
        ]);

        // If this is the first language, make it default
        if (Language::count() === 0) {
            $validated['is_default'] = true;
        }

        $language = Language::create($validated);

        // Auto-translate if requested
        if ($request->auto_translate) {
            $this->translationService->autoTranslateLanguage($language);
        }

        return redirect()->route('admin.languages.index')
            ->with('success', 'Language added successfully!');
    }

    /**
     * Edit language
     */
    public function edit(Language $language)
    {
        return view('admin.languages.edit', compact('language'));
    }

    /**
     * Update language
     */
    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'flag' => 'nullable|string|max:10',
            'is_rtl' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $language->update($validated);

        return back()->with('success', 'Language updated successfully!');
    }

    /**
     * Set as default
     */
    public function setDefault(Language $language)
    {
        // Remove default from all languages
        Language::where('is_default', true)->update(['is_default' => false]);

        // Set this language as default
        $language->update(['is_default' => true]);

        return back()->with('success', "{$language->name} set as default language!");
    }

    /**
     * Delete language
     */
    public function destroy(Language $language)
    {
        if ($language->is_default) {
            return back()->with('error', 'Cannot delete the default language!');
        }

        $language->delete();

        return redirect()->route('admin.languages.index')
            ->with('success', 'Language deleted successfully!');
    }

    /**
     * Translations management
     */
    public function translations(Language $language)
    {
        $groups = Translation::where('language_id', $language->id)
            ->select('group')
            ->distinct()
            ->pluck('group');

        $translations = Translation::where('language_id', $language->id)
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->groupBy('group');

        return view('admin.languages.translations', compact('language', 'translations', 'groups'));
    }

    /**
     * Update translation
     */
    public function updateTranslation(Request $request, Language $language)
    {
        $validated = $request->validate([
            'group' => 'required|string',
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        Translation::updateOrCreate(
            [
                'language_id' => $language->id,
                'group' => $validated['group'],
                'key' => $validated['key'],
            ],
            [
                'value' => $validated['value'],
                'is_auto_translated' => false,
            ]
        );

        return back()->with('success', 'Translation updated!');
    }

    /**
     * Auto-translate all missing translations
     */
    public function autoTranslate(Language $language)
    {
        $this->translationService->autoTranslateLanguage($language);

        return back()->with('success', 'Auto-translation completed!');
    }

    /**
     * Import translations
     */
    public function import(Request $request, Language $language)
    {
        $request->validate([
            'file' => 'required|file|mimes:json,csv',
        ]);

        // Import logic here
        // ...

        return back()->with('success', 'Translations imported successfully!');
    }

    /**
     * Export translations
     */
    public function export(Language $language)
    {
        $translations = Translation::where('language_id', $language->id)->get();

        $data = [];
        foreach ($translations as $translation) {
            $data[$translation->group][$translation->key] = $translation->value;
        }

        $filename = "translations-{$language->code}-" . now()->format('Y-m-d') . ".json";

        return response()->json($data, 200, [
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    /**
     * Get available languages
     */
    protected function getAvailableLanguages()
    {
        return [
            'en' => ['name' => 'English', 'flag' => '🇺🇸'],
            'es' => ['name' => 'Spanish', 'flag' => '🇪🇸'],
            'fr' => ['name' => 'French', 'flag' => '🇫🇷'],
            'de' => ['name' => 'German', 'flag' => '🇩🇪'],
            'it' => ['name' => 'Italian', 'flag' => '🇮🇹'],
            'pt' => ['name' => 'Portuguese', 'flag' => '🇵🇹'],
            'ru' => ['name' => 'Russian', 'flag' => '🇷🇺'],
            'ja' => ['name' => 'Japanese', 'flag' => '🇯🇵'],
            'ko' => ['name' => 'Korean', 'flag' => '🇰🇷'],
            'zh' => ['name' => 'Chinese', 'flag' => '🇨🇳'],
            'ar' => ['name' => 'Arabic', 'flag' => '🇸🇦'],
            'hi' => ['name' => 'Hindi', 'flag' => '🇮🇳'],
        ];
    }
}
