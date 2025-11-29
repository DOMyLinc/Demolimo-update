<?php

namespace App\Services;

use App\Models\Plugin;
use App\Models\PluginHook;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class PluginManager
{
    protected $loadedPlugins = [];
    protected $hooks = [];

    /**
     * Load all active plugins
     */
    public function loadPlugins(): void
    {
        $plugins = Plugin::where('is_active', true)
            ->orderBy('priority')
            ->get();

        foreach ($plugins as $plugin) {
            $this->loadPlugin($plugin);
        }
    }

    /**
     * Load a single plugin
     */
    public function loadPlugin(Plugin $plugin): bool
    {
        $mainFile = $plugin->getMainFilePath();

        if (!File::exists($mainFile)) {
            return false;
        }

        try {
            require_once $mainFile;
            $this->loadedPlugins[$plugin->slug] = $plugin;
            $this->registerPluginHooks($plugin);
            $this->registerPluginRoutes($plugin);
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to load plugin {$plugin->slug}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Register plugin hooks
     */
    protected function registerPluginHooks(Plugin $plugin): void
    {
        $hooks = $plugin->hooks;

        foreach ($hooks as $hook) {
            if (!isset($this->hooks[$hook->hook_name])) {
                $this->hooks[$hook->hook_name] = [];
            }

            $this->hooks[$hook->hook_name][] = [
                'callback' => [$hook->callback_class, $hook->callback_method],
                'priority' => $hook->priority,
            ];
        }

        // Sort hooks by priority
        foreach ($this->hooks as $hookName => $callbacks) {
            usort($this->hooks[$hookName], function ($a, $b) {
                return $a['priority'] <=> $b['priority'];
            });
        }
    }

    /**
     * Register plugin routes
     */
    protected function registerPluginRoutes(Plugin $plugin): void
    {
        $routes = $plugin->routes;

        foreach ($routes as $route) {
            $method = strtolower($route->method);
            $uri = $route->uri;
            $controller = $route->controller;
            $action = $route->action;

            \Route::$method($uri, [$controller, $action])
                ->name($route->name)
                ->middleware($route->middleware ? explode(',', $route->middleware) : []);
        }
    }

    /**
     * Execute a hook
     */
    public function doAction(string $hookName, ...$args): void
    {
        if (!isset($this->hooks[$hookName])) {
            return;
        }

        foreach ($this->hooks[$hookName] as $hook) {
            try {
                call_user_func_array($hook['callback'], $args);
            } catch (\Exception $e) {
                \Log::error("Hook execution failed for {$hookName}: " . $e->getMessage());
            }
        }
    }

    /**
     * Apply filters
     */
    public function applyFilters(string $hookName, $value, ...$args)
    {
        if (!isset($this->hooks[$hookName])) {
            return $value;
        }

        foreach ($this->hooks[$hookName] as $hook) {
            try {
                $value = call_user_func_array($hook['callback'], array_merge([$value], $args));
            } catch (\Exception $e) {
                \Log::error("Filter execution failed for {$hookName}: " . $e->getMessage());
            }
        }

        return $value;
    }

    /**
     * Dynamically add an action hook
     */
    public function addAction(string $hookName, callable $callback, int $priority = 10): void
    {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
        }

        $this->hooks[$hookName][] = [
            'callback' => $callback,
            'priority' => $priority,
        ];

        // Re-sort by priority
        usort($this->hooks[$hookName], function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
    }

    /**
     * Dynamically add a filter hook
     */
    public function addFilter(string $hookName, callable $callback, int $priority = 10): void
    {
        // Filters and actions use the same mechanism
        $this->addAction($hookName, $callback, $priority);
    }

    /**
     * Install a plugin from ZIP
     */
    public function installPlugin(string $zipPath): bool
    {
        $pluginsPath = base_path('plugins');

        if (!File::exists($pluginsPath)) {
            File::makeDirectory($pluginsPath, 0755, true);
        }

        try {
            $zip = new \ZipArchive;

            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($pluginsPath);
                $zip->close();

                // Read plugin info from plugin.json
                $pluginInfo = $this->readPluginInfo($pluginsPath);

                if ($pluginInfo) {
                    $plugin = Plugin::create($pluginInfo);
                    $this->runPluginMigrations($plugin);
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("Plugin installation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Read plugin info from plugin.json
     */
    protected function readPluginInfo(string $pluginPath): ?array
    {
        $infoFile = $pluginPath . '/plugin.json';

        if (!File::exists($infoFile)) {
            return null;
        }

        $info = json_decode(File::get($infoFile), true);

        return [
            'name' => $info['name'] ?? 'Unknown Plugin',
            'slug' => $info['slug'] ?? '',
            'description' => $info['description'] ?? '',
            'version' => $info['version'] ?? '1.0.0',
            'author' => $info['author'] ?? '',
            'author_url' => $info['author_url'] ?? '',
            'plugin_url' => $info['plugin_url'] ?? '',
            'main_file' => $info['main_file'] ?? 'plugin.php',
            'requires' => $info['requires'] ?? [],
            'is_installed' => true,
        ];
    }

    /**
     * Run plugin migrations
     */
    protected function runPluginMigrations(Plugin $plugin): void
    {
        $migrationsPath = $plugin->getPluginPath() . '/migrations';

        if (File::exists($migrationsPath)) {
            Artisan::call('migrate', [
                '--path' => 'plugins/' . $plugin->slug . '/migrations',
                '--force' => true,
            ]);
        }
    }

    /**
     * Uninstall a plugin
     */
    public function uninstallPlugin(Plugin $plugin): bool
    {
        try {
            // Deactivate first
            $plugin->deactivate();

            // Rollback migrations
            $this->rollbackPluginMigrations($plugin);

            // Delete plugin files
            File::deleteDirectory($plugin->getPluginPath());

            // Delete from database
            $plugin->delete();

            return true;
        } catch (\Exception $e) {
            \Log::error("Plugin uninstallation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rollback plugin migrations
     */
    protected function rollbackPluginMigrations(Plugin $plugin): void
    {
        // This would rollback plugin-specific migrations
        // Implementation depends on how you track plugin migrations
    }

    /**
     * Get all available plugins (installed or not)
     */
    public function getAvailablePlugins(): array
    {
        $pluginsPath = base_path('plugins');
        $available = [];

        if (!File::exists($pluginsPath)) {
            return $available;
        }

        $directories = File::directories($pluginsPath);

        foreach ($directories as $dir) {
            $slug = basename($dir);
            $plugin = Plugin::where('slug', $slug)->first();

            if ($plugin) {
                $available[] = $plugin;
            } else {
                // Plugin exists but not in database
                $info = $this->readPluginInfo($dir);
                if ($info) {
                    $available[] = (object) array_merge($info, ['is_installed' => false]);
                }
            }
        }

        return $available;
    }

    /**
     * Check if plugin is loaded
     */
    public function isPluginLoaded(string $slug): bool
    {
        return isset($this->loadedPlugins[$slug]);
    }

    /**
     * Get loaded plugins
     */
    public function getLoadedPlugins(): array
    {
        return $this->loadedPlugins;
    }
}
