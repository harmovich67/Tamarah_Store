<?php

namespace App\Core;

use Database\Database;

class Auth
{
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
    }

    public static function check(): bool
    {
        self::init();
        return isset($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        self::init();
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $user = Database::fetchOne("
            SELECT u.*, r.name as role_name, r.display_name_ar as role_ar, r.display_name_en as role_en
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = ?
        ", [$_SESSION['user_id']]);

        return $user ?: null;
    }

    public static function id(): ?int
    {
        self::init();
        return $_SESSION['user_id'] ?? null;
    }

    public static function isSuperAdmin(): bool
    {
        $user = self::user();
        return $user && $user['role_name'] === 'super_admin';
    }

    public static function requireSuperAdmin(): void
    {
        if (!self::isSuperAdmin()) {
            Response::redirect('/login?required_admin=1');
            exit;
        }
    }

    public static function isStoreManager(): bool
    {
        $user = self::user();
        return $user && in_array($user['role_name'], ['store_manager', 'school_admin', 'super_admin']) && $user['status'] === 'active';
    }

    public static function isSchoolAdmin(): bool
    {
        return self::isStoreManager();
    }

    public static function login(string $identifier, string $password): array
    {
        self::init();
        $user = Database::fetchOne("
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.email = ? OR u.phone = ?
        ", [$identifier, $identifier]);

        if (!$user) {
            return ['success' => false, 'message' => __('auth_failed', default: 'بيانات الدخول غير صحيحة')];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => __('auth_failed', default: 'بيانات الدخول غير صحيحة')];
        }

        // Check user status
        if ($user['status'] === 'pending') {
            return ['success' => false, 'message' => __('account_pending_approval')];
        }

        if ($user['status'] === 'rejected') {
            return ['success' => false, 'message' => __('account_rejected')];
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_name'] = $user['role_name'];

        return ['success' => true, 'user' => $user];
    }

    public static function loginUsingId(int $userId): ?array
    {
        self::init();
        $user = Database::fetchOne("
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ?
        ", [$userId]);

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_name'] = $user['role_name'];
            return $user;
        }

        return null;
    }

    public static function logout(): void
    {
        self::init();
        unset($_SESSION['user_id'], $_SESSION['role_name']);
        session_destroy();
    }
}
