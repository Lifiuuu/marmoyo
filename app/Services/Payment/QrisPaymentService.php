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
     * Verify QRIS payment and dispatch order to kitchen.
     */
    public function verifyPayment(Payment $payment): bool
    {
        // Idempotency: if already verified as success, return true immediately
        if ($payment->isSuccess()) {
            return true;
        }

        if ($payment->isExpired()) {
            $payment->update(['status' => 'expired']);
            $payment->order->update(['payment_status' => 'expired']);
            return false;
        }

        // If Midtrans is configured and we want to verify via Midtrans status API
        if ($this->midtransService->isConfigured()) {
            $midtransStatus = $this->midtransService->getTransactionStatus($payment->order->order_number);
            if ($midtransStatus) {
                $status = $midtransStatus['transaction_status'] ?? '';
                if (!in_array($status, ['settlement', 'capture'])) {
                    if (in_array($status, ['expire', 'cancel', 'deny'])) {
                        $payment->update(['status' => 'expired']);
                        $payment->order->update(['payment_status' => 'expired']);
                    }
                    return false;
                }
            }
        }

        return DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => 'success',
                'paid_at' => Carbon::now(),
            ]);

            $order = $payment->order;
            $order->update([
                'status' => 'menunggu', // Antrean menunggu disajikan
                'payment_status' => 'paid',
                'payment_method' => 'qris',
            ]);

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

            return true;
        });
    }
}
