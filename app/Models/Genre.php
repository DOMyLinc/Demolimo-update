<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
        'sort_order',
        'tracks_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function tracks()
    {
        return $this->hasMany(Track::class, 'genre', 'name');
    }

    public function updateTracksCount()
    {
        $this->tracks_count = $this->tracks()->count();
        $this->save();
    }

    public static function getActive()
    {
        return static::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }
}
