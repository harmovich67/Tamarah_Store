<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;

class LogsController
{
    public function index(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::redirect('/admin');
            return;
        }

        $level = $request->get('level', 'all');
        $channel = $request->get('channel', 'all');
        $search = trim((string)$request->get('q', ''));
        $limit = (int)$request->get('limit', 150);
        if ($limit <= 0 || $limit > 1000) {
            $limit = 150;
        }

        $logs = Logger::getLogs($limit, $level === 'all' ? null : $level, $channel === 'all' ? null : $channel, $search ?: null);
        $stats = Logger::getStats();

        Response::view('admin/logs', [
            'logs' => $logs,
            'stats' => $stats,
            'currentLevel' => $level,
            'currentChannel' => $channel,
            'search' => $search,
            'limit' => $limit,
            'cleared' => $request->get('cleared') == 1
        ], 'admin');
    }

    public function clear(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::json(['success' => false, 'error' => 'Forbidden'], 403);
            return;
        }

        Logger::clear();

        if ($request->isAjax()) {
            Response::json(['success' => true, 'message' => 'تم تفريغ كافة سجلات النظام بنجاح']);
            return;
        }

        Response::redirect('/admin/logs?cleared=1');
    }

    public function download(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $filePath = Logger::getMainLogPath();
        if (!file_exists($filePath)) {
            @file_put_contents($filePath, '');
        }

        $fileName = 'alaz_logs_' . date('Y-m-d_His') . '.log';
        header('Content-Description: File Transfer');
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}
