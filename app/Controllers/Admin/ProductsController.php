<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\CustomFields;
use Database\Database;

class ProductsController
{
    public function index(Request $request): void
    {
        $isSuper = Auth::isSuperAdmin();
        $selectedCat = $request->get('cat');

        $sql = "
            SELECT p.*, 
                   c.name_ar as category_name_ar, c.name_en as category_name_en,
                   COUNT(pv.id) as variants_count,
                   COALESCE(SUM(pv.stock_quantity), p.stock_quantity) as total_stock,
                   COALESCE(MIN(pv.price), p.price) as min_price, 
                   COALESCE(MAX(pv.price), p.price) as max_price
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
        ";

        $params = [];
        if (!empty($selectedCat)) {
            $sql .= " WHERE c.slug = ? OR c.id = ?";
            $params[] = $selectedCat;
            $params[] = $selectedCat;
        }

        $sql .= " GROUP BY p.id ORDER BY p.id DESC";
        $products = Database::fetchAll($sql, $params);

        Response::view('admin/products', [
            'products' => $products,
            'isSuper' => $isSuper,
            'page_title' => 'إدارة التمور والأصناف الملكية'
        ], 'admin');
    }

    public function create(Request $request): void
    {
        $categories = Database::fetchAll("SELECT * FROM categories ORDER BY order_index ASC");

        Response::view('admin/product_create', [
            'categories' => $categories,
            'isSuper' => Auth::isSuperAdmin(),
            'page_title' => 'إضافة صنف تمور جديد'
        ], 'admin');
    }

    public function apiGetStages(Request $request): void
    {
        Response::json(['success' => true, 'stages' => []]);
    }

    public function store(Request $request): void
    {
        $categoryId = (int)$request->get('category_id', 1);
        $nameAr = trim((string)$request->get('name_ar'));
        $nameEn = trim((string)$request->get('name_en'));
        $descAr = trim((string)$request->get('description_ar'));
        $descEn = trim((string)$request->get('description_en'));
        $price = (float)$request->get('price', 0);
        $salePrice = $request->get('sale_price') ? (float)$request->get('sale_price') : null;
        $weight = trim((string)$request->get('weight', '1 كجم'));
        $stockQty = (int)$request->get('stock_quantity', 50);
        $sku = trim((string)$request->get('sku', 'TUM-' . rand(100, 999)));
        $badge = trim((string)$request->get('badge'));
        $badgeEn = trim((string)$request->get('badge_en'));
        $isPreorder = $request->get('is_preorder') === '1' ? 1 : 0;
        $preorderDate = trim((string)$request->get('preorder_date'));
        $isFragile = $request->get('is_fragile') === '1' ? 1 : 0;
        $imageUrl = trim((string)$request->get('featured_image'));

        $uploadedImage = \App\Core\Uploader::upload('image_file', 'products');
        if ($uploadedImage) {
            $imageUrl = $uploadedImage;
        }

        if (empty($imageUrl)) {
            $imageUrl = 'assets/images/ajwa_luxury_box_1787053900509.jpg';
        }

        $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($nameEn ?: $nameAr));
        $slug = trim($slug, '-');
        if (empty($slug)) $slug = 'date-product';
        $slug .= '-' . rand(100, 999);

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                INSERT INTO products (
                    category_id, name_ar, name_en, slug, description_ar, description_en,
                    price, sale_price, weight, stock_quantity, sku, badge, badge_en,
                    is_preorder, preorder_date, is_fragile, featured_image, gallery, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'published')
            ");

            $stmt->execute([
                $categoryId, $nameAr, $nameEn ?: $nameAr, $slug, $descAr, $descEn,
                $price, $salePrice, $weight, $stockQty, $sku, $badge, $badgeEn,
                $isPreorder, $preorderDate, $isFragile, $imageUrl, json_encode([$imageUrl])
            ]);

            $productId = (int)$pdo->lastInsertId();

            // Insert variants if provided
            $sizes = $request->get('sizes') ?? [];
            $sizesEn = $request->get('sizes_en') ?? [];
            $prices = $request->get('prices') ?? [];
            $sales = $request->get('sales') ?? [];
            $stocks = $request->get('stocks') ?? [];
            $skus = $request->get('skus') ?? [];

            $stmtVar = $pdo->prepare("
                INSERT INTO product_variants (product_id, size_name, size_name_en, sku, price, sale_price, stock_quantity, weight_kg)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if (!empty($sizes) && is_array($sizes)) {
                for ($i = 0; $i < count($sizes); $i++) {
                    $sizeName = trim((string)($sizes[$i] ?? ''));
                    if (empty($sizeName)) continue;

                    $sizeNameEn = trim((string)($sizesEn[$i] ?? ''));
                    $vPrice = (float)($prices[$i] ?? $price);
                    $vSale = !empty($sales[$i]) ? (float)$sales[$i] : null;
                    $vStock = (int)($stocks[$i] ?? $stockQty);
                    $vSku = !empty($skus[$i]) ? trim((string)$skus[$i]) : ($sku . '-' . ($i + 1));

                    $stmtVar->execute([$productId, $sizeName, $sizeNameEn ?: null, $vSku, $vPrice, $vSale, $vStock, 1.0]);
                }
            } else {
                $stmtVar->execute([$productId, $weight ?: 'عبوة 1 كجم', '1kg Pack', $sku . '-01', $price, $salePrice, $stockQty, 1.0]);
            }

            CustomFields::saveEntityFields('product', $productId, $request->all(), $_FILES);

            $pdo->commit();
            Response::redirect('/admin/products?created=1');
        } catch (\Exception $e) {
            $pdo->rollBack();
            Response::error('فشل حفظ الصنف: ' . $e->getMessage());
        }
    }

    public function edit(Request $request): void
    {
        $id = (int)$request->get('id');
        $product = Database::fetchOne("SELECT * FROM products WHERE id = ?", [$id]);

        if (!$product) {
            Response::redirect('/admin/products');
            return;
        }

        $categories = Database::fetchAll("SELECT * FROM categories ORDER BY order_index ASC");
        $variants = Database::fetchAll("SELECT * FROM product_variants WHERE product_id = ?", [$id]);

        Response::view('admin/product_edit', [
            'product' => $product,
            'categories' => $categories,
            'variants' => $variants,
            'isSuper' => Auth::isSuperAdmin(),
            'page_title' => 'تعديل صنف: ' . $product['name_ar']
        ], 'admin');
    }

    public function update(Request $request): void
    {
        $id = (int)$request->get('id');
        $product = Database::fetchOne("SELECT * FROM products WHERE id = ?", [$id]);

        if (!$product) {
            Response::error('المنتج غير موجود', 404);
            return;
        }

        $categoryId = (int)$request->get('category_id', $product['category_id']);
        $nameAr = trim((string)$request->get('name_ar'));
        $nameEn = trim((string)$request->get('name_en'));
        $descAr = trim((string)$request->get('description_ar'));
        $descEn = trim((string)$request->get('description_en'));
        $price = (float)$request->get('price', $product['price']);
        $salePrice = $request->get('sale_price') ? (float)$request->get('sale_price') : null;
        $weight = trim((string)$request->get('weight', $product['weight']));
        $stockQty = (int)$request->get('stock_quantity', $product['stock_quantity']);
        $sku = trim((string)$request->get('sku', $product['sku']));
        $badge = trim((string)$request->get('badge'));
        $badgeEn = trim((string)$request->get('badge_en'));
        $isPreorder = $request->get('is_preorder') === '1' ? 1 : 0;
        $preorderDate = trim((string)$request->get('preorder_date'));
        $isFragile = $request->get('is_fragile') === '1' ? 1 : 0;
        $status = trim((string)$request->get('status', 'published'));
        $imageUrl = trim((string)$request->get('featured_image', $product['featured_image']));

        $uploadedImage = \App\Core\Uploader::upload('image_file', 'products');
        if ($uploadedImage) {
            $imageUrl = $uploadedImage;
        }

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
                UPDATE products SET
                    category_id = ?, name_ar = ?, name_en = ?, description_ar = ?, description_en = ?,
                    price = ?, sale_price = ?, weight = ?, stock_quantity = ?, sku = ?,
                    badge = ?, badge_en = ?, is_preorder = ?, preorder_date = ?, is_fragile = ?,
                    featured_image = ?, status = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $categoryId, $nameAr, $nameEn ?: $nameAr, $descAr, $descEn,
                $price, $salePrice, $weight, $stockQty, $sku,
                $badge, $badgeEn, $isPreorder, $preorderDate, $isFragile,
                $imageUrl, $status, $id
            ]);

            // If variants posted, update or recreate
            $sizes = $request->get('sizes') ?? [];
            if (!empty($sizes) && is_array($sizes)) {
                $pdo->exec("DELETE FROM product_variants WHERE product_id = " . (int)$id);
                $stmtVar = $pdo->prepare("
                    INSERT INTO product_variants (product_id, size_name, size_name_en, sku, price, sale_price, stock_quantity, weight_kg)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $prices = $request->get('prices') ?? [];
                $sales = $request->get('sales') ?? [];
                $stocks = $request->get('stocks') ?? [];
                $skus = $request->get('skus') ?? [];

                for ($i = 0; $i < count($sizes); $i++) {
                    $sizeName = trim((string)($sizes[$i] ?? ''));
                    if (empty($sizeName)) continue;
                    $vPrice = (float)($prices[$i] ?? $price);
                    $vSale = !empty($sales[$i]) ? (float)$sales[$i] : null;
                    $vStock = (int)($stocks[$i] ?? $stockQty);
                    $vSku = !empty($skus[$i]) ? trim((string)$skus[$i]) : ($sku . '-' . ($i + 1));
                    $stmtVar->execute([$id, $sizeName, $sizeName, $vSku, $vPrice, $vSale, $vStock, 1.0]);
                }
            }

            CustomFields::saveEntityFields('product', $id, $request->all(), $_FILES);

            $pdo->commit();
            Response::redirect('/admin/products?updated=1');
        } catch (\Exception $e) {
            $pdo->rollBack();
            Response::error('فشل تحديث الصنف: ' . $e->getMessage());
        }
    }

    public function delete(Request $request): void
    {
        $id = (int)$request->get('id');
        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $pdo->exec("DELETE FROM product_variants WHERE product_id = " . (int)$id);
            $pdo->exec("DELETE FROM custom_field_values WHERE entity_type = 'product' AND entity_id = " . (int)$id);
            $pdo->exec("DELETE FROM products WHERE id = " . (int)$id);
            $pdo->commit();
            Response::redirect('/admin/products?deleted=1');
        } catch (\Exception $e) {
            $pdo->rollBack();
            Response::error('فشل حذف الصنف: ' . $e->getMessage());
        }
    }

    public function apiUpdateStock(Request $request): void
    {
        $id = (int)$request->get('id');
        $stock = max(0, (int)$request->get('stock'));
        Database::execute("UPDATE product_variants SET stock_quantity = ? WHERE id = ?", [$stock, $id]);
        Response::json(['success' => true, 'stock' => $stock]);
    }
}
