<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeaturedContent;
use App\Models\Track;
use App\Models\Album;
use App\Models\User;
use Illuminate\Http\Request;

class FeaturedContentController extends Controller
{
    public function index()
    {
        $featured = FeaturedContent::with('featurable')->orderBy('position')->paginate(20);
        return view('admin.featured.index', compact('featured'));
    }

    public function create()
    {
        return view('admin.featured.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:track,album,artist',
            'id' => 'required|integer',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $type = match ($request->type) {
            'track' => Track::class,
            'album' => Album::class,
            'artist' => User::class,
        };

        // Get the highest position and add 1
        $position = FeaturedContent::max('position') + 1;

        FeaturedContent::create([
            'featurable_type' => $type,
            'featurable_id' => $request->id,
            'position' => $position,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('admin.featured.index')
            ->with('success', 'Content featured successfully.');
    }

    public function destroy(FeaturedContent $featured)
    {
        $featured->delete();

        return redirect()->route('admin.featured.index')
            ->with('success', 'Featured content removed.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:featured_content,id',
            'items.*.position' => 'required|integer',
        ]);

        foreach ($request->items as $item) {
            FeaturedContent::where('id', $item['id'])
                ->update(['position' => $item['position']]);
        }

        return back()->with('success', 'Order updated successfully.');
    }
}
