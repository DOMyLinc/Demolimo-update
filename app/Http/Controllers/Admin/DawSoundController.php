<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DawSound;
use App\Models\DawSoundCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DawSoundController extends Controller
{
    public function index(Request $request)
    {
        $query = DawSound::with('category');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Filter by format
        if ($request->filled('format')) {
            $query->where('format', $request->format);
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $sounds = $query->latest()->paginate(20);
        $categories = DawSoundCategory::active()->get();

        $stats = [
            'total_sounds' => DawSound::count(),
            'active_sounds' => DawSound::where('is_active', true)->count(),
            'total_downloads' => DawSound::sum('download_count'),
            'total_size' => DawSound::sum('file_size'),
        ];

        return view('admin.daw-sounds.index', compact('sounds', 'categories', 'stats'));
    }

    public function create()
    {
        $categories = DawSoundCategory::active()->get();
        return view('admin.daw-sounds.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:daw_sound_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:mp3,wav,ogg,flac|max:51200', // 50MB max
            'bpm' => 'nullable|integer|min:1|max:300',
            'key' => 'nullable|string|max:10',
            'tags' => 'nullable|string',
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = Str::slug($validated['name']) . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('daw-sounds', $filename, 'public');

            // Get file metadata
            $fileSize = $file->getSize();
            $format = $file->getClientOriginalExtension();

            // Get duration using getID3 or similar (simplified here)
            $duration = null; // You can implement getID3 library for accurate duration

            // Process tags
            $tags = $request->tags ? array_map('trim', explode(',', $request->tags)) : [];

            DawSound::create([
                'category_id' => $validated['category_id'],
                'name' => $validated['name'],
                'description' => $validated['description'],
                'file_path' => $path,
                'file_size' => $fileSize,
                'duration' => $duration,
                'format' => $format,
                'bpm' => $validated['bpm'] ?? null,
                'key' => $validated['key'] ?? null,
                'tags' => $tags,
                'is_active' => $request->has('is_active'),
                'is_premium' => $request->has('is_premium'),
            ]);

            return redirect()->route('admin.daw-sounds.index')
                ->with('success', 'Sound uploaded successfully!');
        }

        return back()->with('error', 'File upload failed.');
    }

    public function edit(DawSound $dawSound)
    {
        $categories = DawSoundCategory::active()->get();
        return view('admin.daw-sounds.edit', compact('dawSound', 'categories'));
    }

    public function update(Request $request, DawSound $dawSound)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:daw_sound_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'bpm' => 'nullable|integer|min:1|max:300',
            'key' => 'nullable|string|max:10',
            'tags' => 'nullable|string',
            'is_active' => 'boolean',
            'is_premium' => 'boolean',
        ]);

        // Process tags
        $tags = $request->tags ? array_map('trim', explode(',', $request->tags)) : [];

        $dawSound->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'bpm' => $validated['bpm'] ?? null,
            'key' => $validated['key'] ?? null,
            'tags' => $tags,
            'is_active' => $request->has('is_active'),
            'is_premium' => $request->has('is_premium'),
        ]);

        return redirect()->route('admin.daw-sounds.index')
            ->with('success', 'Sound updated successfully!');
    }

    public function destroy(DawSound $dawSound)
    {
        // Delete file from storage
        if (Storage::disk('public')->exists($dawSound->file_path)) {
            Storage::disk('public')->delete($dawSound->file_path);
        }

        $dawSound->delete();

        return redirect()->route('admin.daw-sounds.index')
            ->with('success', 'Sound deleted successfully!');
    }

    public function categories()
    {
        $categories = DawSoundCategory::withCount('sounds')
            ->orderBy('sort_order')
            ->get();

        return view('admin.daw-sounds.categories', compact('categories'));
    }
}
