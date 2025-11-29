<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTwoFactor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'secret',
        'enabled',
        'confirmed_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
