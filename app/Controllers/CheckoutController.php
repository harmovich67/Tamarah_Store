<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\I18n;
use App\Core\Logger;
use App\Services\Payment\PaymentManager;
use Database\Database;
use PDO;

class CheckoutController
{
    public static function saudiCities(): array
    {
        try {
            $rows = Database::fetchAll("SELECT * FROM shipping_zones WHERE is_active = 1 ORDER BY id ASC");
            if (!empty($rows)) {
                return $rows;
            }
        } catch (\Throwable $e) {}

        return [
            ['city_name_ar' => 'الرياض', 'city_name_en' => 'Riyadh', 'shipping_fee' => 20.0, 'estimated_delivery' => 'توصيل مبرد نفس اليوم (خلال ساعات)', 'is_cold_shipping' => 1],
            ['city_name_ar' => 'جدة', 'city_name_en' => 'Jeddah', 'shipping_fee' => 25.0, 'estimated_delivery' => 'توصيل مبرد خلال 24-48 ساعة', 'is_cold_shipping' => 1],
            ['city_name_ar' => 'مكة المكرمة', 'city_name_en' => 'Makkah', 'shipping_fee' => 25.0, 'estimated_delivery' => 'توصيل مبرد خلال 24-48 ساعة', 'is_cold_shipping' => 1],
            ['city_name_ar' => 'المدينة المنورة', 'city_name_en' => 'Madinah', 'shipping_fee' => 20.0, 'estimated_delivery' => 'توصيل مبرد سريع (24 ساعة)', 'is_cold_shipping' => 1],
            ['city_name_ar' => 'الدمام', 'city_name_en' => 'Dammam', 'shipping_fee' => 25.0, 'estimated_delivery' => 'توصيل مبرد خلال 24-48 ساعة', 'is_cold_shipping' => 1],
        ];
    }

    public static function getCityShippingFee(string $cityName, float $subtotal): array
    {
        $freeThresholdSetting = Database::fetchOne("SELECT `value` FROM settings WHERE `key` = 'shipping_free_threshold'");
        $threshold = $freeThresholdSetting ? max(0.0, (float)$freeThresholdSetting['value']) : 300.0;

        if ($subtotal >= $threshold) {
            return [
                'fee' => 0.0,
                'is_free' => true,
                'delivery' => 'توصيل مبرد مجاني للطلبات المميزة',
                'is_cold' => 1
            ];
        }

        $zone = Database::fetchOne("SELECT * FROM shipping_zones WHERE city_name_ar = ? AND is_active = 1", [$cityName]);
        if ($zone) {
            return [
                'fee' => (float)$zone['shipping_fee'],
                'is_free' => false,
                'delivery' => $zone['estimated_delivery'] ?: 'توصيل مبرد خلال 24-48 ساعة',
                'is_cold' => (int)$zone['is_cold_shipping']
            ];
        }

        $standardFeeSetting = Database::fetchOne("SELECT `value` FROM settings WHERE `key` = 'shipping_standard_fee'");
        $stdFee = $standardFeeSetting ? max(0.0, (float)$standardFeeSetting['value']) : 25.0;

        return [
            'fee' => $stdFee,
            'is_free' => false,
            'delivery' => 'توصيل مبرد خلال 24-48 ساعة',
            'is_cold' => 1
        ];
    }

    private static function guestCheckoutAllowed(): bool
    {
        $guestSetting = Database::fetchOne("SELECT `value` FROM settings WHERE `key` = 'allow_guest_checkout'");
        return $guestSetting === null || $guestSetting['value'] === '1';
    }

    public function index(Request $request): void
    {
        $cart = CartController::getCartSummary();
        if (empty($cart['items'])) {
            Response::redirect('/cart');
            return;
        }

        if (!self::guestCheckoutAllowed() && !Auth::check()) {
            $_SESSION['intended_checkout'] = true;
            Response::redirect('/login?required_login=1');
            return;
        }

        $userId = Auth::id();
        $savedAddresses = $userId ? Database::fetchAll("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC", [$userId]) : [];
        $defaultAddr = !empty($savedAddresses) ? $savedAddresses[0] : null;

        $cities = self::saudiCities();
        $defaultCityName = !empty($cities) ? ($cities[0]['city_name_ar'] ?? 'الرياض') : 'الرياض';
        $shippingCalc = self::getCityShippingFee($defaultCityName, (float)$cart['subtotal']);
        $shippingFee = $shippingCalc['fee'];
        $total = max(0.0, (float)$cart['total'] + $shippingFee);

        $settings = PaymentManager::getSettings();

        Response::view('storefront/checkout', [
            'cart' => $cart,
            'cities' => $cities,
            'defaultAddress' => $defaultAddr,
            'savedAddresses' => $savedAddresses,
            'shippingFee' => $shippingFee,
            'shippingDelivery' => $shippingCalc['delivery'],
            'total' => $total,
            'settings' => $settings,
            'error_message' => $request->get('error'),
            'page_title' => 'إنهاء الطلب والدفع'
        ]);
    }

    public function apiCalculateShipping(Request $request): void
    {
        $cart = CartController::getCartSummary();
        $shippingType = $request->get('shipping_type', 'home_delivery');
        $cityName = trim((string)$request->get('city', 'الرياض'));

        $fee = 0.0;
        $delivery = 'استلام من فروع تمرنا المعتمدة';
        $isCold = 0;

        if ($shippingType === 'branch_pickup') {
            $fee = 0.0;
        } else {
            $calc = self::getCityShippingFee($cityName, (float)$cart['subtotal']);
            $fee = $calc['fee'];
            $delivery = $calc['delivery'];
            $isCold = $calc['is_cold'];
        }

        $total = max(0.0, (float)$cart['subtotal'] - (float)$cart['discount'] + $fee);

        Response::json([
            'success' => true,
            'shipping_fee' => $fee,
            'shipping_fee_formatted' => $fee == 0 ? 'مجاناً' : (number_format($fee, 2) . ' ' . currency()),
            'delivery_estimate' => $delivery,
            'is_cold' => $isCold,
            'subtotal' => $cart['subtotal'],
            'discount' => $cart['discount'],
            'total' => $total,
            'total_formatted' => number_format($total, 2) . ' ' . currency()
        ]);
    }

    public function process(Request $request): void
    {
        $cart = CartController::getCartSummary();
        if (empty($cart['items'])) {
            Response::redirect('/cart');
            return;
        }

        // Strict Guest Checkout Policy enforcement
        if (!self::guestCheckoutAllowed() && !Auth::check()) {
            $_SESSION['intended_checkout'] = true;
            Response::redirect('/login?required_login=1');
            return;
        }

        $firstName = trim((string)$request->get('first_name', ''));
        $lastName = trim((string)$request->get('last_name', ''));
        $fullName = trim("{$firstName} {$lastName}");
        if (empty($fullName)) {
            $fullName = trim((string)$request->get('customer_name', 'عميل المتجر'));
        }

        $phone = trim((string)$request->get('phone', '')) ?: trim((string)$request->get('customer_phone', ''));
        $email = trim((string)$request->get('email', '')) ?: trim((string)$request->get('customer_email', ''));
        $city = trim((string)$request->get('city', 'الرياض'));
        $district = trim((string)$request->get('district', ''));
        $address = trim((string)$request->get('address', '')) ?: trim((string)$request->get('shipping_address', ''));
        $shippingType = $request->get('shipping_type') ?: ($request->get('delivery_type') === 'branch' || $request->get('delivery_type') === 'pickup' ? 'branch_pickup' : 'home_delivery');
        $paymentMethod = $request->get('payment_method', 'mada');
        $notes = trim((string)$request->get('notes', ''));

        if (empty($phone) || (empty($address) && $shippingType === 'home_delivery')) {
            Response::error('يرجى ملء كافة حقول العنوان ورقم الهاتف للتوصيل');
            return;
        }

        // Calculate dynamic shipping fee
        $shippingFee = 0.0;
        if ($shippingType === 'branch_pickup') {
            $shippingFee = 0.0;
        } else {
            $calc = self::getCityShippingFee($city, (float)$cart['subtotal']);
            $shippingFee = $calc['fee'];
        }

        // COD additional fee if applicable
        $settings = PaymentManager::getSettings();
        $codFee = ($paymentMethod === 'cod') ? max(0.0, (float)($settings['cod_fee'] ?? 0.0)) : 0.0;

        $discountAmount = (float)($cart['discount'] ?? 0.0);
        $couponCode = !empty($cart['coupon_code']) ? $cart['coupon_code'] : null;
        $total = max(0.0, (float)$cart['subtotal'] - $discountAmount + $shippingFee + $codFee);

        $pdo = Database::getConnection();
        $pdo->beginTransaction();

        try {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
            $userId = Auth::id() ?: null;
            $paymentStatus = 'pending'; // Start as pending until verified by gateway

            $fullAddressText = $shippingType === 'branch_pickup'
                ? "استلام من الفرع ({$city})"
                : "المدينة: {$city} | الحي: {$district} | العنوان: {$address}";

            $stmtOrder = $pdo->prepare("
                INSERT INTO orders (
                    order_number, user_id, customer_name, customer_phone, customer_email,
                    shipping_address, city, district, shipping_type, subtotal,
                    shipping_fee, discount, coupon_code, total, payment_method, payment_status,
                    shipping_service, shipping_status, notes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'standard_freight', 'processing', ?, NOW())
            ");

            $stmtOrder->execute([
                $orderNumber, $userId, $fullName, $phone, $email,
                $fullAddressText, $city, $district, $shippingType,
                $cart['subtotal'], $shippingFee + $codFee, $discountAmount, $couponCode, $total,
                $paymentMethod, $paymentStatus, $notes
            ]);

            $orderId = (int)$pdo->lastInsertId();

            // Increment coupon usage if used (gift card codes are consumed instead)
            if ($couponCode) {
                if (!empty($cart['coupon_details']['is_gift_card'])) {
                    \App\Services\GiftCardService::consume($pdo, $couponCode, $discountAmount);
                } else {
                    $stmtCoupon = $pdo->prepare("UPDATE coupons SET times_used = times_used + 1 WHERE code = ?");
                    $stmtCoupon->execute([$couponCode]);
                }
            }

            // Insert order items & update stock
            $stmtItem = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, variant_id, product_name, size_name, unit_price, quantity, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmtStockVar = $pdo->prepare("
                UPDATE product_variants 
                SET stock_quantity = GREATEST(0, stock_quantity - ?) 
                WHERE id = ?
            ");

            $stmtStockProd = $pdo->prepare("
                UPDATE products 
                SET stock_quantity = GREATEST(0, stock_quantity - ?) 
                WHERE id = ?
            ");

            foreach ($cart['items'] as $item) {
                if (!empty($item['is_gift_card'])) {
                    $stmtItem->execute([
                        $orderId, 0, null, $item['product_name'],
                        $item['size_name'], $item['unit_price'], $item['quantity'],
                        $item['subtotal']
                    ]);
                    \App\Services\GiftCardService::issueForOrder($pdo, $orderId, $item);
                    continue;
                }

                $pId = (int)($item['product_id'] ?? 0);
                $vId = !empty($item['variant_id']) ? (int)$item['variant_id'] : null;

                $stmtItem->execute([
                    $orderId, $pId, $vId, $item['product_name'],
                    $item['size_name'], $item['unit_price'], $item['quantity'],
                    $item['subtotal']
                ]);

                if ($vId) {
                    $stmtStockVar->execute([$item['quantity'], $vId]);
                }
                if ($pId) {
                    $stmtStockProd->execute([$item['quantity'], $pId]);
                }
            }

            // Save address if user logged in
            if ($userId && $request->get('save_address') === '1') {
                $checkAddr = $pdo->prepare("SELECT id FROM user_addresses WHERE user_id = ? AND address = ?");
                $checkAddr->execute([$userId, $address]);
                if (!$checkAddr->fetch()) {
                    $stmtAddr = $pdo->prepare("
                        INSERT INTO user_addresses (user_id, recipient_name, phone, city, district, address, is_default)
                        VALUES (?, ?, ?, ?, ?, ?, 0)
                    ");
                    $stmtAddr->execute([$userId, $fullName, $phone, $city, $district, $address]);
                }
            }

            $pdo->commit();

            $orderRecord = [
                'id' => $orderId,
                'order_number' => $orderNumber,
                'total' => $total,
                'customer_name' => $fullName,
                'customer_phone' => $phone,
                'customer_email' => $email,
                'shipping_address' => $fullAddressText,
                'city' => $city,
                'shipping_fee' => $shippingFee,
                'discount' => $discountAmount
            ];

            // Resolve provider and prepare URLs
            $provider = PaymentManager::resolveProvider($paymentMethod);
            $urls = [
                'return_url' => url('/checkout/payment/callback/' . $provider),
                'cancel_url' => url('/checkout/payment/cancel?order_number=' . urlencode($orderNumber)),
                'webhook_url' => url('/checkout/payment/webhook/' . $provider)
            ];

            // Initiate payment via PaymentManager
            $paymentResult = PaymentManager::initiate($orderRecord, $paymentMethod, $urls);

            if (!empty($paymentResult['payment_reference'])) {
                Database::execute("UPDATE orders SET payment_reference = ? WHERE id = ?", [
                    $paymentResult['payment_reference'],
                    $orderId
                ]);
            }

            // If offline payment (Cash on Delivery or Direct Bank Transfer)
            if (!empty($paymentResult['is_offline'])) {
                $_SESSION['cart'] = [];
                unset($_SESSION['coupon_code']);
                Logger::order("Offline payment order confirmed: {$orderNumber} ({$paymentMethod})");
                Response::redirect("/order/{$orderNumber}");
                return;
            }

            // If gateway initiation succeeded, redirect to checkout URL or simulator
            if ($paymentResult['success'] && !empty($paymentResult['redirect_url'])) {
                $_SESSION['pending_order_number'] = $orderNumber;
                Response::redirect($paymentResult['redirect_url']);
                return;
            }

            // If gateway initiation failed
            $errMsg = $paymentResult['message'] ?? 'فشل الاتصال ببوابة الدفع، يرجى المحاولة مرة أخرى أو اختيار وسيلة دفع مختلفة.';
            Logger::error("Payment initiation failed for {$orderNumber}: {$errMsg}");
            Response::redirect('/checkout?error=' . urlencode($errMsg));

        } catch (\Throwable $e) {
            $pdo->rollBack();
            Logger::error("Order processing failed: " . $e->getMessage());
            Response::error('حدث خطأ أثناء معالجة الطلب: ' . $e->getMessage());
        }
    }

    /**
     * Return callback endpoint after completing payment on gateway
     */
    public function paymentCallback(Request $request, string $provider = ''): void
    {
        $provider = strtolower(trim($provider ?: (string)$request->get('provider', '')));
        if (empty($provider)) {
            Response::redirect('/checkout?error=' . urlencode('لم يتم تحديد بوابة الدفع للتحقق'));
            return;
        }

        $verification = PaymentManager::verify($request, $provider);

        $orderNumber = $verification['order_number'] ?? ($request->get('order_number') ?? ($_SESSION['pending_order_number'] ?? null));
        $order = $orderNumber ? Database::fetchOne("SELECT * FROM orders WHERE order_number = ?", [$orderNumber]) : null;

        if ($verification['paid']) {
            if ($order) {
                Database::execute("
                    UPDATE orders 
                    SET payment_status = 'paid', 
                        payment_reference = COALESCE(?, payment_reference),
                        updated_at = NOW()
                    WHERE id = ?
                ", [
                    $verification['payment_reference'] ?? null,
                    $order['id']
                ]);
            }

            if ($order) {
                \App\Services\GiftCardService::activateForOrder((int)$order['id']);
            }

            // Clear session cart
            $_SESSION['cart'] = [];
            unset($_SESSION['coupon_code']);
            unset($_SESSION['pending_order_number']);

            Logger::order("Payment verified successfully for order {$orderNumber} via {$provider}", [
                'reference' => $verification['payment_reference'] ?? null
            ]);

            Response::redirect("/order/{$orderNumber}?payment_status=success");
            return;
        }

        // Payment failed or declined
        $errorMsg = $verification['error'] ?? 'تم رفض أو إلغاء عملية الدفع. يمكنك إعادة المحاولة.';
        if ($order) {
            Database::execute("UPDATE orders SET notes = CONCAT(COALESCE(notes, ''), ' | تفاصيل فشل الدفع: ', ?) WHERE id = ?", [
                $errorMsg,
                $order['id']
            ]);
        }

        Logger::warning("Payment verification failed for order {$orderNumber} via {$provider}: {$errorMsg}");
        Response::redirect('/checkout?error=' . urlencode($errorMsg));
    }

    /**
     * Webhook endpoint for asynchronous server-to-server gateway notifications
     */
    public function paymentWebhook(Request $request, string $provider = ''): void
    {
        $provider = strtolower(trim($provider ?: (string)$request->get('provider', '')));
        Logger::info("Incoming webhook for provider {$provider}");

        $verification = PaymentManager::verify($request, $provider);

        if ($verification['paid'] && !empty($verification['order_number'])) {
            Database::execute("
                UPDATE orders 
                SET payment_status = 'paid', 
                    payment_reference = COALESCE(?, payment_reference),
                    updated_at = NOW()
                WHERE order_number = ?
            ", [
                $verification['payment_reference'] ?? null,
                $verification['order_number']
            ]);
            $paidOrder = Database::fetchOne("SELECT id FROM orders WHERE order_number = ?", [$verification['order_number']]);
            if ($paidOrder) {
                \App\Services\GiftCardService::activateForOrder((int)$paidOrder['id']);
            }
        }

        Response::json([
            'status' => 'received',
            'verified' => $verification['verified'],
            'paid' => $verification['paid']
        ]);
    }

    /**
     * Payment simulator screen for local sandbox / test testing
     */
    public function paymentSimulator(Request $request): void
    {
        $orderNumber = (string)$request->get('order_number', '');
        $provider = (string)$request->get('provider', 'moyasar');
        $method = (string)$request->get('method', 'mada');

        $order = Database::fetchOne("SELECT * FROM orders WHERE order_number = ?", [$orderNumber]);
        if (!$order) {
            Response::error('الطلب غير موجود', 404);
            return;
        }

        $successUrl = url("/checkout/payment/callback/{$provider}?order_number=" . urlencode($orderNumber) . "&status=paid&success=true&tabby_status=success&tamara_status=success&id=SIM_" . time());
        $cancelUrl = url("/checkout/payment/cancel?order_number=" . urlencode($orderNumber) . "&provider=" . urlencode($provider));

        Response::view('storefront/payment_simulator', [
            'order' => $order,
            'provider' => $provider,
            'paymentMethod' => $method,
            'successUrl' => $successUrl,
            'cancelUrl' => $cancelUrl,
            'page_title' => 'محاكي بوابة الدفع التجريبي'
        ]);
    }

    /**
     * Handle payment cancellation by user
     */
    public function paymentCancel(Request $request): void
    {
        $orderNumber = (string)$request->get('order_number', '');
        Response::redirect('/checkout?error=' . urlencode('تم إلغاء عملية الدفع، يمكنك اختيار وسيلة دفع أخرى أو إعادة المحاولة'));
    }

    /**
     * Test connection to payment gateway API (Super Admin AJAX endpoint)
     */
    public function apiTestConnection(Request $request): void
    {
        if (!Auth::isSuperAdmin()) {
            Response::json(['success' => false, 'message' => 'Unauthorized'], 403);
            return;
        }

        $provider = trim((string)$request->get('provider', ''));
        if (empty($provider)) {
            Response::json(['success' => false, 'message' => 'يرجى تحديد بوابة الدفع للاختبار'], 400);
            return;
        }

        // Collect custom credentials passed from the active form
        $customConfig = [
            'paymob_api_key' => $request->get('paymob_api_key'),
            'paymob_integration_id' => $request->get('paymob_integration_id'),
            'paymob_iframe_id' => $request->get('paymob_iframe_id'),
            'paymob_hmac' => $request->get('paymob_hmac'),
            'gateway_secret_key' => $request->get('gateway_secret_key'),
            'gateway_publishable_key' => $request->get('gateway_publishable_key'),
            'tabby_secret_key' => $request->get('tabby_secret_key'),
            'tabby_public_key' => $request->get('tabby_public_key'),
            'tamara_merchant_token' => $request->get('tamara_merchant_token'),
            'tamara_mode' => $request->get('tamara_mode'),
            'tabby_mode' => $request->get('tabby_mode'),
            'gateway_mode' => $request->get('gateway_mode')
        ];

        // Filter out nulls
        $customConfig = array_filter($customConfig, fn($v) => $v !== null);

        $result = PaymentManager::testConnection($provider, $customConfig);
        Response::json($result);
    }

    public function orderConfirmation(Request $request, string $orderNumber = ''): void
    {
        $orderNumber = $orderNumber ?: (string)$request->get('order_number', '');
        $order = Database::fetchOne("SELECT * FROM orders WHERE order_number = ?", [$orderNumber]);

        if (!$order) {
            Response::error('الطلب غير موجود', 404);
            return;
        }

        $items = Database::fetchAll("
            SELECT oi.*, p.featured_image, p.slug as product_slug
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ", [$order['id']]);

        Response::view('storefront/order_success', [
            'order' => $order,
            'items' => $items,
            'page_title' => 'تم تأكيد طلبك بنجاح'
        ]);
    }
}
