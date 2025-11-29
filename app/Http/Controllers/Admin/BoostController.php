<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use App\Models\User;
use App\Models\Event;
use App\Services\BlockchainValuationService;
use Illuminate\Http\Request;

class BoostController extends Controller
{
    protected $blockchainService;

    public function __construct(BlockchainValuationService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    public function index()
    {
        return view('admin.boost.index');
    }

    /**
     * Boost track metrics
     */
    public function boostTrack(Request $request, Track $track)
    {
        $validated = $request->validate([
            'plays' => 'nullable|integer|min:0',
            'views' => 'nullable|integer|min:0',
            'likes' => 'nullable|integer|min:0',
            'shares' => 'nullable|integer|min:0',
            'downloads' => 'nullable|integer|min:0',
            'mode' => 'required|in:add,set', // add to current or set absolute
        ]);

        $mode = $validated['mode'];
        unset($validated['mode']);

        foreach ($validated as $metric => $value) {
            if ($value !== null) {
                if ($mode === 'add') {
                    $track->$metric += $value;
                } else {
                    $track->$metric = $value;
                }
            }
        }

        $track->save();

        // Recalculate blockchain value if enabled
        if (\App\Models\PlatformSetting::get('blockchain_enabled', true)) {
            $this->blockchainService->calculateTrackValue($track);
        }

        return back()->with('success', "Track metrics boosted successfully! New plays: {$track->plays}, likes: {$track->likes}");
    }

    /**
     * Boost user metrics
     */
    public function boostUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'points' => 'nullable|integer|min:0',
            'followers_count' => 'nullable|integer|min:0',
            'mode' => 'required|in:add,set',
        ]);

        $mode = $validated['mode'];
        unset($validated['mode']);

        if (isset($validated['points'])) {
            if ($mode === 'add') {
                $user->points += $validated['points'];
            } else {
                $user->points = $validated['points'];
            }
        }

        $user->save();

        return back()->with('success', "User metrics boosted successfully!");
    }

    /**
     * Boost event metrics
     */
    public function boostEvent(Request $request, Event $event)
    {
        $validated = $request->validate([
            'views' => 'nullable|integer|min:0',
            'interested_count' => 'nullable|integer|min:0',
            'mode' => 'required|in:add,set',
        ]);

        $mode = $validated['mode'];
        unset($validated['mode']);

        foreach ($validated as $metric => $value) {
            if ($value !== null) {
                if ($mode === 'add') {
                    $event->$metric += $value;
                } else {
                    $event->$metric = $value;
                }
            }
        }

        $event->save();

        return back()->with('success', "Event metrics boosted successfully!");
    }

    /**
     * Auto-boost trending content
     */
    public function autoBoost(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|in:tracks,users,events',
            'count' => 'required|integer|min:1|max:100',
            'plays_min' => 'nullable|integer|min:0',
            'plays_max' => 'nullable|integer|min:0',
            'likes_min' => 'nullable|integer|min:0',
            'likes_max' => 'nullable|integer|min:0',
        ]);

        $boosted = 0;

        switch ($validated['target']) {
            case 'tracks':
                $tracks = Track::where('is_public', true)
                    ->inRandomOrder()
                    ->limit($validated['count'])
                    ->get();

                foreach ($tracks as $track) {
                    $track->plays += rand($validated['plays_min'] ?? 10, $validated['plays_max'] ?? 100);
                    $track->likes += rand($validated['likes_min'] ?? 5, $validated['likes_max'] ?? 50);
                    $track->views += rand($validated['plays_min'] ?? 10, $validated['plays_max'] ?? 100);
                    $track->save();

                    if (\App\Models\PlatformSetting::get('blockchain_enabled', true)) {
                        $this->blockchainService->calculateTrackValue($track);
                    }

                    $boosted++;
                }
                break;

            case 'users':
                $users = User::where('role', '!=', 'admin')
                    ->inRandomOrder()
                    ->limit($validated['count'])
                    ->get();

                foreach ($users as $user) {
                    $user->points += rand(10, 100);
                    $user->save();
                    $boosted++;
                }
                break;

            case 'events':
                $events = Event::where('status', 'published')
                    ->inRandomOrder()
                    ->limit($validated['count'])
                    ->get();

                foreach ($events as $event) {
                    $event->views += rand(10, 100);
                    $event->save();
                    $boosted++;
                }
                break;
        }

        return back()->with('success', "Auto-boosted {$boosted} {$validated['target']}!");
    }

    /**
     * Reset metrics
     */
    public function resetMetrics(Request $request)
    {
        $validated = $request->validate([
            'target' => 'required|in:track,user,event,all',
            'target_id' => 'nullable|integer',
        ]);

        switch ($validated['target']) {
            case 'track':
                $track = Track::findOrFail($validated['target_id']);
                $track->update([
                    'plays' => 0,
                    'views' => 0,
                    'likes' => 0,
                    'shares' => 0,
                    'downloads' => 0,
                ]);
                break;

            case 'all':
                Track::query()->update([
                    'plays' => 0,
                    'views' => 0,
                    'likes' => 0,
                    'shares' => 0,
                    'downloads' => 0,
                ]);
                break;
        }

        return back()->with('success', 'Metrics reset successfully!');
    }
}
