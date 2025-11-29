<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiMusicGeneration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'model_id',
        'prompt',
        'parameters',
        'status',
        'generation_id',
        'audio_url',
        'file_path',
        'duration',
        'cost',
        'payment_method',
        'error_message',
        'completed_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'cost' => 'decimal:4',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        return $this->belongsTo(AiMusicModel::class, 'model_id');
    }

    public function markAsCompleted($audioUrl, $filePath, $duration)
    {
        $this->update([
            'status' => 'completed',
            'audio_url' => $audioUrl,
            'file_path' => $filePath,
            'duration' => $duration,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
