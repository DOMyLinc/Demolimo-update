<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SongBattle;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    public function index()
    {
        // Check if feature is enabled
        if (!feature_enabled('music_competitions')) {
            abort(404, 'Music competitions feature is currently disabled.');
        }

        // Get active competitions (using Song Battles as backend)
        $activeCompetitions = SongBattle::where('status', 'active')
            ->with(['versions.track.artist', 'versions.user'])
            ->orderBy('end_date', 'asc')
            ->paginate(12);

        $upcomingCompetitions = SongBattle::where('status', 'pending')
            ->with(['versions.track.artist'])
            ->orderBy('start_date', 'asc')
            ->limit(6)
            ->get();

        $pastWinners = SongBattle::where('status', 'completed')
            ->with([
                'versions' => function ($query) {
                    $query->orderBy('votes', 'desc')->limit(1);
                }
            ])
            ->orderBy('end_date', 'desc')
            ->limit(6)
            ->get();

        return view('user.competitions.index', compact('activeCompetitions', 'upcomingCompetitions', 'pastWinners'));
    }
}
