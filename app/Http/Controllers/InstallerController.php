<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\SystemConfiguration;
use App\Models\Theme;
use App\Models\DatabaseConfiguration;

class InstallerController extends Controller
{
    public function index()
    {
        // Check if already installed
        if (file_exists(storage_path('installed'))) {
            return redirect('/');
        }

        $requirements = [
            'PHP Version >= 8.1' => version_compare(phpversion(), '8.1.0', '>='),
            'BCMath Extension' => extension_loaded('bcmath'),
            'Ctype Extension' => extension_loaded('ctype'),
            'Fileinfo Extension' => extension_loaded('fileinfo'),
            'JSON Extension' => extension_loaded('json'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'PDO Extension' => extension_loaded('pdo'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension' => extension_loaded('xml'),
            'ZIP Extension' => extension_loaded('zip'),
        ];

        $allMet = !in_array(false, $requirements);

        return view('installer.index', compact('requirements', 'allMet'));
    }

    public function database()
    {
        $databases = [
            'mysql' => 'MySQL',
            'pgsql' => 'PostgreSQL',
        ];
        return view('installer.database', compact('databases'));
    }

    public function setupDatabase(Request $request)
    {
        $request->validate([
            'connection' => 'required|in:mysql,pgsql',
            'host' => 'required',
            'port' => 'required',
            'database' => 'required',
            'username' => 'required',
        ]);

        // Test connection
        try {
            $dsn = $request->connection === 'pgsql'
                ? "pgsql:host={$request->host};port={$request->port};dbname={$request->database}"
                : "mysql:host={$request->host};port={$request->port};dbname={$request->database}";

            $pdo = new \PDO($dsn, $request->username, $request->password);
        } catch (\Exception $e) {
            return back()->with('error', 'Could not connect to database: ' . $e->getMessage());
        }

        // Update .env
        $this->updateEnv([
            'DB_CONNECTION' => $request->connection,
            'DB_HOST' => $request->host,
            'DB_PORT' => $request->port,
            'DB_DATABASE' => $request->database,
            'DB_USERNAME' => $request->username,
            'DB_PASSWORD' => $request->password,
        ]);

        // Clear config cache
        Artisan::call('config:clear');

        // Run migrations
        try {
            Artisan::call('migrate:fresh', ['--force' => true]);
        } catch (\Exception $e) {
            Log::error('Migration Failed: ' . $e->getMessage());
            return back()->with('error', 'Migration failed: ' . $e->getMessage() . '. Please ensure your database credentials are correct and the user has sufficient privileges.');
        }

        return redirect()->route('installer.admin');
    }

    public function admin()
    {
        return view('installer.admin');
    }

    public function setupAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Create Admin User
        $username = Str::slug($request->name) . rand(100, 999);
        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'admin',
            'username' => $username,
            'email_verified_at' => now(),
            'is_verified' => true,
        ]);

        return redirect()->route('installer.features');
    }

    public function features()
    {
        // Detect OS and suggest FFMPEG path
        $suggestedPath = PHP_OS_FAMILY === 'Windows'
            ? 'C:\\ffmpeg\\bin\\ffmpeg.exe'
            : '/usr/bin/ffmpeg';

        return view('installer.features', compact('suggestedPath'));
    }

    public function setupFeatures(Request $request)
    {
        // Store feature settings in session for later (after tables are created)
        session([
            'ffmpeg_settings' => [
                'auto_download' => $request->auto_download_ffmpeg,
                'path' => $request->ffmpeg_path,
            ],
            'feature_settings' => [
                'chat_enabled' => $request->has('chat_enabled'),
                'blog_enabled' => $request->has('blog_enabled'),
                'radio_enabled' => $request->has('radio_enabled'),
                'podcasts_enabled' => $request->has('podcasts_enabled'),
                'affiliate_enabled' => $request->has('affiliate_enabled'),
                'points_enabled' => $request->has('points_enabled'),
                'import_enabled' => $request->has('import_enabled'),
                'waveform_generation' => $request->has('waveform_generation'),
            ],
        ]);

        return redirect()->route('installer.settings');
    }

    public function settings()
    {
        // Get all available themes
        $themes = Theme::all();

        return view('installer.settings', compact('themes'));
    }

    public function setupSettings(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
        ]);

        // Update .env with new settings
        $envUpdates = [
            'APP_NAME' => '"' . $request->app_name . '"',
            'APP_URL' => $request->app_url,

            // Registration & Verification
            'REGISTRATION_ENABLED' => 'true',
            'EMAIL_VERIFICATION_REQUIRED' => 'false',

            // Mail
            'MAIL_HOST' => $request->mail_host,
            'MAIL_PORT' => $request->mail_port,
            'MAIL_USERNAME' => $request->mail_username,
            'MAIL_PASSWORD' => $request->mail_password,
            'MAIL_ENCRYPTION' => $request->mail_encryption,
            'MAIL_FROM_ADDRESS' => $request->mail_from_address,

            // Storage
            'FILESYSTEM_DISK' => $request->filesystem_disk,
            'AWS_ACCESS_KEY_ID' => $request->aws_access_key_id,
            'AWS_SECRET_ACCESS_KEY' => $request->aws_secret_access_key,
            'AWS_DEFAULT_REGION' => $request->aws_default_region,
            'AWS_BUCKET' => $request->aws_bucket,

            // Pusher
            'PUSHER_APP_ID' => $request->pusher_app_id,
            'PUSHER_APP_KEY' => $request->pusher_app_key,
            'PUSHER_APP_SECRET' => $request->pusher_app_secret,
            'PUSHER_APP_CLUSTER' => $request->pusher_app_cluster,
        ];

        $this->updateEnv($envUpdates);

        // Clear config cache after env update
        try {
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            Log::warning('Config clear failed: ' . $e->getMessage());
        }

        // Seed Feature Flags
        try {
            if (class_exists('Database\\Seeders\\FeatureFlagSeeder')) {
                Artisan::call('db:seed', ['--class' => 'FeatureFlagSeeder', '--force' => true]);
            } else {
                $this->seedFeatureFlags();
            }
        } catch (\Exception $e) {
            Log::error('Feature Flag Seeding Failed: ' . $e->getMessage());
            // Fallback to manual seeding
            $this->seedFeatureFlags();
        }

        // Seed default data
        try {
            Artisan::call('db:seed', ['--force' => true]);
        } catch (\Exception $e) {
            Log::error('Database Seeding Failed: ' . $e->getMessage());
            // Continue anyway - some seeders may be optional
        }

        // Apply feature settings from session
        $this->applyFeatureSettings();

        // Set default themes if they exist
        try {
            if ($request->filled('frontend_theme')) {
                $frontendTheme = Theme::find($request->frontend_theme);
                if ($frontendTheme) {
                    Theme::query()->update(['is_active' => false, 'is_default' => false]);
                    $frontendTheme->update(['is_active' => true, 'is_default' => true]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Theme setup failed: ' . $e->getMessage());
        }

        // Create storage link
        try {
            Artisan::call('storage:link');
        } catch (\Exception $e) {
            Log::warning('Storage link creation failed: ' . $e->getMessage());
        }

        // Optimize application
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
        } catch (\Exception $e) {
            Log::warning('Cache optimization failed: ' . $e->getMessage());
        }

        // Mark as installed
        file_put_contents(storage_path('installed'), 'Installed on ' . now());

        // Clear session data
        session()->forget(['ffmpeg_settings', 'feature_settings']);

        return redirect('/')->with('success', 'Installation completed successfully! Your music platform is ready to use.');
    }

    protected function autoDownloadFFMPEG()
    {
        try {
            $os = PHP_OS_FAMILY;

            if ($os === 'Windows') {
                $downloadUrl = 'https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-essentials.zip';
                $extractPath = 'C:\\ffmpeg';
                $zipPath = storage_path('app/ffmpeg.zip');

                // Download FFMPEG
                $response = Http::timeout(300)->get($downloadUrl);

                if ($response->successful()) {
                    File::put($zipPath, $response->body());

                    // Extract ZIP
                    $zip = new \ZipArchive();
                    if ($zip->open($zipPath) === true) {
                        if (!File::exists($extractPath)) {
                            File::makeDirectory($extractPath, 0755, true);
                        }

                        $zip->extractTo($extractPath);
                        $zip->close();

                        // Find ffmpeg.exe
                        $ffmpegExe = $this->findFFMPEGExecutable($extractPath);

                        if ($ffmpegExe) {
                            SystemConfiguration::set('ffmpeg_path', $ffmpegExe);
                            SystemConfiguration::set('ffmpeg_enabled', '1');
                        }

                        // Clean up
                        File::delete($zipPath);
                    }
                }
            } elseif ($os === 'Linux') {
                // Try to install via package manager
                exec('which ffmpeg 2>&1', $output, $returnCode);

                if ($returnCode !== 0) {
                    // Try apt-get
                    exec('sudo apt-get install -y ffmpeg 2>&1', $output, $returnCode);
                }

                // Get path
                $ffmpegPath = trim(shell_exec('which ffmpeg'));
                if ($ffmpegPath) {
                    SystemConfiguration::set('ffmpeg_path', $ffmpegPath);
                    SystemConfiguration::set('ffmpeg_enabled', '1');
                }
            }
        } catch (\Exception $e) {
            // Silently fail - user can configure manually later
        }
    }

    protected function findFFMPEGExecutable(string $directory): ?string
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === 'ffmpeg.exe') {
                return $file->getPathname();
            }
        }

        return null;
    }

    protected function seedFeatureFlags()
    {
        $features = [
            // Core Features
            ['key' => 'user_registration', 'name' => 'User Registration', 'category' => 'core', 'description' => 'Allow new users to register', 'is_enabled' => true],
            ['key' => 'track_upload', 'name' => 'Track Upload', 'category' => 'core', 'description' => 'Allow users to upload tracks', 'is_enabled' => true],
            ['key' => 'album_creation', 'name' => 'Album Creation', 'category' => 'core', 'description' => 'Allow users to create albums', 'is_enabled' => true],
            ['key' => 'playlist_creation', 'name' => 'Playlist Creation', 'category' => 'core', 'description' => 'Allow users to create playlists', 'is_enabled' => true],
            ['key' => 'search', 'name' => 'Search', 'category' => 'core', 'description' => 'Enable search functionality', 'is_enabled' => true],

            // Social Features
            ['key' => 'follow_system', 'name' => 'Follow System', 'category' => 'social', 'description' => 'Allow users to follow each other', 'is_enabled' => true],
            ['key' => 'activity_feed', 'name' => 'Activity Feed', 'category' => 'social', 'description' => 'Show activity feed', 'is_enabled' => true],

            // Monetization
            ['key' => 'subscriptions', 'name' => 'Subscriptions', 'category' => 'monetization', 'description' => 'Enable subscription plans', 'is_enabled' => true],
            ['key' => 'track_sales', 'name' => 'Track Sales', 'category' => 'monetization', 'description' => 'Allow selling tracks', 'is_enabled' => true],

            // Content
            ['key' => 'song_battles', 'name' => 'Song Battles', 'category' => 'content', 'description' => 'Enable song battle competitions', 'is_enabled' => true],
            ['key' => 'events', 'name' => 'Events', 'category' => 'content', 'description' => 'Allow event creation', 'is_enabled' => true],
            ['key' => 'zipcode_panel', 'name' => 'Zipcode Panel', 'category' => 'content', 'description' => 'Enable zipcode communities', 'is_enabled' => true],
        ];

        foreach ($features as $feature) {
            \App\Models\FeatureFlag::updateOrCreate(
                ['key' => $feature['key']],
                $feature
            );
        }
    }

    protected function updateEnv($data)
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            $content = file_get_contents($path);
            foreach ($data as $key => $value) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            }
            file_put_contents($path, $content);
        }
    }

    protected function applyFeatureSettings()
    {
        $ffmpegSettings = session('ffmpeg_settings', []);
        $featureSettings = session('feature_settings', []);

        // Setup FFMPEG if requested
        if (!empty($ffmpegSettings['auto_download'])) {
            $this->autoDownloadFFMPEG();
        } elseif (!empty($ffmpegSettings['path'])) {
            try {
                if (class_exists('App\\Models\\SystemConfiguration')) {
                    SystemConfiguration::updateOrCreate(
                        ['key' => 'ffmpeg_path'],
                        ['value' => $ffmpegSettings['path']]
                    );
                    SystemConfiguration::updateOrCreate(
                        ['key' => 'ffmpeg_enabled'],
                        ['value' => '1']
                    );
                }
            } catch (\Exception $e) {
                Log::warning('FFMPEG configuration failed: ' . $e->getMessage());
            }
        }

        // Apply feature settings
        if (!empty($featureSettings)) {
            try {
                if (class_exists('App\\Models\\SystemConfiguration')) {
                    foreach ($featureSettings as $key => $enabled) {
                        SystemConfiguration::updateOrCreate(
                            ['key' => $key],
                            ['value' => $enabled ? '1' : '0']
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Feature configuration failed: ' . $e->getMessage());
            }
        }
    }
}
