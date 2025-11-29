<?php

namespace App\Services;

use App\Models\User;
use App\Models\Track;
use App\Models\UserRecommendation;
use App\Models\Like;
use App\Models\Listener;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecommendationEngine
{
    /**
     * Train the recommendation engine for all users.
     * This is intended to be run via a scheduled job.
     */
    public function trainAll()
    {
        $users = User::where('is_active', true)->get();
        foreach ($users as $user) {
            try {
                $this->generateRecommendationsForUser($user->id);
            } catch (\Exception $e) {
                Log::error("Failed to generate recommendations for user {$user->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Generate recommendations for a specific user.
     */
    public function generateRecommendationsForUser($userId)
    {
        // 1. Get User's Top Tags/Genres based on Likes and Plays
        $topTags = $this->getUserTopTags($userId);

        // 2. Content-Based Filtering: Find tracks with matching tags
        $contentBasedTracks = $this->getContentBasedRecommendations($userId, $topTags);

        // 3. Collaborative Filtering: Find tracks liked by similar users
        $collaborativeTracks = $this->getCollaborativeRecommendations($userId);

        // 4. Merge and Score
        $recommendations = $this->mergeAndScore($contentBasedTracks, $collaborativeTracks);

        // 5. Store in Database
        $this->storeRecommendations($userId, $recommendations);
    }

    private function getUserTopTags($userId)
    {
        // Get tags from liked tracks
        $likedTrackIds = Like::where('user_id', $userId)
            ->where('likeable_type', Track::class)
            ->pluck('likeable_id');

        $tags = [];
        $tracks = Track::whereIn('id', $likedTrackIds)->get();

        foreach ($tracks as $track) {
            if ($track->tags) {
                foreach ($track->tags as $tag) {
                    if (!isset($tags[$tag])) {
                        $tags[$tag] = 0;
                    }
                    $tags[$tag]++;
                }
            }
        }

        // Sort by frequency
        arsort($tags);
        return array_slice(array_keys($tags), 0, 5); // Top 5 tags
    }

    private function getContentBasedRecommendations($userId, $topTags)
    {
        if (empty($topTags)) {
            return [];
        }

        // Find tracks that have at least one of the top tags
        // This is a simplified implementation. For better performance with JSON tags, 
        // we might need a more robust search or normalized tags table.
        // For now, we'll fetch recent tracks and filter in PHP to avoid complex JSON queries on shared hosting.

        $candidates = Track::where('user_id', '!=', $userId) // Don't recommend own tracks
            ->where('is_public', true)
            ->latest()
            ->take(200) // Limit candidate pool for performance
            ->get();

        $scored = [];
        foreach ($candidates as $track) {
            $score = 0;
            if ($track->tags) {
                foreach ($track->tags as $tag) {
                    if (in_array($tag, $topTags)) {
                        $score += 1;
                    }
                }
            }
            if ($score > 0) {
                $scored[$track->id] = [
                    'score' => $score,
                    'reason' => ['type' => 'content_match', 'tags' => array_intersect($track->tags ?? [], $topTags)]
                ];
            }
        }

        return $scored;
    }

    private function getCollaborativeRecommendations($userId)
    {
        // Find users who liked the same tracks as the current user
        $userLikedTrackIds = Like::where('user_id', $userId)
            ->where('likeable_type', Track::class)
            ->pluck('likeable_id');

        if ($userLikedTrackIds->isEmpty()) {
            return [];
        }

        // Find other users who liked these tracks
        $similarUserIds = Like::whereIn('likeable_id', $userLikedTrackIds)
            ->where('likeable_type', Track::class)
            ->where('user_id', '!=', $userId)
            ->groupBy('user_id')
            ->pluck('user_id');

        if ($similarUserIds->isEmpty()) {
            return [];
        }

        // Get tracks liked by these similar users, excluding tracks already liked by current user
        $suggestedTracks = Like::whereIn('user_id', $similarUserIds)
            ->where('likeable_type', Track::class)
            ->whereNotIn('likeable_id', $userLikedTrackIds)
            ->select('likeable_id', DB::raw('count(*) as popularity'))
            ->groupBy('likeable_id')
            ->orderByDesc('popularity')
            ->take(50)
            ->get();

        $scored = [];
        foreach ($suggestedTracks as $suggestion) {
            $scored[$suggestion->likeable_id] = [
                'score' => $suggestion->popularity * 1.5, // Weight collaborative higher
                'reason' => ['type' => 'similar_users', 'count' => $suggestion->popularity]
            ];
        }

        return $scored;
    }

    private function mergeAndScore($contentBased, $collaborative)
    {
        $final = [];

        // Merge Content Based
        foreach ($contentBased as $id => $data) {
            $final[$id] = $data;
        }

        // Merge Collaborative (boost score if exists in both)
        foreach ($collaborative as $id => $data) {
            if (isset($final[$id])) {
                $final[$id]['score'] += $data['score'];
                $final[$id]['reason']['collaborative_boost'] = true;
            } else {
                $final[$id] = $data;
            }
        }

        // Sort by score
        uasort($final, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return array_slice($final, 0, 20, true); // Top 20
    }

    private function storeRecommendations($userId, $recommendations)
    {
        // Clear old recommendations
        UserRecommendation::where('user_id', $userId)->delete();

        $data = [];
        foreach ($recommendations as $trackId => $info) {
            $data[] = [
                'user_id' => $userId,
                'track_id' => $trackId,
                'score' => $info['score'],
                'reason' => json_encode($info['reason']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($data)) {
            UserRecommendation::insert($data);
        }
    }
}
