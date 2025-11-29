<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZipcodeGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'zipcode_id',
        'name',
        'description',
        'creator_id',
        'is_private',
    ];

    public function zipcode()
    {
        return $this->belongsTo(Zipcode::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'zipcode_group_members')
            ->withPivot('role')
            ->withTimestamps();
    }
}
