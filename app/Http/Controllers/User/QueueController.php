<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\UserQueue;
use App\Models\Track;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    public function index()
    {
        $queue = UserQueue::getOrCreateForUser(Auth::id());
        $currentTrack = $queue->getCurrentTrack();

        return view('user.queue.index', compact('queue', 'currentTrack'));
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'track_id' => 'required|exists:tracks,id',
            'position' => 'nullable|integer',
        ]);

        $queue = UserQueue::getOrCreateForUser(Auth::id());
        $queue->addTrack($validated['track_id'], $validated['position'] ?? null);

        return response()->json([
            'success' => true,
            'queue' => $queue,
        ]);
    }

    public function remove(Request $request)
    {
        $validated = $request->validate([
            'index' => 'required|integer',
        ]);

        $queue = UserQueue::getOrCreateForUser(Auth::id());
        $queue->removeTrack($validated['index']);

        return response()->json([
            'success' => true,
            'queue' => $queue,
        ]);
    }

    public function next()
    {
        $user = Auth::user();

        // Check skip limits for free users
        if (!$user->isPro()) {
            $skipLimit = (int) FeatureFlag::getValue('free_skip_limit', 6);

            // Reset counter if needed
            if (!$user->skip_count_reset_at || $user->skip_count_reset_at->isPast()) {
                $user->update([
                    'skip_count' => 0,
                    'skip_count_reset_at' => now()->addHour(),
                ]);
            }

            if ($user->skip_count >= $skipLimit) {
                return response()->json([
                    'error' => "Skip limit reached. Upgrade to Pro for unlimited skips.",
                    'limit_reached' => true,
                ], 429);
            }

            $user->increment('skip_count');
        }

        $queue = UserQueue::getOrCreateForUser(Auth::id());
        $queue->next();

        return response()->json([
            'success' => true,
            'current_track' => $queue->getCurrentTrack(),
        ]);
    }

    public function previous()
    {
        $queue = UserQueue::getOrCreateForUser(Auth::id());
        $queue->previous();

        return response()->json([
            'success' => true,
            'current_track' => $queue->getCurrentTrack(),
        ]);
    }

    public function toggleShuffle()
    {
        $queue = UserQueue::getOrCreateForUser(Auth::id());
        $queue->toggleShuffle();

        return response()->json([
            'success' => true,
            'shuffle_enabled' => $queue->shuffle_enabled,
        ]);
    }

    public function setRepeat(Request $request)
    {
        $validated = $request->validate([
            'mode' => 'required|in:off,one,all',
        ]);

        $queue = UserQueue::getOrCreateForUser(Auth::id());
        $queue->setRepeatMode($validated['mode']);

        return response()->json([
            'success' => true,
            'repeat_mode' => $queue->repeat_mode,
        ]);
    }

    public function clear()
    {
        $queue = UserQueue::getOrCreateForUser(Auth::id());
        $queue->clear();

        return response()->json([
            'success' => true,
        ]);
    }
}
