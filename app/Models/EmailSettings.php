<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
        'logo_url',
        'use_queue',
        'is_active',
    ];

    protected $casts = [
        'use_queue' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];
}
