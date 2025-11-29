<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemUpdate extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'name',
        'description',
        'changelog',
        'file_path',
        'backup_path',
        'status',
        'requires_version',
        'files_modified',
        'migrations_run',
        'installed_at',
        'installed_by',
    ];

    protected $casts = [
        'files_modified' => 'array',
        'migrations_run' => 'array',
        'installed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_INSTALLING = 'installing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_ROLLED_BACK = 'rolled_back';

    /**
     * Get the admin who installed this update
     */
    public function installer()
    {
        return $this->belongsTo(User::class, 'installed_by');
    }

    /**
     * Check if update is installed
     */
    public function isInstalled()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if update failed
     */
    public function hasFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if update can be rolled back
     */
    public function canRollback()
    {
        return $this->status === self::STATUS_COMPLETED && $this->backup_path;
    }

    /**
     * Get current system version
     */
    public static function getCurrentVersion()
    {
        $latest = self::where('status', self::STATUS_COMPLETED)
            ->orderByDesc('installed_at')
            ->first();

        return $latest ? $latest->version : '1.0.0';
    }
}
