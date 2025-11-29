<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DawSound extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'file_path',
        'file_size',
        'duration',
        'format',
        'bpm',
        'key',
        'tags',
        'is_active',
        'is_premium',
        'download_count',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'duration' => 'integer',
        'bpm' => 'integer',
        'tags' => 'array',
        'is_active' => 'boolean',
        'is_premium' => 'boolean',
        'download_count' => 'integer',
    ];

    /**
     * Get the category this sound belongs to
     */
    public function category()
    {
        return $this->belongsTo(DawSoundCategory::class, 'category_id');
    }

    /**
     * Get the file URL
     */
    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get human-readable file size
     */
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get formatted duration
     */
    public function getDurationFormattedAttribute()
    {
        if (!$this->duration)
            return 'N/A';

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    /**
     * Increment download count
     */
    public function incrementDownloads()
    {
        $this->increment('download_count');
    }

    /**
     * Scope to get only active sounds
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope to filter by format
     */
    public function scopeByFormat($query, $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Scope to search by name or tags
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereJsonContains('tags', $search);
        });
    }
}
