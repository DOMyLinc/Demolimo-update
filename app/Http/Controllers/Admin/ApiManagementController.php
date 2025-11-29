<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiApplication;
use App\Models\User;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiManagementController extends Controller
{
    /**
     * API applications overview
     */
    public function applications()
    {
        $applications = ApiApplication::with('user')
            ->latest()
            ->paginate(20);

        $stats = [
            'total_apps' => ApiApplication::count(),
            'approved_apps' => ApiApplication::where('is_approved', true)->count(),
            'pending_approval' => ApiApplication::where('is_approved', false)->count(),
            'active_apps' => ApiApplication::where('is_active', true)->count(),
            'total_api_calls_today' => $this->getApiCallsToday(),
        ];

        return view('admin.api.applications', compact('applications', 'stats'));
    }

    /**
     * View application details
     */
    public function showApplication(ApiApplication $app)
    {
        $app->load('user');

        $apiLogs = DB::table('api_logs')
            ->where('application_id', $app->id)
            ->latest()
            ->limit(100)
            ->get();

        $stats = [
            'total_calls' => DB::table('api_logs')->where('application_id', $app->id)->count(),
            'calls_today' => DB::table('api_logs')
                ->where('application_id', $app->id)
                ->whereDate('created_at', today())
                ->count(),
            'success_rate' => $this->getSuccessRate($app->id),
        ];

        return view('admin.api.application-details', compact('app', 'apiLogs', 'stats'));
    }

    /**
     * Approve application
     */
    public function approveApplication(ApiApplication $app)
    {
        $app->update(['is_approved' => true]);

        // Notify developer
        // Mail::to($app->user->email)->send(new ApiApplicationApprovedMail($app));

        return back()->with('success', 'Application approved!');
    }

    /**
     * Reject application
     */
    public function rejectApplication(Request $request, ApiApplication $app)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $app->update(['is_approved' => false, 'is_active' => false]);

        // Notify developer
        // Mail::to($app->user->email)->send(new ApiApplicationRejectedMail($app, $validated['reason']));

        return back()->with('success', 'Application rejected!');
    }

    /**
     * Suspend application
     */
    public function suspendApplication(ApiApplication $app)
    {
        $app->update(['is_active' => false]);

        // Revoke all active tokens
        $app->tokens()->delete();

        return back()->with('success', 'Application suspended!');
    }

    /**
     * Activate application
     */
    public function activateApplication(ApiApplication $app)
    {
        $app->update(['is_active' => true]);
        return back()->with('success', 'Application activated!');
    }

    /**
     * Update rate limit
     */
    public function updateRateLimit(Request $request, ApiApplication $app)
    {
        $validated = $request->validate([
            'rate_limit' => 'required|integer|min:100|max:10000',
        ]);

        $app->update(['rate_limit' => $validated['rate_limit']]);

        return back()->with('success', 'Rate limit updated!');
    }

    /**
     * User API access management
     */
    public function userAccess()
    {
        $users = User::select('id', 'name', 'email', 'role', 'api_access_enabled')
            ->paginate(50);

        $stats = [
            'total_users' => User::count(),
            'api_enabled' => User::where('api_access_enabled', true)->count(),
            'api_disabled' => User::where('api_access_enabled', false)->count(),
        ];

        return view('admin.api.user-access', compact('users', 'stats'));
    }

    /**
     * Enable API access for user
     */
    public function enableUserApiAccess(User $user)
    {
        $user->update(['api_access_enabled' => true]);

        // Notify user
        // Mail::to($user->email)->send(new ApiAccessEnabledMail());

        return back()->with('success', "API access enabled for {$user->name}!");
    }

    /**
     * Disable API access for user
     */
    public function disableUserApiAccess(User $user)
    {
        $user->update(['api_access_enabled' => false]);

        // Suspend all their applications
        ApiApplication::where('user_id', $user->id)
            ->update(['is_active' => false]);

        // Revoke all tokens
        DB::table('api_tokens')
            ->where('user_id', $user->id)
            ->delete();

        return back()->with('success', "API access disabled for {$user->name}!");
    }

    /**
     * Bulk enable API access
     */
    public function bulkEnableApiAccess(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        User::whereIn('id', $validated['user_ids'])
            ->update(['api_access_enabled' => true]);

        return back()->with('success', 'API access enabled for selected users!');
    }

    /**
     * Bulk disable API access
     */
    public function bulkDisableApiAccess(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        User::whereIn('id', $validated['user_ids'])
            ->update(['api_access_enabled' => false]);

        // Suspend applications
        ApiApplication::whereIn('user_id', $validated['user_ids'])
            ->update(['is_active' => false]);

        return back()->with('success', 'API access disabled for selected users!');
    }

    /**
     * API settings
     */
    public function settings()
    {
        $settings = [
            'api_enabled' => FeatureFlag::isEnabled('enable_api'),
            'public_docs_enabled' => FeatureFlag::isEnabled('enable_public_api_docs'),
            'require_approval' => config('api.require_approval', false),
            'default_rate_limit' => config('api.default_rate_limit', 1000),
            'max_rate_limit' => config('api.max_rate_limit', 10000),
        ];

        return view('admin.api.settings', compact('settings'));
    }

    /**
     * Update API settings
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'api_enabled' => 'boolean',
            'public_docs_enabled' => 'boolean',
            'require_approval' => 'boolean',
            'default_rate_limit' => 'integer|min:100|max:10000',
            'max_rate_limit' => 'integer|min:1000|max:100000',
        ]);

        // Update feature flags
        FeatureFlag::where('key', 'enable_api')
            ->update(['is_enabled' => $validated['api_enabled'] ?? false]);

        FeatureFlag::updateOrCreate(
            ['key' => 'enable_public_api_docs'],
            [
                'is_enabled' => $validated['public_docs_enabled'] ?? false,
                'name' => 'Public API Documentation',
                'description' => 'Allow public access to API documentation',
            ]
        );

        // Update config (would need to write to config file or database)
        // For now, store in feature flags
        FeatureFlag::updateOrCreate(
            ['key' => 'api_require_approval'],
            ['value' => $validated['require_approval'] ?? false, 'type' => 'boolean']
        );

        FeatureFlag::updateOrCreate(
            ['key' => 'api_default_rate_limit'],
            ['value' => $validated['default_rate_limit'], 'type' => 'integer']
        );

        return back()->with('success', 'API settings updated!');
    }

    /**
     * API analytics
     */
    public function analytics()
    {
        $stats = [
            'total_calls_today' => $this->getApiCallsToday(),
            'total_calls_week' => $this->getApiCallsWeek(),
            'total_calls_month' => $this->getApiCallsMonth(),
            'average_response_time' => $this->getAverageResponseTime(),
            'error_rate' => $this->getErrorRate(),
        ];

        $topApplications = DB::table('api_logs')
            ->select('application_id', DB::raw('COUNT(*) as calls'))
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('application_id')
            ->orderByDesc('calls')
            ->limit(10)
            ->get();

        $callsByDay = DB::table('api_logs')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as calls'))
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.api.analytics', compact('stats', 'topApplications', 'callsByDay'));
    }

    /**
     * Get API calls today
     */
    protected function getApiCallsToday()
    {
        return DB::table('api_logs')
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Get API calls this week
     */
    protected function getApiCallsWeek()
    {
        return DB::table('api_logs')
            ->whereDate('created_at', '>=', now()->subWeek())
            ->count();
    }

    /**
     * Get API calls this month
     */
    protected function getApiCallsMonth()
    {
        return DB::table('api_logs')
            ->whereDate('created_at', '>=', now()->subMonth())
            ->count();
    }

    /**
     * Get average response time
     */
    protected function getAverageResponseTime()
    {
        return DB::table('api_logs')
            ->whereDate('created_at', '>=', now()->subDay())
            ->avg('response_time') ?? 0;
    }

    /**
     * Get error rate
     */
    protected function getErrorRate()
    {
        $total = DB::table('api_logs')
            ->whereDate('created_at', '>=', now()->subDay())
            ->count();

        if ($total === 0)
            return 0;

        $errors = DB::table('api_logs')
            ->whereDate('created_at', '>=', now()->subDay())
            ->where('status_code', '>=', 400)
            ->count();

        return round(($errors / $total) * 100, 2);
    }

    /**
     * Get success rate for application
     */
    protected function getSuccessRate($appId)
    {
        $total = DB::table('api_logs')
            ->where('application_id', $appId)
            ->count();

        if ($total === 0)
            return 100;

        $success = DB::table('api_logs')
            ->where('application_id', $appId)
            ->where('status_code', '<', 400)
            ->count();

        return round(($success / $total) * 100, 2);
    }
}
