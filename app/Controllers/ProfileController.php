<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Services\OtpService;
use Database\Database;

class ProfileController
{
    private function requireAuth(): ?array
    {
        if (!Auth::check()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/profile';
            Response::redirect('/login?required_login=1');
            return null;
        }

        return Auth::user();
    }

    public function index(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        // Fetch user stats
        $userId = (int)$user['id'];
        $userPhone = $user['phone'] ?? '';
        $userEmail = $user['email'] ?? '';

        $ordersCount = Database::fetchOne("
            SELECT COUNT(*) as cnt 
            FROM orders 
            WHERE user_id = ? OR customer_phone = ? OR (customer_email = ? AND customer_email != '')
        ", [$userId, $userPhone, $userEmail])['cnt'] ?? 0;

        $addressesCount = Database::fetchOne("
            SELECT COUNT(*) as cnt 
            FROM user_addresses 
            WHERE user_id = ?
        ", [$userId])['cnt'] ?? 0;

        $defaultAddress = Database::fetchOne("
            SELECT * FROM user_addresses 
            WHERE user_id = ? AND is_default = 1
            LIMIT 1
        ", [$userId]);

        // Fetch all addresses for the overview section
        $addresses = Database::fetchAll("
            SELECT * FROM user_addresses 
            WHERE user_id = ? 
            ORDER BY is_default DESC, id DESC
        ", [$userId]);

        // Recent 3 orders with lines
        $recentOrders = Database::fetchAll("
            SELECT o.*, 
                   COUNT(oi.id) as items_count
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ? OR o.customer_phone = ? OR (o.customer_email = ? AND o.customer_email != '')
            GROUP BY o.id
            ORDER BY o.id DESC
            LIMIT 3
        ", [$userId, $userPhone, $userEmail]);

        $recentOrderItems = [];
        if ($recentOrders) {
            $ids = array_map(fn($o) => (int)$o['id'], $recentOrders);
            $marks = implode(',', array_fill(0, count($ids), '?'));
            $rows = Database::fetchAll("
                SELECT oi.order_id, oi.product_name, oi.quantity, oi.price
                FROM order_items oi
                WHERE oi.order_id IN ($marks)
                ORDER BY oi.id ASC
            ", $ids);
            foreach ($rows as $row) {
                $recentOrderItems[(int)$row['order_id']][] = $row;
            }
        }

        $passError = null;
        if ($request->get('pass_error') === 'mismatch') $passError = 'كلمتا المرور غير متطابقتين أو الحقول ناقصة.';
        elseif ($request->get('pass_error') === 'length') $passError = 'استخدم كلمة مرور من 8 أحرف على الأقل تشمل حرفًا ورقمًا.';
        elseif ($request->get('pass_error') === 'wrong_current') $passError = 'كلمة المرور الحالية غير صحيحة.';

        Response::view('storefront/profile/index', [
            'user' => $user,
            'ordersCount' => (int)$ordersCount,
            'addressesCount' => (int)$addressesCount,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'recentOrders' => $recentOrders,
            'recentOrderItems' => $recentOrderItems,
            'success' => $request->get('saved') ? 'تم تحديث بيانات الحساب بنجاح!' : ($request->get('pass_saved') ? 'تم تحديث كلمة المرور بنجاح!' : null),
            'passError' => $passError,
            'error' => $request->get('error') === 'email_exists' ? 'البريد الإلكتروني مسجل بالفعل لمستخدم آخر.' : null
        ]);
    }

    public function changePassword(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        $userId = (int)$user['id'];
        $currentPassword = (string)$request->get('currentPassword');
        $newPassword = (string)$request->get('password');
        $confirm = (string)$request->get('confirm');

        if (empty($currentPassword) || empty($newPassword) || $newPassword !== $confirm) {
            Response::redirect('/profile?pass_error=mismatch');
            return;
        }

        if (strlen($newPassword) < 8) {
            Response::redirect('/profile?pass_error=length');
            return;
        }

        $dbUser = Database::fetchOne("SELECT password FROM users WHERE id = ?", [$userId]);
        if (!$dbUser || !password_verify($currentPassword, $dbUser['password'])) {
            Response::redirect('/profile?pass_error=wrong_current');
            return;
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        Database::execute("UPDATE users SET password = ? WHERE id = ?", [$hashed, $userId]);

        Response::redirect('/profile?pass_saved=1');
    }

    public function updateInfo(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        $userId = (int)$user['id'];
        $name = trim((string)$request->get('name'));
        $email = trim((string)$request->get('email'));
        $phone = trim((string)$request->get('phone'));

        if (empty($name)) {
            Response::redirect('/profile?error=name_required');
            return;
        }

        // Verify email unique if provided
        if (!empty($email) && $email !== ($user['email'] ?? '')) {
            $exists = Database::fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
            if ($exists) {
                Response::redirect('/profile?error=email_exists');
                return;
            }
        }

        if (!empty($phone)) {
            $phone = OtpService::cleanPhone($phone);
            Database::execute("
                UPDATE users 
                SET name = ?, email = ?, phone = ? 
                WHERE id = ?
            ", [$name, $email, $phone, $userId]);
        } else {
            Database::execute("
                UPDATE users 
                SET name = ?, email = ? 
                WHERE id = ?
            ", [$name, $email, $userId]);
        }

        // Refresh user in session
        $updatedUser = Database::fetchOne("
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            WHERE u.id = ?
        ", [$userId]);
        if ($updatedUser) {
            unset($updatedUser['password']);
            $_SESSION['user'] = $updatedUser;
        }

        Response::redirect('/profile?saved=1');
    }

    private function getSettings(): array
    {
        $settingsRows = Database::fetchAll("SELECT `key`, `value` FROM settings");
        $settings = [];
        foreach ($settingsRows as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return $settings;
    }

    public function addresses(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        $userId = (int)$user['id'];
        $addresses = Database::fetchAll("
            SELECT * FROM user_addresses 
            WHERE user_id = ? 
            ORDER BY is_default DESC, id DESC
        ", [$userId]);

        $shippingZones = Database::fetchAll("SELECT * FROM shipping_zones WHERE is_active = 1 ORDER BY city_name_ar ASC");
        $cities = !empty($shippingZones) ? array_column($shippingZones, 'city_name_ar') : \App\Controllers\CheckoutController::saudiCities();
        $settings = $this->getSettings();

        $errorMsg = null;
        $err = $request->get('error');
        if ($err === 'multiple_not_allowed') {
            $errorMsg = __('multiple_addresses_not_allowed');
        } elseif ($err === 'max_limit_reached') {
            $errorMsg = __('max_addresses_reached');
        } elseif ($err === 'missing_fields') {
            $errorMsg = __('missing_fields');
        } elseif ($err === 'invalid_phone') {
            $errorMsg = __('invalid_phone');
        }

        Response::view('storefront/profile/addresses', [
            'user' => $user,
            'addresses' => $addresses,
            'cities' => $cities,
            'shippingZones' => $shippingZones,
            'settings' => $settings,
            'success' => $request->get('saved') ? __('address_saved') : ($request->get('deleted') ? __('address_deleted') : null),
            'error' => $errorMsg
        ]);
    }

    public function storeAddress(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        $userId = (int)$user['id'];
        $id = (int)$request->get('id');
        $title = trim((string)$request->get('title', 'المنزل'));
        $recipientName = trim((string)$request->get('recipient_name', $user['name']));
        $rawPhone = trim((string)$request->get('phone', $user['phone'] ?? ''));
        $phone = OtpService::cleanPhone($rawPhone);
        $city = trim((string)$request->get('city', 'الرياض'));
        $governorate = trim((string)$request->get('governorate', $city));
        $streetAddress = trim((string)$request->get('street_address', ''));
        $buildingFloor = trim((string)$request->get('building_floor', ''));
        $landmark = trim((string)$request->get('landmark', ''));
        $isDefault = $request->get('is_default') ? 1 : 0;

        if (empty($recipientName) || empty($phone) || empty($streetAddress) || empty($city)) {
            Response::redirect('/profile/addresses?error=missing_fields');
            return;
        }

        if (strlen($phone) < 9 || !str_starts_with($phone, '05')) {
            Response::redirect('/profile/addresses?error=invalid_phone');
            return;
        }

        $settings = $this->getSettings();
        $count = (int)Database::fetchOne("SELECT COUNT(*) as cnt FROM user_addresses WHERE user_id = ?", [$userId])['cnt'];
        $allowMultiple = ($settings['allow_multiple_addresses'] ?? '1') == '1';
        $maxAddresses = (int)($settings['max_addresses_per_user'] ?? 10);

        if ($id <= 0) {
            if (!$allowMultiple && $count >= 1) {
                Response::redirect('/profile/addresses?error=multiple_not_allowed');
                return;
            }
            if ($count >= $maxAddresses) {
                Response::redirect('/profile/addresses?error=max_limit_reached');
                return;
            }
        }

        // If user has no existing addresses, force first address as default
        if ($count === 0) {
            $isDefault = 1;
        }

        if ($isDefault) {
            Database::execute("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?", [$userId]);
        }

        $fullAddress = trim($streetAddress . (!empty($buildingFloor) ? ' - ' . $buildingFloor : '') . (!empty($landmark) ? ' (' . $landmark . ')' : ''));

        if ($id > 0) {
            // Update
            Database::execute("
                UPDATE user_addresses SET 
                    title = ?, recipient_name = ?, phone = ?, governorate = ?, 
                    city = ?, address = ?, street_address = ?, building_floor = ?, landmark = ?, is_default = ?
                WHERE id = ? AND user_id = ?
            ", [$title, $recipientName, $phone, $governorate, $city, $fullAddress, $streetAddress, $buildingFloor, $landmark, $isDefault, $id, $userId]);
        } else {
            // Insert
            Database::execute("
                INSERT INTO user_addresses (
                    user_id, title, recipient_name, phone, governorate, 
                    city, address, street_address, building_floor, landmark, is_default
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [$userId, $title, $recipientName, $phone, $governorate, $city, $fullAddress, $streetAddress, $buildingFloor, $landmark, $isDefault]);
        }

        Response::redirect('/profile/addresses?saved=1');
    }

    public function deleteAddress(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        $userId = (int)$user['id'];
        $id = (int)$request->get('id');

        // If deleting default, assign default to another address if available
        $address = Database::fetchOne("SELECT * FROM user_addresses WHERE id = ? AND user_id = ?", [$id, $userId]);
        if ($address) {
            Database::execute("DELETE FROM user_addresses WHERE id = ? AND user_id = ?", [$id, $userId]);
            if ($address['is_default'] == 1) {
                Database::execute("UPDATE user_addresses SET is_default = 1 WHERE user_id = ? ORDER BY id DESC LIMIT 1", [$userId]);
            }
        }

        Response::redirect('/profile/addresses?deleted=1');
    }

    public function setDefaultAddress(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        $userId = (int)$user['id'];
        $id = (int)$request->get('id');

        Database::execute("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?", [$userId]);
        Database::execute("UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?", [$id, $userId]);

        Response::redirect('/profile/addresses?saved=1');
    }

    public function orders(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        $userId = (int)$user['id'];
        $userPhone = $user['phone'] ?? '';
        $userEmail = $user['email'] ?? '';

        $isSqlite = Database::getDriver() === 'sqlite';
        $concat = $isSqlite ? "GROUP_CONCAT(oi.product_name, ' • ')" : "GROUP_CONCAT(DISTINCT oi.product_name SEPARATOR ' • ')";

        $orders = Database::fetchAll("
            SELECT o.*, 
                   COUNT(oi.id) as items_count,
                   {$concat} as products_snippet
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ? OR o.customer_phone = ? OR (o.customer_email = ? AND o.customer_email != '')
            GROUP BY o.id
            ORDER BY o.id DESC
        ", [$userId, $userPhone, $userEmail]);

        // Product thumbnails for the order cards (one extra query for the whole list)
        $orderItems = [];
        if ($orders) {
            $ids = array_map(fn($o) => (int)$o['id'], $orders);
            $marks = implode(',', array_fill(0, count($ids), '?'));
            $rows = Database::fetchAll("
                SELECT oi.order_id, oi.product_name, oi.quantity, p.slug, p.featured_image
                FROM order_items oi
                LEFT JOIN products p ON p.id = oi.product_id
                WHERE oi.order_id IN ($marks)
                ORDER BY oi.id ASC
            ", $ids);
            foreach ($rows as $row) $orderItems[(int)$row['order_id']][] = $row;
        }

        Response::view('storefront/profile/orders', [
            'user' => $user,
            'orders' => $orders,
            'orderItems' => $orderItems
        ]);
    }

    public function orderDetail(Request $request, string $orderNumber = ''): void
    {
        $orderNumber = $orderNumber ?: (string)$request->get('order_number', '');
        $user = $this->requireAuth();
        if (!$user) return;

        $userId = (int)$user['id'];
        $userPhone = $user['phone'] ?? '';
        $userEmail = $user['email'] ?? '';

        $order = Database::fetchOne("
            SELECT * FROM orders 
            WHERE order_number = ? AND (user_id = ? OR customer_phone = ? OR customer_email = ?)
        ", [$orderNumber, $userId, $userPhone, $userEmail]);

        if (!$order) {
            Response::redirect('/profile/orders');
            return;
        }

        $items = Database::fetchAll("
            SELECT oi.*, p.featured_image as product_image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ", [$order['id']]);

        $settings = $this->getSettings();

        Response::view('storefront/profile/order_detail', [
            'user' => $user,
            'order' => $order,
            'items' => $items,
            'settings' => $settings,
            'cancelled' => $request->get('cancelled') == '1',
            'error' => $request->get('error') === 'cancel_not_allowed' ? __('cancel_order_not_allowed') : null
        ]);
    }

    public function cancelOrder(Request $request, string $orderNumber = ''): void
    {
        $orderNumber = $orderNumber ?: (string)$request->get('order_number', '');
        $user = $this->requireAuth();
        if (!$user) return;

        $userId = (int)$user['id'];
        $userPhone = $user['phone'] ?? '';
        $userEmail = $user['email'] ?? '';
        $settings = $this->getSettings();

        if (($settings['allow_customer_order_cancellation'] ?? '1') != '1') {
            Response::redirect('/profile/order/' . urlencode($orderNumber) . '?error=cancel_not_allowed');
            return;
        }

        $order = Database::fetchOne("
            SELECT * FROM orders 
            WHERE order_number = ? AND (user_id = ? OR customer_phone = ? OR customer_email = ?)
        ", [$orderNumber, $userId, $userPhone, $userEmail]);

        if (!$order) {
            Response::redirect('/profile/orders');
            return;
        }

        if (in_array(strtolower($order['shipping_status']), ['pending', 'قيد المراجعة', '']) && $order['payment_status'] !== 'paid') {
            Database::execute("UPDATE orders SET shipping_status = 'cancelled', updated_at = NOW() WHERE id = ?", [$order['id']]);
            Response::redirect('/profile/order/' . urlencode($orderNumber) . '?cancelled=1');
        } else {
            Response::redirect('/profile/order/' . urlencode($orderNumber) . '?error=cancel_not_allowed');
        }
    }

    public function giftCards(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        $userEmail = $user['email'] ?? '';
        $userPhone = $user['phone'] ?? '';

        $cards = Database::fetchAll("
            SELECT * FROM gift_cards 
            WHERE recipient_email = ? OR recipient_phone = ? OR sender_name = ?
            ORDER BY id DESC
        ", [$userEmail, $userPhone, $user['name']]);

        // If no user-specific cards in DB yet, show sample review cards matching frontend design
        if (empty($cards)) {
            $cards = [
                [
                    'code' => 'TMR-GIFT-500-ROYAL',
                    'card_type' => 'digital',
                    'amount' => 500.00,
                    'balance' => 500.00,
                    'sender_name' => 'فهد الناصر',
                    'recipient_name' => $user['name'] ?: 'خالد السعدون',
                    'order_number' => '1024',
                    'status' => 'unused',
                    'created_at' => date('Y-m-d', strtotime('-5 days')),
                    'message' => 'كل عام وأنتم بخير بمناسبة قدوم شهر الخير والبركة'
                ],
                [
                    'code' => 'TMR-GIFT-250-WOOD',
                    'card_type' => 'printed',
                    'amount' => 250.00,
                    'balance' => 0.00,
                    'sender_name' => 'سلطان المقرن',
                    'recipient_name' => 'محمد القحطاني',
                    'order_number' => '1018',
                    'status' => 'used',
                    'created_at' => date('Y-m-d', strtotime('-20 days')),
                    'message' => 'أجمل التهاني والتبريكات، ضيافة ملكية تليق بمقامك الكريم'
                ]
            ];
        }

        Response::view('storefront/profile/gift_cards', [
            'user' => $user,
            'cards' => $cards
        ]);
    }

    public function notifications(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        $readIds = $_SESSION['notifications_read'] ?? [];
        if ($request->get('mark_all')) {
            $_SESSION['notifications_read'] = ['notif-1', 'notif-2', 'notif-3'];
            Response::redirect('/profile/notifications');
            return;
        }

        if ($readId = $request->get('read')) {
            if (!in_array($readId, $readIds, true)) {
                $readIds[] = $readId;
                $_SESSION['notifications_read'] = $readIds;
            }
            Response::redirect('/profile/notifications');
            return;
        }

        $notifications = [
            [
                'id' => 'notif-1',
                'title' => 'تم تأكيد طلبك #1024 بنجاح',
                'title_en' => 'Your order #1024 is confirmed',
                'body' => 'بدأ تجهيز عبوات التمور الفاخرة بعناية فائقة تمهيداً للشحن المبرد.',
                'body_en' => 'Preparation of your royal dates shipment with temperature control has started.',
                'created_at' => date('Y-m-d', strtotime('-1 day')),
                'is_read' => in_array('notif-1', $readIds, true)
            ],
            [
                'id' => 'notif-2',
                'title' => 'وصلت بطاقة إهداء جديدة إلى بريدك',
                'title_en' => 'A new gift card has arrived',
                'body' => 'أهدى إليك فهد الناصر بطاقة إهداء فاخرة بقيمة 500 ر.س.',
                'body_en' => 'Fahad Al-Nasser sent you a luxury gift card of 500 SAR.',
                'created_at' => date('Y-m-d', strtotime('-4 days')),
                'is_read' => in_array('notif-2', $readIds, true) || !isset($_SESSION['notifications_read'])
            ],
            [
                'id' => 'notif-3',
                'title' => 'موسم تمور السكري الملكي الفاخر',
                'title_en' => 'Royal Sukkari dates season',
                'body' => 'بدأ حصاد الدفعة الأولى من مزارع القصيم وعنيزة. الكميات محدودة.',
                'body_en' => 'Harvest of the first harvest batch has started. Limited quantities.',
                'created_at' => date('Y-m-d', strtotime('-10 days')),
                'is_read' => in_array('notif-3', $readIds, true) || !isset($_SESSION['notifications_read'])
            ],
        ];

        Response::view('storefront/profile/notifications', [
            'user' => $user,
            'notifications' => $notifications
        ]);
    }

    public function settings(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        $settings = $_SESSION['user_account_settings'] ?? [
            'orders' => true,
            'gifts' => true,
            'marketing' => false
        ];

        Response::view('storefront/profile/settings', [
            'user' => $user,
            'settings' => $settings,
            'success' => $request->get('saved') ? 'تم حفظ الإعدادات بنجاح على هذا المتصفح.' : null
        ]);
    }

    public function saveSettings(Request $request): void
    {
        $user = $this->requireAuth();
        if (!$user) return;

        $_SESSION['user_account_settings'] = [
            'orders' => (bool)$request->get('notif_orders'),
            'gifts' => (bool)$request->get('notif_gifts'),
            'marketing' => (bool)$request->get('notif_marketing')
        ];

        Response::redirect('/profile/settings?saved=1');
    }
}
