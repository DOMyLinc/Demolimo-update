<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PluginManager;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register PluginManager as singleton
        $this->app->singleton('plugin.manager', function ($app) {
            return new PluginManager();
        });

        // Register PluginSettingsManager (to be created)
        $this->app->singleton('plugin.settings', function ($app) {
            return new \App\Services\PluginSettingsManager();
        });

        // Register PluginAssetManager (to be created)
        $this->app->singleton('plugin.assets', function ($app) {
            return new \App\Services\PluginAssetManager();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load helper functions
        require_once app_path('Helpers/plugin_helpers.php');

        // Load all active plugins
        $this->app['plugin.manager']->loadPlugins();

        // Register Blade directive for hooks
        \Blade::directive('hook', function ($expression) {
            return "<?php do_action($expression); ?>";
        });

        // Register Blade directive for filters
        \Blade::directive('filter', function ($expression) {
            list($hook, $value) = explode(',', $expression, 2);
            return "<?php echo apply_filters($hook, $value); ?>";
        });
    }
}
