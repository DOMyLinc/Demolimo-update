<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\PodcastEpisode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MyPodcastController extends Controller
{
    public function index()
    {
        $podcasts = auth()->user()->podcasts()->withCount('episodes')->latest()->paginate(12);

        return view('user.my-podcasts.index', compact('podcasts'));
    }

    public function create()
    {
        $categories = ['Music', 'Comedy', 'News', 'Education', 'Technology', 'Business', 'Arts', 'Sports'];

        return view('user.my-podcasts.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'cover_image' => 'required|image|max:5120', // 5MB
            'is_explicit' => 'boolean',
            'website_url' => 'nullable|url',
            'social_links' => 'nullable|array',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['slug'] = Str::slug($validated['title']);
        $validated['author_name'] = auth()->user()->name;
        $validated['author_email'] = auth()->user()->email;
        $validated['is_active'] = false; // Pending approval

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('podcasts/covers', 'public');
        }

        $podcast = Podcast::create($validated);

        // Generate RSS feed URL
        $podcast->update(['rss_feed_url' => $podcast->generateRSSFeed()]);

        return redirect()->route('my-podcasts.show', $podcast)
            ->with('success', 'Podcast created! It will be active after admin approval.');
    }

    public function show(Podcast $myPodcast)
    {
        // Ensure user owns this podcast
        if ($myPodcast->user_id !== auth()->id()) {
            abort(403);
        }

        $episodes = $myPodcast->episodes()->latest('published_at')->paginate(20);

        return view('user.my-podcasts.show', compact('myPodcast', 'episodes'));
    }

    public function edit(Podcast $myPodcast)
    {
        // Ensure user owns this podcast
        if ($myPodcast->user_id !== auth()->id()) {
            abort(403);
        }

        $categories = ['Music', 'Comedy', 'News', 'Education', 'Technology', 'Business', 'Arts', 'Sports'];

        return view('user.my-podcasts.edit', compact('myPodcast', 'categories'));
    }

    public function update(Request $request, Podcast $myPodcast)
    {
        // Ensure user owns this podcast
        if ($myPodcast->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'cover_image' => 'nullable|image|max:5120',
            'is_explicit' => 'boolean',
            'website_url' => 'nullable|url',
            'social_links' => 'nullable|array',
        ]);

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('podcasts/covers', 'public');
        }

        $myPodcast->update($validated);

        return back()->with('success', 'Podcast updated successfully!');
    }

    public function destroy(Podcast $myPodcast)
    {
        // Ensure user owns this podcast
        if ($myPodcast->user_id !== auth()->id()) {
            abort(403);
        }

        $myPodcast->delete();

        return redirect()->route('my-podcasts.index')->with('success', 'Podcast deleted successfully!');
    }

    // Episode Management
    public function createEpisode(Podcast $myPodcast)
    {
        // Ensure user owns this podcast
        if ($myPodcast->user_id !== auth()->id()) {
            abort(403);
        }

        return view('user.my-podcasts.create-episode', compact('myPodcast'));
    }

    public function storeEpisode(Request $request, Podcast $myPodcast)
    {
        // Ensure user owns this podcast
        if ($myPodcast->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'audio_file' => 'required|file|mimes:mp3,wav,m4a|max:102400', // 100MB
            'cover_image' => 'nullable|image|max:5120',
            'episode_number' => 'nullable|integer',
            'season_number' => 'nullable|integer',
            'is_explicit' => 'boolean',
        ]);

        $validated['podcast_id'] = $myPodcast->id;
        $validated['slug'] = Str::slug($validated['title']);

        // Handle audio file upload
        if ($request->hasFile('audio_file')) {
            $audioFile = $request->file('audio_file');
            $validated['audio_file'] = $audioFile->store('podcasts/episodes', 'public');
            $validated['file_size'] = $audioFile->getSize();

            // Get audio duration (requires getID3 library or similar)
            // For now, set to 0 and update later
            $validated['duration'] = 0;
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('podcasts/episodes/covers', 'public');
        }

        $validated['published_at'] = now();

        $episode = PodcastEpisode::create($validated);

        // Update podcast episode count
        $myPodcast->increment('total_episodes');

        return redirect()->route('my-podcasts.show', $myPodcast)
            ->with('success', 'Episode uploaded successfully!');
    }

    public function deleteEpisode(Podcast $myPodcast, PodcastEpisode $episode)
    {
        // Ensure user owns this podcast
        if ($myPodcast->user_id !== auth()->id() || $episode->podcast_id !== $myPodcast->id) {
            abort(403);
        }

        $episode->delete();
        $myPodcast->decrement('total_episodes');

        return back()->with('success', 'Episode deleted successfully!');
    }
}
