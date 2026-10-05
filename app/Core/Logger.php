<?php

declare(strict_types=1);

namespace App\Core;

class Logger
{
    private static string $logDir = __DIR__ . '/../../storage/logs';
    private static string $mainLogFile = 'alaz.log';
    private static string $securityLogFile = 'security.log';
    private static int $maxSizeBytes = 5242880; // 5 MB

    /**
     * Ensure storage/logs directory exists with secure permissions
     */
    public static function init(): void
    {
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0775, true);
        }

        // Write .htaccess in storage/logs directory to block direct web access
        $htaccess = self::$logDir . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Order allow,deny\nDeny from all\n");
        }
    }

    /**
     * Log an emergency message
     */
    public static function emergency(string $message, array $context = [], string $channel = 'system'): void
    {
        self::log('emergency', $message, $context, $channel);
    }

    /**
     * Log an alert message
     */
    public static function alert(string $message, array $context = [], string $channel = 'system'): void
    {
        self::log('alert', $message, $context, $channel);
    }

    /**
     * Log a critical message
     */
    public static function critical(string $message, array $context = [], string $channel = 'system'): void
    {
        self::log('critical', $message, $context, $channel);
    }

    /**
     * Log an error message
     */
    public static function error(string $message, array $context = [], string $channel = 'system'): void
    {
        self::log('error', $message, $context, $channel);
    }

    /**
     * Log a warning message
     */
    public static function warning(string $message, array $context = [], string $channel = 'system'): void
    {
        self::log('warning', $message, $context, $channel);
    }

    /**
     * Log a notice message
     */
    public static function notice(string $message, array $context = [], string $channel = 'system'): void
    {
        self::log('notice', $message, $context, $channel);
    }

    /**
     * Log an informational message
     */
    public static function info(string $message, array $context = [], string $channel = 'system'): void
    {
        self::log('info', $message, $context, $channel);
    }

    /**
     * Log a debug message
     */
    public static function debug(string $message, array $context = [], string $channel = 'system'): void
    {
        self::log('debug', $message, $context, $channel);
    }

    /**
     * Dedicated security event log (writes to both security.log and alaz.log)
     */
    public static function security(string $message, array $context = []): void
    {
        self::log('security', $message, $context, 'security');
        self::appendToFile(self::$securityLogFile, 'security', $message, $context, 'security');
    }

    /**
     * Dedicated authentication event log
     */
    public static function auth(string $message, array $context = []): void
    {
        self::log('info', $message, $context, 'auth');
    }

    /**
     * Dedicated order & payment event log
     */
    public static function order(string $message, array $context = []): void
    {
        self::log('info', $message, $context, 'order');
    }

    /**
     * General log method
     */
    public static function log(string $level, string $message, array $context = [], string $channel = 'system'): void
    {
        self::appendToFile(self::$mainLogFile, $level, $message, $context, $channel);
    }

    /**
     * Append formatted entry to target log file
     */
    private static function appendToFile(string $fileName, string $level, string $message, array $context, string $channel): void
    {
        self::init();
        $filePath = self::$logDir . '/' . $fileName;

        // Auto-rotate if needed
        self::checkRotate($filePath);

        $time = date('Y-m-d H:i:s');
        $ip = self::getClientIp();
        $userTag = 'Guest';

        if (class_exists('App\\Core\\Auth') && \App\Core\Auth::check()) {
            $u = \App\Core\Auth::user();
            $userTag = '#' . ($u['id'] ?? '0') . ' (' . ($u['email'] ?? $u['phone'] ?? 'User') . ')';
        }

        $contextJson = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $line = sprintf(
            "[%s] [%s.%s] [IP: %s] [User: %s]: %s%s%s",
            $time,
            strtoupper($channel),
            strtoupper($level),
            $ip,
            $userTag,
            $message,
            $contextJson,
            PHP_EOL
        );

        @file_put_contents($filePath, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Rotate log file if it exceeds size limit
     */
    private static function checkRotate(string $filePath): void
    {
        if (file_exists($filePath) && filesize($filePath) >= self::$maxSizeBytes) {
            $rotated = self::$logDir . '/' . basename($filePath, '.log') . '-' . date('Ymd-His') . '.log';
            @rename($filePath, $rotated);

            // Clean up old archive logs, keep maximum 5 archives
            $pattern = self::$logDir . '/' . basename($filePath, '.log') . '-*.log';
            $archives = glob($pattern);
            if ($archives && count($archives) > 5) {
                // Sort by modification time ascending (oldest first)
                usort($archives, fn($a, $b) => filemtime($a) <=> filemtime($b));
                while (count($archives) > 5) {
                    $oldest = array_shift($archives);
                    @unlink($oldest);
                }
            }
        }
    }

    /**
     * Get client IP address safely
     */
    public static function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', (string)$_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Retrieve parsed logs with filtering
     * @return array[]
     */
    public static function getLogs(int $limit = 250, ?string $level = null, ?string $channel = null, ?string $search = null): array
    {
        self::init();
        $filePath = self::$logDir . '/' . self::$mainLogFile;
        if (!file_exists($filePath)) {
            return [];
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$lines) {
            return [];
        }

        // Reverse to show newest entries first
        $lines = array_reverse($lines);
        $logs = [];

        foreach ($lines as $line) {
            // Match format: [2026-09-14 11:15:20] [CHANNEL.LEVEL] [IP: 127.0.0.1] [User: Guest]: Message {JSON}
            $pattern = '/^\[(.*?)\]\s+\[(.*?)\.(.*?)\]\s+\[IP:\s+(.*?)\]\s+\[User:\s+(.*?)\]:\s+(.*)$/u';
            if (preg_match($pattern, $line, $matches)) {
                $itemTime = $matches[1];
                $itemChannel = strtolower($matches[2]);
                $itemLevel = strtolower($matches[3]);
                $itemIp = $matches[4];
                $itemUser = $matches[5];
                $rawRest = $matches[6];

                // Check for JSON context at the end
                $message = $rawRest;
                $context = [];
                $jsonPos = strpos($rawRest, ' {');
                if ($jsonPos !== false) {
                    $jsonCandidate = substr($rawRest, $jsonPos + 1);
                    $decoded = json_decode($jsonCandidate, true);
                    if (is_array($decoded)) {
                        $message = substr($rawRest, 0, $jsonPos);
                        $context = $decoded;
                    }
                }

                // Filter by level
                if ($level !== null && $level !== 'all' && $itemLevel !== strtolower($level)) {
                    continue;
                }

                // Filter by channel
                if ($channel !== null && $channel !== 'all' && $itemChannel !== strtolower($channel)) {
                    continue;
                }

                // Filter by search query
                if ($search !== null && $search !== '') {
                    $needle = mb_strtolower($search, 'UTF-8');
                    $haystack = mb_strtolower($itemTime . ' ' . $itemChannel . ' ' . $itemLevel . ' ' . $itemIp . ' ' . $itemUser . ' ' . $message, 'UTF-8');
                    if (!str_contains($haystack, $needle)) {
                        continue;
                    }
                }

                $logs[] = [
                    'timestamp' => $itemTime,
                    'channel' => $itemChannel,
                    'level' => $itemLevel,
                    'ip' => $itemIp,
                    'user' => $itemUser,
                    'message' => $message,
                    'context' => $context
                ];

                if (count($logs) >= $limit) {
                    break;
                }
            }
        }

        return $logs;
    }

    /**
     * Get statistics on current logs
     */
    public static function getStats(): array
    {
        self::init();
        $filePath = self::$logDir . '/' . self::$mainLogFile;
        $fileSize = file_exists($filePath) ? filesize($filePath) : 0;

        $stats = [
            'total_size' => $fileSize,
            'total_size_formatted' => Cache::formatSize($fileSize),
            'total_events' => 0,
            'errors_count' => 0,
            'warnings_count' => 0,
            'security_count' => 0,
            'info_count' => 0,
        ];

        if (file_exists($filePath)) {
            $handle = @fopen($filePath, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $stats['total_events']++;
                    if (str_contains($line, '.ERROR]') || str_contains($line, '.CRITICAL]') || str_contains($line, '.EMERGENCY]')) {
                        $stats['errors_count']++;
                    } elseif (str_contains($line, '.WARNING]')) {
                        $stats['warnings_count']++;
                    } elseif (str_contains($line, '.SECURITY]') || str_contains($line, '[SECURITY.')) {
                        $stats['security_count']++;
                    } else {
                        $stats['info_count']++;
                    }
                }
                fclose($handle);
            }
        }

        return $stats;
    }

    /**
     * Clear all current log files
     */
    public static function clear(): bool
    {
        self::init();
        $files = glob(self::$logDir . '/*.log');
        if ($files) {
            foreach ($files as $f) {
                @unlink($f);
            }
        }

        // Re-create blank main log
        @file_put_contents(self::$logDir . '/' . self::$mainLogFile, '');
        self::info("Log files cleared by admin", [], 'system');
        return true;
    }

    /**
     * Get full path to main log file for download
     */
    public static function getMainLogPath(): string
    {
        self::init();
        return self::$logDir . '/' . self::$mainLogFile;
    }
}
