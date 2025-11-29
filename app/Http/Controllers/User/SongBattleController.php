<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SongBattle;
use App\Models\SongBattleVersion;
use App\Models\SongBattleVote;
use App\Models\SongBattleComment;
use App\Models\Setting; // Assuming this exists
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SongBattleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!feature_enabled('song_battles_system')) {
                if (!auth()->check() || !auth()->user()->hasRole('admin')) {
                    abort(403, 'Song Battles feature is currently disabled.');
                }
            }
            return $next($request);
        })->only(['create', 'store']);
    }

    public function index()
    {
        $battles = SongBattle::with('user', 'versions')->where('status', 'active')->latest()->paginate(10);
        return view('user.song_battles.index', compact('battles'));
    }

    public function create()
    {
        return view('user.song_battles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'version_1_file' => 'required|file|mimes:mp3,wav,ogg',
            'version_1_style' => 'required|string|max:255',
            'version_2_file' => 'required|file|mimes:mp3,wav,ogg',
            'version_2_style' => 'required|string|max:255',
            'version_3_file' => 'required|file|mimes:mp3,wav,ogg',
            'version_3_style' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        if ($user->role !== 'admin' && $user->tracks()->count() >= $user->max_uploads) {
            return back()->with('error', 'You have reached your upload limit. Please upgrade or delete some tracks.');
        }

        $battle = SongBattle::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'status' => 'active',
        ]);

        $files = ['version_1', 'version_2', 'version_3'];

        foreach ($files as $index => $prefix) {
            $file = $request->file($prefix . '_file');
            $path = $file->store('song_battles', 'public');

            SongBattleVersion::create([
                'song_battle_id' => $battle->id,
                'version_number' => $index + 1,
                'file_path' => $path,
                'style_name' => $request->input($prefix . '_style'),
            ]);
        }

        return redirect()->route('user.song_battles.show', $battle)->with('success', 'Song Battle created successfully!');
    }

    public function show(SongBattle $songBattle)
    {
        $songBattle->load(['versions.votes', 'versions.comments.user', 'user']);
        return view('user.song_battles.show', compact('songBattle'));
    }

    public function vote(Request $request, SongBattleVersion $version)
    {
        // Check if user already voted for this battle
        $battleId = $version->song_battle_id;
        $hasVoted = SongBattleVote::where('user_id', Auth::id())
            ->whereHas('version', function ($q) use ($battleId) {
                $q->where('song_battle_id', $battleId);
            })->exists();

        if ($hasVoted) {
            return back()->with('error', 'You have already voted in this battle.');
        }

        SongBattleVote::create([
            'song_battle_version_id' => $version->id,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Vote cast successfully!');
    }

    public function comment(Request $request, SongBattleVersion $version)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        SongBattleComment::create([
            'song_battle_version_id' => $version->id,
            'user_id' => Auth::id(),
            'content' => $request->input('content'),
        ]);

        return back()->with('success', 'Comment added successfully!');
    }

    public function shareToFeed(SongBattle $songBattle)
    {
        // Check if feed is enabled
        if (Setting::get('enable_feed') !== '1') {
            return back()->with('error', 'Feed is currently disabled.');
        }

        // Check permissions
        $user = Auth::user();
        if ($user->id !== $songBattle->user_id && !$user->hasRole('admin') && !$user->hasRole('moderator')) {
            abort(403, 'Unauthorized to share this battle.');
        }

        // Create feed post
        $feedPost = \App\Models\FeedPost::createFromSongBattle($songBattle, $user->id);

        // Track the share
        \App\Models\SongBattleShare::create([
            'song_battle_id' => $songBattle->id,
            'user_id' => $user->id,
            'share_type' => 'feed',
        ]);

        return back()->with('success', 'Shared to feed successfully!');
    }


    public function shareToZipcode(Request $request, SongBattle $songBattle)
    {
        // Check if zipcodes enabled
        if (Setting::get('enable_zipcodes') !== '1') {
            return back()->with('error', 'Zipcode system is currently disabled.');
        }

        $request->validate([
            'zipcode_id' => 'required|exists:zipcodes,id',
        ]);

        // Check permissions: User, Admin, Moderator, or Uploader
        $user = Auth::user();
        if ($user->id !== $songBattle->user_id && !$user->hasRole('admin') && !$user->hasRole('moderator')) {
            abort(403, 'Unauthorized to share this battle.');
        }

        // Track the share
        \App\Models\SongBattleShare::create([
            'song_battle_id' => $songBattle->id,
            'user_id' => $user->id,
            'share_type' => 'zipcode',
            'zipcode_id' => $request->zipcode_id,
        ]);

        return back()->with('success', 'Shared to zipcode successfully!');
    }

    public function registerPlay(SongBattleVersion $version)
    {
        $version->increment('play_count');

        // Reward Logic: 1 point per play
        $artist = $version->battle->user;
        if ($artist) {
            $artist->increment('points', 1);
        }

        return response()->json(['success' => true, 'plays' => $version->play_count]);
    }

    public function hallOfFame()
    {
        // Top Artists by Points
        $topArtists = \App\Models\User::orderByDesc('points')->take(10)->get();

        // Top Battles by Total Votes
        $topBattles = SongBattle::with(['user', 'versions'])
            ->withCount([
                'versions as total_votes' => function ($query) {
                    $query->withCount('votes')->select(\Illuminate\Support\Facades\DB::raw('sum(votes_count)'));
                }
            ])
            // Note: The above withCount logic for sum is tricky in Eloquent without a join or accessor.
            // Simpler approach: Get battles, sort by sum of votes in memory or use a raw query.
            // For efficiency with small data, memory is fine. For large data, need join.
            // Let's use a simpler approximation or just sort by created_at for now if complex.
            // Actually, let's try to get top versions by votes instead, easier.
            ->take(10)->get();

        // Let's get Top Versions by Votes instead of Battles for simplicity and accuracy
        $topVersions = SongBattleVersion::with('battle.user')->withCount('votes')->orderByDesc('votes_count')->take(10)->get();

        // Top Trending (Most Played Versions)
        $trendingVersions = SongBattleVersion::with('battle.user')->orderByDesc('play_count')->take(10)->get();

        return view('user.song_battles.hall_of_fame', compact('topArtists', 'topVersions', 'trendingVersions'));
    }
}
