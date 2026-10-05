<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Cache;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;
use App\Core\Security;
use Database\Database;

class SecurityController
{
    public function index(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::redirect('/admin');
            return;
        }

        $audit = Security::audit();
        $blockedIps = Security::getBlockedIps();
        $recentSecurityLogs = Logger::getLogs(40, null, 'security');

        // Security configuration settings from DB
        $settingsRows = Database::fetchAll("
            SELECT `key`, `value` FROM settings 
            WHERE `key` IN ('security_rate_limit_max', 'security_rate_limit_decay', 'security_force_https', 'security_block_tor')
        ");
        $secSettings = [];
        foreach ($settingsRows as $r) {
            $secSettings[$r['key']] = $r['value'];
        }

        Response::view('admin/security', [
            'audit' => $audit,
            'blockedIps' => $blockedIps,
            'recentLogs' => $recentSecurityLogs,
            'secSettings' => $secSettings,
            'saved' => $request->get('saved') == 1,
            'blocked' => $request->get('blocked') == 1,
            'unblocked' => $request->get('unblocked') == 1
        ], 'admin');
    }

    public function blockIp(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::json(['success' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        $ip = trim((string)$request->get('ip'));
        $reason = trim((string)$request->get('reason', 'Manual block by Super Admin'));

        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP)) {
            Response::json(['success' => false, 'error' => 'العنوان المدخل غير صالح (Invalid IP)'], 400);
            return;
        }

        // Prevent admin from accidentally blocking their own current IP
        $currentIp = Logger::getClientIp();
        if ($ip === $currentIp || $ip === '127.0.0.1' || $ip === '::1') {
            Response::json(['success' => false, 'error' => 'لا يمكنك حظر عنوان IP الحالي أو عنوان الخادم المحلي منعاً لإغلاق لوحة التحكم.'], 400);
            return;
        }

        Security::blockIp($ip, $reason);

        if ($request->isAjax()) {
            Response::json(['success' => true, 'message' => "تم حظر العنوان {$ip} بنجاح وإضافته لقائمة الحظر."]);
            return;
        }

        Response::redirect('/admin/security?blocked=1');
    }

    public function unblockIp(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::json(['success' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        $ip = trim((string)$request->get('ip'));
        if (!empty($ip)) {
            Security::unblockIp($ip);
        }

        if ($request->isAjax()) {
            Response::json(['success' => true, 'message' => "تم رفع الحظر عن العنوان {$ip} بنجاح."]);
            return;
        }

        Response::redirect('/admin/security?unblocked=1');
    }

    public function updateSettings(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $allowedKeys = [
            'security_rate_limit_max',
            'security_rate_limit_decay',
            'security_force_https',
            'security_block_tor'
        ];

        foreach ($allowedKeys as $k) {
            $val = $request->get($k);
            if ($val !== null) {
                Database::execute("
                    INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
                ", [$k, (string)$val]);
            }
        }

        if (class_exists('\\App\\Core\\Cache')) {
            Cache::flush('settings');
        }

        Logger::security("Security policy settings updated by " . Auth::user()['name']);

        Response::redirect('/admin/security?saved=1');
    }
}
