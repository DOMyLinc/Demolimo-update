<?php

namespace App\Services;

use App\Models\SystemUpdate;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class UpdateManager
{
    protected $updatePath;
    protected $backupPath;
    protected $tempPath;

    public function __construct()
    {
        $this->updatePath = storage_path('app/updates');
        $this->backupPath = storage_path('app/backups');
        $this->tempPath = storage_path('app/temp/updates');

        $this->ensureDirectories();
    }

    protected function ensureDirectories()
    {
        File::ensureDirectoryExists($this->updatePath);
        File::ensureDirectoryExists($this->backupPath);
        File::ensureDirectoryExists($this->tempPath);
    }

    /**
     * Upload and validate update package
     */
    public function uploadUpdate($file)
    {
        $filename = 'update_' . time() . '.zip';
        $path = $file->storeAs('updates', $filename);

        // Extract and validate
        $extractPath = $this->tempPath . '/' . pathinfo($filename, PATHINFO_FILENAME);
        $this->extractZip(storage_path('app/' . $path), $extractPath);

        $metadata = $this->readUpdateMetadata($extractPath);

        if (!$metadata) {
            File::deleteDirectory($extractPath);
            return null;
        }

        // Create update record
        $update = SystemUpdate::create([
            'version' => $metadata['version'],
            'name' => $metadata['name'],
            'description' => $metadata['description'] ?? '',
            'changelog' => $metadata['changelog'] ?? '',
            'file_path' => $path,
            'requires_version' => $metadata['requires'] ?? null,
            'status' => SystemUpdate::STATUS_PENDING,
        ]);

        File::deleteDirectory($extractPath);

        return $update;
    }

    /**
     * Install update
     */
    public function installUpdate(SystemUpdate $update)
    {
        try {
            $update->update(['status' => SystemUpdate::STATUS_INSTALLING]);

            // Extract update
            $extractPath = $this->tempPath . '/install_' . $update->id;
            $this->extractZip(storage_path('app/' . $update->file_path), $extractPath);

            // Read metadata
            $metadata = $this->readUpdateMetadata($extractPath);

            // Create backup
            $backupPath = $this->createBackup($update, $metadata);
            $update->update(['backup_path' => $backupPath]);

            // Apply file changes
            $filesModified = $this->applyFileChanges($extractPath, $metadata);

            // Run migrations
            $migrationsRun = $this->runMigrations($extractPath);

            // Clear caches
            $this->clearCaches();

            // Mark as completed
            $update->update([
                'status' => SystemUpdate::STATUS_COMPLETED,
                'installed_at' => now(),
                'installed_by' => auth()->id(),
                'files_modified' => $filesModified,
                'migrations_run' => $migrationsRun,
            ]);

            // Cleanup
            File::deleteDirectory($extractPath);

            return true;
        } catch (\Exception $e) {
            $update->update(['status' => SystemUpdate::STATUS_FAILED]);
            \Log::error('Update installation failed: ' . $e->getMessage());

            // Attempt rollback
            if ($update->backup_path) {
                $this->rollbackUpdate($update);
            }

            return false;
        }
    }

    /**
     * Rollback update
     */
    public function rollbackUpdate(SystemUpdate $update)
    {
        if (!$update->canRollback()) {
            return false;
        }

        try {
            $backupPath = storage_path('app/' . $update->backup_path);

            // Restore files
            $this->restoreBackup($backupPath);

            $update->update(['status' => SystemUpdate::STATUS_ROLLED_BACK]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Rollback failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract ZIP file
     */
    protected function extractZip($zipPath, $extractTo)
    {
        $zip = new ZipArchive;

        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($extractTo);
            $zip->close();
            return true;
        }

        return false;
    }

    /**
     * Read update metadata
     */
    protected function readUpdateMetadata($path)
    {
        $metadataFile = $path . '/update.json';

        if (!File::exists($metadataFile)) {
            return null;
        }

        return json_decode(File::get($metadataFile), true);
    }

    /**
     * Create backup
     */
    protected function createBackup(SystemUpdate $update, $metadata)
    {
        $backupName = 'backup_v' . $update->version . '_' . time();
        $backupPath = $this->backupPath . '/' . $backupName;

        File::ensureDirectoryExists($backupPath);

        // Backup files that will be modified
        if (isset($metadata['files'])) {
            foreach ($metadata['files'] as $file) {
                $sourcePath = base_path($file);
                $backupFilePath = $backupPath . '/' . $file;

                if (File::exists($sourcePath)) {
                    File::ensureDirectoryExists(dirname($backupFilePath));
                    File::copy($sourcePath, $backupFilePath);
                }
            }
        }

        // Backup database
        $this->backupDatabase($backupPath);

        return 'backups/' . $backupName;
    }

    /**
     * Apply file changes
     */
    protected function applyFileChanges($extractPath, $metadata)
    {
        $filesPath = $extractPath . '/files';
        $modified = [];

        if (!File::exists($filesPath)) {
            return $modified;
        }

        $files = File::allFiles($filesPath);

        foreach ($files as $file) {
            $relativePath = str_replace($filesPath . '/', '', $file->getPathname());
            $targetPath = base_path($relativePath);

            File::ensureDirectoryExists(dirname($targetPath));
            File::copy($file->getPathname(), $targetPath);

            $modified[] = $relativePath;
        }

        return $modified;
    }

    /**
     * Run migrations
     */
    protected function runMigrations($extractPath)
    {
        $migrationsPath = $extractPath . '/migrations';
        $run = [];

        if (File::exists($migrationsPath)) {
            $migrations = File::files($migrationsPath);

            foreach ($migrations as $migration) {
                File::copy($migration->getPathname(), database_path('migrations/' . $migration->getFilename()));
                $run[] = $migration->getFilename();
            }

            Artisan::call('migrate', ['--force' => true]);
        }

        return $run;
    }

    /**
     * Backup database
     */
    /**
     * Backup database
     */
    protected function backupDatabase($backupPath)
    {
        $filename = $backupPath . '/database_backup.sql';

        // Get all tables
        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . DB::getDatabaseName();

        $content = "-- Database Backup\n";
        $content .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $content .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;

            // Structure
            $createTable = DB::select("SHOW CREATE TABLE `$tableName`");
            $content .= "DROP TABLE IF EXISTS `$tableName`;\n";
            $content .= $createTable[0]->{'Create Table'} . ";\n\n";

            // Data
            $rows = DB::table($tableName)->get();
            foreach ($rows as $row) {
                $values = array_map(function ($value) {
                    if ($value === null)
                        return "NULL";
                    return "'" . addslashes($value) . "'";
                }, (array) $row);

                $content .= "INSERT INTO `$tableName` VALUES (" . implode(", ", $values) . ");\n";
            }
            $content .= "\n";
        }

        $content .= "SET FOREIGN_KEY_CHECKS=1;\n";

        File::put($filename, $content);

        return $filename;
    }

    /**
     * Check for remote updates
     */
    public function checkForUpdates()
    {
        // Placeholder for remote update check
        // In a real scenario, this would call an API endpoint
        $remoteUrl = config('app.update_server_url', 'https://updates.demolimo.com/api/check');
        $currentVersion = config('app.version', '1.0.0');

        try {
            // Simulate API call
            // $response = Http::get($remoteUrl, ['version' => $currentVersion]);
            // return $response->json();

            return [
                'has_update' => false,
                'latest_version' => $currentVersion,
                'message' => 'You are running the latest version.'
            ];
        } catch (\Exception $e) {
            return [
                'has_update' => false,
                'error' => 'Could not connect to update server.'
            ];
        }
    }

    /**
     * Restore backup
     */
    protected function restoreBackup($backupPath)
    {
        $files = File::allFiles($backupPath);

        foreach ($files as $file) {
            if ($file->getFilename() === 'database_backup.sql') {
                continue; // Handle database separately
            }

            $relativePath = str_replace($backupPath . '/', '', $file->getPathname());
            $targetPath = base_path($relativePath);

            File::copy($file->getPathname(), $targetPath);
        }
    }

    /**
     * Clear caches
     */
    protected function clearCaches()
    {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
    }
}
