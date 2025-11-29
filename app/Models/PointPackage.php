<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'points',
        'price',
        'bonus_points',
        'is_popular',
        'is_active',
        'sort_order',
        'description',
        'features',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'bonus_points' => 'decimal:2',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'features' => 'array',
    ];

    public function getTotalPointsAttribute()
    {
        return $this->points + $this->bonus_points;
    }

    public function getPricePerPointAttribute()
    {
        return $this->total_points > 0 ? $this->price / $this->total_points : 0;
    }
}
