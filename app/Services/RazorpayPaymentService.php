<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RazorpayPaymentService
{
    private function client(): PendingRequest
    {
        $key = config('services.razorpay.key_id');
        $secret = config('services.razorpay.key_secret');
        if (! $key || ! $secret) throw new RuntimeException('Razorpay credentials are not configured.');
        return Http::baseUrl('https://api.razorpay.com/v1')->withBasicAuth($key, $secret)->acceptJson();
    }

    public function createOrder(int $amountPaise, string $receipt, array $notes = []): array
    {
        $response = $this->client()->post('/orders', ['amount' => $amountPaise, 'currency' => 'INR', 'receipt' => $receipt, 'notes' => $notes]);
        if ($response->failed()) throw new RuntimeException('Razorpay order creation failed.');
        return $response->json();
    }

    public function fetchPayment(string $paymentId): array
    {
        $response = $this->client()->get('/payments/'.rawurlencode($paymentId));
        if ($response->failed()) throw new RuntimeException('Razorpay payment status could not be verified.');
        return $response->json();
    }
}
