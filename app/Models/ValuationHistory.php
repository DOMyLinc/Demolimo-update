<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ValuationHistory extends Model
{
    use HasFactory;

    protected $table = 'valuation_history';

    protected $fillable = [
        'track_id',
        'value',
        'change_percentage',
        'change_reason',
        'metrics',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'change_percentage' => 'decimal:4',
        'metrics' => 'array',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}
