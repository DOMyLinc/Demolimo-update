<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrackTrial extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'start_date',
        'end_date',
        'is_featured',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_featured' => 'boolean',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(TrackTrialEntry::class);
    }
}
