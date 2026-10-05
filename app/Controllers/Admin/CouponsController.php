<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Logger;
use Database\Database;

class CouponsController
{
    public function index(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::redirect('/admin');
            return;
        }

        $search = trim((string)$request->get('q', ''));
        $status = $request->get('status', 'all');

        $whereClauses = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $whereClauses[] = "(`code` LIKE ?)";
            $params[] = "%{$search}%";
        }

        if ($status === 'active') {
            $whereClauses[] = "(`is_active` = 1)";
        } elseif ($status === 'inactive') {
            $whereClauses[] = "(`is_active` = 0)";
        }

        $whereSql = implode(' AND ', $whereClauses);
        $coupons = Database::fetchAll("
            SELECT * FROM coupons 
            WHERE {$whereSql} 
            ORDER BY id DESC
        ", $params);

        // Calculate KPI Stats
        $stats = [
            'total' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM coupons")['c'] ?? 0),
            'active' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM coupons WHERE is_active = 1")['c'] ?? 0),
            'total_uses' => (int)(Database::fetchOne("SELECT SUM(times_used) as c FROM coupons")['c'] ?? 0),
            'percentage_count' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM coupons WHERE discount_type = 'percentage'")['c'] ?? 0),
            'fixed_count' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM coupons WHERE discount_type = 'fixed'")['c'] ?? 0),
        ];

        Response::view('admin/coupons', [
            'coupons' => $coupons,
            'stats' => $stats,
            'search' => $search,
            'status' => $status,
            'saved' => $request->get('saved') == 1,
            'deleted' => $request->get('deleted') == 1,
            'error' => $request->get('error')
        ], 'admin');
    }

    public function store(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $code = strtoupper(preg_replace('/[^A-Z0-9_-]/', '', trim((string)$request->get('code'))));
        $discountType = $request->get('discount_type') === 'fixed' ? 'fixed' : 'percentage';
        $discountValue = max(0.01, (float)$request->get('discount_value'));
        $minOrder = max(0.0, (float)$request->get('min_order_amount', 0));
        $usageLimit = !empty($request->get('usage_limit')) ? max(1, (int)$request->get('usage_limit')) : null;
        $startDate = !empty($request->get('start_date')) ? $request->get('start_date') : null;
        $endDate = !empty($request->get('end_date')) ? $request->get('end_date') : null;
        $isActive = $request->get('is_active') == '1' ? 1 : 0;

        if (empty($code)) {
            Response::redirect('/admin/coupons?error=' . urlencode('يرجى كتابة رمز الكوبون بشكل صحيح'));
            return;
        }

        // Check if code already exists
        $existing = Database::fetchOne("SELECT id FROM coupons WHERE code = ?", [$code]);
        if ($existing) {
            Response::redirect('/admin/coupons?error=' . urlencode('رمز الكوبون مسجل مسبقاً، يرجى اختيار رمز آخر'));
            return;
        }

        Database::execute("
            INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, usage_limit, times_used, start_date, end_date, is_active)
            VALUES (?, ?, ?, ?, ?, 0, ?, ?, ?)
        ", [$code, $discountType, $discountValue, $minOrder, $usageLimit, $startDate, $endDate, $isActive]);

        Logger::info("New coupon created: {$code} ({$discountType} {$discountValue})");
        Response::redirect('/admin/coupons?saved=1');
    }

    public function update(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $id = (int)$request->get('id');
        $code = strtoupper(preg_replace('/[^A-Z0-9_-]/', '', trim((string)$request->get('code'))));
        $discountType = $request->get('discount_type') === 'fixed' ? 'fixed' : 'percentage';
        $discountValue = max(0.01, (float)$request->get('discount_value'));
        $minOrder = max(0.0, (float)$request->get('min_order_amount', 0));
        $usageLimit = !empty($request->get('usage_limit')) ? max(1, (int)$request->get('usage_limit')) : null;
        $startDate = !empty($request->get('start_date')) ? $request->get('start_date') : null;
        $endDate = !empty($request->get('end_date')) ? $request->get('end_date') : null;
        $isActive = $request->get('is_active') == '1' ? 1 : 0;

        if (empty($id) || empty($code)) {
            Response::redirect('/admin/coupons?error=' . urlencode('بيانات الكوبون غير مكتملة'));
            return;
        }

        // Check code uniqueness excluding this id
        $existing = Database::fetchOne("SELECT id FROM coupons WHERE code = ? AND id != ?", [$code, $id]);
        if ($existing) {
            Response::redirect('/admin/coupons?error=' . urlencode('رمز الكوبون مستخدم في كوبون آخر'));
            return;
        }

        Database::execute("
            UPDATE coupons 
            SET code = ?, discount_type = ?, discount_value = ?, min_order_amount = ?, 
                usage_limit = ?, start_date = ?, end_date = ?, is_active = ?
            WHERE id = ?
        ", [$code, $discountType, $discountValue, $minOrder, $usageLimit, $startDate, $endDate, $isActive, $id]);

        Logger::info("Coupon updated: ID {$id} ({$code})");
        Response::redirect('/admin/coupons?saved=1');
    }

    public function delete(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $id = (int)$request->get('id');
        if ($id > 0) {
            Database::execute("DELETE FROM coupons WHERE id = ?", [$id]);
            Logger::info("Coupon deleted: ID {$id}");
        }

        Response::redirect('/admin/coupons?deleted=1');
    }

    public function toggle(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::json(['success' => false, 'message' => 'Forbidden'], 403);
            return;
        }

        $id = (int)$request->get('id');
        $coupon = Database::fetchOne("SELECT id, is_active FROM coupons WHERE id = ?", [$id]);
        if (!$coupon) {
            Response::json(['success' => false, 'message' => 'الكوبون غير موجود'], 404);
            return;
        }

        $newStatus = $coupon['is_active'] ? 0 : 1;
        Database::execute("UPDATE coupons SET is_active = ? WHERE id = ?", [$newStatus, $id]);

        Response::json([
            'success' => true,
            'is_active' => $newStatus,
            'message' => $newStatus ? 'تم تفعيل الكوبون' : 'تم إيقاف الكوبون'
        ]);
    }
}
