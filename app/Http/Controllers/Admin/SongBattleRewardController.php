<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SongBattle;
use App\Models\SongBattleReward;
use App\Models\SongBattleVersion;
use App\Models\User;
use Illuminate\Http\Request;

class SongBattleRewardController extends Controller
{
    public function index()
    {
        $rewards = SongBattleReward::with(['battle', 'winner', 'winnerVersion', 'awardedBy'])
            ->latest()
            ->paginate(20);

        return view('admin.song_battle_rewards.index', compact('rewards'));
    }

    public function create(Request $request)
    {
        $battleId = $request->get('battle_id');
        $battle = $battleId ? SongBattle::with('versions.votes')->findOrFail($battleId) : null;

        // Get all completed battles without rewards
        $battles = SongBattle::where('status', 'completed')
            ->whereDoesntHave('reward')
            ->with('versions')
            ->get();

        return view('admin.song_battle_rewards.create', compact('battles', 'battle'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'song_battle_id' => 'required|exists:song_battles,id',
            'winner_version_id' => 'required|exists:song_battle_versions,id',
            'reward_type' => 'required|in:cash,points,premium_subscription,custom',
            'cash_amount' => 'nullable|numeric|min:0',
            'points_amount' => 'nullable|integer|min:0',
            'premium_days' => 'nullable|integer|min:1',
            'custom_reward_description' => 'nullable|string',
            'notes' => 'nullable|string',
            'auto_award' => 'boolean',
        ]);

        $version = SongBattleVersion::with('battle')->findOrFail($validated['winner_version_id']);
        $battle = $version->battle;

        // Get the winner user from the battle creator (or version uploader if different)
        $winnerUserId = $battle->user_id;

        $reward = SongBattleReward::create([
            'song_battle_id' => $validated['song_battle_id'],
            'winner_version_id' => $validated['winner_version_id'],
            'winner_user_id' => $winnerUserId,
            'reward_type' => $validated['reward_type'],
            'cash_amount' => $validated['cash_amount'] ?? null,
            'points_amount' => $validated['points_amount'] ?? null,
            'premium_days' => $validated['premium_days'] ?? null,
            'custom_reward_description' => $validated['custom_reward_description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'awarded_by' => auth()->id(),
        ]);

        // Auto-award if requested
        if ($request->boolean('auto_award')) {
            $reward->award();
            $message = 'Song Battle reward created and awarded successfully!';
        } else {
            $message = 'Song Battle reward created successfully!';
        }

        return redirect()->route('admin.song_battle_rewards.index')->with('success', $message);
    }

    public function show(SongBattleReward $reward)
    {
        $reward->load(['battle', 'winner', 'winnerVersion', 'awardedBy']);
        return view('admin.song_battle_rewards.show', compact('reward'));
    }

    public function edit(SongBattleReward $reward)
    {
        if ($reward->status === 'claimed') {
            return back()->with('error', 'Cannot edit a claimed reward.');
        }

        $battles = SongBattle::with('versions')->get();
        return view('admin.song_battle_rewards.edit', compact('reward', 'battles'));
    }

    public function update(Request $request, SongBattleReward $reward)
    {
        if ($reward->status === 'claimed') {
            return back()->with('error', 'Cannot edit a claimed reward.');
        }

        $validated = $request->validate([
            'reward_type' => 'required|in:cash,points,premium_subscription,custom',
            'cash_amount' => 'nullable|numeric|min:0',
            'points_amount' => 'nullable|integer|min:0',
            'premium_days' => 'nullable|integer|min:1',
            'custom_reward_description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $reward->update($validated);

        return redirect()->route('admin.song_battle_rewards.show', $reward)
            ->with('success', 'Reward updated successfully!');
    }

    public function award(SongBattleReward $reward)
    {
        try {
            $reward->award();
            return back()->with('success', 'Reward awarded successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to award reward: ' . $e->getMessage());
        }
    }

    public function destroy(SongBattleReward $reward)
    {
        if ($reward->status === 'claimed') {
            return back()->with('error', 'Cannot delete a claimed reward.');
        }

        $reward->delete();
        return redirect()->route('admin.song_battle_rewards.index')
            ->with('success', 'Reward deleted successfully!');
    }

    public function getWinner(Request $request)
    {
        $battleId = $request->get('battle_id');

        if (!$battleId) {
            return response()->json(['error' => 'Battle ID required'], 400);
        }

        $battle = SongBattle::with([
            'versions' => function ($query) {
                $query->withCount('votes')->orderByDesc('votes_count');
            }
        ])->findOrFail($battleId);

        $winningVersion = $battle->versions->first();

        return response()->json([
            'winner_version_id' => $winningVersion?->id,
            'winner_user_id' => $battle->user_id,
            'votes_count' => $winningVersion?->votes_count ?? 0,
        ]);
    }
}
