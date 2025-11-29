<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'author_url',
        'plugin_url',
        'main_file',
        'requires',
        'settings',
        'is_active',
        'is_installed',
        'priority',
    ];

    protected $casts = [
        'requires' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_installed' => 'boolean',
    ];

    public function hooks()
    {
        return $this->hasMany(PluginHook::class);
    }

    public function routes()
    {
        return $this->hasMany(PluginRoute::class);
    }

    public function migrations()
    {
        return $this->hasMany(PluginMigration::class);
    }

    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function getMainFilePath()
    {
        return base_path('plugins/' . $this->slug . '/' . $this->main_file);
    }

    public function getPluginPath()
    {
        return base_path('plugins/' . $this->slug);
    }
}
