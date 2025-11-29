<?php

namespace App\Services;

use App\Models\Track;
use App\Models\Album;
use App\Models\User;
use App\Models\Playlist;
use App\Models\SearchHistory;
use Illuminate\Support\Facades\DB;

class AdvancedSearchService
{
    /**
     * Perform advanced search with filters
     */
    public function search($query, array $filters = [], $userId = null)
    {
        $results = [
            'tracks' => $this->searchTracks($query, $filters),
            'albums' => $this->searchAlbums($query, $filters),
            'artists' => $this->searchArtists($query, $filters),
            'playlists' => $this->searchPlaylists($query, $filters),
        ];

        // Log search
        $this->logSearch($query, $filters, $results, $userId);

        return $results;
    }

    /**
     * Search tracks with advanced filters
     */
    protected function searchTracks($query, array $filters = [])
    {
        $tracksQuery = Track::query();

        // Text search
        if ($query) {
            $tracksQuery->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhereHas('user', function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%");
                    });
            });
        }

        // Genre filter
        if (isset($filters['genre'])) {
            $tracksQuery->where('genre', $filters['genre']);
        }

        // Year filter
        if (isset($filters['year'])) {
            $tracksQuery->whereYear('created_at', $filters['year']);
        }

        // BPM range
        if (isset($filters['bpm_min'])) {
            $tracksQuery->where('bpm', '>=', $filters['bpm_min']);
        }
        if (isset($filters['bpm_max'])) {
            $tracksQuery->where('bpm', '<=', $filters['bpm_max']);
        }

        // Key filter
        if (isset($filters['key'])) {
            $tracksQuery->where('key', $filters['key']);
        }

        // Mood tags
        if (isset($filters['mood'])) {
            $tracksQuery->whereJsonContains('mood_tags', $filters['mood']);
        }

        // Duration range
        if (isset($filters['duration_min'])) {
            $tracksQuery->where('duration', '>=', $filters['duration_min']);
        }
        if (isset($filters['duration_max'])) {
            $tracksQuery->where('duration', '<=', $filters['duration_max']);
        }

        // Popularity filter
        if (isset($filters['min_plays'])) {
            $tracksQuery->where('plays', '>=', $filters['min_plays']);
        }

        // Has lyrics
        if (isset($filters['has_lyrics']) && $filters['has_lyrics']) {
            $tracksQuery->whereHas('lyrics');
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'relevance';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        switch ($sortBy) {
            case 'plays':
                $tracksQuery->orderBy('plays', $sortDirection);
                break;
            case 'likes':
                $tracksQuery->orderBy('likes', $sortDirection);
                break;
            case 'date':
                $tracksQuery->orderBy('created_at', $sortDirection);
                break;
            case 'title':
                $tracksQuery->orderBy('title', $sortDirection);
                break;
            default:
                // Relevance - order by exact match first, then partial
                if ($query) {
                    $tracksQuery->orderByRaw("CASE WHEN title = ? THEN 0 ELSE 1 END", [$query])
                        ->orderBy('plays', 'desc');
                }
        }

        return $tracksQuery->with('user')->limit(20)->get();
    }

    /**
     * Search albums
     */
    protected function searchAlbums($query, array $filters = [])
    {
        $albumsQuery = Album::query();

        if ($query) {
            $albumsQuery->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%")
                    ->orWhereHas('user', function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%");
                    });
            });
        }

        if (isset($filters['year'])) {
            $albumsQuery->whereYear('created_at', $filters['year']);
        }

        return $albumsQuery->with('user')->limit(10)->get();
    }

    /**
     * Search artists
     */
    protected function searchArtists($query, array $filters = [])
    {
        $artistsQuery = User::query();

        if ($query) {
            $artistsQuery->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('username', 'LIKE', "%{$query}%")
                    ->orWhere('bio', 'LIKE', "%{$query}%");
            });
        }

        // Only verified artists
        if (isset($filters['verified']) && $filters['verified']) {
            $artistsQuery->where('is_verified', true);
        }

        return $artistsQuery->withCount('tracks', 'followers')
            ->orderBy('followers_count', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Search playlists
     */
    protected function searchPlaylists($query, array $filters = [])
    {
        $playlistsQuery = Playlist::query();

        if ($query) {
            $playlistsQuery->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            });
        }

        // Public only by default
        if (!isset($filters['include_private'])) {
            $playlistsQuery->where('is_public', true);
        }

        return $playlistsQuery->with('user')
            ->withCount('tracks')
            ->orderBy('tracks_count', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get search suggestions
     */
    public function getSuggestions($query, $limit = 5)
    {
        // Get popular searches
        $popularSearches = SearchHistory::select('query', DB::raw('COUNT(*) as count'))
            ->where('query', 'LIKE', "%{$query}%")
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('query');

        // Get matching track titles
        $trackTitles = Track::where('title', 'LIKE', "%{$query}%")
            ->orderBy('plays', 'desc')
            ->limit($limit)
            ->pluck('title');

        // Get matching artist names
        $artistNames = User::where('name', 'LIKE', "%{$query}%")
            ->orderBy('followers_count', 'desc')
            ->limit($limit)
            ->pluck('name');

        return collect($popularSearches)
            ->merge($trackTitles)
            ->merge($artistNames)
            ->unique()
            ->take($limit)
            ->values();
    }

    /**
     * Log search for analytics
     */
    protected function logSearch($query, $filters, $results, $userId)
    {
        $totalResults = collect($results)->sum(function ($items) {
            return is_countable($items) ? count($items) : 0;
        });

        SearchHistory::create([
            'user_id' => $userId,
            'query' => $query,
            'filters' => $filters,
            'results_count' => $totalResults,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Get trending searches
     */
    public function getTrendingSearches($limit = 10)
    {
        return SearchHistory::select('query', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }
}
