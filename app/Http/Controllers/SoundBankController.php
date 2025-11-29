<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SoundBankController extends Controller
{
    public function index()
    {
        // Mock Sound Library
        $sounds = [
            ['name' => 'Kick Drum 808', 'category' => 'Drums', 'icon' => '🥁', 'url' => '/sounds/kick.mp3'],
            ['name' => 'Snare Trap', 'category' => 'Drums', 'icon' => '🥁', 'url' => '/sounds/snare.mp3'],
            ['name' => 'HiHat Closed', 'category' => 'Drums', 'icon' => '🥢', 'url' => '/sounds/hihat.mp3'],
            ['name' => 'Piano Chord C', 'category' => 'Keys', 'icon' => '🎹', 'url' => '/sounds/piano.mp3'],
            ['name' => 'Synth Lead', 'category' => 'Synth', 'icon' => '🎹', 'url' => '/sounds/synth.mp3'],
        ];

        return response()->json($sounds);
    }
}
