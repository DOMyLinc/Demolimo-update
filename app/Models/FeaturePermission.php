<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeaturePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_name',
        'display_name',
        'description',
        'free_plan',
        'pro_plan',
        'premium_plan',
        'limits',
        'is_active',
    ];

    protected $casts = [
        'free_plan' => 'boolean',
        'pro_plan' => 'boolean',
        'premium_plan' => 'boolean',
        'limits' => 'array',
        'is_active' => 'boolean',
    ];

    public function userAccess()
    {
        return $this->hasMany(UserFeatureAccess::class);
    }

    public function isAvailableForPlan($plan)
    {
        return $this->{$plan . '_plan'} ?? false;
    }
}
