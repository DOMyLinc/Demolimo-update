<?php

namespace App\Http\Controllers;

use App\Models\CmsPage;
use App\Models\Language;
use Illuminate\Http\Request;

class PageController extends Controller
{
    /**
     * Show CMS page
     */
    public function show($slug)
    {
        $page = CmsPage::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Get translated version if available
        $page = $page->getTranslation(app()->getLocale());

        return view('pages.show', compact('page'));
    }

    /**
     * Change language
     */
    public function changeLanguage(Request $request, $code)
    {
        $language = Language::where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        // Store in session
        session(['locale' => $code]);

        // Set app locale
        app()->setLocale($code);

        return back();
    }
}
