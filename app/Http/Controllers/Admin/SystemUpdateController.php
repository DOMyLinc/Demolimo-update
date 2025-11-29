<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemUpdate;
use App\Services\UpdateManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SystemUpdateController extends Controller
{
    protected $updateManager;

    public function __construct(UpdateManager $updateManager)
    {
        $this->updateManager = $updateManager;
    }

    /**
     * Show list of updates.
     */
    public function index()
    {
        $updates = SystemUpdate::orderBy('created_at', 'desc')->get();
        return view('admin.updates.index', compact('updates'));
    }

    /**
     * Show form to upload a new update package.
     */
    public function create()
    {
        return view('admin.updates.create');
    }

    /**
     * Store a new update package.
     */
    public function store(Request $request)
    {
        $request->validate([
            'zip_file' => 'required|file|mimes:zip|max:51200', // 50MB
            'version' => 'required|string|max:20|unique:system_updates,version',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'changelog' => 'nullable|string',
        ]);

        $path = $request->file('zip_file')->store('updates', 'local');

        $update = SystemUpdate::create([
            'version' => $request->input('version'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'changelog' => $request->input('changelog'),
            'file_path' => $path,
            'status' => 'pending',
            'installed_by' => auth()->id(),
        ]);

        return redirect()->route('updates.index')->with('success', 'Update package uploaded.');
    }

    /**
     * Show details of a single update.
     */
    public function show(SystemUpdate $update)
    {
        return view('admin.updates.show', compact('update'));
    }

    /**
     * Apply the update.
     */
    public function apply(SystemUpdate $update)
    {
        if ($update->status !== 'pending') {
            return back()->with('error', 'Update already processed.');
        }

        $result = $this->updateManager->applyUpdate($update);

        if ($result['success']) {
            $update->update(['status' => 'completed', 'installed_at' => now()]);
            return redirect()->route('updates.index')->with('success', 'Update applied successfully.');
        }

        $update->update(['status' => 'failed']);
        Log::error('Update failed', ['update_id' => $update->id, 'error' => $result['error'] ?? 'unknown']);
        return back()->with('error', 'Update failed: ' . ($result['error'] ?? 'unknown'));
    }

    /**
     * Delete an update record and its file.
     */
    public function destroy(SystemUpdate $update)
    {
        Storage::disk('local')->delete($update->file_path);
        $update->delete();
        return redirect()->route('updates.index')->with('success', 'Update removed.');
    }
}
?>