<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Cache;
use App\Core\Logger;
use App\Core\Request;
use App\Core\Response;

class CacheController
{
    public function index(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::redirect('/admin');
            return;
        }

        $stats = Cache::stats();

        Response::view('admin/cache', [
            'stats' => $stats,
            'cleared' => $request->get('cleared'),
            'preloaded' => $request->get('preloaded') == 1,
            'warmLog' => $_SESSION['_cache_warm_log'] ?? []
        ], 'admin');

        unset($_SESSION['_cache_warm_log']);
    }

    public function clear(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'error' => 'Forbidden'], 403);
            } else {
                Response::redirect('/admin');
            }
            return;
        }

        $group = trim((string)$request->get('group', 'all'));
        Cache::flush($group);

        Logger::info("Cache flushed for group [{$group}] by " . Auth::user()['name'], [], 'system');

        if ($request->isAjax()) {
            $updatedStats = Cache::stats();
            Response::json([
                'success' => true,
                'message' => 'تم تفريغ الذاكرة المؤقتة بنجاح',
                'group' => $group,
                'stats' => $updatedStats
            ]);
            return;
        }

        Response::redirect('/admin/cache?cleared=' . urlencode($group));
    }

    public function preload(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            if ($request->isAjax()) {
                Response::json(['success' => false, 'error' => 'Forbidden'], 403);
            } else {
                Response::redirect('/admin');
            }
            return;
        }

        $warmLog = Cache::preload();
        $_SESSION['_cache_warm_log'] = $warmLog;

        Logger::info("Cache preloaded & warmed by " . Auth::user()['name'], ['items' => $warmLog], 'system');

        if ($request->isAjax()) {
            $updatedStats = Cache::stats();
            Response::json([
                'success' => true,
                'message' => 'تم إحماء وتوليد الكاش بنجاح',
                'warmed' => $warmLog,
                'stats' => $updatedStats
            ]);
            return;
        }

        Response::redirect('/admin/cache?preloaded=1');
    }
}
