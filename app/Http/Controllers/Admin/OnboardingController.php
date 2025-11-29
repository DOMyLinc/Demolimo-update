<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OnboardingTutorial;
use App\Models\WelcomeMessage;
use App\Models\User;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    /**
     * Onboarding Dashboard
     */
    public function index()
    {
        $stats = [
            'total_tutorials' => OnboardingTutorial::count(),
            'active_tutorials' => OnboardingTutorial::where('is_active', true)->count(),
            'total_users' => User::count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
        ];

        return view('admin.onboarding.index', compact('stats'));
    }

    /**
     * Tutorials Management
     */
    public function tutorials()
    {
        $tutorials = OnboardingTutorial::orderBy('step_order')->get();
        return view('admin.onboarding.tutorials', compact('tutorials'));
    }

    public function createTutorial()
    {
        return view('admin.onboarding.tutorial-form');
    }

    public function storeTutorial(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:onboarding_tutorials,slug',
            'description' => 'nullable|string',
            'content' => 'required|string',
            'target_element' => 'nullable|string',
            'position' => 'required|string',
            'step_order' => 'required|integer',
            'user_type' => 'required|string',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'icon' => 'nullable|string',
            'video_url' => 'nullable|url',
        ]);

        OnboardingTutorial::create($validated);

        return redirect()->route('admin.onboarding.tutorials')
            ->with('success', 'Tutorial created successfully!');
    }

    public function editTutorial(OnboardingTutorial $tutorial)
    {
        return view('admin.onboarding.tutorial-form', compact('tutorial'));
    }

    public function updateTutorial(Request $request, OnboardingTutorial $tutorial)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:onboarding_tutorials,slug,' . $tutorial->id,
            'description' => 'nullable|string',
            'content' => 'required|string',
            'target_element' => 'nullable|string',
            'position' => 'required|string',
            'step_order' => 'required|integer',
            'user_type' => 'required|string',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'icon' => 'nullable|string',
            'video_url' => 'nullable|url',
        ]);

        $tutorial->update($validated);

        return redirect()->route('admin.onboarding.tutorials')
            ->with('success', 'Tutorial updated successfully!');
    }

    public function deleteTutorial(OnboardingTutorial $tutorial)
    {
        $tutorial->delete();
        return back()->with('success', 'Tutorial deleted!');
    }

    /**
     * Welcome Messages
     */
    public function welcomeMessages()
    {
        $messages = WelcomeMessage::orderBy('priority')->get();
        return view('admin.onboarding.welcome-messages', compact('messages'));
    }

    public function createWelcomeMessage()
    {
        return view('admin.onboarding.welcome-form');
    }

    public function storeWelcomeMessage(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user_type' => 'required|string',
            'display_type' => 'required|string',
            'show_once' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'required|integer',
            'button_text' => 'required|string',
            'button_link' => 'nullable|string',
        ]);

        WelcomeMessage::create($validated);

        return redirect()->route('admin.onboarding.welcome-messages')
            ->with('success', 'Welcome message created successfully!');
    }

    public function editWelcomeMessage(WelcomeMessage $message)
    {
        return view('admin.onboarding.welcome-form', compact('message'));
    }

    public function updateWelcomeMessage(Request $request, WelcomeMessage $message)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user_type' => 'required|string',
            'display_type' => 'required|string',
            'show_once' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'required|integer',
            'button_text' => 'required|string',
            'button_link' => 'nullable|string',
        ]);

        $message->update($validated);

        return redirect()->route('admin.onboarding.welcome-messages')
            ->with('success', 'Welcome message updated successfully!');
    }

    public function deleteWelcomeMessage(WelcomeMessage $message)
    {
        $message->delete();
        return back()->with('success', 'Welcome message deleted!');
    }

    /**
     * User Verification Stats
     */
    public function verificationStats()
    {
        $verified = User::whereNotNull('email_verified_at')
            ->orderBy('email_verified_at', 'desc')
            ->paginate(20);

        $unverified = User::whereNull('email_verified_at')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => User::count(),
            'verified_count' => User::whereNotNull('email_verified_at')->count(),
            'unverified_count' => User::whereNull('email_verified_at')->count(),
            'verified_today' => User::whereDate('email_verified_at', today())->count(),
        ];

        return view('admin.onboarding.verification-stats', compact('verified', 'unverified', 'stats'));
    }

    /**
     * Resend Verification Email
     */
    public function resendVerification(User $user)
    {
        if ($user->email_verified_at) {
            return back()->with('error', 'User is already verified!');
        }

        // Generate verification token
        $token = bin2hex(random_bytes(32));
        $user->update([
            'verification_token' => $token,
            'verification_sent_at' => now(),
            'verification_attempts' => $user->verification_attempts + 1,
        ]);

        // Send verification email
        $verificationLink = url("/verify-email/{$token}");
        app(\App\Services\EmailService::class)->sendVerificationEmail($user, $verificationLink);

        return back()->with('success', 'Verification email resent!');
    }

    /**
     * Manually Verify User
     */
    public function manuallyVerify(User $user)
    {
        $user->update([
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);

        return back()->with('success', 'User manually verified!');
    }
}
