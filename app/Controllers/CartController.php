<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\I18n;
use Database\Database;

class CartController
{
    private static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public static function getCartSummary(): array
    {
        self::initSession();
        $cart = $_SESSION['cart'];
        $items = [];
        $subtotal = 0.0;
        $totalItems = 0;

        if (!empty($cart)) {
            foreach ($cart as $key => $cartItem) {
                // Gift card purchases live in the cart as their own line type
                if (is_array($cartItem) && ($cartItem['type'] ?? '') === 'gift_card') {
                    $line = \App\Services\GiftCardService::cartLine((string)$key, $cartItem);
                    $subtotal += $line['subtotal'];
                    $totalItems += $line['quantity'];
                    $items[] = $line;
                    continue;
                }

                // Support both legacy integer variantId key and array structure
                $qty = is_array($cartItem) ? ($cartItem['qty'] ?? 1) : (int)$cartItem;
                $variantId = is_array($cartItem) ? ($cartItem['variant_id'] ?? null) : (int)$key;
                $productId = is_array($cartItem) ? ($cartItem['product_id'] ?? null) : null;

                $itemRow = null;
                if (!empty($variantId)) {
                    $itemRow = Database::fetchOne("
                        SELECT pv.id as variant_id, pv.product_id, pv.size_name, pv.size_name_en, pv.sku,
                               pv.price as variant_price, pv.sale_price as variant_sale_price, pv.stock_quantity as max_stock,
                               p.name_ar, p.name_en, p.slug as product_slug, p.featured_image, p.price, p.sale_price,
                               p.weight, p.is_fragile, p.is_preorder
                        FROM product_variants pv
                        JOIN products p ON pv.product_id = p.id
                        WHERE pv.id = ?
                    ", [$variantId]);
                }

                if (!$itemRow && (!empty($productId) || is_numeric($key))) {
                    $pId = !empty($productId) ? (int)$productId : (int)$key;
                    $itemRow = Database::fetchOne("
                        SELECT NULL as variant_id, p.id as product_id, p.weight as size_name, p.weight as size_name_en, p.sku,
                               p.price as variant_price, p.sale_price as variant_sale_price, p.stock_quantity as max_stock,
                               p.name_ar, p.name_en, p.slug as product_slug, p.featured_image, p.price, p.sale_price,
                               p.weight, p.is_fragile, p.is_preorder
                        FROM products p
                        WHERE p.id = ?
                    ", [$pId]);
                }

                if ($itemRow) {
                    $qty = max(1, min($qty, (int)($itemRow['max_stock'] ?: 99)));
                    $effectivePrice = ($itemRow['variant_sale_price'] !== null && $itemRow['variant_sale_price'] > 0)
                        ? (float)$itemRow['variant_sale_price']
                        : (($itemRow['variant_price'] !== null && $itemRow['variant_price'] > 0) ? (float)$itemRow['variant_price'] : (float)$itemRow['price']);

                    $itemTotal = $effectivePrice * $qty;
                    $subtotal += $itemTotal;
                    $totalItems += $qty;

                    $items[] = [
                        'key' => $key,
                        'variant_id' => $itemRow['variant_id'],
                        'product_id' => $itemRow['product_id'],
                        'product_name' => I18n::getLocale() === 'ar' ? $itemRow['name_ar'] : $itemRow['name_en'],
                        'size_name' => I18n::getLocale() === 'ar' ? ($itemRow['size_name'] ?: $itemRow['weight']) : ($itemRow['size_name_en'] ?: $itemRow['weight']),
                        'sku' => $itemRow['sku'],
                        'image' => $itemRow['featured_image'],
                        'unit_price' => $effectivePrice,
                        'unit_price_formatted' => number_format($effectivePrice, 2) . ' ' . currency(),
                        'quantity' => $qty,
                        'max_stock' => $itemRow['max_stock'],
                        'is_preorder' => !empty($itemRow['is_preorder']),
                        'is_fragile' => !empty($itemRow['is_fragile']),
                        'subtotal' => $itemTotal,
                        'subtotal_formatted' => number_format($itemTotal, 2) . ' ' . currency(),
                    ];
                }
            }
        }

        // Free shipping calculation (threshold from settings, default 300 SAR)
        $freeThresholdSetting = Database::fetchOne("SELECT `value` FROM settings WHERE `key` = 'shipping_free_threshold'");
        $freeShippingThreshold = $freeThresholdSetting ? max(0.0, (float)$freeThresholdSetting['value']) : 300.0;
        $freeShippingRemaining = max(0.0, $freeShippingThreshold - $subtotal);
        $freeShippingProgress = $freeShippingThreshold > 0 ? min(100, round(($subtotal / $freeShippingThreshold) * 100)) : 100;

        // Dynamic Coupon discount from Database
        $discountAmount = 0.0;
        $couponCode = $_SESSION['coupon_code'] ?? null;
        $couponDetails = null;

        if (!empty($couponCode) && $subtotal > 0) {
            $today = date('Y-m-d');
            $coupon = Database::fetchOne("
                SELECT * FROM coupons 
                WHERE code = ? AND is_active = 1
                AND (start_date IS NULL OR start_date <= ?)
                AND (end_date IS NULL OR end_date >= ?)
            ", [$couponCode, $today, $today]);

            if ($coupon) {
                // Check usage limit
                $limitExceeded = (!empty($coupon['usage_limit']) && (int)$coupon['times_used'] >= (int)$coupon['usage_limit']);
                // Check min order amount
                $minSpendMet = empty($coupon['min_order_amount']) || $subtotal >= (float)$coupon['min_order_amount'];

                if (!$limitExceeded && $minSpendMet) {
                    if ($coupon['discount_type'] === 'fixed') {
                        $discountAmount = min($subtotal, (float)$coupon['discount_value']);
                    } else {
                        $pct = (float)$coupon['discount_value'];
                        $discountAmount = round($subtotal * ($pct / 100), 2);
                    }
                    $couponDetails = [
                        'code' => $coupon['code'],
                        'discount_type' => $coupon['discount_type'],
                        'discount_value' => (float)$coupon['discount_value'],
                        'discount_label' => $coupon['discount_type'] === 'percentage'
                            ? ((float)$coupon['discount_value'] . '%')
                            : (number_format((float)$coupon['discount_value'], 2) . ' ' . currency())
                    ];
                } else {
                    unset($_SESSION['coupon_code']);
                    $couponCode = null;
                }
            } elseif ($gift = \App\Services\GiftCardService::findRedeemable($couponCode)) {
                // Gift card codes are redeemed through the same promo field (fixed value, up to the balance)
                $discountAmount = min($subtotal, (float)$gift['balance']);
                $couponDetails = [
                    'code' => $gift['code'],
                    'discount_type' => 'gift_card',
                    'discount_value' => (float)$gift['balance'],
                    'discount_label' => number_format((float)$gift['balance'], 2) . ' ' . currency(),
                    'is_gift_card' => true,
                ];
            } else {
                unset($_SESSION['coupon_code']);
                $couponCode = null;
            }
        }

        $total = max(0.0, $subtotal - $discountAmount);

        return [
            'items' => $items,
            'count' => $totalItems,
            'item_count' => $totalItems,
            'subtotal' => $subtotal,
            'subtotal_formatted' => number_format($subtotal, 2) . ' ' . currency(),
            'discount' => $discountAmount,
            'discount_formatted' => number_format($discountAmount, 2) . ' ' . currency(),
            'coupon_code' => $couponCode,
            'coupon_details' => $couponDetails,
            'total' => $total,
            'total_formatted' => number_format($total, 2) . ' ' . currency(),
            'free_shipping_threshold' => $freeShippingThreshold,
            'free_shipping_remaining' => $freeShippingRemaining,
            'free_shipping_progress' => $freeShippingProgress,
            'is_free_shipping' => $subtotal >= $freeShippingThreshold
        ];
    }


    public function index(Request $request): void
    {
        $summary = self::getCartSummary();
        Response::view('storefront/cart', ['cart' => $summary, 'page_title' => 'سلة المشتريات']);
    }

    public function apiGet(Request $request): void
    {
        $summary = self::getCartSummary();
        Response::json([
            'success' => true,
            'cart' => $summary
        ]);
    }

    public function apiAdd(Request $request): void
    {
        self::initSession();
        $variantId = (int)$request->get('variant_id');
        $productId = (int)$request->get('product_id');
        $qty = max(1, (int)$request->get('quantity', 1));

        if ($variantId > 0) {
            $variant = Database::fetchOne("
                SELECT pv.*, p.name_ar, p.name_en 
                FROM product_variants pv 
                JOIN products p ON pv.product_id = p.id 
                WHERE pv.id = ?
            ", [$variantId]);

            if (!$variant) {
                Response::json(['success' => false, 'message' => 'المنتج أو العبوة المطلوبة غير موجودة'], 404);
                return;
            }

            $currentInCart = is_array($_SESSION['cart'][$variantId] ?? null)
                ? ($_SESSION['cart'][$variantId]['qty'] ?? 0)
                : (int)($_SESSION['cart'][$variantId] ?? 0);

            $newQty = $currentInCart + $qty;
            if ($newQty > $variant['stock_quantity']) {
                Response::json([
                    'success' => false,
                    'message' => "عذراً، الكمية المتاحة في المخزون هي {$variant['stock_quantity']} فقط."
                ], 400);
                return;
            }

            $_SESSION['cart'][$variantId] = [
                'variant_id' => $variantId,
                'product_id' => $variant['product_id'],
                'qty' => $newQty
            ];
        } elseif ($productId > 0) {
            $product = Database::fetchOne("SELECT * FROM products WHERE id = ? AND status = 'published'", [$productId]);
            if (!$product) {
                Response::json(['success' => false, 'message' => 'المنتج غير موجود'], 404);
                return;
            }

            // Find first variant if exists
            $firstVariant = Database::fetchOne("SELECT id, stock_quantity FROM product_variants WHERE product_id = ? LIMIT 1", [$productId]);
            $cartKey = $firstVariant ? (int)$firstVariant['id'] : $productId;
            $stock = $firstVariant ? (int)$firstVariant['stock_quantity'] : (int)$product['stock_quantity'];

            $currentInCart = is_array($_SESSION['cart'][$cartKey] ?? null)
                ? ($_SESSION['cart'][$cartKey]['qty'] ?? 0)
                : (int)($_SESSION['cart'][$cartKey] ?? 0);

            $newQty = $currentInCart + $qty;
            if ($newQty > $stock) {
                Response::json([
                    'success' => false,
                    'message' => "عذراً، الكمية المتاحة في المخزون هي {$stock} فقط."
                ], 400);
                return;
            }

            $_SESSION['cart'][$cartKey] = [
                'variant_id' => $firstVariant ? (int)$firstVariant['id'] : null,
                'product_id' => $productId,
                'qty' => $newQty
            ];
        } else {
            Response::json(['success' => false, 'message' => 'بيانات المنتج غير صحيحة'], 400);
            return;
        }

        $summary = self::getCartSummary();
        Response::json([
            'success' => true,
            'message' => 'تمت إضافة التمر إلى سلة التسوق بنجاح',
            'cart' => $summary
        ]);
    }

    public function apiUpdate(Request $request): void
    {
        self::initSession();
        $key = $request->get('key') ?: $request->get('variant_id');
        $qty = (int)$request->get('quantity', 1);

        if ($key !== null) {
            if ($qty <= 0) {
                unset($_SESSION['cart'][$key]);
            } elseif (isset($_SESSION['cart'][$key])) {
                if (is_array($_SESSION['cart'][$key])) {
                    $_SESSION['cart'][$key]['qty'] = $qty;
                } else {
                    $_SESSION['cart'][$key] = $qty;
                }
            }
        }

        $summary = self::getCartSummary();
        Response::json([
            'success' => true,
            'cart' => $summary
        ]);
    }

    public function apiRemove(Request $request): void
    {
        self::initSession();
        $key = $request->get('key') ?: $request->get('variant_id');
        if ($key !== null) {
            unset($_SESSION['cart'][$key]);
        }

        $summary = self::getCartSummary();
        Response::json([
            'success' => true,
            'message' => 'تم حذف الصنف من السلة',
            'cart' => $summary
        ]);
    }

    public function apiClear(Request $request): void
    {
        self::initSession();
        $_SESSION['cart'] = [];
        unset($_SESSION['coupon_code']);
        $summary = self::getCartSummary();
        Response::json([
            'success' => true,
            'cart' => $summary
        ]);
    }

    public function apiApplyCoupon(Request $request): void
    {
        self::initSession();
        $code = strtoupper(trim((string)($request->get('code') ?: $request->get('coupon_code', ''))));
        if (empty($code)) {
            unset($_SESSION['coupon_code']);
            Response::json(['success' => false, 'message' => 'يرجى إدخال رمز الكوبون'], 400);
            return;
        }

        $cart = self::getCartSummary();
        if (empty($cart['items'])) {
            Response::json(['success' => false, 'message' => 'سلة التسوق فارغة'], 400);
            return;
        }

        $today = date('Y-m-d');
        $coupon = Database::fetchOne("SELECT * FROM coupons WHERE code = ?", [$code]);

        if (!$coupon && ($gift = \App\Services\GiftCardService::findRedeemable($code))) {
            $_SESSION['coupon_code'] = $gift['code'];
            $updatedSummary = self::getCartSummary();
            Response::json([
                'success' => true,
                'message' => 'تم تطبيق بطاقة الإهداء بقيمة ' . $updatedSummary['discount_formatted'],
                'coupon' => [
                    'code' => $gift['code'],
                    'discount' => $updatedSummary['discount'],
                    'discount_formatted' => $updatedSummary['discount_formatted'],
                ],
                'cart' => $updatedSummary,
            ]);
            return;
        }

        if (!$coupon) {
            Response::json(['success' => false, 'message' => 'رمز الكوبون غير صحيح أو غير مسجل'], 400);
            return;
        }

        if (!$coupon['is_active']) {
            Response::json(['success' => false, 'message' => 'عذراً، هذا الكوبون متوقف حالياً'], 400);
            return;
        }

        if (!empty($coupon['start_date']) && $coupon['start_date'] > $today) {
            Response::json(['success' => false, 'message' => 'عذراً، لم يبدأ سريان هذا الكوبون بعد'], 400);
            return;
        }

        if (!empty($coupon['end_date']) && $coupon['end_date'] < $today) {
            Response::json(['success' => false, 'message' => 'عذراً، انتهت فترة صلاحية هذا الكوبون'], 400);
            return;
        }

        if (!empty($coupon['usage_limit']) && (int)$coupon['times_used'] >= (int)$coupon['usage_limit']) {
            Response::json(['success' => false, 'message' => 'عذراً، استنفد هذا الكوبون الحد الأقصى لمرات الاستخدام'], 400);
            return;
        }

        if (!empty($coupon['min_order_amount']) && $cart['subtotal'] < (float)$coupon['min_order_amount']) {
            $minFormatted = number_format((float)$coupon['min_order_amount'], 2);
            Response::json([
                'success' => false,
                'message' => "الحد الأدنى للطلب لتفعيل هذا الكوبون هو {$minFormatted} " . currency()
            ], 400);
            return;
        }

        $_SESSION['coupon_code'] = $code;
        $updatedSummary = self::getCartSummary();

        $discountMsg = $coupon['discount_type'] === 'percentage'
            ? "تم تطبيق خصم {$coupon['discount_value']}% بنجاح!"
            : "تم تطبيق خصم " . number_format((float)$coupon['discount_value'], 2) . " " . currency() . " بنجاح!";

        Response::json([
            'success' => true,
            'message' => $discountMsg,
            'coupon' => [
                'code' => $coupon['code'],
                'discount' => $updatedSummary['discount'],
                'discount_formatted' => $updatedSummary['discount_formatted']
            ],
            'cart' => $updatedSummary
        ]);
    }

    /**
     * Add a configured gift card (type, value, sender, recipient, message) to the cart
     */
    public function apiAddGiftCard(Request $request): void
    {
        self::initSession();
        [$entry, $error] = \App\Services\GiftCardService::buildEntry([
            'card_type' => $request->get('card_type'),
            'amount' => $request->get('amount'),
            'sender_name' => $request->get('sender_name'),
            'sender_phone' => $request->get('sender_phone'),
            'recipient_name' => $request->get('recipient_name'),
            'recipient_phone' => $request->get('recipient_phone'),
            'recipient_email' => $request->get('recipient_email'),
            'message' => $request->get('message'),
        ]);

        if ($error) {
            Response::json(['success' => false, 'message' => $error], 422);
            return;
        }

        $_SESSION['cart']['gift_' . bin2hex(random_bytes(6))] = $entry;
        $summary = self::getCartSummary();
        Response::json([
            'success' => true,
            'message' => \App\Core\I18n::getLocale() === 'en' ? 'Gift card added to your cart' : 'تمت إضافة بطاقة الإهداء إلى السلة',
            'cart' => $summary,
        ]);
    }

    public function apiRemoveCoupon(Request $request): void
    {
        self::initSession();
        unset($_SESSION['coupon_code']);
        $summary = self::getCartSummary();
        Response::json([
            'success' => true,
            'message' => 'تم إلغاء الكوبون بنجاح',
            'cart' => $summary
        ]);
    }
}

