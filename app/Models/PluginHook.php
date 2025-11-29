<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluginHook extends Model
{
    use HasFactory;

    protected $fillable = [
        'plugin_id',
        'hook_name',
        'callback_class',
        'callback_method',
        'priority',
    ];

    public function plugin()
    {
        return $this->belongsTo(Plugin::class);
    }
}
