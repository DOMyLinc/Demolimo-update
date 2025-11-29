<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModerationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'content_type',
        'conditions',
        'action',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'conditions' => 'array',
        'is_active' => 'boolean',
    ];
}
