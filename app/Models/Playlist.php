<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'cover_image',
        'is_public',
        'is_collaborative',
        'plays',
        'likes',
        'followers',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_collaborative' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tracks()
    {
        return $this->belongsToMany(Track::class, 'playlist_track')
            ->withPivot('position', 'added_by')
            ->withTimestamps()
            ->orderBy('playlist_track.position');
    }

    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function getTotalDurationAttribute()
    {
        return $this->tracks->sum('duration');
    }

    public function getTrackCountAttribute()
    {
        return $this->tracks->count();
    }
}
