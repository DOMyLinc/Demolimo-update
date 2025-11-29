<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFeatureAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'feature_permission_id',
        'is_granted',
        'custom_limits',
    ];

    protected $casts = [
        'is_granted' => 'boolean',
        'custom_limits' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function featurePermission()
    {
        return $this->belongsTo(FeaturePermission::class);
    }
}
