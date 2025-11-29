<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        if (!file_exists(storage_path('installed'))) {
            return redirect()->route('installer.index');
        }

        $trendingTracks = Track::where('is_public', true)
            ->orderBy('plays', 'desc')
            ->take(10)
            ->get();

        return view('welcome', compact('trendingTracks'));
    }
}
