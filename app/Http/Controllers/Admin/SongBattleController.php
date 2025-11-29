<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SongBattle;
use Illuminate\Http\Request;

class SongBattleController extends Controller
{
    public function index()
    {
        $battles = SongBattle::with(['user', 'reward'])
            ->withCount('versions')
            ->latest()
            ->paginate(15);
        return view('admin.song_battles.index', compact('battles'));
    }

    public function show(SongBattle $songBattle)
    {
        $songBattle->load(['user', 'versions.votes', 'reward']);

        // Calculate winner
        $winningVersion = $songBattle->versions()
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->first();

        return view('admin.song_battles.show', compact('songBattle', 'winningVersion'));
    }

    public function destroy(SongBattle $songBattle)
    {
        $songBattle->delete();
        return back()->with('success', 'Song Battle deleted successfully.');
    }

    public function complete(SongBattle $songBattle)
    {
        $songBattle->status = 'completed';
        $songBattle->save();

        return back()->with('success', 'Song Battle marked as completed.');
    }

    public function reopen(SongBattle $songBattle)
    {
        $songBattle->status = 'active';
        $songBattle->save();

        return back()->with('success', 'Song Battle reopened.');
    }
}

