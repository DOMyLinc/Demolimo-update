<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class DatabaseConfiguration extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'driver',
        'host',
        'port',
        'database',
        'username',
        'password',
        'charset',
        'collation',
        'prefix',
        'is_active',
        'is_primary',
        'priority',
        'auto_failover',
        'last_health_check',
        'is_healthy',
        'failed_attempts',
        'last_failure',
        'ssl_enabled',
        'ssl_ca',
        'ssl_cert',
        'ssl_key',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'auto_failover' => 'boolean',
        'is_healthy' => 'boolean',
        'ssl_enabled' => 'boolean',
        'last_health_check' => 'datetime',
        'last_failure' => 'datetime',
    ];

    /**
     * Encrypt password when setting
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Decrypt password when getting
     */
    public function getPasswordAttribute($value)
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get primary database
     */
    public static function getPrimary()
    {
        return self::where('is_primary', true)
            ->where('is_active', true)
            ->where('is_healthy', true)
            ->first();
    }

    /**
     * Get failover database
     */
    public static function getFailover()
    {
        return self::where('is_active', true)
            ->where('is_healthy', true)
            ->where('auto_failover', true)
            ->orderBy('priority', 'desc')
            ->first();
    }

    /**
     * Test database connection
     */
    public function testConnection(): bool
    {
        try {
            $config = [
                'driver' => $this->driver,
                'host' => $this->host,
                'port' => $this->port,
                'database' => $this->database,
                'username' => $this->username,
                'password' => $this->password,
                'charset' => $this->charset,
                'collation' => $this->collation,
                'prefix' => $this->prefix,
            ];

            if ($this->ssl_enabled) {
                $config['options'] = [
                    \PDO::MYSQL_ATTR_SSL_CA => $this->ssl_ca,
                    \PDO::MYSQL_ATTR_SSL_CERT => $this->ssl_cert,
                    \PDO::MYSQL_ATTR_SSL_KEY => $this->ssl_key,
                ];
            }

            $pdo = new \PDO(
                "{$this->driver}:host={$this->host};port={$this->port};dbname={$this->database}",
                $this->username,
                $this->password
            );

            $pdo->query('SELECT 1');

            $this->update([
                'is_healthy' => true,
                'failed_attempts' => 0,
                'last_health_check' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->update([
                'is_healthy' => false,
                'failed_attempts' => $this->failed_attempts + 1,
                'last_failure' => now(),
                'last_health_check' => now(),
            ]);

            return false;
        }
    }

    /**
     * Set as primary database
     */
    public function setAsPrimary()
    {
        // Remove primary from all databases
        self::where('is_primary', true)->update(['is_primary' => false]);

        // Set this as primary
        $this->update(['is_primary' => true, 'is_active' => true]);

        // Update Laravel config
        $this->updateLaravelConfig();

        return $this;
    }

    /**
     * Update Laravel database configuration
     */
    public function updateLaravelConfig()
    {
        config([
            'database.default' => $this->name,
            "database.connections.{$this->name}" => [
                'driver' => $this->driver,
                'host' => $this->host,
                'port' => $this->port,
                'database' => $this->database,
                'username' => $this->username,
                'password' => $this->password,
                'charset' => $this->charset,
                'collation' => $this->collation,
                'prefix' => $this->prefix,
                'strict' => true,
                'engine' => null,
            ],
        ]);

        // Reconnect
        DB::purge($this->name);
        DB::reconnect($this->name);
    }

    /**
     * Get connection configuration array
     */
    public function getConnectionConfig(): array
    {
        return [
            'driver' => $this->driver,
            'host' => $this->host,
            'port' => $this->port,
            'database' => $this->database,
            'username' => $this->username,
            'password' => $this->password,
            'charset' => $this->charset,
            'collation' => $this->collation,
            'prefix' => $this->prefix,
        ];
    }
}
