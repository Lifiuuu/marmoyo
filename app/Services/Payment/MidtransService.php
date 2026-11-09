<?php

namespace App\Services\Payment;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    protected string $serverKey;
    protected string $clientKey;
    protected string $baseUrl;
    protected bool $isProduction;

    public function __construct()
    {
        $this->serverKey = (string) config('midtrans.server_key', '');
        $this->clientKey = (string) config('midtrans.client_key', '');
        $this->baseUrl = (string) config('midtrans.base_url', 'https://api.sandbox.midtrans.com');
        $this->isProduction = (bool) config('midtrans.is_production', false);
    }

    /**
     * Check if valid credentials are configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->serverKey) && !str_contains($this->serverKey, 'placeholder');
    }

    /**
     * Generate dynamic QRIS charge via Midtrans Core API.
     *
     * @param string $orderNumber
     * @param float $amount
     * @param array $items
     * @param array $customerDetails
     * @return array
     * @throws Exception
     */
    public function createQrisCharge(
        string $orderNumber,
        float $amount,
        array $items = [],
        array $customerDetails = []
    ): array {
        // If credentials are placeholder / not yet configured, provide a realistic mock response
        if (!$this->isConfigured()) {
            Log::info("Midtrans credentials not configured, generating mock Midtrans QRIS for order {$orderNumber}");
            $mockQrUrl = "https://api.sandbox.midtrans.com/v2/qris/mock-{$orderNumber}/qr-code";
            $mockQrString = "00020101021226670016ID.CO.QRIS.WWW0118ID102026MARMOYO01520458125303360540" .
                number_format($amount, 2, '', '') .
                "5802ID5918MARMOYO SMART-DINE6008SURABAYA62070703A016304" . strtoupper(substr(md5($orderNumber), 0, 4));

            return [
                'status_code' => '201',
                'status_message' => 'QRIS transaction is created (Mock Sandbox)',
                'transaction_id' => 'mock-' . md5($orderNumber . microtime(true)),
                'order_id' => $orderNumber,
                'gross_amount' => number_format($amount, 2, '.', ''),
                'payment_type' => 'qris',
                'transaction_time' => Carbon::now()->toDateTimeString(),
                'transaction_status' => 'pending',
                'qr_string' => $mockQrString,
                'qris_url' => $mockQrUrl,
                'expiry_time' => Carbon::now()->addMinutes(15)->toDateTimeString(),
                'raw' => [
                    'actions' => [
                        ['name' => 'generate-qr-code', 'method' => 'GET', 'url' => $mockQrUrl]
                    ]
                ],
            ];
        }

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderNumber,
                'gross_amount' => (int) round($amount),
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
            'custom_expiry' => [
                'expiry_duration' => 15,
                'unit' => 'minute',
            ],
        ];

        if (!empty($items)) {
            $payload['item_details'] = $items;
        }

        if (!empty($customerDetails)) {
            $payload['customer_details'] = $customerDetails;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
            ])->timeout(10)->post("{$this->baseUrl}/v2/charge", $payload);

            if ($response->failed()) {
                Log::error('Midtrans QRIS Charge failed', [
                    'order_id' => $orderNumber,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                throw new Exception('Gagal menghubungi payment gateway Midtrans: ' . ($response->json('status_message') ?? 'Terjadi kesalahan'));
            }

            $data = $response->json();
            $qrUrl = null;
            if (isset($data['actions']) && is_array($data['actions'])) {
                foreach ($data['actions'] as $action) {
                    if (($action['name'] ?? '') === 'generate-qr-code') {
                        $qrUrl = $action['url'] ?? null;
                        break;
                    }
                }
            }

            return [
                'status_code' => $data['status_code'] ?? '201',
                'status_message' => $data['status_message'] ?? 'QRIS transaction created',
                'transaction_id' => $data['transaction_id'] ?? null,
                'order_id' => $data['order_id'] ?? $orderNumber,
                'gross_amount' => $data['gross_amount'] ?? $amount,
                'payment_type' => $data['payment_type'] ?? 'qris',
                'transaction_time' => $data['transaction_time'] ?? Carbon::now()->toDateTimeString(),
                'transaction_status' => $data['transaction_status'] ?? 'pending',
                'qr_string' => $data['qr_string'] ?? null,
                'qris_url' => $qrUrl,
                'expiry_time' => $data['expiry_time'] ?? Carbon::now()->addMinutes(15)->toDateTimeString(),
                'raw' => $data,
            ];
        } catch (Exception $e) {
            Log::error('Exception in Midtrans QRIS Charge: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            throw $e;
        }
    }

    /**
     * Query transaction status directly from Midtrans.
     *
     * @param string $orderId
     * @return array|null
     */
    public function getTransactionStatus(string $orderId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($this->serverKey . ':'),
            ])->timeout(8)->get("{$this->baseUrl}/v2/{$orderId}/status");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (Exception $e) {
            Log::warning("Midtrans check status error for order {$orderId}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify Midtrans webhook notification signature key.
     * Formula: SHA512(order_id + status_code + gross_amount + ServerKey)
     *
     * @param string $orderId
     * @param string $statusCode
     * @param string $grossAmount
     * @param string $signatureKey
     * @return bool
     */
    public function verifySignature(string $orderId, string $statusCode, string $grossAmount, string $signatureKey): bool
    {
        if (empty($this->serverKey)) {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        return hash_equals($expectedSignature, $signatureKey);
    }
}

