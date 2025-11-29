<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plugin;
use App\Services\PluginManager;
use Illuminate\Http\Request;

class PluginController extends Controller
{
    protected $pluginManager;

    public function __construct(PluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    public function index()
    {
        $plugins = Plugin::all();
        $available = $this->pluginManager->getAvailablePlugins();

        $stats = [
            'total_plugins' => Plugin::count(),
            'active_plugins' => Plugin::where('is_active', true)->count(),
            'inactive_plugins' => Plugin::where('is_active', false)->count(),
        ];

        return view('admin.plugins.index', compact('plugins', 'available', 'stats'));
    }

    public function show(Plugin $plugin)
    {
        return view('admin.plugins.show', compact('plugin'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'plugin_file' => 'required|file|mimes:zip|max:51200', // 50MB max
        ]);

        $file = $request->file('plugin_file');
        $path = $file->storeAs('temp', $file->getClientOriginalName());

        $success = $this->pluginManager->installPlugin(storage_path('app/' . $path));

        // Clean up temp file
        \Storage::delete($path);

        if ($success) {
            return redirect()->route('admin.plugins.index')
                ->with('success', 'Plugin installed successfully');
        }

        return back()->with('error', 'Failed to install plugin');
    }

    public function activate(Plugin $plugin)
    {
        try {
            $plugin->activate();
            $this->pluginManager->loadPlugin($plugin);

            return back()->with('success', 'Plugin activated successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to activate plugin: ' . $e->getMessage());
        }
    }

    public function deactivate(Plugin $plugin)
    {
        $plugin->deactivate();

        return back()->with('success', 'Plugin deactivated successfully');
    }

    public function uninstall(Plugin $plugin)
    {
        $success = $this->pluginManager->uninstallPlugin($plugin);

        if ($success) {
            return redirect()->route('admin.plugins.index')
                ->with('success', 'Plugin uninstalled successfully');
        }

        return back()->with('error', 'Failed to uninstall plugin');
    }

    public function settings(Plugin $plugin)
    {
        return view('admin.plugins.settings', compact('plugin'));
    }

    public function updateSettings(Request $request, Plugin $plugin)
    {
        $settings = $request->except('_token', '_method');

        $plugin->update(['settings' => $settings]);

        return back()->with('success', 'Plugin settings updated successfully');
    }

    public function updatePriority(Request $request, Plugin $plugin)
    {
        $request->validate([
            'priority' => 'required|integer|min:1|max:100',
        ]);

        $plugin->update(['priority' => $request->priority]);

        return back()->with('success', 'Plugin priority updated');
    }
}
