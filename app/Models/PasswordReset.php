<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PasswordReset extends Model
{
    public $timestamps = false;
    protected $fillable = ['email', 'token', 'created_at'];

    /**
     * Determine if the token has expired (default 60 minutes).
     */
    public function isExpired(): bool
    {
        return $this->created_at && Carbon::parse($this->created_at)->addMinutes(60)->isPast();
    }
}
