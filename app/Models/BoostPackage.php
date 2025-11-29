<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoostPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_days',
        'target_views',
        'target_impressions',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get duration in human-readable format
     */
    public function getDurationTextAttribute(): string
    {
        if ($this->duration_days == 1) {
            return '1 day';
        }

        if ($this->duration_days == 7) {
            return '1 week';
        }

        if ($this->duration_days == 14) {
            return '2 weeks';
        }

        if ($this->duration_days == 30) {
            return '1 month';
        }

        return $this->duration_days . ' days';
    }
}
