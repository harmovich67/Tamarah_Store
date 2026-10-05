<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use Database\Database;

class CatalogController
{
    public function catalog(Request $request): void
    {
        $selectedCat = $request->get('cat');
        $filterPreorder = $request->get('filter') === 'preorder';
        $searchQuery = trim((string)$request->get('search', ''));
        $sort = $request->get('sort', 'featured');
        $priceMin = $request->get('price_min');
        $priceMax = $request->get('price_max');
        $priceMin = (is_numeric($priceMin) && (float)$priceMin > 0) ? (float)$priceMin : null;
        $priceMax = (is_numeric($priceMax) && (float)$priceMax > 0) ? (float)$priceMax : null;
        $availability = $request->get('availability', []);
        $availability = array_values(array_intersect((array)$availability, ['available', 'preorder', 'out_of_stock']));

        // Price bounds of the published range (drives the price slider)
        $bounds = Database::fetchOne("
            SELECT MIN(COALESCE(NULLIF(sale_price, 0), price)) AS min_price, MAX(COALESCE(NULLIF(sale_price, 0), price)) AS max_price
            FROM products WHERE status = 'published'
        ");

        // Fetch categories with active product count
        $categories = Database::fetchAll("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.status = 'published') as count
            FROM categories c 
            ORDER BY c.order_index ASC
        ");

        $sql = "
            SELECT p.*, 
                   c.name_ar as category_name_ar, c.name_en as category_name_en, c.slug as category_slug,
                   MIN(pv.price) as min_price, MIN(pv.sale_price) as min_sale_price,
                   SUM(pv.stock_quantity) as total_stock,
                   COUNT(pv.id) as variants_count
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            WHERE p.status = 'published'
        ";
        $params = [];

        if (!empty($selectedCat)) {
            if (is_numeric($selectedCat)) {
                $sql .= " AND p.category_id = ?";
                $params[] = (int)$selectedCat;
            } else {
                $sql .= " AND (c.slug = ? OR c.id = ?)";
                $params[] = $selectedCat;
                $params[] = $selectedCat;
            }
        }

        if ($filterPreorder) {
            $sql .= " AND p.is_preorder = 1";
        }

        if (!empty($searchQuery)) {
            $sql .= " AND (p.name_ar LIKE ? OR p.name_en LIKE ? OR p.description_ar LIKE ? OR p.description_en LIKE ?)";
            $term = "%{$searchQuery}%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        if ($availability) {
            $conds = [];
            if (in_array('available', $availability, true)) $conds[] = "(p.stock_quantity > 0 AND p.is_preorder = 0)";
            if (in_array('preorder', $availability, true)) $conds[] = "p.is_preorder = 1";
            if (in_array('out_of_stock', $availability, true)) $conds[] = "(p.stock_quantity <= 0 AND p.is_preorder = 0)";
            $sql .= " AND (" . implode(' OR ', $conds) . ")";
        }

        if ($priceMin !== null) {
            $sql .= " AND COALESCE(NULLIF(p.sale_price, 0), p.price) >= ?";
            $params[] = $priceMin;
        }
        if ($priceMax !== null) {
            $sql .= " AND COALESCE(NULLIF(p.sale_price, 0), p.price) <= ?";
            $params[] = $priceMax;
        }

        $sql .= " GROUP BY p.id";

        if ($sort === 'price_asc') {
            $sql .= " ORDER BY p.price ASC";
        } elseif ($sort === 'price_desc') {
            $sql .= " ORDER BY p.price DESC";
        } elseif ($sort === 'rating') {
            $sql .= " ORDER BY p.rating DESC";
        } elseif ($sort === 'bestseller') {
            $sql .= " ORDER BY p.is_bestseller DESC, p.sales_count DESC";
        } else {
            $sql .= " ORDER BY p.is_featured DESC, p.id ASC";
        }

        $products = Database::fetchAll($sql, $params);

        Response::view('storefront/catalog', [
            'products' => $products,
            'categories' => $categories,
            'selected_cat' => $selectedCat,
            'filter_preorder' => $filterPreorder,
            'search_query' => $searchQuery,
            'sort' => $sort,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'availability' => $availability,
            'price_bounds' => [
                'min' => (int)floor((float)($bounds['min_price'] ?? 0)),
                'max' => (int)ceil((float)($bounds['max_price'] ?? 0)),
            ],
            'page_title' => $filterPreorder ? 'تشكيلة الحجز المسبق للحصاد' : 'كافة أقسام التمور الملكية'
        ]);
    }

    public function productDetail(Request $request): void
    {
        $slug = $request->get('slug');
        $product = Database::fetchOne("
            SELECT p.*, 
                   c.name_ar as category_name_ar, c.name_en as category_name_en, c.slug as category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE (p.slug = ? OR p.id = ?) AND p.status = 'published'
        ", [$slug, is_numeric($slug) ? (int)$slug : 0]);

        if (!$product) {
            Response::error(__('no_products_found'), 404);
            return;
        }

        // Fetch variants (weights / packages)
        $variants = Database::fetchAll("
            SELECT * FROM product_variants 
            WHERE product_id = ? 
            ORDER BY price ASC
        ", [$product['id']]);

        // Related products from same category
        $relatedProducts = Database::fetchAll("
            SELECT p.*, 
                   c.name_ar as category_name_ar, c.name_en as category_name_en, c.slug as category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.category_id = ? AND p.id != ? AND p.status = 'published'
            LIMIT 4
        ", [$product['category_id'], $product['id']]);

        Response::view('storefront/product_detail', [
            'product' => $product,
            'variants' => $variants,
            'relatedProducts' => $relatedProducts,
            'page_title' => $product['name_ar']
        ]);
    }

    public function apiProducts(Request $request): void
    {
        $categoryId = $request->get('category_id');
        $search = trim((string)$request->get('search', ''));
        $isPreorder = $request->get('preorder') === '1';

        $sql = "
            SELECT p.*, 
                   c.name_ar as category_name_ar, c.name_en as category_name_en, c.slug as category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.status = 'published'
        ";
        $params = [];

        if (!empty($categoryId)) {
            $sql .= " AND p.category_id = ?";
            $params[] = (int)$categoryId;
        }
        if ($isPreorder) {
            $sql .= " AND p.is_preorder = 1";
        }
        if (!empty($search)) {
            $sql .= " AND (p.name_ar LIKE ? OR p.description_ar LIKE ?)";
            $term = "%{$search}%";
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY p.is_featured DESC, p.id ASC";
        $products = Database::fetchAll($sql, $params);

        Response::json([
            'success' => true,
            'count' => count($products),
            'products' => $products
        ]);
    }

    public function apiVariant(Request $request): void
    {
        $id = (int)$request->get('id');
        $variant = Database::fetchOne("SELECT * FROM product_variants WHERE id = ?", [$id]);

        if (!$variant) {
            Response::json(['success' => false, 'message' => 'Variant not found'], 404);
            return;
        }

        Response::json([
            'success' => true,
            'variant' => $variant
        ]);
    }
}
