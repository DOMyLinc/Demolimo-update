<?php

namespace App\Services;

use App\Models\User;
use App\Models\FeaturePermission;
use App\Models\PlatformSetting;

class PermissionService
{
    /**
     * Check if a user has access to a specific feature
     */
    public function hasFeatureAccess(User $user, string $featureName): bool
    {
        // Check if feature is globally disabled
        if (!PlatformSetting::get("feature_{$featureName}_enabled", true)) {
            return false;
        }

        $feature = FeaturePermission::where('feature_name', $featureName)
            ->where('is_active', true)
            ->first();

        if (!$feature) {
            return true; // Feature doesn't exist in permissions, allow by default
        }

        // Check for custom user access
        $customAccess = $user->featureAccess()
            ->where('feature_permission_id', $feature->id)
            ->first();

        if ($customAccess) {
            return $customAccess->is_granted;
        }

        // Check plan-based access
        $userPlan = $this->getUserPlan($user);
        return $feature->isAvailableForPlan($userPlan);
    }

    /**
     * Get user's current plan
     */
    public function getUserPlan(User $user): string
    {
        $subscription = $user->subscription;

        if (!$subscription || !$subscription->is_active) {
            return 'free';
        }

        return $subscription->plan->slug ?? 'free';
    }

    /**
     * Get feature limit for a user
     */
    public function getFeatureLimit(User $user, string $featureName, string $limitKey, $default = null)
    {
        $feature = FeaturePermission::where('feature_name', $featureName)->first();

        if (!$feature) {
            return $default;
        }

        // Check for custom user limits
        $customAccess = $user->featureAccess()
            ->where('feature_permission_id', $feature->id)
            ->first();

        if ($customAccess && isset($customAccess->custom_limits[$limitKey])) {
            return $customAccess->custom_limits[$limitKey];
        }

        // Check plan-based limits
        $limits = $feature->limits ?? [];
        $userPlan = $this->getUserPlan($user);

        return $limits[$userPlan][$limitKey] ?? $default;
    }

    /**
     * Check if user has reached a feature limit
     */
    public function hasReachedLimit(User $user, string $featureName, string $limitKey, int $currentUsage): bool
    {
        $limit = $this->getFeatureLimit($user, $featureName, $limitKey);

        if ($limit === null || $limit === -1) {
            return false; // Unlimited
        }

        return $currentUsage >= $limit;
    }

    /**
     * Get all available features for a user
     */
    public function getAvailableFeatures(User $user): array
    {
        $allFeatures = FeaturePermission::where('is_active', true)->get();
        $available = [];

        foreach ($allFeatures as $feature) {
            if ($this->hasFeatureAccess($user, $feature->feature_name)) {
                $available[] = [
                    'name' => $feature->feature_name,
                    'display_name' => $feature->display_name,
                    'limits' => $this->getFeatureLimits($user, $feature->feature_name),
                ];
            }
        }

        return $available;
    }

    /**
     * Get all limits for a specific feature
     */
    protected function getFeatureLimits(User $user, string $featureName): array
    {
        $feature = FeaturePermission::where('feature_name', $featureName)->first();

        if (!$feature) {
            return [];
        }

        // Check for custom user limits
        $customAccess = $user->featureAccess()
            ->where('feature_permission_id', $feature->id)
            ->first();

        if ($customAccess && $customAccess->custom_limits) {
            return $customAccess->custom_limits;
        }

        // Return plan-based limits
        $userPlan = $this->getUserPlan($user);
        $limits = $feature->limits ?? [];

        return $limits[$userPlan] ?? [];
    }

    /**
     * Require feature access or throw exception
     */
    public function requireFeatureAccess(User $user, string $featureName): void
    {
        if (!$this->hasFeatureAccess($user, $featureName)) {
            $feature = FeaturePermission::where('feature_name', $featureName)->first();
            $displayName = $feature ? $feature->display_name : $featureName;

            throw new \Exception("You don't have access to {$displayName}. Please upgrade your plan.");
        }
    }
}
