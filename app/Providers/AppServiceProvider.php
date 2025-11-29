<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use App\Models\SystemConfiguration;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton('translation.loader', function ($app) {
            return new \App\Services\DatabaseTranslationLoader($app['files'], $app['path.lang']);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        try {
            if (Schema::hasTable('system_configurations')) {
                $ffmpegPath = SystemConfiguration::get('ffmpeg_path');
                if ($ffmpegPath) {
                    config(['media.ffmpeg_path' => $ffmpegPath]);
                }

                $ffmpegEnabled = SystemConfiguration::get('ffmpeg_enabled');
                if ($ffmpegEnabled) {
                    config(['media.ffmpeg_enabled' => (bool) $ffmpegEnabled]);
                }

                $waveformGeneration = SystemConfiguration::get('waveform_generation');
                if ($waveformGeneration) {
                    config(['media.waveform_generation' => (bool) $waveformGeneration]);
                }
            }
        } catch (\Exception $e) {
            // Ignore if database not ready
        }

        // Register translation Blade directive
        Blade::directive('t', function ($expression) {
            return "<?php echo t($expression); ?>";
        });
    }
}
