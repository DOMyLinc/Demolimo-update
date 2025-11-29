<?php

if (!function_exists('feature_enabled')) {
    /**
     * Check if a feature flag is enabled
     *
     * @param string $key Feature flag key
     * @return bool
     */
    function feature_enabled($key)
    {
        return \App\Models\FeatureFlag::isEnabled($key);
    }
}
