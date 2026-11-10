<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Ai\AiMenuRecommendationService;
use App\Services\Supabase\SupabaseClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QrisPaymentService
{
    public function __construct(
        protected SupabaseClient $supabaseClient,
        protected MidtransService $midtransService
    ) {}

    /**
     * Generate or renew a dynamic Midtrans QRIS transaction.
     */
    public function generateQrisPayment(Order $order): Payment
    {
        return DB::transaction(function () use ($order) {
            $paymentCode = 'QRIS-' . str_replace('MRM-', '', $order->order_number);

            // Prepare item details for Midtrans
            $items = [];
            if ($order->relationLoaded('items') || $order->items()->exists()) {
                foreach ($order->items as $item) {
                    $items[] = [
                        'id' => (string) $item->menu_id,
                        'price' => (int) round($item->unit_price),
                        'quantity' => $item->quantity,
                        'name' => mb_substr($item->menu_name, 0, 50),
                    ];
                }
            }

            // Customer details
            $customerDetails = [
                'first_name' => $order->customer_name ?: 'Pelanggan Meja ' . ($order->table?->table_number ?? '-'),
            ];

            // Request QRIS Charge to Midtrans Core API
            $charge = $this->midtransService->createQrisCharge(
                $order->order_number,
                (float) $order->final_amount,
                $items,
                $customerDetails
            );

            $expiresAt = isset($charge['expiry_time'])
                ? Carbon::parse($charge['expiry_time'])
                : Carbon::now()->addMinutes(15);

            $payment = Payment::updateOrCreate([
                'order_id' => $order->id,
            ], [
                'payment_code' => $paymentCode,
                'transaction_id' => $charge['transaction_id'] ?? null,
                'method' => 'qris',
                'amount' => $order->final_amount,
                'qris_payload' => $charge['qr_string'] ?? null,
                'qris_url' => $charge['qris_url'] ?? null,
                'payload_meta' => $charge['raw'] ?? $charge,
                'status' => 'pending',
                'expires_at' => $expiresAt,
                'paid_at' => null,
            ]);

            $order->update([
                'payment_method' => 'qris',
                'payment_status' => 'unpaid',
            ]);

            return $payment;
        });
    }

    /**
     * Strictly verify QRIS payment against database & payment gateway.
     *
     * @param Payment $payment
     * @return array [
     *     'success' => bool,
     *     'status' => string,
     *     'message' => string
     * ]
     */
    public function verifyPaymentWithStatus(Payment $payment): array
    {
        // 1. Idempotency: if already recorded as success in database, return true immediately
        if ($payment->isSuccess() || ($payment->order && $payment->order->isPaid())) {
            return [
                'success' => true,
                'status' => 'success',
                'message' => 'Pembayaran telah diverifikasi dan tercatat di database.',
            ];
        }

        // 2. Check if payment expired
        if ($payment->isExpired()) {
            $payment->update(['status' => 'expired']);
            if ($payment->order) {
                $payment->order->update(['payment_status' => 'expired']);
            }
            return [
                'success' => false,
                'status' => 'expired',
                'message' => 'Waktu pembayaran QRIS telah kedaluwarsa. Silakan lakukan pemesanan ulang atau pilih bayar tunai di kasir.',
            ];
        }

        // 3. Check with payment gateway (Midtrans) if configured
        if ($this->midtransService->isConfigured()) {
            $midtransStatus = $this->midtransService->getTransactionStatus($payment->order->order_number);

            if (!$midtransStatus) {
                return [
                    'success' => false,
                    'status' => 'unpaid',
                    'message' => 'Pembayaran belum terdeteksi oleh sistem payment gateway. Pastikan Anda telah menyelesaikan pembayaran QRIS melalui mobile banking / e-wallet Anda.',
                ];
            }

            $trxStatus = $midtransStatus['transaction_status'] ?? '';
            $fraudStatus = $midtransStatus['fraud_status'] ?? 'accept';

            // Only 'settlement' or 'capture' (with accept) means real money has been received
            if ($trxStatus === 'settlement' || ($trxStatus === 'capture' && $fraudStatus === 'accept')) {
                return $this->markPaymentSuccess($payment, $midtransStatus);
            }

            if ($trxStatus === 'pending') {
                $payment->update(['status' => 'pending']);
                return [
                    'success' => false,
                    'status' => 'pending',
                    'message' => 'Pembayaran belum kami terima! Status transaksi QRIS Anda masih PENDING di perbankan. Silakan selesaikan pembayaran di aplikasi Anda terlebih dahulu.',
                ];
            }

            if (in_array($trxStatus, ['expire', 'cancel', 'deny'])) {
                $payment->update(['status' => 'expired']);
                if ($payment->order) {
                    $payment->order->update(['payment_status' => 'expired']);
                }
                return [
                    'success' => false,
                    'status' => 'expired',
                    'message' => 'Transaksi QRIS dibatalkan atau telah kedaluwarsa oleh sistem payment gateway.',
                ];
            }

            return [
                'success' => false,
                'status' => 'unpaid',
                'message' => 'Pembayaran QRIS belum tercatat lunas di perbankan (Status: ' . strtoupper($trxStatus) . ').',
            ];
        }

        // 4. If payment gateway is not configured:
        // NEVER allow user clicking the button to automatically approve their own order!
        return [
            'success' => false,
            'status' => 'unpaid',
            'message' => 'Pembayaran QRIS belum tercatat lunas di database sistem. Pesanan belum dapat diteruskan ke dapur.',
        ];
    }

    /**
     * Legacy boolean wrapper for verifyPayment.
     */
    public function verifyPayment(Payment $payment): bool
    {
        $result = $this->verifyPaymentWithStatus($payment);
        return $result['success'];
    }

    /**
     * Mark payment as success in DB and dispatch to kitchen.
     */
    public function markPaymentSuccess(Payment $payment, ?array $meta = null): array
    {
        return DB::transaction(function () use ($payment, $meta) {
            $updateData = [
                'status' => 'success',
                'paid_at' => Carbon::now(),
            ];
            if ($meta) {
                $updateData['payload_meta'] = $meta;
                if (!empty($meta['transaction_id'])) {
                    $updateData['transaction_id'] = $meta['transaction_id'];
                }
            }

            $payment->update($updateData);

            $order = $payment->order;
            if ($order) {
                $order->update([
                    'status' => 'menunggu', // Antrean menunggu disajikan
                    'payment_status' => 'paid',
                    'payment_method' => 'qris',
                ]);
            }

            // Sync dynamic bestseller rankings based on new sales transaction
            try {
                AiMenuRecommendationService::syncBestsellerStatus();
            } catch (\Throwable $e) {
                Log::warning('Failed syncing bestseller status: ' . $e->getMessage());
            }

            // Broadcast to Supabase Realtime / POS stream
            try {
                $this->supabaseClient->broadcast('orders', 'order:paid', [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'table_number' => $order->table?->table_number ?? '-',
                    'method' => 'qris',
                    'amount' => $order->final_amount,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed broadcasting to Supabase: ' . $e->getMessage());
            }

            return [
                'success' => true,
                'status' => 'success',
                'message' => 'Pembayaran QRIS berhasil dikonfirmasi!',
            ];
        });
    }
}
