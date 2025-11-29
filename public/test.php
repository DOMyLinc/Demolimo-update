<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DemoLimo - Server Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }

        h1 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 32px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .test-item {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 10px;
            border-left: 4px solid #ddd;
            background: #f8f9fa;
        }

        .test-item.success {
            border-left-color: #28a745;
            background: #d4edda;
        }

        .test-item.error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }

        .test-item.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }

        .test-label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .test-value {
            color: #666;
            font-size: 14px;
            font-family: 'Courier New', monospace;
        }

        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 10px;
        }

        .status.ok {
            background: #28a745;
            color: white;
        }

        .status.fail {
            background: #dc3545;
            color: white;
        }

        .status.warn {
            background: #ffc107;
            color: #333;
        }

        .next-steps {
            margin-top: 30px;
            padding: 20px;
            background: #e7f3ff;
            border-radius: 10px;
            border-left: 4px solid #2196F3;
        }

        .next-steps h3 {
            color: #2196F3;
            margin-bottom: 15px;
        }

        .next-steps a {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 10px;
            transition: background 0.3s;
        }

        .next-steps a:hover {
            background: #764ba2;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>🎵 DemoLimo</h1>
        <p class="subtitle">Server Configuration Test</p>

        <?php
        $tests = [];

        // PHP Version Test
        $phpVersion = phpversion();
        $phpOk = version_compare($phpVersion, '8.1.0', '>=');
        $tests[] = [
            'label' => 'PHP Version',
            'value' => $phpVersion,
            'status' => $phpOk ? 'success' : 'error',
            'message' => $phpOk ? 'OK' : 'FAIL (Requires PHP 8.1+)'
        ];

        // Required Extensions
        $requiredExtensions = [
            'openssl' => 'OpenSSL',
            'pdo' => 'PDO',
            'mbstring' => 'Mbstring',
            'tokenizer' => 'Tokenizer',
            'xml' => 'XML',
            'ctype' => 'Ctype',
            'json' => 'JSON',
            'bcmath' => 'BCMath',
            'fileinfo' => 'Fileinfo',
            'gd' => 'GD (Image Processing)',
        ];

        $allExtensionsOk = true;
        foreach ($requiredExtensions as $ext => $name) {
            $loaded = extension_loaded($ext);
            $allExtensionsOk = $allExtensionsOk && $loaded;
            $tests[] = [
                'label' => $name . ' Extension',
                'value' => $loaded ? 'Loaded' : 'Not Loaded',
                'status' => $loaded ? 'success' : 'error',
                'message' => $loaded ? 'OK' : 'FAIL'
            ];
        }

        // Directory Permissions
        $directories = [
            'storage' => __DIR__ . '/storage',
            'bootstrap/cache' => __DIR__ . '/bootstrap/cache',
        ];

        foreach ($directories as $name => $path) {
            $writable = is_writable($path);
            $tests[] = [
                'label' => $name . ' Directory',
                'value' => $writable ? 'Writable' : 'Not Writable',
                'status' => $writable ? 'success' : 'error',
                'message' => $writable ? 'OK' : 'FAIL'
            ];
        }

        // .env File
        $envExists = file_exists(__DIR__ . '/.env');
        $tests[] = [
            'label' => '.env File',
            'value' => $envExists ? 'Exists' : 'Missing',
            'status' => $envExists ? 'success' : 'warning',
            'message' => $envExists ? 'OK' : 'WARN'
        ];

        // Vendor Directory
        $vendorExists = is_dir(__DIR__ . '/vendor');
        $tests[] = [
            'label' => 'Composer Dependencies',
            'value' => $vendorExists ? 'Installed' : 'Missing',
            'status' => $vendorExists ? 'success' : 'error',
            'message' => $vendorExists ? 'OK' : 'FAIL'
        ];

        // mod_rewrite
        $modRewrite = function_exists('apache_get_modules') ? in_array('mod_rewrite', apache_get_modules()) : 'Unknown';
        $tests[] = [
            'label' => 'Apache mod_rewrite',
            'value' => $modRewrite === true ? 'Enabled' : ($modRewrite === false ? 'Disabled' : 'Unknown'),
            'status' => $modRewrite === true ? 'success' : 'warning',
            'message' => $modRewrite === true ? 'OK' : 'WARN'
        ];

        // Display Results
        foreach ($tests as $test) {
            $statusClass = $test['status'];
            $statusLabel = $test['message'];
            $statusType = strpos($statusLabel, 'OK') !== false ? 'ok' : (strpos($statusLabel, 'FAIL') !== false ? 'fail' : 'warn');

            echo "<div class='test-item {$statusClass}'>";
            echo "<div class='test-label'>{$test['label']} <span class='status {$statusType}'>{$statusLabel}</span></div>";
            echo "<div class='test-value'>{$test['value']}</div>";
            echo "</div>";
        }
        ?>

        <div class="next-steps">
            <h3>✅ Next Steps</h3>
            <p>If all tests passed, you can proceed to the installer:</p>
            <a href="/installer">Go to Installer</a>

            <?php if (!$phpOk || !$allExtensionsOk || !$vendorExists): ?>
                <p style="margin-top: 15px; color: #dc3545; font-weight: 600;">
                    ⚠️ Some tests failed. Please contact your hosting provider to resolve the issues above before
                    proceeding.
                </p>
            <?php endif; ?>
        </div>

        <div
            style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; text-align: center; color: #999; font-size: 12px;">
            <p>DemoLimo Music Platform • Server Test v1.0</p>
            <p style="margin-top: 5px;">Delete this file (test.php) after successful installation</p>
        </div>
    </div>
</body>

</html>