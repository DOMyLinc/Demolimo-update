<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerificationController extends Controller
{
    public function index()
    {
        $pending = VerificationRequest::where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();

        $reviewed = VerificationRequest::whereIn('status', ['approved', 'rejected'])
            ->with(['user', 'reviewer'])
            ->latest()
            ->paginate(20);

        return view('admin.verification.index', compact('pending', 'reviewed'));
    }

    public function show(VerificationRequest $request)
    {
        $request->load('user');

        return view('admin.verification.show', compact('request'));
    }

    public function approve(Request $request, VerificationRequest $verificationRequest)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        $verificationRequest->approve(Auth::id(), $validated['notes'] ?? null);

        return redirect()->route('admin.verification.index')
            ->with('success', 'Verification request approved!');
    }

    public function reject(Request $request, VerificationRequest $verificationRequest)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:500'
        ]);

        $verificationRequest->reject(Auth::id(), $validated['notes']);

        return redirect()->route('admin.verification.index')
            ->with('success', 'Verification request rejected.');
    }
}
