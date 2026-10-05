<?php

namespace App\Services;

use App\Services\Payment\PaymobGateway;
use App\Services\Payment\PaymentManager;

class PaymobService
{
    private PaymobGateway $gateway;

    public function __construct()
    {
        $this->gateway = new PaymobGateway();
    }

    /**
     * Initiate Paymob checkout workflow
     */
    public function initiatePayment(array $orderData, string $paymentMethod = 'paymob_card'): array
    {
        $settings = PaymentManager::getSettings();
        $urls = [
            'return_url' => url('/checkout/payment/callback/paymob'),
            'cancel_url' => url('/checkout')
        ];

        return $this->gateway->initiatePayment($orderData, $settings, $urls);
    }
}
