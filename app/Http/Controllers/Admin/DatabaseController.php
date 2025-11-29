<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseConfiguration;
use App\Services\DatabaseManager;
use Illuminate\Http\Request;

class DatabaseController extends Controller
{
    protected $databaseManager;

    public function __construct(DatabaseManager $databaseManager)
    {
        $this->databaseManager = $databaseManager;
    }

    public function index()
    {
        $databases = DatabaseConfiguration::all();
        $stats = $this->databaseManager->getStatistics();

        return view('admin.database.index', compact('databases', 'stats'));
    }

    public function update(Request $request, DatabaseConfiguration $database)
    {
        $validated = $request->validate([
            'host' => 'required|string',
            'port' => 'required|integer',
            'database' => 'required|string',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'is_active' => 'boolean',
            'auto_failover' => 'boolean',
            'priority' => 'required|integer|min:0|max:100',
        ]);

        $database->update($validated);

        return back()->with('success', ucfirst($database->display_name) . ' configuration updated!');
    }

    public function testConnection(DatabaseConfiguration $database)
    {
        $result = $database->testConnection();

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Connection to ' . $database->display_name . ' successful!',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to connect to ' . $database->display_name,
        ]);
    }

    public function setPrimary(DatabaseConfiguration $database)
    {
        // Test connection first
        if (!$database->testConnection()) {
            return back()->with('error', 'Cannot set as primary: Connection test failed!');
        }

        $database->setAsPrimary();

        return back()->with('success', ucfirst($database->display_name) . ' is now the primary database!');
    }

    public function monitorHealth()
    {
        $databases = $this->databaseManager->monitorConnections();

        return response()->json([
            'databases' => $databases->map(function ($db) {
                return [
                    'name' => $db->display_name,
                    'healthy' => $db->is_healthy,
                    'last_check' => $db->last_health_check,
                    'failed_attempts' => $db->failed_attempts,
                ];
            }),
        ]);
    }

    public function forceFailover()
    {
        $result = $this->databaseManager->checkAndFailover();

        if ($result) {
            return back()->with('success', 'Failover completed successfully!');
        }

        return back()->with('error', 'Failover failed: No healthy backup database available.');
    }

    public function getStats()
    {
        $stats = $this->databaseManager->getStatistics();

        return response()->json($stats);
    }
}
