<?php

declare(strict_types=1);

namespace App\Core;

class Cache
{
    private static string $baseDir = __DIR__ . '/../../storage/cache';

    /**
     * Ensure cache storage directories exist
     */
    public static function init(): void
    {
        $groups = ['data', 'translations', 'settings', 'views', 'queries', 'security'];
        foreach ($groups as $group) {
            $dir = self::$baseDir . '/' . $group;
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
        }
    }

    /**
     * Get path for a cache key
     */
    private static function getFilePath(string $key, string $group = 'data'): string
    {
        self::init();
        $safeKey = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $key);
        $hash = md5($key);
        return self::$baseDir . '/' . $group . '/' . $safeKey . '_' . substr($hash, 0, 8) . '.cache';
    }

    /**
     * Retrieve an item from the cache
     */
    public static function get(string $key, mixed $default = null, string $group = 'data'): mixed
    {
        $file = self::getFilePath($key, $group);
        if (!file_exists($file)) {
            return $default;
        }

        $raw = @file_get_contents($file);
        if ($raw === false) {
            return $default;
        }

        $data = @unserialize($raw);
        if (!is_array($data) || !isset($data['expires_at']) || !array_key_exists('value', $data)) {
            @unlink($file);
            return $default;
        }

        // Check expiration (0 means never expires)
        if ($data['expires_at'] !== 0 && time() > $data['expires_at']) {
            @unlink($file);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Store an item in the cache
     * @param int $ttl Time to live in seconds (0 = forever)
     */
    public static function set(string $key, mixed $value, int $ttl = 3600, string $group = 'data'): bool
    {
        self::init();
        $file = self::getFilePath($key, $group);
        $payload = [
            'key' => $key,
            'group' => $group,
            'created_at' => time(),
            'expires_at' => $ttl > 0 ? (time() + $ttl) : 0,
            'value' => $value
        ];

        return @file_put_contents($file, serialize($payload), LOCK_EX) !== false;
    }

    /**
     * Check if a key exists in cache and has not expired
     */
    public static function has(string $key, string $group = 'data'): bool
    {
        return self::get($key, '__CACHE_MISS__', $group) !== '__CACHE_MISS__';
    }

    /**
     * Delete an item from the cache
     */
    public static function forget(string $key, string $group = 'data'): bool
    {
        $file = self::getFilePath($key, $group);
        if (file_exists($file)) {
            return @unlink($file);
        }
        return true;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     */
    public static function remember(string $key, int $ttl, callable $callback, string $group = 'data'): mixed
    {
        $val = self::get($key, '__CACHE_MISS__', $group);
        if ($val !== '__CACHE_MISS__') {
            return $val;
        }

        $computed = $callback();
        self::set($key, $computed, $ttl, $group);
        return $computed;
    }

    /**
     * Flush cache by group or completely
     */
    public static function flush(?string $group = null): bool
    {
        self::init();
        if ($group !== null && $group !== 'all') {
            $dir = self::$baseDir . '/' . $group;
            if (is_dir($dir)) {
                self::deleteDirFiles($dir);
            }
            return true;
        }

        // Flush all groups
        $items = glob(self::$baseDir . '/*');
        foreach ($items as $item) {
            if (is_dir($item)) {
                self::deleteDirFiles($item);
            } elseif (is_file($item)) {
                @unlink($item);
            }
        }

        return true;
    }

    /**
     * Preload and warm-up critical cache items (Translations, Site Settings)
     */
    public static function preload(): array
    {
        self::init();
        $warmed = [];

        // 1. Warm Translations
        $langDir = __DIR__ . '/../../resources/lang';
        foreach (['ar', 'en'] as $lang) {
            $langFile = $langDir . '/' . $lang . '.php';
            if (file_exists($langFile)) {
                $translations = require $langFile;
                if (is_array($translations)) {
                    self::set('translations_' . $lang, $translations, 86400, 'translations');
                    $warmed[] = "Translations ({$lang}) - " . count($translations) . " keys";
                }
            }
        }

        // 2. Warm Settings
        try {
            if (class_exists('Database\\Database')) {
                $rows = \Database\Database::fetchAll("SELECT * FROM settings");
                $settings = [];
                foreach ($rows as $row) {
                    $settings[$row['key']] = $row['value'];
                }
                self::set('system_settings', $settings, 86400, 'settings');
                $warmed[] = "System Settings - " . count($settings) . " keys";
            }
        } catch (\Throwable $e) {
            // DB not ready or offline
        }

        return $warmed;
    }

    /**
     * Gather comprehensive statistics about cache storage
     */
    public static function stats(): array
    {
        self::init();
        $stats = [
            'total_size' => 0,
            'total_files' => 0,
            'groups' => []
        ];

        $groups = ['data', 'translations', 'settings', 'views', 'queries', 'security'];
        foreach ($groups as $grp) {
            $dir = self::$baseDir . '/' . $grp;
            $files = is_dir($dir) ? glob($dir . '/*.cache') : [];
            $size = 0;
            $fileCount = 0;

            if ($files) {
                foreach ($files as $f) {
                    $fileCount++;
                    $size += filesize($f);
                }
            }

            $stats['groups'][$grp] = [
                'count' => $fileCount,
                'size' => $size,
                'size_formatted' => self::formatSize($size)
            ];

            $stats['total_size'] += $size;
            $stats['total_files'] += $fileCount;
        }

        $stats['total_size_formatted'] = self::formatSize($stats['total_size']);
        return $stats;
    }

    /**
     * Helper to format bytes
     */
    public static function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Recursively delete files inside a directory without removing the directory itself
     */
    private static function deleteDirFiles(string $dir): void
    {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
