<?php

namespace App\Services\Payment;

use App\Core\Request;
use App\Core\Logger;
use Database\Database;

class PaymentManager
{
    private static ?array $settingsCache = null;

    public static function getSettings(): array
    {
        if (self::$settingsCache !== null) {
            return self::$settingsCache;
        }

        try {
            $rows = Database::fetchAll("SELECT `key`, `value` FROM settings");
            $data = [];
            foreach ($rows as $r) {
                $data[$r['key']] = $r['value'];
            }
            // Merge with environment variables if available
            if (!empty($_ENV['PAYMOB_API_KEY'])) $data['paymob_api_key'] = $_ENV['PAYMOB_API_KEY'];
            if (!empty($_ENV['PAYMOB_INTEGRATION_ID'])) $data['paymob_integration_id'] = $_ENV['PAYMOB_INTEGRATION_ID'];
            if (!empty($_ENV['PAYMOB_IFRAME_ID'])) $data['paymob_iframe_id'] = $_ENV['PAYMOB_IFRAME_ID'];
            if (!empty($_ENV['PAYMOB_HMAC'])) $data['paymob_hmac'] = $_ENV['PAYMOB_HMAC'];

            self::$settingsCache = $data;
            return $data;
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function resolveProvider(string $paymentMethod): string
    {
        $settings = self::getSettings();
        $paymentMethod = strtolower(trim($paymentMethod));

        if (in_array($paymentMethod, ['tabby'])) {
            return 'tabby';
        }
        if (in_array($paymentMethod, ['tamara'])) {
            return 'tamara';
        }
        if ($paymentMethod === 'paymob') {
            return 'paymob';
        }
        if (in_array($paymentMethod, ['cod', 'bank_transfer'])) {
            return $paymentMethod;
        }

        // For cards: mada, apple_pay, credit_card
        $provider = strtolower(trim((string)($settings['gateway_provider'] ?? 'moyasar')));
        if (empty($provider)) {
            $provider = 'moyasar';
        }

        return $provider;
    }

    public static function getGateway(string $provider): ?PaymentGatewayInterface
    {
        switch ($provider) {
            case 'paymob':
                return new PaymobGateway();
            case 'moyasar':
                return new MoyasarGateway();
            case 'stripe':
                return new StripeGateway();
            case 'tabby':
                return new TabbyGateway();
            case 'tamara':
                return new TamaraGateway();
            default:
                return null;
        }
    }

    /**
     * Check if the gateway has required credentials configured
     */
    public static function hasValidCredentials(string $provider): bool
    {
        $settings = self::getSettings();
        $isPlaceholder = function(string $val): bool {
            $val = strtolower(trim($val));
            return empty($val) 
                || str_contains($val, 'sample') 
                || str_contains($val, 'demo') 
                || str_contains($val, 'mock') 
                || str_contains($val, 'tumurna') 
                || str_contains($val, 'fake') 
                || str_contains($val, 'your_');
        };

        switch ($provider) {
            case 'paymob':
                $key = (string)($settings['paymob_api_key'] ?? '');
                $iid = (string)($settings['paymob_integration_id'] ?? '');
                $ifid = (string)($settings['paymob_iframe_id'] ?? '');
                return !$isPlaceholder($key) && !empty($iid) && !empty($ifid);

            case 'moyasar':
                $sk = (string)($settings['gateway_secret_key'] ?? ($settings['moyasar_secret_key'] ?? ''));
                return !$isPlaceholder($sk);

            case 'stripe':
                $sk = (string)($settings['gateway_secret_key'] ?? ($settings['stripe_secret_key'] ?? ''));
                return !$isPlaceholder($sk);

            case 'tabby':
                $sk = (string)($settings['tabby_secret_key'] ?? '');
                return !$isPlaceholder($sk);

            case 'tamara':
                $token = (string)($settings['tamara_merchant_token'] ?? '');
                return !$isPlaceholder($token);

            default:
                return false;
        }
    }

    /**
     * Initiate payment workflow for an order
     */
    public static function initiate(array $order, string $paymentMethod, array $urls): array
    {
        $provider = self::resolveProvider($paymentMethod);
        $settings = self::getSettings();

        // Offline methods
        if ($provider === 'cod' || $provider === 'bank_transfer') {
            return [
                'success' => true,
                'is_offline' => true,
                'redirect_url' => $urls['return_url'] ?? "/order/{$order['order_number']}",
                'payment_reference' => strtoupper($provider) . '-' . time(),
                'message' => 'طلب قيد المعالجة (دفع غير متزامن)'
            ];
        }

        $gateway = self::getGateway($provider);
        if (!$gateway) {
            return [
                'success' => false,
                'is_offline' => false,
                'redirect_url' => '',
                'payment_reference' => null,
                'message' => "بوابة الدفع '{$provider}' غير مدعومة أو غير مفعلة."
            ];
        }

        // If credentials are valid and live/configured, execute REAL payment handshake!
        if (self::hasValidCredentials($provider)) {
            Logger::info("Initiating real payment via {$provider} for order {$order['order_number']}");
            $res = $gateway->initiatePayment($order, $settings, $urls);

            if ($res['success'] && !empty($res['redirect_url'])) {
                return [
                    'success' => true,
                    'is_offline' => false,
                    'is_simulation' => false,
                    'redirect_url' => $res['redirect_url'],
                    'payment_reference' => $res['payment_reference'] ?? null,
                    'message' => $res['message'] ?? 'تم إنشاء رابط الدفع الفعلي بنجاح'
                ];
            }

            Logger::error("Gateway {$provider} initiation failed: " . ($res['message'] ?? 'Unknown error'));
            return $res;
        }

        // Seamless Simulator fallback for development/sandbox when credentials are demo or not yet provided
        Logger::info("Using payment simulator for {$provider} (no live production keys configured)");
        $simUrl = url("/checkout/simulator?order_number=" . urlencode($order['order_number']) . "&provider=" . urlencode($provider) . "&method=" . urlencode($paymentMethod));

        return [
            'success' => true,
            'is_offline' => false,
            'is_simulation' => true,
            'redirect_url' => $simUrl,
            'payment_reference' => 'SIM-' . strtoupper($provider) . '-' . rand(100000, 999999),
            'message' => 'تم توجيه الطلب إلى محاكي الدفع التجريبي'
        ];
    }

    /**
     * Verify payment on callback
     */
    public static function verify(Request $request, string $provider): array
    {
        $gateway = self::getGateway($provider);
        if (!$gateway) {
            return [
                'verified' => false,
                'paid' => false,
                'order_number' => null,
                'payment_reference' => null,
                'error' => "بوابة الدفع '{$provider}' غير معروفة."
            ];
        }

        $settings = self::getSettings();
        return $gateway->verifyCallback($request, $settings);
    }

    /**
     * Test API connection with a given gateway
     */
    public static function testConnection(string $provider, array $customConfig = []): array
    {
        $gateway = self::getGateway($provider);
        if (!$gateway) {
            return [
                'success' => false,
                'message' => "بوابة الدفع '{$provider}' غير معرّفة للاختبار."
            ];
        }

        $config = array_merge(self::getSettings(), $customConfig);
        return $gateway->testConnection($config);
    }
}
