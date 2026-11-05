<?php

namespace App\Http\Controllers\Cashier;

use App\Actions\Order\CreateTakeAwayOrderAction;
use App\Actions\Payment\ValidateCashPaymentAction;
use App\Http\Controllers\Controller;
use App\Models\CashReconciliation;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Printer\KitchenPrintFormatter;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierController extends Controller
{
    public function __construct(
        protected ValidateCashPaymentAction $validateCashAction,
        protected KitchenPrintFormatter $printFormatter,
        protected CreateTakeAwayOrderAction $takeAwayAction
    ) {}

    /**
     * Cashier Live Order Queue Screen (Page 29 & 30 SRS).
     */
    public function liveOrders(Request $request)
    {
        $today = Carbon::today();

        // Orders metrics
        $todayPaidOrders = Order::whereDate('created_at', $today)->where('payment_status', 'paid');
        $todayOmzet = $todayPaidOrders->sum('final_amount');

        // Simplified active workflow: Menunggu vs Selesai
        $waitingOrdersCount = Order::where('payment_status', 'paid')
            ->where('status', 'menunggu')
            ->whereDate('created_at', $today)
            ->count();

        $completedOrdersCount = Order::whereIn('status', ['selesai', 'completed'])
            ->where(function ($query) use ($today) {
                $query->whereDate('updated_at', $today)
                    ->orWhereDate('created_at', $today);
            })
            ->count();

        // Active kitchen/serving queue: ONLY paid orders that are waiting to be completed
        $activeOrders = Order::with(['items', 'table', 'payment'])
            ->where('payment_status', 'paid')
            ->where('status', 'menunggu')
            ->orderBy('created_at', 'asc')
            ->get();

        // Unpaid orders kept in background for Cash Validation modal lookup
        $unpaidOrders = Order::with(['table', 'payment'])
            ->where('payment_status', 'unpaid')
            ->whereDate('created_at', $today)
            ->get();

        $pendingOrdersMap = $unpaidOrders->mapWithKeys(function ($order) {
            $code = $order->payment->payment_code ?? ('TUNAI-' . str_replace('MRM-', '', $order->order_number));
            return [$code => [
                'amount' => (float) $order->final_amount,
                'order_number' => $order->order_number,
                'table_number' => $order->isTakeAway() ? 'TA' : ($order->table->table_number ?? '00'),
                'customer_name' => $order->customer_name,
                'order_type' => $order->order_type ?? 'dine_in',
            ]];
        });

        return view('cashier.live-orders', compact(
            'todayOmzet',
            'waitingOrdersCount',
            'completedOrdersCount',
            'activeOrders',
            'pendingOrdersMap'
        ));
    }

    /**
     * Cashier Realtime Live Orders Feed API (Auto-sync without refresh).
     */
    public function liveOrdersFeed(Request $request)
    {
        $today = Carbon::today();

        // 1. Compute lightweight hash signature
        $activeSignature = Order::where('payment_status', 'paid')
            ->where('status', 'menunggu')
            ->selectRaw('count(*) as count, max(updated_at) as max_updated, sum(final_amount) as total')
            ->first();

        $unpaidSignature = Order::where('payment_status', 'unpaid')
            ->whereDate('created_at', $today)
            ->selectRaw('count(*) as count, max(updated_at) as max_updated')
            ->first();

        $completedCount = Order::whereIn('status', ['selesai', 'completed'])
            ->where(function ($query) use ($today) {
                $query->whereDate('updated_at', $today)
                    ->orWhereDate('created_at', $today);
            })
            ->count();

        $currentHash = md5(json_encode([
            $activeSignature->count ?? 0,
            $activeSignature->max_updated ?? '',
            $activeSignature->total ?? 0,
            $unpaidSignature->count ?? 0,
            $unpaidSignature->max_updated ?? '',
            $completedCount,
        ]));

        if ($request->query('hash') && $request->query('hash') === $currentHash) {
            return response()->json([
                'changed' => false,
                'hash' => $currentHash,
            ]);
        }

        // 2. Metrics & full data if hash differs
        $todayPaidOrders = Order::whereDate('created_at', $today)->where('payment_status', 'paid');
        $todayOmzet = (float) $todayPaidOrders->sum('final_amount');

        $waitingOrdersCount = (int) ($activeSignature->count ?? 0);
        $completedOrdersCount = $completedCount;

        $activeOrders = Order::with(['items', 'table', 'payment'])
            ->where('payment_status', 'paid')
            ->where('status', 'menunggu')
            ->orderBy('created_at', 'asc')
            ->get();

        $unpaidOrders = Order::with(['table', 'payment'])
            ->where('payment_status', 'unpaid')
            ->whereDate('created_at', $today)
            ->get();

        $pendingOrdersMap = $unpaidOrders->mapWithKeys(function ($order) {
            $code = $order->payment->payment_code ?? ('TUNAI-' . str_replace('MRM-', '', $order->order_number));
            return [$code => [
                'amount' => (float) $order->final_amount,
                'order_number' => $order->order_number,
                'table_number' => $order->isTakeAway() ? 'TA' : ($order->table->table_number ?? '00'),
                'customer_name' => $order->customer_name,
                'order_type' => $order->order_type ?? 'dine_in',
            ]];
        });

        $html = view('cashier.partials.active-orders-grid', compact('activeOrders'))->render();

        return response()->json([
            'changed' => true,
            'hash' => $currentHash,
            'omzet_formatted' => 'Rp' . number_format($todayOmzet, 0, ',', '.'),
            'waiting_orders_count' => $waitingOrdersCount,
            'completed_orders_count' => $completedOrdersCount,
            'active_orders_count' => $activeOrders->count(),
            'orders_html' => $html,
            'pending_orders_map' => $pendingOrdersMap,
        ]);
    }

    /**
     * Cash Validation from Modal (Page 30 SRS).
     */
    public function validateCash(Request $request)
    {
        $validated = $request->validate([
            'payment_code' => 'required|string',
            'cash_received' => 'required|numeric|min:0',
        ]);

        $cashierId = (int) Auth::id();

        $payment = $this->validateCashAction->execute(
            strtoupper(trim($validated['payment_code'])),
            (float) $validated['cash_received'],
            $cashierId
        );

        return redirect()->route('cashier.live-orders')->with('success', "Pembayaran kode {$payment->payment_code} berhasil divalidasi! Kembalian: Rp " . number_format($payment->cash_change, 0, ',', '.') . ".");
    }

    /**
     * Cashier Take Away Order Terminal (POS Bungkus).
     */
    public function takeAwayCreate()
    {
        $categories = Category::with(['menus' => function ($q) {
            $q->orderBy('name');
        }])->orderBy('sort_order')->get();

        return view('cashier.take-away', compact('categories'));
    }

    /**
     * Process Take Away order creation and instant payment.
     */
    public function takeAwayStore(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|exists:menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.spice_level' => 'nullable|string',
            'items.*.noodle_type' => 'nullable|string',
            'items.*.special_notes' => 'nullable|string',
            'packaging_fee' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cash,qris',
            'cash_received' => 'nullable|numeric|min:0',
            'kitchen_notes' => 'nullable|string|max:255',
        ]);

        $cashierId = (int) Auth::id();

        $order = $this->takeAwayAction->execute($validated, $cashierId);

        $changeMsg = '';
        if ($order->isPaid() && $order->payment && $order->payment->cash_change > 0) {
            $changeMsg = " Kembalian: Rp " . number_format($order->payment->cash_change, 0, ',', '.') . ".";
        }

        return redirect()->route('cashier.live-orders')
            ->with('success', "Pesanan Take Away #{$order->order_number} ({$order->customer_name}) berhasil dibuat!{$changeMsg}")
            ->with('last_order_id', $order->id);
    }

    /**
     * Advance order status: menunggu -> selesai.
     */
    public function updateStatus(Request $request, int $id)
    {
        $order = Order::with('table')->findOrFail($id);
        $newStatus = $request->input('status'); // menunggu, selesai (also accepts completed, batal, cancelled)

        if (in_array($newStatus, ['menunggu', 'selesai', 'completed', 'batal', 'cancelled'])) {
            $mappedStatus = in_array($newStatus, ['selesai', 'completed']) ? 'selesai' : $newStatus;
            $order->update(['status' => $mappedStatus]);

            // If order completed/selesai or cancelled/batal, free the table (for dine-in) if no active orders remain
            if (in_array($mappedStatus, ['selesai', 'completed', 'batal', 'cancelled'])) {
                if ($order->table_id && $order->table) {
                    $hasActiveOrders = Order::where('table_id', $order->table_id)
                        ->where('id', '!=', $order->id)
                        ->where('status', 'menunggu')
                        ->exists();

                    if (!$hasActiveOrders) {
                        $order->table->update(['status' => 'available']);
                    }
                }
            }
        }

        $statusLabel = in_array($newStatus, ['selesai', 'completed']) ? 'SELESAI DISAJIKAN' : strtoupper($newStatus);
        return redirect()->route('cashier.live-orders')->with('success', "Pesanan #{$order->order_number} berhasil diperbarui: {$statusLabel}");
    }

    /**
     * Cashier Stock Availability Control (Page 30 SRS).
     * Cashiers can only view and toggle availability (Habis / Tersedia), cannot add or edit menus.
     */
    public function menuStock()
    {
        $categories = Category::with('menus')->orderBy('sort_order')->get();
        return view('cashier.menus.index', compact('categories'));
    }

    /**
     * Cashier 1-click toggle menu stock availability.
     */
    public function toggleStock(int $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->is_available = !$menu->is_available;
        $menu->save();

        $statusText = $menu->is_available ? 'Tersedia' : 'Habis (Sold Out)';
        return redirect()->back()->with('success', "Status stok menu '{$menu->name}' berhasil diubah menjadi: {$statusText}.");
    }

    /**
     * Print Kitchen Ticket (ESC/POS preview).
     */
    public function printKitchen(int $id)
    {
        $order = Order::with(['items', 'table'])->findOrFail($id);
        $ticket = $this->printFormatter->formatKitchenTicket($order);

        return response($ticket)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }

    /**
     * Print Customer Receipt (ESC/POS preview).
     */
    public function printReceipt(int $id)
    {
        $order = Order::with(['items', 'table', 'payment', 'cashier'])->findOrFail($id);
        $receipt = $this->printFormatter->formatCustomerReceipt($order, $order->payment);

        return response($receipt)
            ->header('Content-Type', 'text/plain; charset=utf-8');
    }

    /**
     * Cashier Settlement / End of Shift Cash Reconciliation Screen (Page 30 SRS).
     */
    public function settlement()
    {
        $today = Carbon::today();
        $cashierId = (int) Auth::id();

        $totalCashSales = Payment::where('method', 'cash')
            ->where('status', 'success')
            ->whereDate('paid_at', $today)
            ->sum('amount');

        $totalQrisSales = Payment::where('method', 'qris')
            ->where('status', 'success')
            ->whereDate('paid_at', $today)
            ->sum('amount');

        $existingReconciliation = CashReconciliation::where('shift_date', $today)
            ->where('cashier_id', $cashierId)
            ->first();

        return view('cashier.settlement', compact('totalCashSales', 'totalQrisSales', 'existingReconciliation'));
    }

    /**
     * Close cashier shift and record cash discrepancy audit.
     */
    public function closeShift(Request $request)
    {
        $validated = $request->validate([
            'starting_cash' => 'required|numeric|min:0',
            'actual_physical_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $today = Carbon::today();
        $cashierId = (int) Auth::id();

        $totalCashSales = Payment::where('method', 'cash')
            ->where('status', 'success')
            ->whereDate('paid_at', $today)
            ->sum('amount');

        $totalQrisSales = Payment::where('method', 'qris')
            ->where('status', 'success')
            ->whereDate('paid_at', $today)
            ->sum('amount');

        $expectedCash = $validated['starting_cash'] + $totalCashSales;
        $difference = $validated['actual_physical_cash'] - $expectedCash;

        CashReconciliation::updateOrCreate([
            'cashier_id' => $cashierId,
            'shift_date' => $today,
        ], [
            'starting_cash' => $validated['starting_cash'],
            'total_cash_sales' => $totalCashSales,
            'total_qris_sales' => $totalQrisSales,
            'actual_physical_cash' => $validated['actual_physical_cash'],
            'cash_difference' => $difference,
            'notes' => $validated['notes'],
            'status' => 'closed',
            'closed_at' => Carbon::now(),
        ]);

        $diffMsg = $difference == 0
            ? "Uang fisik klop sempurna (Selisih: Rp 0)!"
            : "Shift ditutup dengan selisih: Rp " . number_format($difference, 0, ',', '.');

        return redirect()->route('cashier.settlement')->with('success', "Shift harian berhasil ditutup. {$diffMsg}");
    }
}

