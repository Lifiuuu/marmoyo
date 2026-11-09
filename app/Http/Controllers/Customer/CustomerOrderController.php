<?php

namespace App\Http\Controllers\Customer;

use App\Actions\Order\CreateOrderAction;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuTopping;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Services\Ai\AiMenuRecommendationService;
use App\Services\Payment\MidtransService;
use App\Services\Payment\QrisPaymentService;
use App\Services\Printer\KitchenPrintFormatter;
use App\Services\Table\TableSessionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CustomerOrderController extends Controller
{
    public function __construct(
        protected TableSessionService $tableService,
        protected AiMenuRecommendationService $aiService,
        protected CreateOrderAction $createOrderAction,
        protected QrisPaymentService $qrisService,
        protected MidtransService $midtransService,
        protected KitchenPrintFormatter $printFormatter
    ) {}

    /**
     * Entry point from physical QR code scan: /scan?table=08&token=xxx
     */
    public function scan(Request $request)
    {
        $tableNumber = $request->query('table');
        $token = $request->query('token');

        if (!$tableNumber || !$token) {
            return response()->view('customer.invalid-table', [
                'message' => 'QR Code tidak valid atau informasi meja tidak lengkap.',
            ], 403);
        }

        $table = $this->tableService->verifyAndSetSession($tableNumber, $token);
        if (!$table) {
            return response()->view('customer.invalid-table', [
                'message' => "Token QR Code Meja {$tableNumber} tidak valid atau telah kedaluwarsa. Silakan hubungi kasir.",
            ], 403);
        }

        return redirect()->route('customer.menu');
    }

    /**
     * Digital Menu exploration page.
     */
    public function menu(Request $request)
    {
        $table = $this->tableService->getCurrentTable();

        $categories = Category::with(['menus' => function ($query) {
            $query->where('is_available', true)->with(['variants']);
        }])->orderBy('sort_order')->get();

        $featuredMenu = Menu::with('variants')
            ->where('is_bestseller', true)
            ->where('is_available', true)
            ->first() ?? Menu::where('is_available', true)->first();

        $allToppings = MenuTopping::where('is_available', true)->get();
        $cart = Session::get('cart', []);
        $cartCount = array_sum(array_column($cart, 'quantity'));
        $cartSubtotal = array_sum(array_column($cart, 'subtotal_price'));

        return view('customer.menu', compact('categories', 'featuredMenu', 'allToppings', 'table', 'cartCount', 'cartSubtotal'));
    }

    /**
     * Add item to cart.
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1',
            'spice_level' => 'nullable|string',
            'noodle_type' => 'nullable|string',
            'toppings' => 'nullable|array',
            'special_notes' => 'nullable|string|max:200',
        ]);

        $menu = Menu::findOrFail($validated['menu_id']);
        $unitPrice = (float) $menu->base_price;

        // Query variant price directly from Supabase database
        if (!empty($validated['spice_level'])) {
            $spiceVariant = MenuVariant::where('menu_id', $menu->id)
                ->where('group_name', 'Tingkat Kepedasan')
                ->where('name', $validated['spice_level'])
                ->first();
            if ($spiceVariant && $spiceVariant->additional_price > 0) {
                $unitPrice += (float) $spiceVariant->additional_price;
            }
        }

        if (!empty($validated['noodle_type'])) {
            $noodleVariant = MenuVariant::where('menu_id', $menu->id)
                ->where('group_name', 'Jenis Mie')
                ->where('name', $validated['noodle_type'])
                ->first();
            if ($noodleVariant && $noodleVariant->additional_price > 0) {
                $unitPrice += (float) $noodleVariant->additional_price;
            }
        }

        $toppingsList = [];
        if (!empty($validated['toppings'])) {
            $toppings = MenuTopping::whereIn('id', $validated['toppings'])->get();
            foreach ($toppings as $top) {
                $unitPrice += (float) $top->price;
                $toppingsList[] = [
                    'id' => $top->id,
                    'name' => $top->name,
                    'price' => (float) $top->price,
                ];
            }
        }

        $cart = Session::get('cart', []);
        $cartKey = md5($menu->id . ($validated['spice_level'] ?? '') . ($validated['noodle_type'] ?? '') . json_encode($toppingsList) . ($validated['special_notes'] ?? ''));

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += (int) $validated['quantity'];
            $cart[$cartKey]['subtotal_price'] = $cart[$cartKey]['quantity'] * $unitPrice;
        } else {
            $cart[$cartKey] = [
                'cart_key' => $cartKey,
                'menu_id' => $menu->id,
                'name' => $menu->name,
                'image_url' => $menu->image_url,
                'unit_price' => $unitPrice,
                'quantity' => (int) $validated['quantity'],
                'subtotal_price' => (int) $validated['quantity'] * $unitPrice,
                'spice_level' => $validated['spice_level'] ?? null,
                'noodle_type' => $validated['noodle_type'] ?? null,
                'toppings' => $toppingsList,
                'special_notes' => $validated['special_notes'] ?? null,
            ];
        }

        Session::put('cart', $cart);

        return redirect()->back()->with('success', "'{$menu->name}' berhasil ditambahkan ke keranjang!");
    }

    /**
     * Cart and AI Recommendation review page.
     */
    public function cart(Request $request)
    {
        $table = $this->tableService->getCurrentTable();
        $cart = Session::get('cart', []);

        $subtotal = array_sum(array_column($cart, 'subtotal_price'));
        $promoCode = $request->query('promo') ?: Session::get('applied_promo', 'MAKANHEMAT');
        $discount = 0;

        if (strtoupper($promoCode) === 'MAKANHEMAT' && $subtotal >= 20000) {
            $discount = 3000;
            Session::put('applied_promo', 'MAKANHEMAT');
        } else {
            Session::forget('applied_promo');
        }

        $taxAmount = round(($subtotal - $discount) * 0.10);
        $finalAmount = max(0, $subtotal - $discount);

        // Fetch AI smart pairings
        $aiRecommendation = $this->aiService->getRecommendationsForCart($cart);

        return view('customer.cart', compact('table', 'cart', 'subtotal', 'discount', 'promoCode', 'taxAmount', 'finalAmount', 'aiRecommendation'));
    }

    /**
     * Remove or adjust quantity in cart.
     */
    public function updateCart(Request $request)
    {
        $key = $request->input('cart_key');
        $action = $request->input('action'); // increment, decrement, remove

        $cart = Session::get('cart', []);
        if (isset($cart[$key])) {
            if ($action === 'increment') {
                $cart[$key]['quantity']++;
                $cart[$key]['subtotal_price'] = $cart[$key]['quantity'] * $cart[$key]['unit_price'];
            } elseif ($action === 'decrement') {
                $cart[$key]['quantity']--;
                if ($cart[$key]['quantity'] <= 0) {
                    unset($cart[$key]);
                } else {
                    $cart[$key]['subtotal_price'] = $cart[$key]['quantity'] * $cart[$key]['unit_price'];
                }
            } elseif ($action === 'remove') {
                unset($cart[$key]);
            }
            Session::put('cart', $cart);
        }

        return redirect()->route('customer.cart');
    }

    /**
     * Checkout: Place order and choose payment.
     */
    public function checkout(Request $request)
    {
        $table = $this->tableService->getCurrentTable();
        if (!$table) {
            return redirect()->route('customer.menu')->with('error', 'Silakan scan QR meja terlebih dahulu.');
        }

        $cart = Session::get('cart', []);
        if (empty($cart)) {
            // If order was already created just moments ago (e.g. resubmission after connection drop or double-click), redirect safely
            if ($lastOrderNumber = Session::get('last_created_order_number')) {
                return redirect()->route('customer.payment', ['orderNumber' => $lastOrderNumber]);
            }
            return redirect()->route('customer.menu')->with('error', 'Keranjang belanja Anda masih kosong.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:qris,cash',
            'kitchen_notes' => 'nullable|string|max:255',
        ], [
            'payment_method.required' => 'Silakan pilih metode pembayaran (QRIS Instan atau Tunai di Kasir) terlebih dahulu.',
            'payment_method.in' => 'Metode pembayaran yang dipilih tidak valid.',
        ]);

        $paymentMethod = $validated['payment_method'];
        $promoCode = Session::get('applied_promo', 'MAKANHEMAT');
        $kitchenNotes = $validated['kitchen_notes'] ?? null;

        // Execute Order creation
        $order = $this->createOrderAction->execute([
            'table_id' => $table->id,
            'customer_name' => 'Pelanggan Meja ' . $table->table_number,
            'items' => array_values($cart),
            'promo_code' => $promoCode,
            'kitchen_notes' => $kitchenNotes,
            'payment_method' => $paymentMethod,
        ]);

        // Generate Payment record
        if ($paymentMethod === 'qris') {
            $payment = $this->qrisService->generateQrisPayment($order);
        } else {
            // Cash payment token: TUNAI-{table}{suffix}
            $paymentCode = 'TUNAI-' . str_replace('MRM-', '', $order->order_number);
            $payment = Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_code' => $paymentCode,
                    'method' => 'cash',
                    'amount' => $order->final_amount,
                    'status' => 'pending',
                ]
            );
        }

        // Store last created order for resilience against page reload / network retry, then clear cart
        Session::put('last_created_order_number', $order->order_number);
        Session::forget(['cart', 'applied_promo']);

        return redirect()->route('customer.payment', ['orderNumber' => $order->order_number]);
    }

    /**
     * API for Customer Realtime Payment & Order Status polling.
     */
    public function paymentStatusApi(string $orderNumber)
    {
        $order = Order::with('payment')->where('order_number', $orderNumber)->first();
        if (!$order) {
            return response()->json(['error' => 'Pesanan tidak ditemukan'], 404);
        }

        // On-demand sync with Midtrans if payment is still pending
        if (!$order->isPaid() && $order->payment && $order->payment->method === 'qris') {
            if ($this->midtransService->isConfigured()) {
                $statusData = $this->midtransService->getTransactionStatus($order->order_number);
                if ($statusData && in_array($statusData['transaction_status'] ?? '', ['settlement', 'capture'])) {
                    $this->qrisService->verifyPayment($order->payment);
                    $order->refresh();
                }
            }
        }

        return response()->json([
            'order_number' => $order->order_number,
            'is_paid' => $order->isPaid(),
            'payment_status' => $order->payment_status,
            'order_status' => $order->status,
            'payment_method' => $order->payment_method,
            'redirect_url' => route('customer.order-status', ['orderNumber' => $order->order_number]),
        ]);
    }

    /**
     * Payment view: Displays QRIS timer or Cash queue instructions.
     */
    public function payment(string $orderNumber)
    {
        $order = Order::with(['items', 'table', 'payment'])->where('order_number', $orderNumber)->firstOrFail();
        $payment = $order->payment;

        if ($order->isPaid()) {
            return redirect()->route('customer.order-status', ['orderNumber' => $order->order_number]);
        }

        return view('customer.payment', compact('order', 'payment'));
    }

    /**
     * Simulate or verify QRIS payment verification.
     */
    public function verifyQris(string $orderNumber)
    {
        $order = Order::with(['payment', 'table'])->where('order_number', $orderNumber)->firstOrFail();
        $payment = $order->payment;

        if (!$payment) {
            return redirect()->back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $success = $this->qrisService->verifyPayment($payment);

        if ($success) {
            return redirect()->route('customer.order-status', ['orderNumber' => $order->order_number])
                ->with('success', 'Pembayaran QRIS Anda berhasil diverifikasi!');
        }

        return redirect()->route('customer.payment', ['orderNumber' => $order->order_number])
            ->with('error', 'Waktu pembayaran QRIS telah habis atau verifikasi gagal.');
    }

    /**
     * Switch payment method from QRIS to Cash or vice-versa.
     */
    public function switchPaymentMethod(Request $request, string $orderNumber)
    {
        $order = Order::with('payment')->where('order_number', $orderNumber)->firstOrFail();
        $newMethod = $request->input('method', 'cash');

        if ($order->payment) {
            $order->payment->delete();
        }

        if ($newMethod === 'cash') {
            $paymentCode = 'TUNAI-' . str_replace('MRM-', '', $order->order_number);
            Payment::create([
                'order_id' => $order->id,
                'payment_code' => $paymentCode,
                'method' => 'cash',
                'amount' => $order->final_amount,
                'status' => 'pending',
            ]);
            $order->update(['payment_method' => 'cash']);
        } else {
            $this->qrisService->generateQrisPayment($order);
            $order->update(['payment_method' => 'qris']);
        }

        return redirect()->route('customer.payment', ['orderNumber' => $order->order_number]);
    }

    /**
     * Live Tracking of Order status and Digital Receipt.
     */
    public function orderStatus(string $orderNumber)
    {
        $order = Order::with(['items', 'table', 'payment', 'cashier'])->where('order_number', $orderNumber)->firstOrFail();
        return view('customer.order-status', compact('order'));
    }

    /**
     * Download or view printable digital receipt.
     */
    public function downloadReceipt(string $orderNumber)
    {
        $order = Order::with(['items', 'table', 'payment', 'cashier'])->where('order_number', $orderNumber)->firstOrFail();
        $receiptText = $this->printFormatter->formatCustomerReceipt($order, $order->payment, '58mm');

        return response($receiptText)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"Struk_{$order->order_number}.txt\"");
    }
}

