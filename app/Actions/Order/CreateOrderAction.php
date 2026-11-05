<?php

namespace App\Actions\Order;

use App\Models\Menu;
use App\Models\MenuTopping;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrderAction
{
    /**
     * Create a new order with items and calculations.
     *
     * @param array $data
     * @return Order
     */
    public function execute(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $table = RestaurantTable::findOrFail($data['table_id']);

            // Calculate Subtotal and format items
            $subtotal = 0;
            $preparedItems = [];

            foreach ($data['items'] as $itemData) {
                $menu = Menu::findOrFail($itemData['menu_id']);
                $unitPrice = (float) $menu->base_price;

                // Add variant prices (e.g. Level 4-5 Super Pedas +1000)
                if (!empty($itemData['variant_ids'])) {
                    $variants = MenuVariant::whereIn('id', $itemData['variant_ids'])->get();
                    foreach ($variants as $variant) {
                        $unitPrice += (float) $variant->additional_price;
                    }
                }

                // Add topping prices
                $toppingsSelected = [];
                if (!empty($itemData['toppings'])) {
                    foreach ($itemData['toppings'] as $top) {
                        $unitPrice += (float) ($top['price'] ?? 0);
                        $toppingsSelected[] = $top;
                    }
                }

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
                    'toppings_selected' => $toppingsSelected,
                    'special_notes' => $itemData['special_notes'] ?? null,
                ];
            }

            // Voucher calculation (e.g. MAKANHEMAT -> -Rp3.000)
            $discountAmount = 0;
            $promoCode = strtoupper(trim($data['promo_code'] ?? ''));
            if ($promoCode === 'MAKANHEMAT' && $subtotal >= 20000) {
                $discountAmount = 3000;
            }

            // Tax and fee breakdown
            // PB1 (10%) is already included in restaurant menu prices as per SRS
            $taxAmount = round(($subtotal - $discountAmount) * 0.10);
            $serviceFee = 0; // Self-ordering is free of service fee
            $finalAmount = max(0, $subtotal - $discountAmount);

            // Idempotency: prevent duplicate transaction if double clicked or if server retried under latency
            $recentOrder = Order::where('table_id', $table->id)
                ->where('final_amount', $finalAmount)
                ->where('created_at', '>=', now()->subSeconds(4))
                ->latest()
                ->first();

            if ($recentOrder) {
                return $recentOrder;
            }

            // Generate unique Order Number: MRM-{table_number}{random_suffix}
            do {
                $randomSuffix = rand(100, 999);
                $orderNumber = sprintf('MRM-%s%d', $table->table_number, $randomSuffix);
            } while (Order::where('order_number', $orderNumber)->exists());

            // Mark table occupied
            $table->update(['status' => 'occupied']);

            // Create Order with simplified status: 'menunggu'
            $order = Order::create([
                'order_number' => $orderNumber,
                'table_id' => $table->id,
                'customer_name' => $data['customer_name'] ?? 'Pelanggan Meja ' . $table->table_number,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'service_fee' => $serviceFee,
                'final_amount' => $finalAmount,
                'status' => 'menunggu',
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'kitchen_notes' => $data['kitchen_notes'] ?? null,
            ]);

            // Save order items
            foreach ($preparedItems as $item) {
                $order->items()->create($item);
            }

            return $order->load(['table', 'items']);
        });
    }
}

