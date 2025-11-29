<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ArtistRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ArtistRequestController extends Controller
{
    public function create()
    {
        // Check if already pending or approved
        $existing = ArtistRequest::where('user_id', Auth::id())->first();
        if ($existing && $existing->status !== 'rejected') {
            return redirect()->route('user.profile.show', Auth::id())
                ->with('info', 'You already have a pending or approved artist request.');
        }

        return view('user.artist.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'stage_name' => 'required|string|max:255',
            'bio' => 'required|string',
            'id_proof' => 'required|file|mimes:jpg,png,pdf|max:2048',
            'social_links' => 'nullable|array',
        ]);

        $path = $request->file('id_proof')->store('id_proofs', 'local'); // Store securely

        ArtistRequest::create([
            'user_id' => Auth::id(),
            'stage_name' => $request->stage_name,
            'bio' => $request->bio,
            'id_proof_path' => $path,
            'social_links' => $request->social_links,
            'status' => 'pending',
        ]);

        return redirect()->route('user.profile.show', Auth::id())
            ->with('success', 'Artist application submitted successfully!');
    }
}
