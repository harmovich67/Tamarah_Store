<?php

namespace Database;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    private static string $driver = 'mysql';

    public static function getConnection(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $config = require __DIR__ . '/../config/database.php';

        // Try MySQL first if configured
        if ($config['default'] === 'mysql') {
            $mysql = $config['connections']['mysql'];
            $passwordsToTry = [$mysql['password']];
            // If default 'root' password failed, also try empty '' (standard XAMPP default)
            if ($mysql['password'] === 'root') {
                $passwordsToTry[] = '';
            } elseif ($mysql['password'] === '') {
                $passwordsToTry[] = 'root';
            }

            foreach ($passwordsToTry as $pwd) {
                try {
                    // Connect without db first to ensure db exists
                    $dsnWithoutDb = "mysql:host={$mysql['host']};port={$mysql['port']};charset={$mysql['charset']}";
                    $initPdo = new PDO($dsnWithoutDb, $mysql['username'], $pwd, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                    $initPdo->exec("CREATE DATABASE IF NOT EXISTS `{$mysql['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

                    // Now connect to the database
                    $dsnWithDb = "mysql:host={$mysql['host']};port={$mysql['port']};dbname={$mysql['database']};charset={$mysql['charset']}";
                    self::$instance = new PDO($dsnWithDb, $mysql['username'], $pwd, [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                    self::$driver = 'mysql';
                    return self::$instance;
                } catch (PDOException $e) {
                    // Try next password
                }
            }

            error_log("MySQL connection failed with all tried credentials - Falling back to SQLite.");
        }

        // SQLite fallback
        $sqlitePath = $config['connections']['sqlite']['database'];
        $dir = dirname($sqlitePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        self::$instance = new PDO("sqlite:" . $sqlitePath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        self::$instance->exec("PRAGMA foreign_keys = ON;");
        self::$driver = 'sqlite';

        return self::$instance;
    }

    public static function getDriver(): string
    {
        return self::$driver;
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function execute(string $sql, array $params = []): bool
    {
        return self::query($sql, $params)->rowCount() > 0;
    }

    public static function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }
}
