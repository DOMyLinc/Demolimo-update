<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class SystemConfigurationController extends Controller
{
    public function index()
    {
        $configurations = SystemConfiguration::orderBy('category')->orderBy('key')->get()->groupBy('category');

        return view('admin.system.index', compact('configurations'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'configurations' => 'required|array',
            'configurations.*' => 'nullable',
        ]);

        foreach ($validated['configurations'] as $key => $value) {
            SystemConfiguration::set($key, $value ?? '');
        }

        // Clear all caches
        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        return back()->with('success', 'System configuration updated successfully!');
    }

    public function testFFMPEG()
    {
        $ffmpegPath = SystemConfiguration::get('ffmpeg_path');

        if (!$ffmpegPath || !file_exists($ffmpegPath)) {
            return response()->json([
                'success' => false,
                'message' => 'FFMPEG executable not found at specified path.',
            ]);
        }

        try {
            $output = shell_exec("\"{$ffmpegPath}\" -version");

            if (str_contains($output, 'ffmpeg version')) {
                return response()->json([
                    'success' => true,
                    'message' => 'FFMPEG is working correctly!',
                    'version' => $this->extractFFMPEGVersion($output),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'FFMPEG found but not responding correctly.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing FFMPEG: ' . $e->getMessage(),
            ]);
        }
    }

    public function downloadFFMPEG(Request $request)
    {
        try {
            // Determine OS
            $os = PHP_OS_FAMILY;

            if ($os === 'Windows') {
                return $this->downloadFFMPEGWindows();
            } elseif ($os === 'Linux') {
                return $this->downloadFFMPEGLinux();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Automatic download not supported for ' . $os . '. Please download manually from https://ffmpeg.org/',
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading FFMPEG: ' . $e->getMessage(),
            ]);
        }
    }

    private function downloadFFMPEGWindows()
    {
        $downloadUrl = 'https://www.gyan.dev/ffmpeg/builds/ffmpeg-release-essentials.zip';
        $extractPath = 'C:\\ffmpeg';
        $zipPath = storage_path('app/ffmpeg.zip');

        // Download FFMPEG
        $response = Http::timeout(300)->get($downloadUrl);

        if (!$response->successful()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download FFMPEG from server.',
            ]);
        }

        File::put($zipPath, $response->body());

        // Extract ZIP
        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            // Create extraction directory
            if (!File::exists($extractPath)) {
                File::makeDirectory($extractPath, 0755, true);
            }

            $zip->extractTo($extractPath);
            $zip->close();

            // Find ffmpeg.exe in extracted files
            $ffmpegExe = $this->findFFMPEGExecutable($extractPath);

            if ($ffmpegExe) {
                // Update configuration
                SystemConfiguration::set('ffmpeg_path', $ffmpegExe);
                SystemConfiguration::set('ffmpeg_enabled', '1');

                // Clean up
                File::delete($zipPath);

                return response()->json([
                    'success' => true,
                    'message' => 'FFMPEG downloaded and configured successfully!',
                    'path' => $ffmpegExe,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'FFMPEG downloaded but executable not found.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to extract FFMPEG archive.',
        ]);
    }

    private function downloadFFMPEGLinux()
    {
        // For Linux, use package manager
        $commands = [
            'apt-get install -y ffmpeg',  // Debian/Ubuntu
            'yum install -y ffmpeg',       // CentOS/RHEL
        ];

        foreach ($commands as $command) {
            exec("which apt-get", $output, $returnCode);
            if ($returnCode === 0) {
                exec("sudo {$command} 2>&1", $output, $returnCode);

                if ($returnCode === 0) {
                    $ffmpegPath = trim(shell_exec('which ffmpeg'));

                    SystemConfiguration::set('ffmpeg_path', $ffmpegPath);
                    SystemConfiguration::set('ffmpeg_enabled', '1');

                    return response()->json([
                        'success' => true,
                        'message' => 'FFMPEG installed successfully!',
                        'path' => $ffmpegPath,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Please install FFMPEG manually: sudo apt-get install ffmpeg',
        ]);
    }

    private function findFFMPEGExecutable(string $directory): ?string
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

    private function extractFFMPEGVersion(string $output): string
    {
        preg_match('/ffmpeg version ([^\s]+)/', $output, $matches);
        return $matches[1] ?? 'Unknown';
    }
}
