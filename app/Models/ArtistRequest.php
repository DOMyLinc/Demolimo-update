<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistRequest extends Model
{
    protected $fillable = [
        'user_id',
        'stage_name',
        'bio',
        'id_proof_path',
        'social_links',
        'status',
        'admin_notes'
    ];

    protected $casts = [
        'social_links' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
