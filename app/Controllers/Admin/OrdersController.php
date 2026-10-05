<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Services\BostaService;
use Database\Database;

class OrdersController
{
    public function index(Request $request): void
    {
        $isSuper = Auth::isSuperAdmin();

        $sql = "
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as items_count,
                   (SELECT GROUP_CONCAT(DISTINCT p.name_ar SEPARATOR '، ') 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = o.id) as items_summary
            FROM orders o
            ORDER BY o.id DESC
        ";

        $orders = Database::fetchAll($sql);

        Response::view('admin/orders', [
            'orders' => $orders,
            'isSuper' => $isSuper
        ], 'admin');
    }

    public function show(Request $request): void
    {
        $id = (int)$request->get('id');
        $order = Database::fetchOne("SELECT * FROM orders WHERE id = ?", [$id]);

        if (!$order) {
            Response::redirect('/admin/orders');
            return;
        }

        $isSuper = Auth::isSuperAdmin();

        $items = Database::fetchAll("
            SELECT oi.*, p.featured_image, p.name_ar as product_name_ar, p.name_en as product_name_en,
                   c.name_ar as category_name_ar
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE oi.order_id = ?
        ", [$id]);

        Response::view('admin/order_detail', [
            'order' => $order,
            'items' => $items,
            'isSuper' => $isSuper
        ], 'admin');
    }

    public function updateStatus(Request $request): void
    {
        $orderId = (int)$request->get('id');
        $shippingStatus = $request->get('shipping_status');
        $paymentStatus = $request->get('payment_status');

        if ($shippingStatus) {
            Database::execute("UPDATE orders SET shipping_status = ? WHERE id = ?", [$shippingStatus, $orderId]);
        }
        if ($paymentStatus) {
            Database::execute("UPDATE orders SET payment_status = ? WHERE id = ?", [$paymentStatus, $orderId]);
            if ($paymentStatus === 'paid') {
                \App\Services\GiftCardService::activateForOrder($orderId);
            }
        }

        if ($request->isAjax()) {
            Response::json(['success' => true, 'message' => 'تم تحديث حالة الطلب بنجاح']);
            return;
        }

        Response::redirect("/admin/orders/{$orderId}?updated=1");
    }

    public function generateWaybill(Request $request): void
    {
        $orderId = (int)$request->get('id');
        $order = Database::fetchOne("SELECT * FROM orders WHERE id = ?", [$orderId]);

        if (!$order) {
            Response::json(['success' => false, 'message' => 'Order not found'], 404);
            return;
        }

        $shippingService = new BostaService();
        $res = $shippingService->createShipment([
            'order_number' => $order['order_number'],
            'customer_name' => $order['customer_name'],
            'customer_phone' => $order['customer_phone'],
            'customer_email' => $order['customer_email'],
            'shipping_address' => $order['shipping_address'],
            'city' => $order['city'],
            'total' => $order['total'],
            'payment_method' => $order['payment_method']
        ]);

        Database::execute("
            UPDATE orders 
            SET tracking_number = ?, waybill_id = ?, shipping_status = 'shipped'
            WHERE id = ?
        ", [$res['tracking_number'], $res['waybill_id'], $orderId]);

        Response::json([
            'success' => true,
            'message' => 'تم إصدار بوليصة الشحن المبرد بنجاح!',
            'tracking_number' => $res['tracking_number'],
            'waybill_id' => $res['waybill_id'],
            'tracking_url' => $res['tracking_url']
        ]);
    }

    public function generateBostaWaybill(Request $request): void
    {
        $this->generateWaybill($request);
    }
}
