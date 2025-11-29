<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorageProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', // s3, local, wasabi, vultr, digitalocean, backblaze
        'driver', // s3, local
        'key',
        'secret',
        'region',
        'bucket',
        'endpoint',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'key' => 'encrypted',
        'secret' => 'encrypted',
    ];
}
