<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\Language;
use App\Models\FooterSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsController extends Controller
{
    /**
     * Pages list
     */
    public function pages()
    {
        $pages = CmsPage::latest()->paginate(20);

        $stats = [
            'total_pages' => CmsPage::count(),
            'published' => CmsPage::where('status', 'published')->count(),
            'draft' => CmsPage::where('status', 'draft')->count(),
        ];

        return view('admin.cms.pages', compact('pages', 'stats'));
    }

    /**
     * Create page
     */
    public function createPage()
    {
        $languages = Language::where('is_active', true)->get();
        return view('admin.cms.create-page', compact('languages'));
    }

    /**
     * Store page
     */
    public function storePage(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:cms_pages,slug',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'status' => 'required|in:draft,published',
            'show_in_footer' => 'boolean',
            'show_in_header' => 'boolean',
            'footer_order' => 'integer|min:0',
            'header_order' => 'integer|min:0',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $page = CmsPage::create($validated);

        return redirect()->route('admin.cms.pages')
            ->with('success', 'Page created successfully!');
    }

    /**
     * Edit page
     */
    public function editPage(CmsPage $page)
    {
        $languages = Language::where('is_active', true)->get();
        $page->load('translations');

        return view('admin.cms.edit-page', compact('page', 'languages'));
    }

    /**
     * Update page
     */
    public function updatePage(Request $request, CmsPage $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:cms_pages,slug,' . $page->id,
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'status' => 'required|in:draft,published',
            'show_in_footer' => 'boolean',
            'show_in_header' => 'boolean',
            'footer_order' => 'integer|min:0',
            'header_order' => 'integer|min:0',
        ]);

        $page->update($validated);

        return back()->with('success', 'Page updated successfully!');
    }

    /**
     * Delete page
     */
    public function deletePage(CmsPage $page)
    {
        $page->delete();

        return redirect()->route('admin.cms.pages')
            ->with('success', 'Page deleted successfully!');
    }

    /**
     * Translate page
     */
    public function translatePage(Request $request, CmsPage $page)
    {
        $validated = $request->validate([
            'language_id' => 'required|exists:languages,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
        ]);

        $page->translations()->updateOrCreate(
            ['language_id' => $validated['language_id']],
            $validated
        );

        return back()->with('success', 'Translation saved!');
    }

    /**
     * Footer settings
     */
    public function footerSettings()
    {
        $settings = FooterSetting::all()->pluck('value', 'key');
        $pages = CmsPage::where('show_in_footer', true)
            ->orderBy('footer_order')
            ->get();

        return view('admin.cms.footer-settings', compact('settings', 'pages'));
    }

    /**
     * Update footer settings
     */
    public function updateFooterSettings(Request $request)
    {
        $validated = $request->validate([
            'copyright_text' => 'required|string',
            'footer_description' => 'nullable|string',
            'social_facebook' => 'nullable|url',
            'social_twitter' => 'nullable|url',
            'social_instagram' => 'nullable|url',
            'social_youtube' => 'nullable|url',
            'social_linkedin' => 'nullable|url',
            'social_tiktok' => 'nullable|url',
        ]);

        foreach ($validated as $key => $value) {
            FooterSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return back()->with('success', 'Footer settings updated!');
    }
}
