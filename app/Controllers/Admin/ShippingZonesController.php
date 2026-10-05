<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Logger;
use Database\Database;

class ShippingZonesController
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
            $whereClauses[] = "(`city_name_ar` LIKE ? OR `city_name_en` LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        if ($status === 'active') {
            $whereClauses[] = "(`is_active` = 1)";
        } elseif ($status === 'inactive') {
            $whereClauses[] = "(`is_active` = 0)";
        }

        $whereSql = implode(' AND ', $whereClauses);
        $zones = Database::fetchAll("
            SELECT * FROM shipping_zones 
            WHERE {$whereSql} 
            ORDER BY is_active DESC, shipping_fee ASC, id ASC
        ", $params);

        $stats = [
            'total' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM shipping_zones")['c'] ?? 0),
            'active' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM shipping_zones WHERE is_active = 1")['c'] ?? 0),
            'avg_fee' => (float)(Database::fetchOne("SELECT AVG(shipping_fee) as a FROM shipping_zones WHERE is_active = 1")['a'] ?? 25.0),
            'cold_count' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM shipping_zones WHERE is_cold_shipping = 1 AND is_active = 1")['c'] ?? 0),
        ];

        Response::view('admin/shipping_zones', [
            'zones' => $zones,
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

        $nameAr = trim((string)$request->get('city_name_ar'));
        $nameEn = trim((string)$request->get('city_name_en'));
        $fee = max(0.0, (float)$request->get('shipping_fee', 25.0));
        $delivery = trim((string)$request->get('estimated_delivery', 'توصيل مبرد خلال 24-48 ساعة'));
        $isCold = $request->get('is_cold_shipping') == '1' ? 1 : 0;
        $isActive = $request->get('is_active') == '1' ? 1 : 0;

        if (empty($nameAr)) {
            Response::redirect('/admin/shipping-zones?error=' . urlencode('يرجى إدخال اسم المدينة بالعربية'));
            return;
        }

        if (empty($nameEn)) {
            $nameEn = $nameAr;
        }

        // Unique check
        $exists = Database::fetchOne("SELECT id FROM shipping_zones WHERE city_name_ar = ?", [$nameAr]);
        if ($exists) {
            Response::redirect('/admin/shipping-zones?error=' . urlencode('هذه المدينة مسجلة مسبقاً في مناطق الشحن'));
            return;
        }

        Database::execute("
            INSERT INTO shipping_zones (city_name_ar, city_name_en, shipping_fee, estimated_delivery, is_cold_shipping, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ", [$nameAr, $nameEn, $fee, $delivery, $isCold, $isActive]);

        Logger::info("New Saudi shipping zone added: {$nameAr} ({$fee} SAR)");
        Response::redirect('/admin/shipping-zones?saved=1');
    }

    public function update(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $id = (int)$request->get('id');
        $nameAr = trim((string)$request->get('city_name_ar'));
        $nameEn = trim((string)$request->get('city_name_en'));
        $fee = max(0.0, (float)$request->get('shipping_fee', 25.0));
        $delivery = trim((string)$request->get('estimated_delivery', 'توصيل مبرد خلال 24-48 ساعة'));
        $isCold = $request->get('is_cold_shipping') == '1' ? 1 : 0;
        $isActive = $request->get('is_active') == '1' ? 1 : 0;

        if (empty($id) || empty($nameAr)) {
            Response::redirect('/admin/shipping-zones?error=' . urlencode('بيانات المدينة غير مكتملة'));
            return;
        }

        $exists = Database::fetchOne("SELECT id FROM shipping_zones WHERE city_name_ar = ? AND id != ?", [$nameAr, $id]);
        if ($exists) {
            Response::redirect('/admin/shipping-zones?error=' . urlencode('اسم المدينة مسجل لمدينة أخرى'));
            return;
        }

        Database::execute("
            UPDATE shipping_zones 
            SET city_name_ar = ?, city_name_en = ?, shipping_fee = ?, estimated_delivery = ?, 
                is_cold_shipping = ?, is_active = ?
            WHERE id = ?
        ", [$nameAr, $nameEn, $fee, $delivery, $isCold, $isActive, $id]);

        Logger::info("Saudi shipping zone updated: ID {$id} ({$nameAr})");
        Response::redirect('/admin/shipping-zones?saved=1');
    }

    public function delete(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $id = (int)$request->get('id');
        if ($id > 0) {
            Database::execute("DELETE FROM shipping_zones WHERE id = ?", [$id]);
            Logger::info("Shipping zone deleted: ID {$id}");
        }

        Response::redirect('/admin/shipping-zones?deleted=1');
    }

    public function toggle(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::json(['success' => false, 'message' => 'Forbidden'], 403);
            return;
        }

        $id = (int)$request->get('id');
        $zone = Database::fetchOne("SELECT id, is_active FROM shipping_zones WHERE id = ?", [$id]);
        if (!$zone) {
            Response::json(['success' => false, 'message' => 'المدينة غير موجودة'], 404);
            return;
        }

        $newStatus = $zone['is_active'] ? 0 : 1;
        Database::execute("UPDATE shipping_zones SET is_active = ? WHERE id = ?", [$newStatus, $id]);

        Response::json([
            'success' => true,
            'is_active' => $newStatus,
            'message' => $newStatus ? 'تم تفعيل التوصيل للمدينة' : 'تم إيقاف التوصيل للمدينة'
        ]);
    }
}
