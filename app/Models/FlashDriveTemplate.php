<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlashDriveTemplate extends Model
{
    protected $fillable = [
        'name',
        'description',
        'preview_image',
        'capacity',
        'type',
        'base_cost',
        'suggested_price',
        'customization_options',
        'is_active',
    ];

    protected $casts = [
        'customization_options' => 'array',
        'is_active' => 'boolean',
    ];
}
