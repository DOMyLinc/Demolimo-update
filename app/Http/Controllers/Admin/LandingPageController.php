<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LandingPageManager;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    protected $manager;

    public function __construct(LandingPageManager $manager)
    {
        $this->manager = $manager;
    }

    public function index()
    {
        $blocks = $this->manager->getBlocks();
        return view('admin.landing.index', compact('blocks'));
    }

    public function update(Request $request, $key)
    {
        $data = $request->validate([
            'content' => 'required|array',
            'is_visible' => 'boolean',
            'order' => 'integer',
        ]);

        $this->manager->updateBlock($key, $data);

        return back()->with('success', 'Block updated successfully');
    }
}
