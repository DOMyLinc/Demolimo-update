<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\UserPrivacySetting;
use App\Models\Referral;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSettingsController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Notifications page
     */
    public function notifications()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        $unreadCount = $this->notificationService->getUnreadCount(Auth::id());

        return view('user.notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        $notification->markAsRead();

        if ($notification->action_url) {
            return redirect($notification->action_url);
        }

        return back();
    }

    /**
     * Mark all as read
     */
    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead(Auth::id());
        return back()->with('success', 'All notifications marked as read!');
    }

    /**
     * Notification preferences
     */
    public function notificationPreferences()
    {
        $preferences = Auth::user()->notificationPreferences
            ?? NotificationPreference::create(['user_id' => Auth::id()]);

        return view('user.settings.notification-preferences', compact('preferences'));
    }

    /**
     * Update notification preferences
     */
    public function updateNotificationPreferences(Request $request)
    {
        $preferences = Auth::user()->notificationPreferences
            ?? NotificationPreference::create(['user_id' => Auth::id()]);

        $preferences->update($request->all());

        return back()->with('success', 'Notification preferences updated!');
    }

    /**
     * Privacy settings
     */
    public function privacySettings()
    {
        $settings = Auth::user()->privacySettings
            ?? UserPrivacySetting::create(['user_id' => Auth::id()]);

        return view('user.settings.privacy', compact('settings'));
    }

    /**
     * Update privacy settings
     */
    public function updatePrivacySettings(Request $request)
    {
        $validated = $request->validate([
            'profile_visibility' => 'required|in:public,followers,private',
            'show_email' => 'boolean',
            'show_listening_activity' => 'boolean',
            'show_playlists' => 'boolean',
            'show_followers' => 'boolean',
            'hide_explicit_content' => 'boolean',
            'safe_mode' => 'boolean',
            'allow_personalized_ads' => 'boolean',
            'allow_analytics' => 'boolean',
            'allow_third_party_sharing' => 'boolean',
        ]);

        $settings = Auth::user()->privacySettings
            ?? UserPrivacySetting::create(['user_id' => Auth::id()]);

        $settings->update($validated);

        return back()->with('success', 'Privacy settings updated!');
    }

    /**
     * Block user
     */
    public function blockUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $settings = Auth::user()->privacySettings
            ?? UserPrivacySetting::create(['user_id' => Auth::id()]);

        $blockedUsers = $settings->blocked_users ?? [];

        if (!in_array($validated['user_id'], $blockedUsers)) {
            $blockedUsers[] = $validated['user_id'];
            $settings->update(['blocked_users' => $blockedUsers]);
        }

        return back()->with('success', 'User blocked successfully!');
    }

    /**
     * Unblock user
     */
    public function unblockUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $settings = Auth::user()->privacySettings;
        if (!$settings) {
            return back();
        }

        $blockedUsers = $settings->blocked_users ?? [];
        $blockedUsers = array_diff($blockedUsers, [$validated['user_id']]);

        $settings->update(['blocked_users' => array_values($blockedUsers)]);

        return back()->with('success', 'User unblocked successfully!');
    }

    /**
     * Export data (GDPR)
     */
    public function exportData()
    {
        $user = Auth::user();

        $data = [
            'profile' => $user->toArray(),
            'tracks' => $user->tracks()->get()->toArray(),
            'playlists' => $user->playlists()->get()->toArray(),
            'followers' => $user->followers()->get()->toArray(),
            'following' => $user->following()->get()->toArray(),
            'listening_history' => \App\Models\Listener::where('user_id', $user->id)->get()->toArray(),
        ];

        $filename = "data-export-{$user->id}-" . now()->format('Y-m-d') . ".json";

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }

    /**
     * Referral dashboard
     */
    public function referrals()
    {
        $user = Auth::user();

        // Get or create referral code
        $referralCode = $user->referral_code;
        if (!$referralCode) {
            $referralCode = Referral::generateCode();
            $user->update(['referral_code' => $referralCode]);
        }

        $referrals = Referral::where('referrer_id', $user->id)
            ->with('referred')
            ->latest()
            ->get();

        $stats = [
            'total_referrals' => $referrals->count(),
            'completed' => $referrals->where('status', 'completed')->count(),
            'pending' => $referrals->where('status', 'pending')->count(),
            'total_rewards' => $referrals->sum('reward_amount'),
            'unclaimed_rewards' => $referrals->where('reward_claimed', false)->sum('reward_amount'),
        ];

        $referralUrl = url('/register?ref=' . $referralCode);

        return view('user.referrals.index', compact('referralCode', 'referrals', 'stats', 'referralUrl'));
    }

    /**
     * Invite via email
     */
    public function sendReferralInvite(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $user = Auth::user();
        $referralCode = $user->referral_code ?? Referral::generateCode();

        // Create referral record
        Referral::create([
            'referrer_id' => $user->id,
            'referral_code' => $referralCode,
            'email' => $validated['email'],
            'status' => 'pending',
        ]);

        // Send invitation email
        // Mail::to($validated['email'])->send(new ReferralInviteMail($user, $referralCode));

        return back()->with('success', 'Invitation sent!');
    }
}
