<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SecureFileUpload
{
    protected SecurityLogger $logger;

    public function __construct(SecurityLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Validate and upload a file securely
     */
    public function upload(UploadedFile $file, string $type = 'audio'): array
    {
        // Validate file type
        $this->validateFileType($file, $type);

        // Validate file size
        $this->validateFileSize($file, $type);

        // Validate MIME type
        $this->validateMimeType($file, $type);

        // Check for malicious content
        $this->scanForViruses($file);

        // Additional validation for images
        if ($type === 'image') {
            $this->validateImage($file);
        }

        // Generate secure filename
        $filename = $this->generateSecureFilename($file);

        // Store file
        $path = $file->storeAs(
            config('uploads.storage.path') . '/' . $type,
            $filename,
            config('uploads.storage.disk')
        );

        return [
            'path' => $path,
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Validate file extension
     */
    protected function validateFileType(UploadedFile $file, string $type): void
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $allowed = config("uploads.allowed_types.{$type}", []);

        if (!in_array($extension, $allowed)) {
            $this->logger->logSuspiciousFileUpload(
                $file->getClientOriginalName(),
                "Invalid extension: {$extension}"
            );
            throw new \Exception("Invalid file type. Allowed types: " . implode(', ', $allowed));
        }
    }

    /**
     * Validate file size
     */
    protected function validateFileSize(UploadedFile $file, string $type): void
    {
        $maxSize = config("uploads.max_sizes.{$type}");

        if ($file->getSize() > $maxSize) {
            throw new \Exception("File size exceeds maximum allowed size of " . ($maxSize / 1048576) . "MB");
        }
    }

    /**
     * Validate MIME type
     */
    protected function validateMimeType(UploadedFile $file, string $type): void
    {
        $mimeType = $file->getMimeType();
        $allowed = config("uploads.mime_types.{$type}", []);

        if (!in_array($mimeType, $allowed)) {
            $this->logger->logSuspiciousFileUpload(
                $file->getClientOriginalName(),
                "Invalid MIME type: {$mimeType}"
            );
            throw new \Exception("Invalid file format.");
        }
    }

    protected function scanForViruses(UploadedFile $file): void
    {
        if (!config('uploads.virus_scan.enabled')) {
            return;
        }

        try {
            $clamavPath = config('uploads.virus_scan.clamav_path', 'clamscan');

            // Check if ClamAV is available
            if (!$this->isClamAVAvailable($clamavPath)) {
                \Log::warning('ClamAV not available, skipping virus scan');
                return;
            }

            // Scan the file
            $filePath = $file->getRealPath();
            $command = sprintf('%s --no-summary %s', escapeshellarg($clamavPath), escapeshellarg($filePath));

            exec($command, $output, $returnCode);

            // Return code 0 = clean, 1 = infected, 2 = error
            if ($returnCode === 1) {
                $this->logger->logSuspiciousFileUpload(
                    $file->getClientOriginalName(),
                    'Virus detected: ' . implode(' ', $output)
                );
                throw new \Exception('File contains malicious content and has been rejected.');
            }

            if ($returnCode === 2) {
                \Log::error('ClamAV scan error: ' . implode(' ', $output));
                // Continue anyway if configured to do so
                if (!config('uploads.virus_scan.block_on_error', false)) {
                    return;
                }
                throw new \Exception('Unable to scan file for viruses.');
            }

        } catch (\Exception $e) {
            if (config('uploads.virus_scan.required', false)) {
                throw $e;
            }
            \Log::error('Virus scan failed: ' . $e->getMessage());
        }
    }

    protected function isClamAVAvailable(string $clamavPath): bool
    {
        exec(escapeshellarg($clamavPath) . ' --version 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Validate image dimensions
     */
    protected function validateImage(UploadedFile $file): void
    {
        $imageInfo = getimagesize($file->getRealPath());

        if (!$imageInfo) {
            throw new \Exception("Invalid image file.");
        }

        [$width, $height] = $imageInfo;

        $maxWidth = config('uploads.image.max_width');
        $maxHeight = config('uploads.image.max_height');
        $minWidth = config('uploads.image.min_width');
        $minHeight = config('uploads.image.min_height');

        if ($width > $maxWidth || $height > $maxHeight) {
            throw new \Exception("Image dimensions too large. Maximum: {$maxWidth}x{$maxHeight}px");
        }

        if ($width < $minWidth || $height < $minHeight) {
            throw new \Exception("Image dimensions too small. Minimum: {$minWidth}x{$minHeight}px");
        }
    }

    /**
     * Generate secure random filename
     */
    protected function generateSecureFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        return Str::random(40) . '.' . $extension;
    }
}
