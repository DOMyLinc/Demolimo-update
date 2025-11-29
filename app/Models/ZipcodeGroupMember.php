<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZipcodeGroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'zipcode_group_id',
        'user_id',
        'role',
    ];
}
