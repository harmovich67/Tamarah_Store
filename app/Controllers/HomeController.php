<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use Database\Database;

class HomeController
{
    public function index(Request $request): void
    {
        // 1. Hero slider banners
        $banners = Database::fetchAll("SELECT * FROM banners WHERE status = 'active' ORDER BY order_index ASC");

        // 2. Main categories with active product count
        $categories = Database::fetchAll("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'published') as count
            FROM categories c 
            ORDER BY c.order_index ASC
        ");

        // 3. Featured date products
        $products = Database::fetchAll("
            SELECT p.*, 
                   c.name_ar as category_name_ar, c.name_en as category_name_en, c.slug as category_slug,
                   MIN(pv.price) as min_price, MIN(pv.sale_price) as min_sale_price,
                   SUM(pv.stock_quantity) as total_stock,
                   COUNT(pv.id) as variants_count
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            WHERE p.status = 'published'
            GROUP BY p.id
            ORDER BY p.is_featured DESC, p.id ASC
            LIMIT 8
        ");

        // 4. Pre-order harvest products
        $preorderProducts = Database::fetchAll("
            SELECT p.*, 
                   c.name_ar as category_name_ar, c.name_en as category_name_en, c.slug as category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.is_preorder = 1 AND p.status = 'published'
            ORDER BY p.id ASC
        ");

        // 5. Bestseller products
        $bestsellers = Database::fetchAll("
            SELECT p.*, 
                   c.name_ar as category_name_ar, c.name_en as category_name_en
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.is_bestseller = 1 AND p.status = 'published'
            ORDER BY p.sales_count DESC
            LIMIT 4
        ");

        // 6. Homepage sections in exact saved order
        $homeSectionsRaw = Database::fetchAll("
            SELECT * FROM home_sections WHERE status = 'active' ORDER BY order_index ASC, id ASC
        ");

        $homeSections = [];
        $sectionsByKey = [];
        foreach ($homeSectionsRaw as $sec) {
            $sec['settings'] = !empty($sec['settings_json']) ? json_decode($sec['settings_json'], true) : [];
            $homeSections[] = $sec;
            $sectionsByKey[$sec['section_key']] = $sec;
        }

        Response::view('storefront/home', [
            'banners' => $banners,
            'categories' => $categories,
            'products' => $products,
            'preorderProducts' => $preorderProducts,
            'bestsellers' => $bestsellers,
            'homeSections' => $homeSections,
            'sectionsByKey' => $sectionsByKey
        ]);
    }
}
