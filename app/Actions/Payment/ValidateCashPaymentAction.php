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
            $code = strtoupper(trim($paymentCode));

            // Clean query: extract code from URL if scanned from full URL
            if (str_contains($code, 'TABLE=')) {
                parse_str(parse_url($paymentCode, PHP_URL_QUERY) ?? '', $urlParams);
                if (!empty($urlParams['table'])) {
                    $code = 'MEJA ' . $urlParams['table'];
                }
            } elseif (str_contains($code, '/PAYMENT/')) {
                $parts = explode('/payment/', $paymentCode);
                if (!empty($parts[1])) {
                    $code = explode('?', explode('/', $parts[1])[0])[0];
                }
            }

            $payment = Payment::with('order.table')
                ->where('payment_code', $code)
                ->orWhereHas('order', function ($q) use ($code) {
                    $q->where('order_number', $code);
                })
                ->lockForUpdate()
                ->first();

            // If not found, check if it's a table number with an active unpaid order (e.g. "08" or "Meja 08")
            if (!$payment) {
                $tableDigits = preg_replace('/[^0-9]/', '', $code);
                if (!empty($tableDigits)) {
                    $order = Order::with('payment')
                        ->where('payment_status', 'unpaid')
                        ->whereHas('table', function ($tq) use ($tableDigits) {
                            $tq->where('table_number', sprintf('%02d', (int) $tableDigits))
                               ->orWhere('table_number', (string) (int) $tableDigits);
                        })
                        ->latest()
                        ->first();
                    if ($order && $order->payment) {
                        $payment = $order->payment;
                    }
                }
            }

            if (!$payment) {
                throw ValidationException::withMessages([
                    'payment_code' => "Kode pembayaran atau pesanan '{$paymentCode}' tidak ditemukan atau sudah diproses.",
                ]);
            }

            // Idempotency: if already paid (e.g. from rapid double-click or server retry), return existing successful payment
            if ($payment->isSuccess()) {
                return $payment;
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
                'status' => 'menunggu', // Antrean menunggu disajikan
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

