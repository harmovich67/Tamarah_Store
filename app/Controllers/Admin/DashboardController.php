<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use Database\Database;

class DashboardController
{
    public function index(Request $request): void
    {
        $user = Auth::user();
        $isSuper = Auth::isSuperAdmin();

        // 1. Sales & Orders KPIs
        $totalSales = (float)(Database::fetchOne("SELECT SUM(total) as s FROM orders WHERE payment_status = 'paid'")['s'] ?? 0);
        $totalOrders = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM orders")['c'] ?? 0);
        $totalProducts = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM products WHERE status = 'published'")['c'] ?? 0);
        $totalCustomers = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM users WHERE role_id = 3")['c'] ?? 0);
        if ($totalCustomers === 0) {
            $totalCustomers = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM users")['c'] ?? 1);
        }

        // 2. Low Stock Alerts (< 20 units)
        $lowStockVariants = Database::fetchAll("
            SELECT pv.*, p.name_ar as product_name_ar, p.name_en as product_name_en, p.featured_image
            FROM product_variants pv
            JOIN products p ON pv.product_id = p.id
            WHERE pv.stock_quantity <= 20
            ORDER BY pv.stock_quantity ASC
            LIMIT 6
        ");

        // 3. Recent Orders
        $recentOrders = Database::fetchAll("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
            FROM orders o
            ORDER BY o.id DESC 
            LIMIT 6
        ");

        // 4. Categories breakdown
        $categoriesCount = Database::fetchAll("
            SELECT c.name_ar, c.slug, COUNT(p.id) as products_count
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id
            GROUP BY c.id
            ORDER BY products_count DESC
        ");

        Response::view('admin/dashboard', [
            'user' => $user,
            'isSuper' => $isSuper,
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'totalProducts' => $totalProducts,
            'totalCustomers' => $totalCustomers,
            'lowStockVariants' => $lowStockVariants,
            'recentOrders' => $recentOrders,
            'categoriesCount' => $categoriesCount,
            'page_title' => 'لوحة المؤشرات والإحصائيات'
        ], 'admin');
    }
}
