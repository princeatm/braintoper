<?php
/**
 * Utility Helper Functions
 */

namespace App\Helpers;

class Utils
{
    /**
     * Generate unique exam code
     */
    public static function generateExamCode(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $code;
    }

    /**
     * Get mime type
     */
    public static function getMimeType(string $filePath): string
    {
        return mime_content_type($filePath) ?: 'application/octet-stream';
    }

    /**
     * Validate image upload
     */
    public static function validateImageUpload(array $file): array
    {
        $config = include __DIR__ . '/../../config/app.php';
        $maxSize = $config['uploads']['max_size'];
        $allowedTypes = $config['uploads']['allowed_types'];

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'Invalid upload'];
        }

        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File too large'];
        }

        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, $allowedTypes)) {
            return ['valid' => false, 'error' => 'Invalid file type'];
        }

        // Additional security: check image integrity
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'error' => 'Invalid image file'];
        }

        return ['valid' => true, 'mime' => $mimeType];
    }

    /**
     * Save uploaded image securely
     */
    public static function saveUploadedImage(array $file, string $directory): array
    {
        $validation = self::validateImageUpload($file);
        
        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = match($validation['mime']) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'jpg'
        };

        $filename = 'img_' . uniqid() . '_' . time() . '.' . $extension;
        $filepath = $directory . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            chmod($filepath, 0644);
            return ['success' => true, 'filename' => $filename, 'path' => $filepath];
        }

        return ['success' => false, 'error' => 'Failed to save file'];
    }

    /**
     * Delete uploaded file
     */
    public static function deleteFile(string $filepath): bool
    {
        if (file_exists($filepath) && is_file($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Convert seconds to readable time format
     */
    public static function formatTime(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%02d:%02d', $minutes, $secs);
    }

    /**
     * Array to JSON with safe encoding
     */
    public static function jsonEncode($data): string
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Check if request is AJAX
     */
    public static function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
