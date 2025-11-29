<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    public function index()
    {
        $features = FeatureFlag::orderBy('category')->orderBy('name')->get();

        $categories = [
            'core' => $features->where('category', FeatureFlag::CATEGORY_CORE),
            'social' => $features->where('category', FeatureFlag::CATEGORY_SOCIAL),
            'monetization' => $features->where('category', FeatureFlag::CATEGORY_MONETIZATION),
            'content' => $features->where('category', FeatureFlag::CATEGORY_CONTENT),
            'analytics' => $features->where('category', FeatureFlag::CATEGORY_ANALYTICS),
            'marketing' => $features->where('category', FeatureFlag::CATEGORY_MARKETING),
        ];

        $stats = [
            'total_features' => FeatureFlag::count(),
            'enabled_features' => FeatureFlag::where('is_enabled', true)->count(),
            'disabled_features' => FeatureFlag::where('is_enabled', false)->count(),
        ];

        return view('admin.features.index', compact('categories', 'stats'));
    }

    public function toggle(FeatureFlag $feature)
    {
        $feature->update(['is_enabled' => !$feature->is_enabled]);

        return response()->json([
            'success' => true,
            'is_enabled' => $feature->is_enabled,
            'message' => $feature->is_enabled ? 'Feature enabled' : 'Feature disabled',
        ]);
    }

    public function bulkToggle(Request $request)
    {
        $validated = $request->validate([
            'feature_ids' => 'required|array',
            'feature_ids.*' => 'exists:feature_flags,id',
            'action' => 'required|in:enable,disable',
        ]);

        $isEnabled = $validated['action'] === 'enable';

        FeatureFlag::whereIn('id', $validated['feature_ids'])
            ->update(['is_enabled' => $isEnabled]);

        return response()->json([
            'success' => true,
            'message' => count($validated['feature_ids']) . ' features ' . $validated['action'] . 'd successfully',
        ]);
    }

    public function update(Request $request, FeatureFlag $feature)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
        ]);

        $feature->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Feature updated successfully',
        ]);
    }

    public function seed()
    {
        $features = [
            // Core Features
            ['key' => 'user_registration', 'name' => 'User Registration', 'category' => 'core', 'description' => 'Allow new users to register', 'is_enabled' => true],
            ['key' => 'track_upload', 'name' => 'Track Upload', 'category' => 'core', 'description' => 'Allow users to upload tracks', 'is_enabled' => true],
            ['key' => 'album_creation', 'name' => 'Album Creation', 'category' => 'core', 'description' => 'Allow users to create albums', 'is_enabled' => true],
            ['key' => 'playlist_creation', 'name' => 'Playlist Creation', 'category' => 'core', 'description' => 'Allow users to create playlists', 'is_enabled' => true],
            ['key' => 'search', 'name' => 'Search', 'category' => 'core', 'description' => 'Enable search functionality', 'is_enabled' => true],
            ['key' => 'download', 'name' => 'Track Downloads', 'category' => 'core', 'description' => 'Allow track downloads', 'is_enabled' => true],

            // Social Features
            ['key' => 'comments', 'name' => 'Comments', 'category' => 'social', 'description' => 'Allow comments on tracks', 'is_enabled' => true],
            ['key' => 'reactions', 'name' => 'Reactions', 'category' => 'social', 'description' => 'Enable emoji reactions', 'is_enabled' => true],
            ['key' => 'follow_system', 'name' => 'Follow System', 'category' => 'social', 'description' => 'Allow users to follow each other', 'is_enabled' => true],
            ['key' => 'activity_feed', 'name' => 'Activity Feed', 'category' => 'social', 'description' => 'Show activity feed', 'is_enabled' => true],
            ['key' => 'direct_messaging', 'name' => 'Direct Messaging', 'category' => 'social', 'description' => 'Enable private messaging', 'is_enabled' => false],
            ['key' => 'posts', 'name' => 'Posts', 'category' => 'social', 'description' => 'Allow users to create posts', 'is_enabled' => true],

            // Monetization Features
            ['key' => 'subscriptions', 'name' => 'Subscriptions', 'category' => 'monetization', 'description' => 'Enable subscription plans', 'is_enabled' => true],
            ['key' => 'track_sales', 'name' => 'Track Sales', 'category' => 'monetization', 'description' => 'Allow selling tracks', 'is_enabled' => true],
            ['key' => 'album_sales', 'name' => 'Album Sales', 'category' => 'monetization', 'description' => 'Allow selling albums', 'is_enabled' => true],
            ['key' => 'tips', 'name' => 'Tips/Donations', 'category' => 'monetization', 'description' => 'Allow tipping artists', 'is_enabled' => true],
            ['key' => 'points_system', 'name' => 'Points System', 'category' => 'monetization', 'description' => 'Enable points/rewards', 'is_enabled' => true],

            // Content Features
            ['key' => 'song_battles', 'name' => 'Song Battles', 'category' => 'content', 'description' => 'Enable song battle competitions', 'is_enabled' => true],
            ['key' => 'events', 'name' => 'Events', 'category' => 'content', 'description' => 'Allow event creation', 'is_enabled' => true],
            ['key' => 'studio', 'name' => 'Online Studio', 'category' => 'content', 'description' => 'Enable online DAW studio', 'is_enabled' => true],
            ['key' => 'ai_music_generation', 'name' => 'AI Music Generation', 'category' => 'content', 'description' => 'Enable AI music creation', 'is_enabled' => false],
            ['key' => 'collaboration', 'name' => 'Collaboration', 'category' => 'content', 'description' => 'Enable track collaboration', 'is_enabled' => false],
            ['key' => 'zipcode_panel', 'name' => 'Zipcode Panel', 'category' => 'content', 'description' => 'Enable zipcode communities feature', 'is_enabled' => true],
            ['key' => 'music_competitions', 'name' => 'Music Competitions', 'category' => 'content', 'description' => 'Enable music competition page', 'is_enabled' => true],

            // Analytics Features
            ['key' => 'user_analytics', 'name' => 'User Analytics', 'category' => 'analytics', 'description' => 'Show analytics to users', 'is_enabled' => true],
            ['key' => 'admin_analytics', 'name' => 'Admin Analytics', 'category' => 'analytics', 'description' => 'Advanced admin analytics', 'is_enabled' => true],
            ['key' => 'listener_insights', 'name' => 'Listener Insights', 'category' => 'analytics', 'description' => 'Detailed listener data', 'is_enabled' => true],

            // Marketing Features
            ['key' => 'email_campaigns', 'name' => 'Email Campaigns', 'category' => 'marketing', 'description' => 'Send email campaigns', 'is_enabled' => false],
            ['key' => 'push_notifications', 'name' => 'Push Notifications', 'category' => 'marketing', 'description' => 'Send push notifications', 'is_enabled' => false],
            ['key' => 'promotional_banners', 'name' => 'Promotional Banners', 'category' => 'marketing', 'description' => 'Show promotional banners', 'is_enabled' => false],

            // Additional System Features
            ['key' => 'direct_messaging', 'name' => 'Direct Messaging', 'category' => 'social', 'description' => 'Enable private messaging between users', 'is_enabled' => true],
            ['key' => 'events_system', 'name' => 'Events System', 'category' => 'content', 'description' => 'Enable event creation and management', 'is_enabled' => true],
            ['key' => 'song_battles_system', 'name' => 'Song Battles', 'category' => 'content', 'description' => 'Enable song battle competitions', 'is_enabled' => true],
            ['key' => 'blockchain_valuation', 'name' => 'Blockchain Valuation', 'category' => 'monetization', 'description' => 'Enable blockchain-based track valuation', 'is_enabled' => false],
            ['key' => 'plugin_system', 'name' => 'Plugin System', 'category' => 'core', 'description' => 'Enable plugin installation and management', 'is_enabled' => false],
        ];

        foreach ($features as $feature) {
            FeatureFlag::updateOrCreate(
                ['key' => $feature['key']],
                $feature
            );
        }

        return redirect()->route('admin.features.index')
            ->with('success', 'Feature flags seeded successfully!');
    }
}
