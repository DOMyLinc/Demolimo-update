<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Track;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SocialController extends Controller
{
    public function feed()
    {
        $user = Auth::user();

        // Get posts from followed users
        $posts = Post::whereIn('user_id', $user->following()->pluck('following_id'))
            ->orWhere('user_id', $user->id)
            ->with(['user', 'reactions', 'comments'])
            ->latest()
            ->paginate(20);

        // Get suggested users to follow
        $suggestedUsers = $this->getSuggestedUsers($user);

        // Get trending tracks
        $trendingTracks = Track::where('created_at', '>=', now()->subDays(7))
            ->orderBy('plays', 'desc')
            ->limit(5)
            ->get();

        return view('user.social.feed', compact('posts', 'suggestedUsers', 'trendingTracks'));
    }

    public function follow(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['error' => 'Cannot follow yourself'], 400);
        }

        Auth::user()->following()->syncWithoutDetaching([$user->id]);

        // Create notification
        $user->notify(new \App\Notifications\NewFollower(Auth::user()));

        return response()->json([
            'success' => true,
            'message' => 'Now following ' . $user->name,
        ]);
    }

    public function unfollow(User $user)
    {
        Auth::user()->following()->detach($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Unfollowed ' . $user->name,
        ]);
    }

    public function followers()
    {
        $followers = Auth::user()->followers()
            ->withCount('tracks', 'followers')
            ->paginate(20);

        return view('user.social.followers', compact('followers'));
    }

    public function following()
    {
        $following = Auth::user()->following()
            ->withCount('tracks', 'followers')
            ->paginate(20);

        return view('user.social.following', compact('following'));
    }

    public function createPost(Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'media_type' => 'nullable|in:image,video,audio',
            'media_url' => 'nullable|string',
            'visibility' => 'in:public,friends,private',
        ]);

        $validated['user_id'] = Auth::id();

        $post = Post::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully',
            'post' => $post->load('user'),
        ]);
    }

    public function deletePost(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }

    public function activity()
    {
        $user = Auth::user();

        $activities = collect();

        // Recent uploads
        $uploads = $user->tracks()
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($track) {
                return [
                    'type' => 'upload',
                    'icon' => '⬆️',
                    'message' => "Uploaded \"{$track->title}\"",
                    'time' => $track->created_at,
                    'data' => $track,
                ];
            });

        // Recent followers
        $newFollowers = $user->followers()
            ->wherePivot('created_at', '>=', now()->subDays(30))
            ->latest('follower_user.created_at')
            ->limit(10)
            ->get()
            ->map(function ($follower) {
                return [
                    'type' => 'follower',
                    'icon' => '👤',
                    'message' => "{$follower->name} started following you",
                    'time' => $follower->pivot->created_at,
                    'data' => $follower,
                ];
            });

        // Merge and sort
        $activities = $uploads->concat($newFollowers)
            ->sortByDesc('time')
            ->take(20);

        return view('user.social.activity', compact('activities'));
    }

    private function getSuggestedUsers($user)
    {
        // Get users in similar genres
        $userGenres = $user->tracks()->pluck('genre')->unique();

        return User::whereHas('tracks', function ($q) use ($userGenres) {
            $q->whereIn('genre', $userGenres);
        })
            ->where('id', '!=', $user->id)
            ->whereNotIn('id', $user->following()->pluck('following_id'))
            ->withCount('followers')
            ->orderBy('followers_count', 'desc')
            ->limit(5)
            ->get();
    }
}
