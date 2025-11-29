<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', // Free, Pro, Premium, Enterprise
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'features', // JSON: storage_limit, upload_limit, etc.
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];
}
