<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\OfflineDownload;
use App\Models\Track;
use App\Models\Album;
use App\Models\Playlist;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfflineController extends Controller
{
    public function index()
    {
        if (!FeatureFlag::isEnabled('enable_offline_downloads')) {
            return back()->with('error', 'Offline downloads are currently disabled.');
        }

        if (!Auth::user()->isPro()) {
            return redirect()->route('user.subscription.plans')
                ->with('error', 'Offline downloads require a Pro subscription.');
        }

        $downloads = OfflineDownload::where('user_id', Auth::id())
            ->with('downloadable')
            ->active()
            ->latest()
            ->paginate(20);

        $stats = [
            'total_downloads' => OfflineDownload::where('user_id', Auth::id())->active()->count(),
            'total_size' => OfflineDownload::where('user_id', Auth::id())->active()->sum('file_size'),
        ];

        return view('user.offline.index', compact('downloads', 'stats'));
    }

    public function download(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:track,album,playlist',
            'id' => 'required|integer',
            'quality' => 'required|in:128kbps,320kbps,flac',
        ]);

        if (!FeatureFlag::isEnabled('enable_offline_downloads')) {
            return response()->json(['error' => 'Offline downloads are disabled'], 403);
        }

        if (!Auth::user()->isPro()) {
            return response()->json(['error' => 'Pro subscription required'], 403);
        }

        // Check quality permission
        if ($validated['quality'] === 'flac' && !FeatureFlag::isEnabled('enable_flac_streaming')) {
            return response()->json(['error' => 'FLAC quality not available'], 403);
        }

        try {
            $downloadable = match ($validated['type']) {
                'track' => Track::findOrFail($validated['id']),
                'album' => Album::findOrFail($validated['id']),
                'playlist' => Playlist::findOrFail($validated['id']),
            };

            $download = OfflineDownload::createDownload(
                Auth::id(),
                $downloadable,
                $validated['quality']
            );

            return response()->json([
                'success' => true,
                'download' => $download,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function delete(OfflineDownload $download)
    {
        if ($download->user_id !== Auth::id()) {
            abort(403);
        }

        // Delete file
        \Storage::delete($download->file_path);

        $download->delete();

        return back()->with('success', 'Download removed successfully.');
    }
}
