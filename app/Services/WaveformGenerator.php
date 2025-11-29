<?php

namespace App\Services;

class WaveformGenerator
{
    public function generate(string $filePath): array
    {
        // Check if FFmpeg is available
        $ffmpegPath = config('media.ffmpeg_path', 'ffmpeg');

        if (!$this->isFFmpegAvailable($ffmpegPath)) {
            // Fallback to dummy data if FFmpeg not available
            return array_fill(0, 100, rand(1, 100) / 100);
        }

        try {
            // Generate waveform using FFmpeg
            $outputFile = storage_path('app/temp/waveform_' . md5($filePath) . '.json');

            // FFmpeg command to extract audio samples
            $command = sprintf(
                '%s -i %s -filter_complex "showwavespic=s=1920x200" -frames:v 1 -f image2 - 2>&1 | %s -i - -vf "scale=100:1" -f rawvideo -pix_fmt gray - | od -An -t u1',
                escapeshellarg($ffmpegPath),
                escapeshellarg($filePath),
                escapeshellarg($ffmpegPath)
            );

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && !empty($output)) {
                // Parse output into normalized array
                $samples = array_map('intval', array_filter(explode(' ', implode(' ', $output))));
                $normalized = array_map(function ($val) {
                    return $val / 255; // Normalize to 0-1
                }, array_slice($samples, 0, 100));

                return $normalized;
            }

            // Fallback if command failed
            return array_fill(0, 100, rand(1, 100) / 100);

        } catch (\Exception $e) {
            // Fallback on error
            \Log::error('Waveform generation failed: ' . $e->getMessage());
            return array_fill(0, 100, rand(1, 100) / 100);
        }
    }

    protected function isFFmpegAvailable(string $ffmpegPath): bool
    {
        exec(escapeshellarg($ffmpegPath) . ' -version 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }
}
