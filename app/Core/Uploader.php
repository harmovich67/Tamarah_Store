<?php

namespace App\Core;

class Uploader
{
    private static array $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
    private static int $maxSizeBytes = 5 * 1024 * 1024; // 5 MB

    /**
     * Upload an uploaded file from $_FILES[$fileKey] to uploads/{$subfolder}/
     * Returns the web path like /uploads/{$subfolder}/unique_name.ext or null if none uploaded
     */
    public static function upload(string $fileKey, string $subfolder = 'general'): ?string
    {
        if (!isset($_FILES[$fileKey]) || empty($_FILES[$fileKey]['name'])) {
            return null;
        }

        $file = $_FILES[$fileKey];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file['size'] > self::$maxSizeBytes) {
            return null;
        }

        $origName = $file['name'];
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        if (!in_array($ext, self::$allowedExtensions)) {
            return null;
        }

        $targetDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $subfolder;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $cleanBase = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
        $filename = time() . '_' . substr($cleanBase, 0, 30) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return '/uploads/' . $subfolder . '/' . $filename;
        }

        return null;
    }
}
