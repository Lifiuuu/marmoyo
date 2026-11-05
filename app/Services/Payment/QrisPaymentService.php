<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Supabase\SupabaseClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QrisPaymentService
{
    public function __construct(
        protected SupabaseClient $supabaseClient
    ) {}

    /**
     * Generate or renew a dynamic QRIS transaction with a 5-minute expiry timer.
     */
    public function generateQrisPayment(Order $order): Payment
    {
        return DB::transaction(function () use ($order) {
            $paymentCode = 'QRIS-' . str_replace('MRM-', '', $order->order_number);
            $expiresAt = Carbon::now()->addMinutes(5);

            // Mock dynamic QRIS EMVCo payload format
            $merchantId = 'ID102026MARMOYO01';
            $qrisPayload = "00020101021226670016ID.CO.QRIS.WWW0118{$merchantId}520458125303360540" .
                number_format($order->final_amount, 2, '', '') .
                "5802ID5918MARMOYO SMART-DINE6008SURABAYA62070703A016304" . strtoupper(substr(md5($order->order_number), 0, 4));

            $payment = Payment::updateOrCreate([
                'order_id' => $order->id,
            ], [
                'payment_code' => $paymentCode,
                'method' => 'qris',
                'amount' => $order->final_amount,
                'qris_payload' => $qrisPayload,
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
            \App\Services\Ai\AiMenuRecommendationService::syncBestsellerStatus();

            // Broadcast to Supabase Realtime / POS stream
            $this->supabaseClient->broadcast('orders', 'order:paid', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'table_number' => $order->table->table_number,
                'method' => 'qris',
                'amount' => $order->final_amount,
            ]);

            return true;
        });
    }
}

