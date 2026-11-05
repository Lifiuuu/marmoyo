<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\MidtransService;
use App\Services\Payment\QrisPaymentService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService,
        protected QrisPaymentService $qrisPaymentService
    ) {}

    /**
     * Handle incoming webhook notification from Midtrans.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount || !$signatureKey) {
            Log::warning('Midtrans Webhook invalid payload structure', $payload);
            return response()->json(['message' => 'Invalid payload structure'], 400);
        }

        // Verify SHA512 signature key
        if ($this->midtransService->isConfigured()) {
            $isValid = $this->midtransService->verifySignature(
                $orderId,
                $statusCode,
                $grossAmount,
                $signatureKey
            );

            if (!$isValid) {
                Log::warning("Midtrans Webhook signature verification failed for order {$orderId}");
                return response()->json(['message' => 'Invalid signature key'], 403);
            }
        }

        // Find associated order
        $order = Order::with('payment')->where('order_number', $orderId)->first();
        if (!$order) {
            Log::error("Midtrans Webhook order not found: {$orderId}");
            return response()->json(['message' => 'Order not found'], 404);
        }

        $payment = $order->payment;
        if (!$payment) {
            Log::error("Midtrans Webhook payment record not found for order: {$orderId}");
            return response()->json(['message' => 'Payment record not found'], 404);
        }

        // Store latest transaction metadata
        $payment->update([
            'transaction_id' => $payload['transaction_id'] ?? $payment->transaction_id,
            'payload_meta' => $payload,
        ]);

        // Handle transaction status
        if ($transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept')) {
            // Idempotent payment completion
            if (!$payment->isSuccess()) {
                $this->qrisPaymentService->verifyPayment($payment);
                Log::info("Midtrans Webhook: Order {$orderId} successfully marked as PAID");
            }
        } elseif ($transactionStatus === 'pending') {
            $payment->update(['status' => 'pending']);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $status = $transactionStatus === 'expire' ? 'expired' : 'failed';
            $payment->update(['status' => $status]);
            $order->update(['payment_status' => $status]);
            Log::info("Midtrans Webhook: Order {$orderId} marked as {$status}");
        }

        return response()->json(['message' => 'Notification processed successfully']);
    }
}

