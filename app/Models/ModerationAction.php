<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModerationAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'moderator_id',
        'actionable_type',
        'actionable_id',
        'action',
        'reason',
        'notes',
    ];

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function actionable()
    {
        return $this->morphTo();
    }
}
