<?php

namespace App\Http\Controllers;

use App\Services\InstallationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use PDO;

class InstallController extends Controller
{
    /**
     * Redirect to step 1
     */
    public function index()
    {
        return redirect()->route('install.step1');
    }

    /**
     * Step 1: Requirements Check
     */
    public function step1()
    {
        $requirements = [
            'PHP Version >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'PDO Extension' => extension_loaded('pdo'),
            'MySQL Extension' => extension_loaded('pdo_mysql'),
            'GD Extension' => extension_loaded('gd'),
            'cURL Extension' => extension_loaded('curl'),
            'Mbstring Extension' => extension_loaded('mbstring'),
            'OpenSSL Extension' => extension_loaded('openssl'),
            'JSON Extension' => extension_loaded('json'),
            'Fileinfo Extension' => extension_loaded('fileinfo'),
            'Tokenizer Extension' => extension_loaded('tokenizer'),
            'XML Extension' => extension_loaded('xml'),
        ];

        $permissions = [
            'storage/' => is_writable(storage_path()),
            'storage/app/' => is_writable(storage_path('app')),
            'storage/framework/' => is_writable(storage_path('framework')),
            'storage/logs/' => is_writable(storage_path('logs')),
            'bootstrap/cache/' => is_writable(base_path('bootstrap/cache')),
        ];

        $allPassed = !in_array(false, $requirements) && !in_array(false, $permissions);

        return view('install.step1-requirements', compact('requirements', 'permissions', 'allPassed'));
    }

    /**
     * Step 2: Database Configuration
     */
    public function step2()
    {
        return view('install.step2-database');
    }

    /**
     * Step 2: Process Database Configuration
     */
    public function step2Post(Request $request)
    {
        $validated = $request->validate([
            'db_host' => 'required',
            'db_name' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable',
            'db_port' => 'required|numeric',
        ]);

        // Test database connection
        try {
            $pdo = new PDO(
                "mysql:host={$validated['db_host']};port={$validated['db_port']};dbname={$validated['db_name']}",
                $validated['db_username'],
                $validated['db_password'] ?? ''
            );

            // Connection successful, update .env file
            $this->updateEnvFile($validated);

            return redirect()->route('install.step3')->with('success', 'Database connection successful!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Step 3: Site Configuration
     */
    public function step3()
    {
        $timezones = timezone_identifiers_list();
        $languages = [
            'en' => 'English',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese',
        ];

        return view('install.step3-configuration', compact('timezones', 'languages'));
    }

    /**
     * Step 3: Process Site Configuration
     */
    public function step3Post(Request $request)
    {
        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_url' => 'required|url',
            'admin_email' => 'required|email',
            'admin_username' => 'required|string|min:3|max:50',
            'admin_password' => 'required|string|min:8|confirmed',
            'timezone' => 'required|string',
            'language' => 'required|string',
        ]);

        // Store in session for step 4
        session(['install_config' => $validated]);

        return redirect()->route('install.step4');
    }

    /**
     * Step 4: Installation Progress
     */
    public function step4()
    {
        return view('install.step4-installation');
    }

    /**
     * Step 4: Process Installation (AJAX)
     */
    public function step4Process()
    {
        try {
            $service = new InstallationService();
            $config = session('install_config');

            if (!$config) {
                return response()->json([
                    'success' => false,
                    'error' => 'Configuration not found. Please start from step 1.'
                ]);
            }

            // Run all installation steps
            $service->runMigrations();
            $service->seedDatabase();
            $service->createAdminUser($config);
            $service->configureSettings($config);
            $service->createStorageLinks();
            $service->clearCaches();
            $service->optimizeApplication();
            $service->createLockFile();

            return response()->json([
                'success' => true,
                'log' => $service->getLog()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'log' => $service->getLog() ?? []
            ]);
        }
    }

    /**
     * Step 5: Installation Complete
     */
    public function step5()
    {
        $config = session('install_config');

        if (!$config) {
            return redirect()->route('install.index');
        }

        // Clear installation config from session
        session()->forget('install_config');

        return view('install.step5-complete', compact('config'));
    }

    /**
     * Update .env file with database credentials
     */
    private function updateEnvFile(array $data)
    {
        $envPath = base_path('.env');

        // Create .env from .env.example if it doesn't exist
        if (!file_exists($envPath)) {
            if (file_exists(base_path('.env.example'))) {
                copy(base_path('.env.example'), $envPath);
            } else {
                // Create basic .env file
                $basicEnv = "APP_NAME=DemoLimo\n";
                $basicEnv .= "APP_ENV=production\n";
                $basicEnv .= "APP_KEY=\n";
                $basicEnv .= "APP_DEBUG=false\n";
                $basicEnv .= "APP_URL=http://localhost\n\n";
                $basicEnv .= "DB_CONNECTION=mysql\n";
                $basicEnv .= "DB_HOST=127.0.0.1\n";
                $basicEnv .= "DB_PORT=3306\n";
                $basicEnv .= "DB_DATABASE=demolimo\n";
                $basicEnv .= "DB_USERNAME=root\n";
                $basicEnv .= "DB_PASSWORD=\n";
                file_put_contents($envPath, $basicEnv);
            }
        }

        $env = file_get_contents($envPath);

        // Update database configuration
        $env = preg_replace('/DB_HOST=.*/', "DB_HOST={$data['db_host']}", $env);
        $env = preg_replace('/DB_PORT=.*/', "DB_PORT={$data['db_port']}", $env);
        $env = preg_replace('/DB_DATABASE=.*/', "DB_DATABASE={$data['db_name']}", $env);
        $env = preg_replace('/DB_USERNAME=.*/', "DB_USERNAME={$data['db_username']}", $env);
        $env = preg_replace('/DB_PASSWORD=.*/', "DB_PASSWORD={$data['db_password']}", $env);

        // Generate APP_KEY if not exists or empty
        if (!preg_match('/APP_KEY=base64:/', $env)) {
            $key = 'base64:' . base64_encode(random_bytes(32));
            $env = preg_replace('/APP_KEY=.*/', "APP_KEY={$key}", $env);
        }

        file_put_contents($envPath, $env);

        // Reload configuration
        try {
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Ignore if fails
        }
    }
}
