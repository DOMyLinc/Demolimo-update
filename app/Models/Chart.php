<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chart extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'type', // trending, top_artists, genre, location
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function entries()
    {
        return $this->hasMany(ChartEntry::class)->orderBy('rank');
    }
}
