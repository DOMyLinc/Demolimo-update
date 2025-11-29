<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerImpression extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_id',
        'user_id',
        'ip_address',
        'user_agent',
        'action',
    ];

    /**
     * Relationships
     */
    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
