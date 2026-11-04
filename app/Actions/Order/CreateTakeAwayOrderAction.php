<?php

namespace App\Actions\Order;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateTakeAwayOrderAction
{
    /**
     * Create and optionally pay a take-away order directly from POS cashier terminal.
     */
    public function execute(array $data, int $cashierId): Order
    {
        return DB::transaction(function () use ($data, $cashierId) {
            $subtotal = 0;
            $preparedItems = [];

            foreach ($data['items'] as $itemData) {
                $menu = Menu::findOrFail($itemData['menu_id']);
                $unitPrice = (float) $menu->base_price;
                $quantity = max(1, (int) ($itemData['quantity'] ?? 1));
                $itemSubtotal = $unitPrice * $quantity;
                $subtotal += $itemSubtotal;

                $preparedItems[] = [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal_price' => $itemSubtotal,
                    'spice_level' => $itemData['spice_level'] ?? null,
                    'noodle_type' => $itemData['noodle_type'] ?? null,
                    'toppings_selected' => $itemData['toppings_selected'] ?? [],
                    'special_notes' => $itemData['special_notes'] ?? null,
                ];
            }

            $packagingFee = isset($data['packaging_fee']) ? max(0, (float) $data['packaging_fee']) : 2000;
            $discountAmount = max(0, (float) ($data['discount_amount'] ?? 0));
            $taxAmount = round(($subtotal - $discountAmount) * 0.10);
            $serviceFee = 0;
            $finalAmount = max(0, $subtotal + $packagingFee - $discountAmount);

            // Generate daily sequential take away number: MRM-TA01, MRM-TA02, etc.
            $today = Carbon::today();
            $todayTaCount = Order::whereDate('created_at', $today)
                ->where('order_type', 'take_away')
                ->count() + 1;
            
            $orderNumber = sprintf('MRM-TA%02d', $todayTaCount);
            // Ensure unique if number exists
            if (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = sprintf('MRM-TA%02d-%d', $todayTaCount, rand(10, 99));
            }

            $paymentMethod = $data['payment_method'] ?? 'cash';
            $cashReceived = (float) ($data['cash_received'] ?? $finalAmount);
            $isPaidDirectly = ($paymentMethod === 'cash' && $cashReceived >= $finalAmount);

            $order = Order::create([
                'order_number' => $orderNumber,
                'order_type' => 'take_away',
                'table_id' => null,
                'customer_name' => trim($data['customer_name'] ?? 'Pelanggan Take Away'),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'service_fee' => $serviceFee,
                'packaging_fee' => $packagingFee,
                'final_amount' => $finalAmount,
                'status' => $isPaidDirectly ? 'cooking' : 'pending_payment',
                'payment_status' => $isPaidDirectly ? 'paid' : 'unpaid',
                'payment_method' => $paymentMethod,
                'cashier_id' => $cashierId,
                'kitchen_notes' => $data['kitchen_notes'] ?? null,
            ]);

            foreach ($preparedItems as $item) {
                $order->items()->create($item);
            }

            // Create Payment record
            if ($isPaidDirectly) {
                $cashChange = max(0, $cashReceived - $finalAmount);
                Payment::create([
                    'order_id' => $order->id,
                    'payment_code' => 'TUNAI-' . str_replace('MRM-', '', $order->order_number),
                    'method' => 'cash',
                    'amount' => $finalAmount,
                    'cash_received' => $cashReceived,
                    'cash_change' => $cashChange,
                    'status' => 'success',
                    'paid_at' => Carbon::now(),
                    'cashier_id' => $cashierId,
                ]);
            } else {
                Payment::create([
                    'order_id' => $order->id,
                    'payment_code' => strtoupper($paymentMethod) . '-' . str_replace('MRM-', '', $order->order_number),
                    'method' => $paymentMethod,
                    'amount' => $finalAmount,
                    'cash_received' => 0,
                    'cash_change' => 0,
                    'status' => 'pending',
                ]);
            }

            if ($isPaidDirectly) {
                \App\Services\Ai\AiMenuRecommendationService::syncBestsellerStatus();
            }

            return $order->load(['items', 'payment', 'cashier']);
        });
    }
}

