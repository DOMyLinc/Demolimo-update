<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\VerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function index()
    {
        $request = VerificationRequest::where('user_id', Auth::id())->latest()->first();

        return view('user.verification.index', compact('request'));
    }

    public function create()
    {
        $existingRequest = VerificationRequest::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->exists();

        if ($existingRequest) {
            return redirect()->route('verification.index')
                ->with('error', 'You already have a pending verification request.');
        }

        if (Auth::user()->is_verified) {
            return redirect()->route('verification.index')
                ->with('info', 'You are already verified.');
        }

        return view('user.verification.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'real_name' => 'required|string|max:255',
            'bio' => 'required|string|max:1000',
            'id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'social_proof' => 'nullable|url',
            'additional_info' => 'nullable|string|max:1000'
        ]);

        if ($request->hasFile('id_document')) {
            $validated['id_document'] = $request->file('id_document')->store('verification/documents', 'public');
        }

        $validated['user_id'] = Auth::id();

        VerificationRequest::create($validated);

        return redirect()->route('verification.index')
            ->with('success', 'Verification request submitted successfully!');
    }
}
