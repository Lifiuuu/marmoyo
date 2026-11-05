<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Services\Payment\MidtransService;
use App\Services\Payment\QrisPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MidtransQrisPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['midtrans.server_key' => 'SB-Mid-server-testkey123']);
        config(['midtrans.is_production' => false]);
    }

    public function test_signature_verification(): void
    {
        $service = app(MidtransService::class);
        $orderId = 'MRM-TEST-001';
        $statusCode = '200';
        $grossAmount = '50000.00';
        $serverKey = 'SB-Mid-server-testkey123';

        $validSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        $this->assertTrue($service->verifySignature($orderId, $statusCode, $grossAmount, $validSignature));

        $invalidSignature = 'wrong-signature-key-hash';
        $this->assertFalse($service->verifySignature($orderId, $statusCode, $grossAmount, $invalidSignature));
    }

    public function test_qris_payment_generation(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://api.sandbox.midtrans.com/v2/charge' => \Illuminate\Support\Facades\Http::response([
                'status_code' => '201',
                'status_message' => 'QRIS transaction is created',
                'transaction_id' => 'midtrans-trx-mock-999',
                'gross_amount' => '50000.00',
                'payment_type' => 'qris',
                'transaction_time' => now()->toDateTimeString(),
                'transaction_status' => 'pending',
                'actions' => [
                    [
                        'name' => 'generate-qr-code',
                        'method' => 'GET',
                        'url' => 'https://api.sandbox.midtrans.com/v2/qris/midtrans-trx-mock-999/qr-code',
                    ]
                ],
                'qr_string' => '00020101021226670016ID.CO.QRIS.WWW...',
                'expiry_time' => now()->addMinutes(15)->toDateTimeString(),
            ], 201),
        ]);

        $table = RestaurantTable::firstOrCreate(
            ['table_number' => '99'],
            ['qr_token' => 'token99', 'status' => 'available']
        );

        $order = Order::create([
            'order_number' => 'MRM-TEST-' . time(),
            'table_id' => $table->id,
            'customer_name' => 'Budi Test',
            'order_type' => 'dine_in',
            'total_amount' => 50000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'final_amount' => 50000,
            'status' => 'menunggu_pembayaran',
            'payment_method' => 'qris',
            'payment_status' => 'unpaid',
        ]);

        $qrisService = app(QrisPaymentService::class);
        $payment = $qrisService->generateQrisPayment($order);

        $this->assertNotNull($payment);
        $this->assertEquals('qris', $payment->method);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals('midtrans-trx-mock-999', $payment->transaction_id);
        $this->assertEquals('https://api.sandbox.midtrans.com/v2/qris/midtrans-trx-mock-999/qr-code', $payment->qris_url);
        $this->assertNotNull($payment->qris_payload);
        $this->assertNotNull($payment->expires_at);
    }

    public function test_midtrans_webhook_settlement_success(): void
    {
        $table = RestaurantTable::firstOrCreate(
            ['table_number' => '98'],
            ['qr_token' => 'token98', 'status' => 'available']
        );

        $orderNumber = 'MRM-TEST-' . time();
        $order = Order::create([
            'order_number' => $orderNumber,
            'table_id' => $table->id,
            'customer_name' => 'Siti Test',
            'order_type' => 'dine_in',
            'total_amount' => 75000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'final_amount' => 75000,
            'status' => 'menunggu_pembayaran',
            'payment_method' => 'qris',
            'payment_status' => 'unpaid',
        ]);

        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_code' => 'QRIS-' . time(),
            'method' => 'qris',
            'amount' => 75000,
            'status' => 'pending',
            'expires_at' => now()->addMinutes(15),
        ]);

        $serverKey = 'SB-Mid-server-testkey123';
        $signature = hash('sha512', $orderNumber . '200' . '75000.00' . $serverKey);

        $response = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderNumber,
            'status_code' => '200',
            'gross_amount' => '75000.00',
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-trx-12345',
        ]);

        $response->assertStatus(200);
        $order->refresh();
        $payment->refresh();

        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('success', $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        $orderNumber = 'MRM-TEST-FAKE';
        $response = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderNumber,
            'status_code' => '200',
            'gross_amount' => '100000.00',
            'signature_key' => 'invalid_sha512_hash',
            'transaction_status' => 'settlement',
        ]);

        $response->assertStatus(403);
    }

    public function test_payment_status_api(): void
    {
        $table = RestaurantTable::firstOrCreate(
            ['table_number' => '97'],
            ['qr_token' => 'token97', 'status' => 'available']
        );

        $orderNumber = 'MRM-TEST-STATUS-' . time();
        $order = Order::create([
            'order_number' => $orderNumber,
            'table_id' => $table->id,
            'customer_name' => 'Polling Test',
            'order_type' => 'dine_in',
            'total_amount' => 25000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'final_amount' => 25000,
            'status' => 'menunggu_pembayaran',
            'payment_method' => 'qris',
            'payment_status' => 'unpaid',
        ]);

        $response = $this->getJson("/payment/{$orderNumber}/status");
        $response->assertStatus(200)
            ->assertJson([
                'order_number' => $orderNumber,
                'is_paid' => false,
                'payment_status' => 'unpaid',
            ]);
    }
}
