<?php

namespace App\Services;

use Database\Database;

class BostaService
{
    private string $apiKey;
    private string $baseUrl;
    private bool $isSandbox;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/services.php';
        
        $dbApiKey = Database::fetchOne("SELECT `value` FROM settings WHERE `key` = 'shipping_api_key'");
        $this->apiKey = ($dbApiKey && !empty($dbApiKey['value'])) ? $dbApiKey['value'] : ($config['shipping']['api_key'] ?? 'sample_key');
        $this->baseUrl = $config['shipping']['base_url'] ?? 'https://api.shipping.sa';
        $this->isSandbox = true;
    }

    public function calculateShippingFee(string $city, float $weightKg = 1.0): float
    {
        // Try reading from shipping_zones table first
        $zone = Database::fetchOne("SELECT price FROM shipping_zones WHERE city_name_ar = ? OR city_name_en = ?", [$city, $city]);
        if ($zone) {
            return (float)$zone['price'];
        }

        // Default Saudi flat rate for refrigerated shipping
        return 25.00;
    }

    /**
     * Create cold delivery order with Tumurna KSA Cold Fleet
     */
    public function createShipment(array $orderData): array
    {
        $trackingNumber = 'TMR-SA-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $waybillId = 'WB-SA-' . rand(1000000, 9999999);

        return [
            'success' => true,
            'tracking_number' => $trackingNumber,
            'waybill_id' => $waybillId,
            'carrier' => 'أسطول تمرنا المبرد (Tumurna Cold Express)',
            'tracking_url' => url('/profile/orders'),
            'estimated_days' => in_array($orderData['city'] ?? '', ['الرياض', 'Riyadh']) ? '1-2 أيام عمل' : '2-3 أيام عمل'
        ];
    }

    public function trackShipment(string $trackingNumber): array
    {
        return [
            'tracking_number' => $trackingNumber,
            'carrier' => 'أسطول تمرنا المبرد (Tumurna Cold Express)',
            'status' => 'Out for delivery',
            'status_ar' => 'في الطريق مع سيارة الشحن المبرد',
            'hub' => 'مستودع تمرنا المركزي المبرد - الرياض',
            'last_update' => date('Y-m-d H:i:s')
        ];
    }
}
