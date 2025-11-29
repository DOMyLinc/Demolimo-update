<?php

namespace App\Services;

use App\Models\Playlist;
use App\Models\SmartPlaylist;
use App\Models\Track;
use Illuminate\Support\Facades\DB;

class SmartPlaylistService
{
    /**
     * Create a smart playlist
     */
    public function createSmartPlaylist($playlistId, array $rules, $maxTracks = 50, $sortBy = 'created_at')
    {
        $smartPlaylist = SmartPlaylist::create([
            'playlist_id' => $playlistId,
            'rules' => $rules,
            'max_tracks' => $maxTracks,
            'sort_by' => $sortBy,
            'auto_update' => true,
        ]);

        // Initial population
        $this->updatePlaylist($smartPlaylist);

        return $smartPlaylist;
    }

    /**
     * Update smart playlist tracks
     */
    public function updatePlaylist(SmartPlaylist $smartPlaylist)
    {
        $tracks = $this->findMatchingTracks($smartPlaylist->rules, $smartPlaylist->max_tracks, $smartPlaylist->sort_by);

        $playlist = $smartPlaylist->playlist;

        // Clear existing tracks
        $playlist->tracks()->detach();

        // Add new tracks
        $playlist->tracks()->attach($tracks->pluck('id'));

        $smartPlaylist->update(['last_updated_at' => now()]);

        return $playlist;
    }

    /**
     * Find tracks matching rules
     */
    protected function findMatchingTracks(array $rules, $maxTracks, $sortBy)
    {
        $query = Track::query();

        foreach ($rules as $rule) {
            $field = $rule['field'];
            $operator = $rule['operator'];
            $value = $rule['value'];

            switch ($field) {
                case 'genre':
                    if ($operator === 'is') {
                        $query->where('genre', $value);
                    } elseif ($operator === 'is_not') {
                        $query->where('genre', '!=', $value);
                    } elseif ($operator === 'in') {
                        $query->whereIn('genre', $value);
                    }
                    break;

                case 'bpm':
                    if ($operator === 'greater_than') {
                        $query->where('bpm', '>', $value);
                    } elseif ($operator === 'less_than') {
                        $query->where('bpm', '<', $value);
                    } elseif ($operator === 'between') {
                        $query->whereBetween('bpm', $value);
                    }
                    break;

                case 'key':
                    $query->where('key', $value);
                    break;

                case 'mood':
                    $query->whereJsonContains('mood_tags', $value);
                    break;

                case 'year':
                    if ($operator === 'is') {
                        $query->whereYear('created_at', $value);
                    } elseif ($operator === 'after') {
                        $query->whereYear('created_at', '>', $value);
                    } elseif ($operator === 'before') {
                        $query->whereYear('created_at', '<', $value);
                    }
                    break;

                case 'plays':
                    if ($operator === 'greater_than') {
                        $query->where('plays', '>', $value);
                    } elseif ($operator === 'less_than') {
                        $query->where('plays', '<', $value);
                    }
                    break;

                case 'likes':
                    if ($operator === 'greater_than') {
                        $query->where('likes', '>', $value);
                    }
                    break;

                case 'duration':
                    if ($operator === 'greater_than') {
                        $query->where('duration', '>', $value);
                    } elseif ($operator === 'less_than') {
                        $query->where('duration', '<', $value);
                    }
                    break;

                case 'artist':
                    $query->where('user_id', $value);
                    break;

                case 'has_lyrics':
                    if ($value) {
                        $query->whereHas('lyrics');
                    } else {
                        $query->whereDoesntHave('lyrics');
                    }
                    break;

                case 'added_date':
                    if ($operator === 'last_days') {
                        $query->where('created_at', '>=', now()->subDays($value));
                    } elseif ($operator === 'last_weeks') {
                        $query->where('created_at', '>=', now()->subWeeks($value));
                    } elseif ($operator === 'last_months') {
                        $query->where('created_at', '>=', now()->subMonths($value));
                    }
                    break;
            }
        }

        // Apply sorting
        switch ($sortBy) {
            case 'plays':
                $query->orderBy('plays', 'desc');
                break;
            case 'likes':
                $query->orderBy('likes', 'desc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            case 'random':
                $query->inRandomOrder();
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        return $query->limit($maxTracks)->get();
    }

    /**
     * Update all auto-updating smart playlists
     */
    public function updateAllSmartPlaylists()
    {
        $smartPlaylists = SmartPlaylist::where('auto_update', true)
            ->where(function ($q) {
                $q->whereNull('last_updated_at')
                    ->orWhere('last_updated_at', '<', now()->subHours(6));
            })
            ->get();

        foreach ($smartPlaylists as $smartPlaylist) {
            $this->updatePlaylist($smartPlaylist);
        }

        return $smartPlaylists->count();
    }

    /**
     * Get available rule fields
     */
    public function getAvailableFields()
    {
        return [
            'genre' => ['type' => 'select', 'operators' => ['is', 'is_not', 'in']],
            'bpm' => ['type' => 'number', 'operators' => ['greater_than', 'less_than', 'between']],
            'key' => ['type' => 'select', 'operators' => ['is']],
            'mood' => ['type' => 'multiselect', 'operators' => ['contains']],
            'year' => ['type' => 'number', 'operators' => ['is', 'after', 'before']],
            'plays' => ['type' => 'number', 'operators' => ['greater_than', 'less_than']],
            'likes' => ['type' => 'number', 'operators' => ['greater_than', 'less_than']],
            'duration' => ['type' => 'number', 'operators' => ['greater_than', 'less_than']],
            'artist' => ['type' => 'select', 'operators' => ['is']],
            'has_lyrics' => ['type' => 'boolean', 'operators' => ['is']],
            'added_date' => ['type' => 'relative', 'operators' => ['last_days', 'last_weeks', 'last_months']],
        ];
    }
}
