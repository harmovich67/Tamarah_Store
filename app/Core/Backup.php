<?php

namespace App\Core;

use PDO;
use Database\Database;

class Backup
{
    /**
     * Export the entire MySQL database into a portable, clean SQL dump file.
     *
     * @param string|null $outputPath Absolute or relative path to save the .sql file
     * @return string Path to the generated backup file
     */
    public static function export(?string $outputPath = null): string
    {
        $outputPath = $outputPath ?: __DIR__ . '/../../database/alaz_store_backup.sql';
        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $pdo = Database::getConnection();
        $tables = [];
        $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }

        $sql = "-- =============================================================================\n";
        $sql .= "-- Tumurna Luxury Dates Store - Complete Database Backup Dump\n";
        $sql .= "-- Generated At: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- PHP Version: " . PHP_VERSION . "\n";
        $sql .= "-- Platform: Tumurna Luxury Dates, Saudi Cold Shipping, Bilingual (AR/EN)\n";
        $sql .= "-- =============================================================================\n\n";

        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
        $sql .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
        $sql .= "SET NAMES utf8mb4;\n";
        $sql .= "SET time_zone = '+00:00';\n\n";

        foreach ($tables as $table) {
            $sql .= "-- -----------------------------------------------------------------------------\n";
            $sql .= "-- Table structure for table `{$table}`\n";
            $sql .= "-- -----------------------------------------------------------------------------\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

            $showCreate = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            $createTableSql = $showCreate['Create Table'] ?? '';
            $sql .= $createTableSql . ";\n\n";

            // Dump Data
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $sql .= "-- Dumping data for table `{$table}` (" . count($rows) . " rows)\n";
                
                $columns = array_keys($rows[0]);
                $escapedColumns = array_map(fn($c) => "`{$c}`", $columns);
                $columnsStr = implode(', ', $escapedColumns);

                $chunkSize = 100;
                $chunks = array_chunk($rows, $chunkSize);

                foreach ($chunks as $chunk) {
                    $valueSets = [];
                    foreach ($chunk as $row) {
                        $escapedValues = [];
                        foreach ($columns as $col) {
                            $val = $row[$col];
                            if ($val === null) {
                                $escapedValues[] = 'NULL';
                            } elseif (is_numeric($val) && !str_starts_with((string)$val, '0')) {
                                $escapedValues[] = $val;
                            } else {
                                $escapedValues[] = $pdo->quote((string)$val);
                            }
                        }
                        $valueSets[] = '(' . implode(', ', $escapedValues) . ')';
                    }
                    $sql .= "INSERT INTO `{$table}` ({$columnsStr}) VALUES\n" . implode(",\n", $valueSets) . ";\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        $sql .= "-- =============================================================================\n";
        $sql .= "-- Backup Completed Successfully\n";
        $sql .= "-- =============================================================================\n";

        file_put_contents($outputPath, $sql);
        return $outputPath;
    }

    /**
     * Import an SQL dump file into the database.
     *
     * @param string $sqlFilePath
     * @return bool
     */
    public static function import(string $sqlFilePath): bool
    {
        if (!file_exists($sqlFilePath)) {
            throw new \RuntimeException("SQL backup file not found: {$sqlFilePath}");
        }

        $pdo = Database::getConnection();
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

        $lines = file($sqlFilePath, FILE_IGNORE_NEW_LINES);
        $buffer = '';

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed) || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }

            $buffer .= $line . "\n";
            if (str_ends_with($trimmed, ';')) {
                $pdo->exec($buffer);
                $buffer = '';
            }
        }

        if (!empty(trim($buffer))) {
            $pdo->exec($buffer);
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        return true;
    }

    /**
     * Directory where downloadable full backup archives are stored
     */
    public static function backupDir(): string
    {
        $dir = dirname(__DIR__, 2) . '/storage/backups';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $htaccess = $dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "Order allow,deny\nDeny from all\n");
        }
        return $dir;
    }

    /**
     * Build a single downloadable .zip containing a fresh database dump plus the
     * entire uploads/ folder (product images, banners, certificates, etc.), ready to be
     * uploaded to any host (e.g. cPanel, VPS, Cloud) and restored via install.php or phpMyAdmin.
     *
     * @return string Absolute path to the generated .zip file
     */
    public static function createFullBackupZip(): string
    {
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('PHP ZipArchive extension is not enabled on this server.');
        }

        $backupDir = self::backupDir();
        $rootDir = dirname(__DIR__, 2);

        // 1. Fresh SQL dump (temp file, folded into the zip then removed)
        $sqlTmpPath = $backupDir . '/_tmp_' . uniqid() . '.sql';
        self::export($sqlTmpPath);

        // 2. Assemble the zip
        $zipName = 'tumurna_full_backup_' . date('Ymd_His') . '.zip';
        $zipPath = $backupDir . '/' . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            @unlink($sqlTmpPath);
            throw new \RuntimeException('Could not create the backup zip archive.');
        }

        $zip->addFile($sqlTmpPath, 'database/tumurna_store_backup.sql');
        $zip->addFromString('HOW_TO_RESTORE.txt', self::buildRestoreReadme());

        $uploadsDir = $rootDir . '/uploads';
        if (is_dir($uploadsDir)) {
            self::addFolderToZip($zip, $uploadsDir, 'uploads');
        }

        $zip->close();
        @unlink($sqlTmpPath);

        self::pruneOldBackups();

        return $zipPath;
    }

    /**
     * Recursively add a folder's contents into an open ZipArchive
     */
    private static function addFolderToZip(\ZipArchive $zip, string $folder, string $zipFolder): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getFilename() === '.htaccess') {
                continue;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($folder) + 1));
            $zip->addFile($file->getPathname(), $zipFolder . '/' . $relative);
        }
    }

    private static function buildRestoreReadme(): string
    {
        $now = date('Y-m-d H:i:s');
        return <<<TXT
        Tumurna Luxury Dates Store - Full Backup Package
        =================================================
        Generated: {$now}

        What's inside:
        - database/tumurna_store_backup.sql -> a complete dump of your database (all tables & data)
        - uploads/                          -> every uploaded product image, banner, certificate, etc.

        How to restore on a new host (e.g. cPanel, Cloud, VPS, or any shared hosting):
        1. Upload/copy the Tumurna project files to the new host as usual.
        2. Replace the project's uploads/ folder with the uploads/ folder from this zip
           (or merge them via your host's File Manager / FTP).
        3. Create a new MySQL database on the new host's control panel.
        4. Restore the data using ONE of the following:
           a) Open phpMyAdmin on the new host, select the new database, go to the
              "Import" tab, and upload database/tumurna_store_backup.sql.
           b) Or run the project's install.php wizard and point it at
              database/tumurna_store_backup.sql when prompted.
        5. Update the .env file (or re-run install.php) with the new host's database
           host / username / password / database name.
        6. Visit the site - everything (date products, categories, orders, coupons, translations, settings) will
           be restored exactly as it was when this backup was generated.

        =================================================
        متجر تَـمْرُنـا للتمور الفاخرة - حزمة النسخة الاحتياطية الكاملة
        =================================================
        تاريخ الإنشاء: {$now}

        محتويات الحزمة:
        - database/tumurna_store_backup.sql  -> نسخة كاملة من قاعدة البيانات بكل الجداول والبيانات
        - uploads/                           -> كل الصور المرفوعة (منتجات التمور، البنرات، الشهادات، إلخ)

        خطوات الاستعادة على استضافة جديدة (cPanel أو سيرفر سحابي أو أي استضافة تدعم PHP و MySQL):
        1. ارفع ملفات المتجر كما هي إلى السيرفر الجديد.
        2. استبدل مجلد uploads/ في المتجر بمجلد uploads/ الموجود في هذه الحزمة
           (أو ادمج الملفات عن طريق File Manager أو FTP).
        3. أنشئ قاعدة بيانات MySQL جديدة من لوحة تحكم الاستضافة.
        4. استورد البيانات بإحدى الطريقتين:
           أ) افتح phpMyAdmin على الاستضافة الجديدة، اختر القاعدة، ثم من تبويب
              "Import" ارفع ملف database/tumurna_store_backup.sql.
           ب) أو شغّل معالج التثبيت install.php واختر نفس الملف عند الطلب.
        5. حدّث ملف .env (أو أعد تشغيل install.php) ببيانات اتصال قاعدة البيانات
           الجديدة (Host / Username / Password / Database name).
        6. افتح المتجر - ستجد كل شيء (منتجات التمور، الأقسام، الطلبات، الكوبونات، الترجمات، الإعدادات) كما كان
           تماماً وقت إنشاء هذه النسخة الاحتياطية.
        TXT;
    }

    /**
     * List all generated full-backup zip files, newest first
     */
    public static function listBackups(): array
    {
        $dir = self::backupDir();
        $files = glob($dir . '/*.zip') ?: [];
        $out = [];
        foreach ($files as $f) {
            $out[] = [
                'name' => basename($f),
                'size' => filesize($f),
                'size_formatted' => Cache::formatSize((int)filesize($f)),
                'created_at' => filemtime($f),
            ];
        }
        usort($out, fn($a, $b) => $b['created_at'] <=> $a['created_at']);
        return $out;
    }

    /**
     * Delete a backup zip by filename (basename only, no path traversal)
     */
    public static function deleteBackup(string $filename): bool
    {
        $safe = basename($filename);
        if (!str_ends_with($safe, '.zip')) {
            return false;
        }
        $path = self::backupDir() . '/' . $safe;
        return file_exists($path) && @unlink($path);
    }

    /**
     * Resolve the absolute path of a backup file for downloading, or null if invalid
     */
    public static function resolveBackupPath(string $filename): ?string
    {
        $safe = basename($filename);
        if (!str_ends_with($safe, '.zip')) {
            return null;
        }
        $path = self::backupDir() . '/' . $safe;
        return file_exists($path) ? $path : null;
    }

    /**
     * Keep only the most recent backups to avoid unbounded disk usage
     */
    private static function pruneOldBackups(int $keep = 5): void
    {
        $backups = self::listBackups();
        if (count($backups) > $keep) {
            foreach (array_slice($backups, $keep) as $old) {
                self::deleteBackup($old['name']);
            }
        }
    }

    // ==========================================
    // Restore
    // ==========================================

    /**
     * Restore the database (and, if present, the uploads/ folder) from a full
     * backup .zip previously generated by createFullBackupZip(). A fresh safety
     * backup of the CURRENT state is taken first so the restore can be undone.
     *
     * @return array{safety_backup:string, tables_restored:int, files_restored:int}
     */
    public static function restoreFromZip(string $zipPath): array
    {
        if (!file_exists($zipPath)) {
            throw new \RuntimeException('Backup file not found.');
        }
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('PHP ZipArchive extension is not enabled on this server.');
        }

        // 1. Safety net: back up the current state before overwriting anything
        $safetyBackupPath = self::createFullBackupZip();

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('Could not open the backup zip archive.');
        }

        $rootDir = dirname(__DIR__, 2);
        $tablesRestored = 0;
        $filesRestored = 0;

        try {
            // 2. Find and import the SQL dump (any *.sql entry in the archive)
            $sqlEntryName = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if ($name !== false && str_ends_with(strtolower($name), '.sql')) {
                    $sqlEntryName = $name;
                    break;
                }
            }

            if ($sqlEntryName === null) {
                throw new \RuntimeException('No .sql database dump found inside this backup file.');
            }

            $sqlContent = $zip->getFromName($sqlEntryName);
            if ($sqlContent === false) {
                throw new \RuntimeException('Could not read the database dump from the backup file.');
            }

            $tmpSqlPath = self::backupDir() . '/_restore_' . uniqid() . '.sql';
            file_put_contents($tmpSqlPath, $sqlContent);
            $tablesRestored = substr_count($sqlContent, 'CREATE TABLE');
            self::import($tmpSqlPath);
            @unlink($tmpSqlPath);

            // 3. Restore uploads/ files, if the backup includes any (merge/overwrite, never delete)
            $uploadsDir = $rootDir . '/uploads';
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if ($name === false || !str_starts_with($name, 'uploads/') || str_ends_with($name, '/')) {
                    continue;
                }
                $relative = substr($name, strlen('uploads/'));
                if ($relative === '' || str_contains($relative, '..')) {
                    continue;
                }
                $destPath = $uploadsDir . '/' . $relative;
                $destDir = dirname($destPath);
                if (!is_dir($destDir)) {
                    @mkdir($destDir, 0775, true);
                }
                $content = $zip->getFromName($name);
                if ($content !== false && @file_put_contents($destPath, $content) !== false) {
                    $filesRestored++;
                }
            }
        } finally {
            $zip->close();
        }

        return [
            'safety_backup' => basename($safetyBackupPath),
            'tables_restored' => $tablesRestored,
            'files_restored' => $filesRestored,
        ];
    }

    /**
     * Restore directly from a raw .sql dump file (no uploads/ folder involved).
     * Also takes a safety backup first.
     */
    public static function restoreFromSqlFile(string $sqlPath): array
    {
        if (!file_exists($sqlPath)) {
            throw new \RuntimeException('SQL file not found.');
        }

        $safetyBackupPath = self::createFullBackupZip();
        $sqlContent = file_get_contents($sqlPath);
        $tablesRestored = substr_count((string)$sqlContent, 'CREATE TABLE');

        self::import($sqlPath);

        return [
            'safety_backup' => basename($safetyBackupPath),
            'tables_restored' => $tablesRestored,
            'files_restored' => 0,
        ];
    }

    // ==========================================
    // Scheduled (automatic) backups - "poor man's cron"
    // ==========================================

    /**
     * If automatic backups are enabled in Settings and the configured interval
     * has elapsed since the last one, generate a new full backup now. Designed
     * to be called cheaply on normal admin page loads (a single settings read
     * in the common case) so it works even on hosts without real cron access.
     */
    public static function maybeRunScheduledBackup(): void
    {
        try {
            $pdo = Database::getConnection();
            $rows = $pdo->query("
                SELECT `key`, `value` FROM settings
                WHERE `key` IN ('backup_auto_enabled', 'backup_auto_interval_hours', 'backup_last_auto_at')
            ")->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (\Throwable $e) {
            return;
        }

        if (empty($rows['backup_auto_enabled']) || $rows['backup_auto_enabled'] !== '1') {
            return;
        }

        $intervalHours = max(1, (int)($rows['backup_auto_interval_hours'] ?? 24));
        $lastRun = !empty($rows['backup_last_auto_at']) ? strtotime($rows['backup_last_auto_at']) : 0;

        if ($lastRun !== false && (time() - $lastRun) < ($intervalHours * 3600)) {
            return; // not due yet
        }

        // Lightweight lock so two concurrent requests don't both trigger a backup
        $lockFile = self::backupDir() . '/.auto_backup.lock';
        $lockHandle = @fopen($lockFile, 'c');
        if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
            if ($lockHandle) {
                fclose($lockHandle);
            }
            return;
        }

        try {
            self::createFullBackupZip();
            $now = date('Y-m-d H:i:s');
            $pdo->prepare("
                INSERT INTO settings (`key`, `value`) VALUES ('backup_last_auto_at', ?)
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
            ")->execute([$now]);
            if (class_exists('\\App\\Core\\Cache')) {
                Cache::flush('settings');
            }
        } catch (\Throwable $e) {
            // Never let a failed scheduled backup break the page that triggered it
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }
}
