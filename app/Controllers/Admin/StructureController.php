<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Uploader;
use Database\Database;

class StructureController
{
    // ==========================================
    // 1. WAREHOUSES MANAGEMENT (مستودعات التمور والمخازن المبردة)
    // ==========================================
    public function warehouses(Request $request): void
    {
        $isSuper = Auth::isSuperAdmin();

        $warehouses = Database::fetchAll("
            SELECT w.*,
                   (SELECT COUNT(*) FROM products p) as products_count,
                   (SELECT COALESCE(SUM(stock_quantity), 0) FROM products) as total_inventory
            FROM warehouses w
            ORDER BY w.id ASC
        ");

        Response::view('admin/warehouses', [
            'warehouses' => $warehouses,
            'isSuper' => $isSuper,
            'saved' => $request->get('saved') == 1,
            'deleted' => $request->get('deleted') == 1,
        ], 'admin');
    }

    public function storeWarehouse(Request $request): void
    {
        $nameAr = trim((string)$request->get('name_ar'));
        $nameEn = trim((string)$request->get('name_en'));
        $code = trim((string)$request->get('code'));
        $location = trim((string)$request->get('location'));
        $phone = trim((string)$request->get('phone', $request->get('manager_phone')));
        $status = trim((string)$request->get('status', 'active'));

        if (empty($nameAr)) {
            Response::redirect('/admin/warehouses?error=empty_name');
            return;
        }

        Database::execute("
            INSERT INTO warehouses (name_ar, name_en, code, location, phone, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ", [$nameAr, $nameEn ?: $nameAr, $code, $location, $phone, $status]);

        Response::redirect('/admin/warehouses?saved=1');
    }

    public function updateWarehouse(Request $request): void
    {
        $id = (int)$request->get('id');
        $nameAr = trim((string)$request->get('name_ar'));
        $nameEn = trim((string)$request->get('name_en'));
        $code = trim((string)$request->get('code'));
        $location = trim((string)$request->get('location'));
        $phone = trim((string)$request->get('phone', $request->get('manager_phone')));
        $status = trim((string)$request->get('status', 'active'));

        Database::execute("
            UPDATE warehouses
            SET name_ar = ?, name_en = ?, code = ?, location = ?, phone = ?, status = ?
            WHERE id = ?
        ", [$nameAr, $nameEn ?: $nameAr, $code, $location, $phone, $status, $id]);

        Response::redirect('/admin/warehouses?saved=1');
    }

    public function deleteWarehouse(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $id = (int)$request->get('id');
        Database::execute("DELETE FROM warehouses WHERE id = ?", [$id]);
        Response::redirect('/admin/warehouses?deleted=1');
    }

    // ==========================================
    // 2. CATEGORIES MANAGEMENT (أصناف وفئات التمور)
    // ==========================================
    public function categories(Request $request): void
    {
        $isSuper = Auth::isSuperAdmin();

        $categories = Database::fetchAll("
            SELECT c.*,
                   (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as products_count
            FROM categories c
            ORDER BY c.id ASC
        ");

        Response::view('admin/categories', [
            'categories' => $categories,
            'stages' => [],
            'isSuper' => $isSuper,
            'saved' => $request->get('saved') == 1,
            'deleted' => $request->get('deleted') == 1,
        ], 'admin');
    }

    public function storeCategory(Request $request): void
    {
        $nameAr = trim((string)$request->get('name_ar'));
        $nameEn = trim((string)$request->get('name_en'));
        $icon = trim((string)$request->get('icon', 'tag'));
        $slug = trim((string)$request->get('slug'));
        if (empty($slug)) {
            $baseSlug = $nameEn ?: $nameAr;
            $slug = preg_replace('/[^a-zA-Z0-9\x{0621}-\x{064A}-]+/u', '-', strtolower($baseSlug));
            $slug = trim($slug, '-');
        }
        if (empty($slug) || Database::fetchOne("SELECT id FROM categories WHERE slug = ?", [$slug])) {
            $slug = ($slug ?: 'category') . '-' . rand(100, 999);
        }

        $uploadedImage = Uploader::upload('image_file', 'categories');
        $imageUrl = $uploadedImage ?: trim((string)$request->get('image'));

        Database::execute("
            INSERT INTO categories (name_ar, name_en, slug, icon, image)
            VALUES (?, ?, ?, ?, ?)
        ", [$nameAr, $nameEn ?: $nameAr, $slug, $icon, $imageUrl]);

        Response::redirect('/admin/categories?saved=1');
    }

    public function updateCategory(Request $request): void
    {
        $id = (int)$request->get('id');
        $nameAr = trim((string)$request->get('name_ar'));
        $nameEn = trim((string)$request->get('name_en'));
        $icon = trim((string)$request->get('icon', 'tag'));
        $slug = trim((string)$request->get('slug'));
        if (empty($slug)) {
            $baseSlug = $nameEn ?: $nameAr;
            $slug = preg_replace('/[^a-zA-Z0-9\x{0621}-\x{064A}-]+/u', '-', strtolower($baseSlug));
            $slug = trim($slug, '-');
        }
        $existingCat = Database::fetchOne("SELECT id FROM categories WHERE slug = ? AND id != ?", [$slug, $id]);
        if (empty($slug) || $existingCat) {
            $slug = ($slug ?: 'category') . '-' . rand(100, 999);
        }

        $uploadedImage = Uploader::upload('image_file', 'categories');

        if ($uploadedImage) {
            Database::execute("
                UPDATE categories
                SET name_ar = ?, name_en = ?, slug = ?, icon = ?, image = ?
                WHERE id = ?
            ", [$nameAr, $nameEn ?: $nameAr, $slug, $icon, $uploadedImage, $id]);
        } else {
            Database::execute("
                UPDATE categories
                SET name_ar = ?, name_en = ?, slug = ?, icon = ?
                WHERE id = ?
            ", [$nameAr, $nameEn ?: $nameAr, $slug, $icon, $id]);
        }

        Response::redirect('/admin/categories?saved=1');
    }

    public function deleteCategory(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::error('Forbidden', 403);
            return;
        }

        $id = (int)$request->get('id');
        Database::execute("DELETE FROM categories WHERE id = ?", [$id]);
        Response::redirect('/admin/categories?deleted=1');
    }
}
