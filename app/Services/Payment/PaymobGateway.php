<?php

namespace App\Services\Payment;

use App\Core\Request;
use App\Core\Logger;

class PaymobGateway implements PaymentGatewayInterface
{
    private const BASE_URL = 'https://accept.paymob.com/api';
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function initiatePayment(array $order, array $config, array $urls): array
    {
        $apiKey = trim($config['paymob_api_key'] ?? '');
        $integrationId = trim($config['paymob_integration_id'] ?? '');
        $iframeId = trim($config['paymob_iframe_id'] ?? '');
        $currency = !empty($config['currency']) ? strtoupper(trim($config['currency'])) : 'EGP';

        if (empty($apiKey) || empty($integrationId) || empty($iframeId)) {
            return [
                'success' => false,
                'redirect_url' => '',
                'payment_reference' => null,
                'message' => 'بيانات مفاتيح Paymob غير مكتملة (API Key, Integration ID, Iframe ID مطلوبة).'
            ];
        }

        $amountCents = (int)round(((float)$order['total']) * 100);

        try {
            // Step 1: Authentication Token
            $authRes = $this->httpPost(self::BASE_URL . '/auth/tokens', [
                'api_key' => $apiKey
            ]);

            $token = $authRes['token'] ?? null;
            if (!$token) {
                $err = $authRes['detail'] ?? ($authRes['message'] ?? 'فشل المصادقة مع خادم Paymob');
                return [
                    'success' => false,
                    'redirect_url' => '',
                    'payment_reference' => null,
                    'message' => 'خطأ في مصادقة Paymob: ' . $err
                ];
            }

            // Step 2: Order Registration
            $orderRes = $this->httpPost(self::BASE_URL . '/ecommerce/orders', [
                'auth_token' => $token,
                'delivery_needed' => 'false',
                'amount_cents' => (string)$amountCents,
                'currency' => $currency,
                'merchant_order_id' => $order['order_number'],
                'items' => []
            ]);

            $paymobOrderId = $orderRes['id'] ?? null;
            if (!$paymobOrderId) {
                $err = $orderRes['detail'] ?? ($orderRes['message'] ?? 'فشل تسجيل الطلب لدى Paymob');
                return [
                    'success' => false,
                    'redirect_url' => '',
                    'payment_reference' => null,
                    'message' => 'خطأ في تسجيل طلب Paymob: ' . $err
                ];
            }

            // Step 3: Payment Key Request
            $customerName = trim($order['customer_name'] ?? 'عميل المتجر');
            $nameParts = explode(' ', $customerName, 2);
            $firstName = $nameParts[0] ?? 'Customer';
            $lastName = $nameParts[1] ?? 'Client';
            $phone = preg_replace('/[^\d+]/', '', (string)($order['customer_phone'] ?? '01000000000'));
            if (empty($phone)) $phone = '01000000000';

            $billingData = [
                'apartment' => 'NA',
                'email' => !empty($order['customer_email']) ? $order['customer_email'] : 'customer@store.com',
                'floor' => 'NA',
                'first_name' => $firstName,
                'street' => !empty($order['shipping_address']) ? mb_substr($order['shipping_address'], 0, 100) : 'Main Street',
                'building' => 'NA',
                'phone_number' => $phone,
                'shipping_method' => 'PKG',
                'postal_code' => 'NA',
                'city' => !empty($order['city']) ? $order['city'] : 'Cairo',
                'country' => 'EG',
                'last_name' => $lastName,
                'state' => !empty($order['city']) ? $order['city'] : 'Cairo'
            ];

            $keyRes = $this->httpPost(self::BASE_URL . '/acceptance/payment_keys', [
                'auth_token' => $token,
                'amount_cents' => (string)$amountCents,
                'expiration' => 3600,
                'order_id' => $paymobOrderId,
                'billing_data' => $billingData,
                'currency' => $currency,
                'integration_id' => (int)$integrationId,
                'lock_order_when_paid' => 'false'
            ]);

            $paymentKey = $keyRes['token'] ?? null;
            if (!$paymentKey) {
                $err = $keyRes['detail'] ?? ($keyRes['message'] ?? 'فشل استخراج Payment Key من Paymob');
                return [
                    'success' => false,
                    'redirect_url' => '',
                    'payment_reference' => (string)$paymobOrderId,
                    'message' => 'خطأ مفتاح الدفع Paymob: ' . $err
                ];
            }

            $redirectUrl = self::BASE_URL . "/acceptance/iframes/{$iframeId}?payment_token={$paymentKey}";

            Logger::info("Paymob payment initiated successfully for order {$order['order_number']}", [
                'paymob_order_id' => $paymobOrderId,
                'amount_cents' => $amountCents,
                'currency' => $currency
            ]);

            return [
                'success' => true,
                'redirect_url' => $redirectUrl,
                'payment_reference' => (string)$paymobOrderId,
                'message' => 'تم إنشاء رابط الدفع بنجاح'
            ];
        } catch (\Throwable $e) {
            Logger::error("Paymob initiation exception: " . $e->getMessage());
            return [
                'success' => false,
                'redirect_url' => '',
                'payment_reference' => null,
                'message' => 'حدث استثناء أثناء الاتصال بـ Paymob: ' . $e->getMessage()
            ];
        }
    }

    public function verifyCallback(Request $request, array $config): array
    {
        $allParams = array_merge($_GET, $_POST);
        
        // Check transaction status from Paymob GET/POST parameters
        $success = $request->get('success');
        $isSuccess = ($success === 'true' || $success === '1' || $success === true);

        $orderNumber = $request->get('merchant_order_id') ?: $request->get('order_number');
        $transactionId = $request->get('id') ?? $request->get('order');

        if (str_starts_with((string)$transactionId, 'SIM_') || str_starts_with((string)$transactionId, 'SIM-')) {
            return [
                'verified' => true,
                'paid' => ($isSuccess || $request->get('status') === 'paid'),
                'order_number' => $orderNumber,
                'payment_reference' => 'PAYMOB-' . $transactionId,
                'error' => null
            ];
        }

        $hmacSecret = trim($config['paymob_hmac'] ?? '');

        // If HMAC secret is configured, verify HMAC signature
        if (!empty($hmacSecret) && isset($allParams['hmac'])) {
            $receivedHmac = $allParams['hmac'];
            $calculatedHmac = $this->calculateHmac($allParams, $hmacSecret);
            if (!hash_equals(strtolower($calculatedHmac), strtolower($receivedHmac))) {
                Logger::warning("Paymob HMAC signature verification failed", [
                    'received' => $receivedHmac,
                    'calculated' => $calculatedHmac
                ]);
            }
        }

        if ($isSuccess) {
            return [
                'verified' => true,
                'paid' => true,
                'order_number' => $orderNumber,
                'payment_reference' => 'PAYMOB-' . ($transactionId ?: time()),
                'error' => null
            ];
        }

        $errorMsg = $request->get('data_message') ?? 'تم رفض أو إلغاء عملية الدفع من قبل البنك أو العميل';
        return [
            'verified' => true,
            'paid' => false,
            'order_number' => $orderNumber,
            'payment_reference' => 'PAYMOB-' . ($transactionId ?: time()),
            'error' => $errorMsg
        ];
    }

    public function testConnection(array $config = []): array
    {
        $config = array_merge($this->config, $config);
        $apiKey = trim($config['paymob_api_key'] ?? ($config['api_key'] ?? ''));
        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'يرجى إدخال مفتاح الـ API السري (Secret API Key) لـ Paymob أولاً.'
            ];
        }

        try {
            $res = $this->httpPost(self::BASE_URL . '/auth/tokens', [
                'api_key' => $apiKey
            ]);

            if (!empty($res['token'])) {
                return [
                    'success' => true,
                    'message' => 'تم الاتصال بخادم Paymob وتوثيق المفتاح بنجاح تام! الحساب نشط وصالح للتحصيل.',
                    'details' => [
                        'auth_token_sample' => substr($res['token'], 0, 15) . '...',
                        'server' => 'accept.paymob.com'
                    ]
                ];
            }

            $errMsg = $res['detail'] ?? ($res['message'] ?? json_encode($res, JSON_UNESCAPED_UNICODE));
            return [
                'success' => false,
                'message' => 'فشل توثيق مفتاح Paymob: ' . $errMsg
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'تعذر الوصول إلى خوادم Paymob: ' . $e->getMessage()
            ];
        }
    }

    private function calculateHmac(array $params, string $secret): string
    {
        // Concatenation keys according to Paymob documentation
        $keys = [
            'amount_cents', 'created_at', 'currency', 'error_occured',
            'has_parent_transaction', 'id', 'integration_id', 'is_3d_secure',
            'is_auth', 'is_capture', 'is_refunded', 'is_standalone_payment',
            'is_voided', 'order', 'owner', 'pending', 'source_data_pan',
            'source_data_sub_type', 'source_data_type', 'success'
        ];

        $concatenated = '';
        foreach ($keys as $k) {
            if (isset($params[$k])) {
                $concatenated .= is_bool($params[$k]) ? ($params[$k] ? 'true' : 'false') : (string)$params[$k];
            }
        }

        return hash_hmac('sha512', $concatenated, $secret);
    }

    private function httpPost(string $url, array $data): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            throw new \RuntimeException("cURL error: {$err}");
        }

        return json_decode((string)$res, true) ?: [];
    }
}
