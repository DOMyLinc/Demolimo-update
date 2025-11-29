<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GenreController extends Controller
{
    public function index()
    {
        $genres = Genre::withCount('tracks')
            ->orderBy('sort_order')
            ->paginate(20);

        $stats = [
            'total_genres' => Genre::count(),
            'active_genres' => Genre::where('is_active', true)->count(),
            'total_tracks' => Genre::sum('tracks_count'),
        ];

        return view('admin.genres.index', compact('genres', 'stats'));
    }

    public function create()
    {
        return view('admin.genres.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:genres,name',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['sort_order'] = Genre::max('sort_order') + 1;

        Genre::create($validated);

        return redirect()->route('admin.genres.index')
            ->with('success', 'Genre created successfully!');
    }

    public function edit(Genre $genre)
    {
        return view('admin.genres.edit', compact('genre'));
    }

    public function update(Request $request, Genre $genre)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:genres,name,' . $genre->id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $genre->update($validated);

        return redirect()->route('admin.genres.index')
            ->with('success', 'Genre updated successfully!');
    }

    public function destroy(Genre $genre)
    {
        // Check if genre has tracks
        if ($genre->tracks()->count() > 0) {
            return back()->with('error', 'Cannot delete genre with existing tracks. Please reassign tracks first.');
        }

        $genre->delete();

        return redirect()->route('admin.genres.index')
            ->with('success', 'Genre deleted successfully!');
    }

    public function toggle(Genre $genre)
    {
        $genre->update(['is_active' => !$genre->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $genre->is_active,
            'message' => $genre->is_active ? 'Genre activated' : 'Genre deactivated',
        ]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'genres' => 'required|array',
            'genres.*' => 'exists:genres,id',
        ]);

        foreach ($validated['genres'] as $order => $genreId) {
            Genre::where('id', $genreId)->update(['sort_order' => $order + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Genres reordered successfully',
        ]);
    }

    public function updateCounts()
    {
        $genres = Genre::all();

        foreach ($genres as $genre) {
            $genre->updateTracksCount();
        }

        return redirect()->route('admin.genres.index')
            ->with('success', 'Genre track counts updated successfully!');
    }
}
