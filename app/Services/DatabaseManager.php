<?php

namespace App\Services;

use App\Models\DatabaseConfiguration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseManager
{
    protected $currentConnection;
    protected $healthCheckInterval = 60; // seconds

    /**
     * Get active database connection
     */
    public function getActiveConnection()
    {
        if ($this->currentConnection && $this->isConnectionHealthy($this->currentConnection)) {
            return $this->currentConnection;
        }

        // Try primary database first
        $primary = DatabaseConfiguration::getPrimary();

        if ($primary && $this->switchToDatabase($primary)) {
            return $this->currentConnection = $primary;
        }

        // Failover to backup database
        $failover = DatabaseConfiguration::getFailover();

        if ($failover && $this->switchToDatabase($failover)) {
            Log::warning("Failed over to database: {$failover->name}");
            return $this->currentConnection = $failover;
        }

        throw new \Exception('No healthy database connection available');
    }

    /**
     * Switch to specific database
     */
    public function switchToDatabase(DatabaseConfiguration $config): bool
    {
        try {
            // Test connection first
            if (!$config->testConnection()) {
                return false;
            }

            // Update Laravel configuration
            $config->updateLaravelConfig();

            // Set as current connection
            $this->currentConnection = $config;

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to switch to database {$config->name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if connection is healthy
     */
    protected function isConnectionHealthy(DatabaseConfiguration $config): bool
    {
        // Check if health check is needed
        if ($config->last_health_check && $config->last_health_check->diffInSeconds(now()) < $this->healthCheckInterval) {
            return $config->is_healthy;
        }

        // Perform health check
        return $config->testConnection();
    }

    /**
     * Monitor all database connections
     */
    public function monitorConnections()
    {
        $databases = DatabaseConfiguration::where('is_active', true)->get();

        foreach ($databases as $database) {
            $database->testConnection();
        }

        return $databases;
    }

    /**
     * Get database statistics
     */
    public function getStatistics(): array
    {
        $primary = DatabaseConfiguration::getPrimary();
        $failover = DatabaseConfiguration::getFailover();

        return [
            'primary' => [
                'name' => $primary->display_name ?? 'None',
                'driver' => $primary->driver ?? 'N/A',
                'healthy' => $primary->is_healthy ?? false,
                'last_check' => $primary->last_health_check ?? null,
            ],
            'failover' => [
                'name' => $failover->display_name ?? 'None',
                'driver' => $failover->driver ?? 'N/A',
                'healthy' => $failover->is_healthy ?? false,
                'last_check' => $failover->last_health_check ?? null,
            ],
            'total_databases' => DatabaseConfiguration::where('is_active', true)->count(),
            'healthy_databases' => DatabaseConfiguration::where('is_active', true)->where('is_healthy', true)->count(),
        ];
    }

    /**
     * Automatic failover check
     */
    public function checkAndFailover()
    {
        $primary = DatabaseConfiguration::getPrimary();

        if (!$primary || !$primary->is_healthy) {
            $failover = DatabaseConfiguration::getFailover();

            if ($failover) {
                $failover->setAsPrimary();
                Log::warning("Automatic failover activated: {$failover->name} is now primary");
                return true;
            }
        }

        return false;
    }
}
