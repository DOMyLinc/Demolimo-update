<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'chart_id',
        'track_id',
        'rank',
        'previous_rank',
        'peak_rank',
        'weeks_on_chart',
    ];

    public function chart()
    {
        return $this->belongsTo(Chart::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }
}
