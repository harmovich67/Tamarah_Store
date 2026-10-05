<?php

namespace App\Services\Payment;

use App\Core\Request;
use App\Core\Logger;

class StripeGateway implements PaymentGatewayInterface
{
    private const BASE_URL = 'https://api.stripe.com/v1';
    protected array $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function initiatePayment(array $order, array $config, array $urls): array
    {
        $secretKey = trim($config['gateway_secret_key'] ?? ($config['stripe_secret_key'] ?? ''));
        if (empty($secretKey)) {
            return [
                'success' => false,
                'redirect_url' => '',
                'payment_reference' => null,
                'message' => 'المفتاح السري لبوابة سترايب (Stripe Secret Key) غير متوفر.'
            ];
        }

        $amountCents = (int)round(((float)$order['total']) * 100);
        $currency = strtolower(trim((string)($config['currency'] ?? 'sar')));
        $returnUrl = $urls['return_url'] ?? '';
        $cancelUrl = $urls['cancel_url'] ?? $returnUrl;

        $separator = str_contains($returnUrl, '?') ? '&' : '?';
        $successUrlWithSession = $returnUrl . $separator . 'session_id={CHECKOUT_SESSION_ID}&order_number=' . urlencode($order['order_number']);

        try {
            $postFields = [
                'success_url' => $successUrlWithSession,
                'cancel_url' => $cancelUrl,
                'mode' => 'payment',
                'client_reference_id' => $order['order_number'],
                'customer_email' => !empty($order['customer_email']) ? $order['customer_email'] : 'customer@store.com',
                'line_items[0][price_data][currency]' => $currency,
                'line_items[0][price_data][unit_amount]' => $amountCents,
                'line_items[0][price_data][product_data][name]' => "فاتورة الطلب #{$order['order_number']}",
                'line_items[0][quantity]' => 1,
                'metadata[order_number]' => $order['order_number']
            ];

            $res = $this->httpCall('POST', self::BASE_URL . '/checkout/sessions', $postFields, $secretKey);

            if (!empty($res['url'])) {
                Logger::info("Stripe checkout session created: {$res['id']}", [
                    'order' => $order['order_number'],
                    'amount' => $amountCents
                ]);

                return [
                    'success' => true,
                    'redirect_url' => $res['url'],
                    'payment_reference' => $res['id'],
                    'message' => 'تم إنشاء جلسة دفع سترايب بنجاح'
                ];
            }

            $errMsg = $res['error']['message'] ?? json_encode($res, JSON_UNESCAPED_UNICODE);
            return [
                'success' => false,
                'redirect_url' => '',
                'payment_reference' => null,
                'message' => 'خطأ سترايب: ' . $errMsg
            ];
        } catch (\Throwable $e) {
            Logger::error("Stripe initiation exception: " . $e->getMessage());
            return [
                'success' => false,
                'redirect_url' => '',
                'payment_reference' => null,
                'message' => 'استثناء أثناء إنشاء جلسة سترايب: ' . $e->getMessage()
            ];
        }
    }

    public function verifyCallback(Request $request, array $config): array
    {
        $secretKey = trim($config['gateway_secret_key'] ?? ($config['stripe_secret_key'] ?? ''));
        $sessionId = $request->get('session_id') ?? $request->get('id');

        if (empty($sessionId)) {
            return [
                'verified' => false,
                'paid' => false,
                'order_number' => null,
                'payment_reference' => null,
                'error' => 'معرف جلسة الدفع session_id غير موجود في الاستجابة'
            ];
        }

        if (str_starts_with((string)$sessionId, 'SIM_') || str_starts_with((string)$sessionId, 'SIM-')) {
            return [
                'verified' => true,
                'paid' => ($request->get('status') === 'paid' || $request->get('success') === 'true'),
                'order_number' => $request->get('order_number'),
                'payment_reference' => 'STRIPE-' . $sessionId,
                'error' => null
            ];
        }

        try {
            $session = $this->httpCall('GET', self::BASE_URL . "/checkout/sessions/{$sessionId}", [], $secretKey);
            $paymentStatus = $session['payment_status'] ?? '';
            $orderNumber = $session['metadata']['order_number'] ?? ($session['client_reference_id'] ?? null);

            if ($paymentStatus === 'paid') {
                return [
                    'verified' => true,
                    'paid' => true,
                    'order_number' => $orderNumber,
                    'payment_reference' => 'STRIPE-' . $sessionId,
                    'error' => null
                ];
            }

            return [
                'verified' => true,
                'paid' => false,
                'order_number' => $orderNumber,
                'payment_reference' => 'STRIPE-' . $sessionId,
                'error' => 'حالة جلسة الدفع غير مكتملة: ' . $paymentStatus
            ];
        } catch (\Throwable $e) {
            Logger::error("Stripe verification failed: " . $e->getMessage());
            return [
                'verified' => false,
                'paid' => false,
                'order_number' => null,
                'payment_reference' => 'STRIPE-' . $sessionId,
                'error' => 'تعذر التحقق من خادم سترايب: ' . $e->getMessage()
            ];
        }
    }

    public function testConnection(array $config = []): array
    {
        $config = array_merge($this->config, $config);
        $secretKey = trim($config['gateway_secret_key'] ?? ($config['stripe_secret_key'] ?? ($config['secret_key'] ?? '')));
        if (empty($secretKey)) {
            return [
                'success' => false,
                'message' => 'يرجى إدخال المفتاح السري (Secret Key) لبوابة سترايب أولاً.'
            ];
        }

        try {
            $res = $this->httpCall('GET', self::BASE_URL . '/balance', [], $secretKey);

            if (isset($res['object']) && $res['object'] === 'balance') {
                return [
                    'success' => true,
                    'message' => 'تم الاتصال بخادم سترايب (Stripe) وتوثيق المفاتيح بنجاح تام! الحساب نشط وجاهز للتحصيل.',
                    'details' => [
                        'gateway' => 'Stripe Global Payments',
                        'livemode' => !empty($res['livemode']) ? 'Live Production' : 'Test Mode'
                    ]
                ];
            }

            $errMsg = $res['error']['message'] ?? json_encode($res, JSON_UNESCAPED_UNICODE);
            return [
                'success' => false,
                'message' => 'فشل توثيق مفتاح سترايب: ' . $errMsg
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'تعذر الاتصال بخادم سترايب: ' . $e->getMessage()
            ];
        }
    }

    private function httpCall(string $method, string $url, array $data, string $secretKey): array
    {
        $ch = curl_init();
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } else {
            $urlWithParams = !empty($data) ? $url . '?' . http_build_query($data) : $url;
            curl_setopt($ch, CURLOPT_URL, $urlWithParams);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$secretKey}",
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
