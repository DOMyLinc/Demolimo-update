<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Podcast;
use App\Models\PodcastEpisode;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PodcastController extends Controller
{
    public function index()
    {
        if (!FeatureFlag::isEnabled('enable_podcasts')) {
            return back()->with('error', 'Podcasts are currently disabled.');
        }

        $podcasts = Podcast::with('user')
            ->withCount('episodes')
            ->latest()
            ->paginate(20);

        return view('user.podcasts.index', compact('podcasts'));
    }

    public function show(Podcast $podcast)
    {
        $podcast->load([
            'user',
            'episodes' => function ($q) {
                $q->whereNotNull('published_at')
                    ->orderBy('published_at', 'desc');
            }
        ]);

        return view('user.podcasts.show', compact('podcast'));
    }

    public function myPodcasts()
    {
        $podcasts = Podcast::where('user_id', Auth::id())
            ->withCount('episodes')
            ->latest()
            ->get();

        return view('user.podcasts.my-podcasts', compact('podcasts'));
    }

    public function create()
    {
        if (!Auth::user()->isPro()) {
            return redirect()->route('user.subscription.plans')
                ->with('error', 'Podcast hosting requires a Pro subscription.');
        }

        return view('user.podcasts.create');
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isPro()) {
            return back()->with('error', 'Podcast hosting requires Pro subscription.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'cover_image' => 'required|image|max:5120',
            'category' => 'required|string',
            'tags' => 'nullable|array',
            'language' => 'required|string',
            'is_explicit' => 'boolean',
        ]);

        // Upload cover image
        $coverPath = $request->file('cover_image')->store('podcasts/covers', 'public');

        $podcast = Podcast::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'description' => $validated['description'],
            'cover_image' => $coverPath,
            'category' => $validated['category'],
            'tags' => $validated['tags'] ?? [],
            'language' => $validated['language'],
            'is_explicit' => $validated['is_explicit'] ?? false,
        ]);

        // Generate RSS feed URL
        $podcast->update([
            'rss_feed_url' => route('podcasts.rss', $podcast->id),
        ]);

        return redirect()->route('user.podcasts.show', $podcast)
            ->with('success', 'Podcast created successfully!');
    }

    public function createEpisode(Podcast $podcast)
    {
        if ($podcast->user_id !== Auth::id()) {
            abort(403);
        }

        return view('user.podcasts.create-episode', compact('podcast'));
    }

    public function storeEpisode(Request $request, Podcast $podcast)
    {
        if ($podcast->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'audio_file' => 'required|file|mimes:mp3,wav|max:512000', // 500MB
            'episode_number' => 'nullable|integer',
            'season_number' => 'nullable|integer',
            'chapters' => 'nullable|array',
            'publish_now' => 'boolean',
        ]);

        // Upload audio file
        $audioPath = $request->file('audio_file')->store('podcasts/episodes', 'public');
        $fileSize = $request->file('audio_file')->getSize();

        // Get duration (would use FFmpeg in production)
        $duration = 0; // Placeholder

        $episode = PodcastEpisode::create([
            'podcast_id' => $podcast->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'audio_file' => $audioPath,
            'file_size' => $fileSize,
            'duration' => $duration,
            'episode_number' => $validated['episode_number'],
            'season_number' => $validated['season_number'],
            'chapters' => $validated['chapters'] ?? [],
            'published_at' => $request->boolean('publish_now') ? now() : null,
        ]);

        $podcast->increment('total_episodes');

        return redirect()->route('user.podcasts.show', $podcast)
            ->with('success', 'Episode created successfully!');
    }

    public function subscribe(Podcast $podcast)
    {
        Auth::user()->podcastSubscriptions()->attach($podcast->id);
        $podcast->increment('subscribers');

        return back()->with('success', 'Subscribed to podcast!');
    }

    public function unsubscribe(Podcast $podcast)
    {
        Auth::user()->podcastSubscriptions()->detach($podcast->id);
        $podcast->decrement('subscribers');

        return back()->with('success', 'Unsubscribed from podcast.');
    }
}
