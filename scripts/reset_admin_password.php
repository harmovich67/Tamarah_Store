<?php

declare(strict_types=1);

// Run from the server terminal only. Read the new password from STDIN so it
// does not appear in the shell command or process arguments.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$password = fgets(STDIN);
if ($password === false) {
    fwrite(STDERR, "Pass the new password on standard input.\n");
    exit(1);
}
$password = rtrim($password, "\r\n");
if (mb_strlen($password) < 12 || strlen($password) > 72 || str_contains($password, "\0")) {
    fwrite(STDERR, "Use a password of at least 12 characters and at most 72 bytes.\n");
    exit(1);
}

require_once __DIR__ . '/../app/Core/helpers.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../database/Migrator.php';

try {
    $pdo = Database\Database::getConnection();
    $expectedDriver = strtolower((string) env('DB_CONNECTION', 'mysql'));
    if (Database\Database::getDriver() !== $expectedDriver) {
        throw new RuntimeException('Configured database is unavailable; refusing to reset an account in the fallback database.');
    }

    // The seed creates the primary admin on an empty database.
    $_ENV['ADMIN_PASSWORD'] = $_SERVER['ADMIN_PASSWORD'] = $password;
    Database\Migrator::run();
    $admin = Database\Database::fetchOne("SELECT u.id, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = 'admin@tumurna.com'");
    if ($admin === null) {
        Database\Migrator::seed();
        $admin = Database\Database::fetchOne("SELECT u.id, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.email = 'admin@tumurna.com'");
    }
    if ($admin === null || $admin['role_name'] !== 'super_admin') {
        throw new RuntimeException('The primary super admin account could not be found.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
    $stmt->execute([$hash, $admin['id']]);
    fwrite(STDOUT, "Password updated for admin@tumurna.com.\n");
} catch (Throwable $error) {
    fwrite(STDERR, 'Password reset failed: ' . $error->getMessage() . "\n");
    exit(1);
}
