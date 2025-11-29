<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PluginMigration extends Model
{
    use HasFactory;

    protected $fillable = [
        'plugin_id',
        'migration',
        'batch',
    ];

    public function plugin()
    {
        return $this->belongsTo(Plugin::class);
    }
}
