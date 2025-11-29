<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageBlock extends Model
{
    protected $fillable = ['key', 'type', 'content', 'is_visible', 'order'];

    protected $casts = [
        'content' => 'array',
        'is_visible' => 'boolean',
    ];
}
