<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = Auth::user()->albums()->latest()->paginate(10);
        return view('user.albums.index', compact('albums'));
    }

    public function create()
    {
        return view('user.albums.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cover_file' => 'nullable|image|max:2048',
            'price' => 'nullable|numeric|min:0',
        ]);

        $coverPath = $request->file('cover_file')
            ? $request->file('cover_file')->store('albums/covers', config('filesystems.default'))
            : null;

        Auth::user()->albums()->create([
            'title' => $request->title,
            'slug' => Str::slug($request->title) . '-' . Str::random(6),
            'description' => $request->description,
            'cover_path' => $coverPath,
            'price' => $request->price ?? 0,
            'is_public' => true,
        ]);

        return redirect()->route('user.albums.index')->with('success', 'Album created successfully!');
    }
}
