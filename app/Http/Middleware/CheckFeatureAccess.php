<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\FeatureAccess;

class CheckFeatureAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $featureKey)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Please login to access this feature.');
        }

        // Check if user has access to the feature
        if (!FeatureAccess::userHasAccess($user, $featureKey)) {
            $feature = FeatureAccess::where('feature_key', $featureKey)->first();

            $betaBadge = $feature && $feature->is_beta ? ' (BETA)' : '';

            return redirect()->back()
                ->with('error', "This feature{$betaBadge} requires a Pro subscription. Please upgrade to continue.");
        }

        // Check if user has reached their limit
        if (FeatureAccess::userHasReachedLimit($user, $featureKey)) {
            $feature = FeatureAccess::where('feature_key', $featureKey)->first();
            $limit = $user->isPro() ? $feature->pro_user_limit : $feature->free_user_limit;

            return redirect()->back()
                ->with('error', "You've reached your limit of {$limit} for this feature. Upgrade to Pro for more!");
        }

        return $next($request);
    }
}
