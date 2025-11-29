<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DawSoundCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get all sounds in this category
     */
    public function sounds()
    {
        return $this->hasMany(DawSound::class, 'category_id');
    }

    /**
     * Get active sounds in this category
     */
    public function activeSounds()
    {
        return $this->hasMany(DawSound::class, 'category_id')->where('is_active', true);
    }

    /**
     * Scope to get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Get total sounds count
     */
    public function getSoundsCountAttribute()
    {
        return $this->sounds()->count();
    }
}
