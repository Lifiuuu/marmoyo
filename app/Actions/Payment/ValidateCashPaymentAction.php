<?php

namespace App\Actions\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Supabase\SupabaseClient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ValidateCashPaymentAction
{
    public function __construct(
        protected SupabaseClient $supabaseClient
    ) {}

    /**
     * Validate cash payment received by cashier at POS counter.
     *
     * @param string $paymentCode TUNAI-xxxx
     * @param float $cashReceived Amount of cash handed by customer
     * @param int $cashierId User ID of cashier
     * @return Payment
     */
    public function execute(string $paymentCode, float $cashReceived, int $cashierId): Payment
    {
        return DB::transaction(function () use ($paymentCode, $cashReceived, $cashierId) {
            $payment = Payment::with('order.table')->where('payment_code', $paymentCode)->first();

            if (!$payment) {
                throw ValidationException::withMessages([
                    'payment_code' => "Kode pembayaran '{$paymentCode}' tidak ditemukan.",
                ]);
            }

            if ($payment->isSuccess()) {
                throw ValidationException::withMessages([
                    'payment_code' => "Pesanan dengan kode '{$paymentCode}' sudah lunas sebelumnya.",
                ]);
            }

            $order = $payment->order;
            $requiredAmount = (float) $order->final_amount;

            if ($cashReceived < $requiredAmount) {
                throw ValidationException::withMessages([
                    'cash_received' => "Nominal uang diterima (Rp " . number_format($cashReceived, 0, ',', '.') . ") kurang dari total tagihan (Rp " . number_format($requiredAmount, 0, ',', '.') . ").",
                ]);
            }

            $cashChange = $cashReceived - $requiredAmount;

            $payment->update([
                'status' => 'success',
                'cash_received' => $cashReceived,
                'cash_change' => $cashChange,
                'paid_at' => Carbon::now(),
            ]);

            $order->update([
                'status' => 'cooking', // Automatically dispatched to kitchen
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'cashier_id' => $cashierId,
            ]);

            // Sync dynamic bestseller rankings based on new sales transaction
            \App\Services\Ai\AiMenuRecommendationService::syncBestsellerStatus();

            // Broadcast to Supabase Realtime / Kitchen display
            $this->supabaseClient->broadcast('orders', 'order:paid', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'table_number' => $order->table->table_number,
                'method' => 'cash',
                'amount' => $order->final_amount,
                'cash_change' => $cashChange,
                'cashier_id' => $cashierId,
            ]);

            return $payment->fresh(['order.items', 'order.table']);
        });
    }
}

