<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiMusicModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'name',
        'model_id',
        'description',
        'price_per_generation',
        'currency',
        'max_duration',
        'capabilities',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_per_generation' => 'decimal:4',
        'capabilities' => 'array',
        'is_active' => 'boolean',
    ];

    public function provider()
    {
        return $this->belongsTo(AiMusicProvider::class, 'provider_id');
    }

    public function generations()
    {
        return $this->hasMany(AiMusicGeneration::class, 'model_id');
    }

    public function getPriceFormatted()
    {
        if ($this->currency === 'points') {
            return number_format($this->price_per_generation, 0) . ' points';
        }
        return '$' . number_format($this->price_per_generation, 2);
    }
}
