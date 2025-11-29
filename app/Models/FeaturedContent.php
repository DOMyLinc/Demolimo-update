<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturedContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'featurable_type',
        'featurable_id',
        'position',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the owning featurable model.
     */
    public function featurable()
    {
        return $this->morphTo();
    }

    /**
     * Scope to get only active featured content
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now());
        })->orderBy('position');
    }
}
