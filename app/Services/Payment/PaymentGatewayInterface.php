<?php

namespace App\Services\Payment;

use App\Core\Request;

interface PaymentGatewayInterface
{
    /**
     * Initiate payment transaction with the gateway
     *
     * @param array $order Order database record
     * @param array $config Gateway credentials and settings
     * @param array $urls Array of callback, return, and cancel URLs
     * @return array ['success' => bool, 'redirect_url' => string, 'payment_reference' => ?string, 'message' => ?string]
     */
    public function initiatePayment(array $order, array $config, array $urls): array;

    /**
     * Verify payment on return / callback / webhook
     *
     * @param Request $request
     * @param array $config Gateway credentials and settings
     * @return array ['verified' => bool, 'paid' => bool, 'order_number' => ?string, 'payment_reference' => ?string, 'error' => ?string]
     */
    public function verifyCallback(Request $request, array $config): array;

    /**
     * Test API connection with the credentials provided
     *
     * @param array $config Gateway credentials and settings
     * @return array ['success' => bool, 'message' => string, 'details' => ?array]
     */
    public function testConnection(array $config = []): array;
}
