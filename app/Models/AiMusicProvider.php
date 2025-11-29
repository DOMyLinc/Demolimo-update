<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiMusicProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'api_endpoint',
        'api_key',
        'cost_per_generation',
        'max_duration',
        'supported_styles',
        'settings',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'cost_per_generation' => 'decimal:4',
        'supported_styles' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_key',
    ];

    public function models()
    {
        return $this->hasMany(AiMusicModel::class, 'provider_id');
    }

    public function generations()
    {
        return $this->hasManyThrough(AiMusicGeneration::class, AiMusicModel::class, 'provider_id', 'model_id');
    }
}
