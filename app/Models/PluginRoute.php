<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluginRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'plugin_id',
        'method',
        'uri',
        'controller',
        'action',
        'middleware',
        'name',
    ];

    public function plugin()
    {
        return $this->belongsTo(Plugin::class);
    }
}
