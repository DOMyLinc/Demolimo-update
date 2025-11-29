<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Track extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'album_id',
        'title',
        'slug',
        'description',
        'lyrics',
        'audio_path',
        'file_path', // alias for audio_path
        'waveform_data',
        'image_path',
        'cover_art', // alias for image_path
        'duration', // in seconds
        'file_size',
        'bitrate',
        'plays',
        'downloads',
        'likes',
        'views',
        'shares',
        'is_public',
        'is_featured',
        'is_downloadable',
        'price', // 0 for free
        'tags', // JSON
        'status', // pending, approved, rejected
        'visibility', // public, private
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'is_downloadable' => 'boolean',
        'tags' => 'array',
    ];

    public function getAudioUrlAttribute()
    {
        return $this->audio_path ? asset('storage/' . $this->audio_path) : null;
    }

    public function getCoverImageAttribute($value)
    {
        // Prefer image_path if set, otherwise fallback to cover_image column, otherwise placeholder
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        if ($value) {
            return asset('storage/' . $value);
        }
        return 'https://via.placeholder.com/300';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    public function valuation()
    {
        return $this->hasOne(TrackValuation::class);
    }

    public function valuationHistory()
    {
        return $this->hasMany(ValuationHistory::class);
    }

    public function investments()
    {
        return $this->hasMany(TrackInvestment::class);
    }
}
