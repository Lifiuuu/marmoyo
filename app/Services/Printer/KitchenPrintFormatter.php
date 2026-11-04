<?php

namespace App\Services\Printer;

use App\Models\Order;
use App\Models\Payment;
use App\Models\StoreSetting;

class KitchenPrintFormatter
{
    /**
     * Format a clean thermal ticket for order preparation (58mm or 80mm width).
     */
    public function formatKitchenTicket(Order $order, string $paperSize = '58mm'): string
    {
        $cols = $paperSize === '80mm' ? 42 : 32;
        $divider = str_repeat('-', $cols);
        $doubleDivider = str_repeat('=', $cols);

        $out = [];
        $out[] = $this->centerText("TIKET PESANAN", $cols);
        $out[] = $this->centerText("KEDAI MIE MARMOYO", $cols);
        $out[] = $doubleDivider;

        if ($order->isTakeAway()) {
            $out[] = $this->centerText(">>> TIKET BUNGKUS / TAKE AWAY <<<", $cols);
            $out[] = $this->centerText("Pelanggan: " . ($order->customer_name ?: 'Bungkus / Take Away'), $cols);
            $out[] = $doubleDivider;
            $out[] = "Pesanan: #" . $order->order_number;
            $out[] = "Waktu  : " . $order->created_at->format('d/m/Y H:i:s');
            $out[] = "Tipe   : TAKE AWAY (Bungkus Box/Bag)";
        } else {
            $out[] = $this->centerText(">>> MEJA " . ($order->table->table_number ?? '00') . " <<<", $cols);
            $out[] = $doubleDivider;
            $out[] = "Pesanan: #" . $order->order_number;
            $out[] = "Waktu  : " . $order->created_at->format('d/m/Y H:i:s');
            $out[] = "Tipe   : DINE-IN (Makan di Tempat)";
        }
        $out[] = $divider;

        foreach ($order->items as $item) {
            $out[] = sprintf("%dx %s", $item->quantity, strtoupper($item->menu_name));

            if (!empty($item->spice_level)) {
                $out[] = "   [PEDAS: " . strtoupper($item->spice_level) . "]";
            }

            if (!empty($item->noodle_type)) {
                $out[] = "   - " . $item->noodle_type;
            }

            if (!empty($item->toppings_selected) && is_array($item->toppings_selected)) {
                foreach ($item->toppings_selected as $top) {
                    $out[] = "   + " . ($top['name'] ?? 'Topping');
                }
            }

            if (!empty($item->special_notes)) {
                $out[] = "   *CATATAN: " . $item->special_notes;
            }

            $out[] = "";
        }

        if (!empty($order->kitchen_notes)) {
            $out[] = $divider;
            $out[] = "PESAN KHUSUS MEJA:";
            $out[] = wordwrap($order->kitchen_notes, $cols);
        }

        $out[] = $divider;
        $out[] = $this->centerText("** PRIORITAS PENYAJIAN **", $cols);
        $out[] = "\n\n";

        return implode("\n", $out);
    }

    /**
     * Format a thermal receipt for cashier counter and customer record.
     */
    public function formatCustomerReceipt(Order $order, ?Payment $payment = null, string $paperSize = '58mm'): string
    {
        $cols = $paperSize === '80mm' ? 42 : 32;
        $divider = str_repeat('-', $cols);
        $doubleDivider = str_repeat('=', $cols);

        $payment = $payment ?? $order->payment;
        $settings = StoreSetting::current();

        $out = [];
        $out[] = $this->centerText(strtoupper($settings->store_name), $cols);
        if (!empty($settings->store_tagline)) {
            $out[] = $this->centerText($settings->store_tagline, $cols);
        }
        if (!empty($settings->store_address)) {
            $out[] = $this->centerText($settings->store_address, $cols);
        }
        if (!empty($settings->store_phone)) {
            $out[] = $this->centerText("Telp/WA: " . $settings->store_phone, $cols);
        }
        $out[] = $doubleDivider;
        $out[] = "No. Trans : #" . $order->order_number;
        if ($order->isTakeAway()) {
            $out[] = "Tipe      : TAKE AWAY (Bungkus)";
            $out[] = "Pelanggan : " . ($order->customer_name ?: 'Pelanggan Take Away');
        } else {
            $out[] = "Tipe      : DINE-IN (Makan di Tempat)";
            $out[] = "Meja      : Meja " . ($order->table->table_number ?? '00');
        }
        $out[] = "Tanggal   : " . ($order->created_at ? $order->created_at->format('d/m/Y H:i') : date('d/m/Y H:i'));
        $out[] = "Kasir     : " . ($order->cashier->name ?? 'Kasir Marmoyo');
        $out[] = $divider;

        foreach ($order->items as $item) {
            $out[] = $item->menu_name;
            $qtyPrice = sprintf("%d x Rp%s", $item->quantity, number_format($item->unit_price, 0, ',', '.'));
            $itemTotal = sprintf("Rp%s", number_format($item->subtotal_price, 0, ',', '.'));
            $out[] = $this->justifyText($qtyPrice, $itemTotal, $cols);

            if (!empty($item->spice_level)) {
                $out[] = "  (" . $item->spice_level . ")";
            }
        }

        $out[] = $divider;
        $out[] = $this->justifyText("Subtotal", "Rp" . number_format($order->subtotal, 0, ',', '.'), $cols);

        if ($order->packaging_fee > 0) {
            $out[] = $this->justifyText("Biaya Kemasan (Box)", "Rp" . number_format($order->packaging_fee, 0, ',', '.'), $cols);
        }

        if ($order->discount_amount > 0) {
            $out[] = $this->justifyText("Diskon Promo", "-Rp" . number_format($order->discount_amount, 0, ',', '.'), $cols);
        }

        $out[] = $this->justifyText("PB1 (10% Resto)", "Termasuk", $cols);
        $out[] = $this->justifyText("Biaya Layanan", "GRATIS", $cols);
        $out[] = $doubleDivider;
        $out[] = $this->justifyText("TOTAL BAYAR", "Rp" . number_format($order->final_amount, 0, ',', '.'), $cols);

        if ($payment) {
            $methodStr = strtoupper($payment->method);
            $out[] = $this->justifyText("Metode", $methodStr, $cols);

            if ($payment->method === 'cash') {
                $out[] = $this->justifyText("Uang Tunai", "Rp" . number_format($payment->cash_received, 0, ',', '.'), $cols);
                $out[] = $this->justifyText("Kembalian", "Rp" . number_format($payment->cash_change, 0, ',', '.'), $cols);
            } elseif ($payment->method === 'qris') {
                $out[] = $this->justifyText("Status QRIS", "LUNAS (Auto-Verified)", $cols);
            }
        }

        $out[] = $doubleDivider;
        if (!empty($settings->receipt_footer)) {
            $footerLines = explode("\n", str_replace("\r", "", $settings->receipt_footer));
            foreach ($footerLines as $fLine) {
                if (trim($fLine) !== '') {
                    $out[] = $this->centerText(trim($fLine), $cols);
                }
            }
        } else {
            $out[] = $this->centerText("Terima Kasih Atas Kunjungan Anda", $cols);
            $out[] = $this->centerText("Nikmati Pedas Mantapnya!", $cols);
        }
        $out[] = "\n\n";

        return implode("\n", $out);
    }

    private function centerText(string $text, int $width): string
    {
        $len = strlen($text);
        if ($len >= $width) {
            return $text;
        }
        $pad = (int) floor(($width - $len) / 2);
        return str_repeat(' ', $pad) . $text;
    }

    private function justifyText(string $left, string $right, int $width): string
    {
        $totalLen = strlen($left) + strlen($right);
        if ($totalLen >= $width) {
            return $left . ' ' . $right;
        }
        $spaces = $width - $totalLen;
        return $left . str_repeat(' ', $spaces) . $right;
    }
}

