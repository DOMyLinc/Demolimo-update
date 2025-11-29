<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\FlashAlbum;
use App\Models\FlashDriveTemplate;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FlashAlbumController extends Controller
{
    public function index()
    {
        $albums = auth()->user()->flashAlbums()
            ->withCount('orders')
            ->latest()
            ->paginate(12);

        $quota = auth()->user()->getFeatureQuota('flash_album');
        $isBeta = true; // Flash Albums are in BETA

        return view('user.flash-albums.index', compact('albums', 'quota', 'isBeta'));
    }

    public function create()
    {
        // Check if user can create more flash albums
        if (!auth()->user()->canCreate('flash_album')) {
            $quota = auth()->user()->getFeatureQuota('flash_album');

            if ($quota === 0) {
                return redirect()->route('flash-albums.index')
                    ->with('error', 'You have reached your Flash Album limit. Upgrade to Pro for more!');
            }
        }

        $templates = FlashDriveTemplate::where('is_active', true)->get();
        $tracks = auth()->user()->tracks()->where('is_approved', true)->get();
        $quota = auth()->user()->getFeatureQuota('flash_album');

        return view('user.flash-albums.create', compact('templates', 'tracks', 'quota'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cover_image' => 'required|image|max:5120', // 5MB
            'label_design' => 'nullable|image|max:5120',
            'packaging_design' => 'nullable|image|max:5120',
            'flash_drive_capacity' => 'required|in:8GB,16GB,32GB,64GB',
            'flash_drive_type' => 'required|in:standard,premium,custom',
            'base_price' => 'required|numeric|min:0',
            'track_ids' => 'required|array|min:1',
            'track_ids.*' => 'exists:tracks,id',
            'bonus_content' => 'nullable|array',
            'include_digital_copy' => 'boolean',
            'stock_quantity' => 'required|integer|min:0',
            'is_pre_order' => 'boolean',
            'release_date' => 'nullable|date',
            'free_shipping' => 'boolean',
            'shipping_cost' => 'nullable|numeric|min:0',
            'shipping_regions' => 'nullable|array',
            'color_scheme' => 'nullable|array',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_available'] = false; // Pending approval

        // Calculate production cost based on capacity and type
        $validated['production_cost'] = $this->calculateProductionCost(
            $validated['flash_drive_capacity'],
            $validated['flash_drive_type']
        );

        // Handle image uploads
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('flash-albums/covers', 'public');
        }

        if ($request->hasFile('label_design')) {
            $validated['label_design'] = $request->file('label_design')->store('flash-albums/labels', 'public');
        }

        if ($request->hasFile('packaging_design')) {
            $validated['packaging_design'] = $request->file('packaging_design')->store('flash-albums/packaging', 'public');
        }

        // Ensure user owns all selected tracks
        $userTrackIds = auth()->user()->tracks()->pluck('id')->toArray();
        $validated['track_ids'] = array_intersect($validated['track_ids'], $userTrackIds);

        $album = FlashAlbum::create($validated);

        return redirect()->route('flash-albums.show', $album)
            ->with('success', 'Flash Album (BETA) created! It will be available after admin approval.');
    }

    public function show(FlashAlbum $flashAlbum)
    {
        // Ensure user owns this album
        if ($flashAlbum->user_id !== auth()->id()) {
            abort(403);
        }

        $album = $flashAlbum->load([
            'orders' => function ($query) {
                $query->latest();
            }
        ]);

        $stats = [
            'total_orders' => $album->orders->count(),
            'total_revenue' => $album->total_revenue,
            'artist_earnings' => $album->artist_earnings,
            'units_sold' => $album->units_sold,
            'stock_remaining' => $album->stock_quantity,
        ];

        return view('user.flash-albums.show', compact('album', 'stats'));
    }

    public function edit(FlashAlbum $flashAlbum)
    {
        // Ensure user owns this album
        if ($flashAlbum->user_id !== auth()->id()) {
            abort(403);
        }

        $templates = FlashDriveTemplate::where('is_active', true)->get();
        $tracks = auth()->user()->tracks()->where('is_approved', true)->get();

        return view('user.flash-albums.edit', compact('flashAlbum', 'templates', 'tracks'));
    }

    public function update(Request $request, FlashAlbum $flashAlbum)
    {
        // Ensure user owns this album
        if ($flashAlbum->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cover_image' => 'nullable|image|max:5120',
            'label_design' => 'nullable|image|max:5120',
            'packaging_design' => 'nullable|image|max:5120',
            'base_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_pre_order' => 'boolean',
            'release_date' => 'nullable|date',
            'free_shipping' => 'boolean',
            'shipping_cost' => 'nullable|numeric|min:0',
            'shipping_regions' => 'nullable|array',
        ]);

        // Handle image uploads
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('flash-albums/covers', 'public');
        }

        if ($request->hasFile('label_design')) {
            $validated['label_design'] = $request->file('label_design')->store('flash-albums/labels', 'public');
        }

        if ($request->hasFile('packaging_design')) {
            $validated['packaging_design'] = $request->file('packaging_design')->store('flash-albums/packaging', 'public');
        }

        $flashAlbum->update($validated);

        return back()->with('success', 'Flash Album updated successfully!');
    }

    public function destroy(FlashAlbum $flashAlbum)
    {
        // Ensure user owns this album
        if ($flashAlbum->user_id !== auth()->id()) {
            abort(403);
        }

        // Don't allow deletion if there are pending orders
        if ($flashAlbum->orders()->whereIn('status', ['pending', 'processing', 'shipped'])->exists()) {
            return back()->with('error', 'Cannot delete album with pending orders.');
        }

        $flashAlbum->delete();

        return redirect()->route('flash-albums.index')
            ->with('success', 'Flash Album deleted successfully!');
    }

    /**
     * Flash Album Builder - Auto-generate from existing album
     */
    public function buildFromAlbum(Request $request)
    {
        $request->validate([
            'album_id' => 'required|exists:albums,id',
        ]);

        $album = \App\Models\Album::findOrFail($request->album_id);

        // Ensure user owns the album
        if ($album->user_id !== auth()->id()) {
            abort(403);
        }

        // Get all tracks from the album
        $trackIds = $album->tracks()->pluck('tracks.id')->toArray();

        $templates = FlashDriveTemplate::where('is_active', true)->get();

        return view('user.flash-albums.build-from-album', compact('album', 'trackIds', 'templates'));
    }

    /**
     * Calculate production cost based on capacity and type
     */
    protected function calculateProductionCost(string $capacity, string $type): float
    {
        $baseCosts = [
            '8GB' => ['standard' => 5.00, 'premium' => 8.00, 'custom' => 12.00],
            '16GB' => ['standard' => 7.00, 'premium' => 10.00, 'custom' => 15.00],
            '32GB' => ['standard' => 10.00, 'premium' => 14.00, 'custom' => 20.00],
            '64GB' => ['standard' => 15.00, 'premium' => 20.00, 'custom' => 28.00],
        ];

        return $baseCosts[$capacity][$type] ?? 10.00;
    }

    /**
     * Preview flash album design
     */
    public function preview(FlashAlbum $flashAlbum)
    {
        // Ensure user owns this album
        if ($flashAlbum->user_id !== auth()->id()) {
            abort(403);
        }

        return view('user.flash-albums.preview', compact('flashAlbum'));
    }
}
