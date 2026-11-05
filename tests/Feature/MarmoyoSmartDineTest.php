<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarmoyoSmartDineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }
    public function test_customer_can_scan_table_qr_and_view_menu(): void
    {
        $table = RestaurantTable::where('table_number', '08')->first();
        $this->assertNotNull($table);

        $response = $this->get('/scan?table=' . $table->table_number . '&token=' . $table->qr_token);
        $response->assertRedirect('/menu');
        $response->assertSessionHas('table_number', '08');

        $menuResponse = $this->get('/menu');
        $menuResponse->assertStatus(200);
        $menuResponse->assertSee('Marmoyo Smart-Dine');
        $menuResponse->assertSee('Mie Kuah Mala Pedas Nampol');
    }

    public function test_customer_can_add_item_to_cart_and_view_cart(): void
    {
        $table = RestaurantTable::where('table_number', '08')->first();
        $menu = Menu::where('slug', 'mie-kuah-mala-pedas-nampol')->first();

        $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->table_number,
            'table_token' => $table->qr_token,
        ]);

        $addResponse = $this->post('/cart/add', [
            'menu_id' => $menu->id,
            'quantity' => 2,
            'spice_level' => 'Level 3 (Pedas Nampol Kebon)',
            'noodle_type' => 'Mie Keriting Kenyal',
            'special_notes' => 'Kuah agak banyak ya kak',
        ]);

        $addResponse->assertSessionHas('cart');

        $cartResponse = $this->get('/cart');
        $cartResponse->assertStatus(200);
        $cartResponse->assertSee('Mie Kuah Mala Pedas Nampol');
        $cartResponse->assertSee('Rekomendasi Spesial AI');
    }

    public function test_customer_can_checkout_with_qris_and_verify_payment(): void
    {
        $table = RestaurantTable::where('table_number', '08')->first();
        $menu = Menu::where('slug', 'mie-kuah-mala-pedas-nampol')->first();

        $cartItem = [
            'cart_key' => 'test_item_key',
            'menu_id' => $menu->id,
            'name' => $menu->name,
            'image_url' => $menu->image_url,
            'unit_price' => 15000,
            'quantity' => 1,
            'subtotal_price' => 15000,
            'spice_level' => 'Level 2',
        ];

        $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->table_number,
            'cart' => [$cartItem],
        ]);

        $checkoutResponse = $this->post('/checkout', [
            'payment_method' => 'qris',
            'kitchen_notes' => 'Alat makan disiapkan ya',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending_payment', $order->status);

        $checkoutResponse->assertRedirect(route('customer.payment', ['orderNumber' => $order->order_number]));

        // Verify payment
        $verifyResponse = $this->post(route('customer.verify-qris', ['orderNumber' => $order->order_number]));
        $verifyResponse->assertRedirect(route('customer.order-status', ['orderNumber' => $order->order_number]));

        $order->refresh();
        $this->assertEquals('cooking', $order->status);
        $this->assertEquals('paid', $order->payment_status);
    }

    public function test_cashier_can_validate_cash_payment(): void
    {
        $cashier = User::where('role', 'kasir')->first();
        $table = RestaurantTable::where('table_number', '08')->first();
        $menu = Menu::first();

        $cartItem = [
            'cart_key' => 'test_item_cash',
            'menu_id' => $menu->id,
            'name' => $menu->name,
            'unit_price' => 10000,
            'quantity' => 1,
            'subtotal_price' => 10000,
        ];

        $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->table_number,
            'cart' => [$cartItem],
        ]);

        $this->post('/checkout', ['payment_method' => 'cash']);

        $order = Order::latest()->first();
        $payment = $order->payment;
        $this->assertNotNull($payment);

        $this->actingAs($cashier);

        // Check cashier live-orders view
        $liveOrdersResponse = $this->get('/cashier/live-orders');
        $liveOrdersResponse->assertStatus(200);
        $liveOrdersResponse->assertSee('Marmoyo Smart-Dine POS');
        $liveOrdersResponse->assertSee('Validasi Pembayaran Tunai');

        $validateResponse = $this->post('/cashier/validate-cash', [
            'payment_code' => $payment->payment_code,
            'cash_received' => 50000,
        ]);

        $validateResponse->assertRedirect('/cashier/live-orders');

        $order->refresh();
        $payment->refresh();

        $this->assertEquals('cooking', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('success', $payment->status);
        $expectedChange = 50000 - (float) $menu->base_price;
        $this->assertEquals($expectedChange, (float) $payment->cash_change);
    }

    public function test_admin_dashboard_is_accessible_by_owner(): void
    {
        $owner = User::where('role', 'admin')->first();
        $this->actingAs($owner);

        $response = $this->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Executive Overview');
        $response->assertSee('Top Menu Terlaris');
    }

    public function test_admin_can_view_edit_form_and_update_menu(): void
    {
        $owner = User::where('role', 'admin')->first();
        $this->actingAs($owner);

        $menu = Menu::first();

        // View edit form
        $editResponse = $this->get("/admin/menus/{$menu->id}/edit");
        $editResponse->assertStatus(200);
        $editResponse->assertSee($menu->name);
        $editResponse->assertSee('Edit Data Menu');

        // Update menu
        $updateResponse = $this->put("/admin/menus/{$menu->id}", [
            'category_id' => $menu->category_id,
            'name' => $menu->name . ' (Updated)',
            'base_price' => 18000,
            'description' => 'Deskripsi baru yang telah diperbarui admin.',
            'is_spicy' => true,
            'default_spiciness_level' => 3,
            'is_bestseller' => true,
            'is_available' => true,
        ]);

        $updateResponse->assertRedirect(route('admin.menus.index'));
        $this->assertDatabaseHas('menus', [
            'id' => $menu->id,
            'name' => $menu->name . ' (Updated)',
            'base_price' => 18000,
        ]);
    }

    public function test_cashier_can_access_live_orders_dashboard(): void
    {
        $cashier = User::where('role', 'kasir')->first();
        $this->actingAs($cashier);

        $response = $this->get('/cashier/live-orders');
        $response->assertStatus(200);
        $response->assertSee('Live Order POS');
        $response->assertSee('Validasi Tunai');
        $response->assertSee('pendingOrdersMap');
    }

    public function test_cashier_can_toggle_stock_but_cannot_add_or_edit_menu(): void
    {
        $cashier = User::where('role', 'kasir')->first();
        $this->actingAs($cashier);

        $menu = Menu::first();
        $initialStatus = $menu->is_available;

        // 1. Cashier can access cashier menu stock page
        $stockPageResponse = $this->get('/cashier/menus');
        $stockPageResponse->assertStatus(200);
        $stockPageResponse->assertSee('Kontrol Ketersediaan Stok Menu');
        $stockPageResponse->assertDontSee('+ Tambah Menu Baru');
        $stockPageResponse->assertDontSee('Hapus');

        // 2. Cashier can toggle menu stock
        $toggleResponse = $this->post("/cashier/menus/{$menu->id}/toggle-stock");
        $toggleResponse->assertRedirect();
        $this->assertEquals(!$initialStatus, $menu->fresh()->is_available);

        // 3. Cashier CANNOT access admin menu creation
        $adminCreateResponse = $this->get('/admin/menus/create');
        $adminCreateResponse->assertStatus(403);

        // 4. Cashier CANNOT access admin menu edit
        $adminEditResponse = $this->get("/admin/menus/{$menu->id}/edit");
        $adminEditResponse->assertStatus(403);
    }

    public function test_cashier_can_input_take_away_order_and_process_payment(): void
    {
        $cashier = User::where('role', 'kasir')->first();
        $this->actingAs($cashier);

        // 1. Can view take away POS screen
        $viewResponse = $this->get('/cashier/take-away');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Input Pesanan Take Away');
        $viewResponse->assertSee('Keranjang Take Away');

        // 2. Submit take away order
        $menu1 = Menu::where('slug', 'mie-kuah-mala-pedas-nampol')->first();
        $menu2 = Menu::where('slug', 'es-teh-manis-jumbo')->first();

        $storeResponse = $this->post('/cashier/take-away', [
            'customer_name' => 'Bpk. Hendra Take Away',
            'packaging_fee' => 2000,
            'payment_method' => 'cash',
            'cash_received' => 60000,
            'kitchen_notes' => 'Bumbu kuah dipisah di cup kecil ya',
            'items' => [
                [
                    'menu_id' => $menu1->id,
                    'quantity' => 2,
                    'spice_level' => 'Level 3 (Pedas Nampol)',
                    'noodle_type' => 'Mie Keriting Kenyal',
                    'special_notes' => 'Kuah jangan terlalu asin',
                ],
                [
                    'menu_id' => $menu2->id,
                    'quantity' => 2,
                    'spice_level' => null,
                    'noodle_type' => null,
                    'special_notes' => 'Es batu dipisah',
                ],
            ],
        ]);

        $storeResponse->assertRedirect(route('cashier.live-orders'));

        // 3. Database verification
        $order = Order::where('customer_name', 'Bpk. Hendra Take Away')->first();
        $this->assertNotNull($order);
        $this->assertEquals('take_away', $order->order_type);
        $this->assertNull($order->table_id);
        $this->assertEquals(2000, (float) $order->packaging_fee);
        $this->assertEquals('cooking', $order->status);
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('cash', $order->payment_method);
        $this->assertStringStartsWith('MRM-TA', $order->order_number);

        // Check items
        $this->assertCount(2, $order->items);

        // Check payment record
        $this->assertNotNull($order->payment);
        $this->assertEquals('success', $order->payment->status);
        $this->assertEquals(60000, (float) $order->payment->cash_received);
        $expectedFinal = ($menu1->base_price * 2) + ($menu2->base_price * 2) + 2000;
        $this->assertEquals($expectedFinal, (float) $order->final_amount);
        $this->assertEquals(60000 - $expectedFinal, (float) $order->payment->cash_change);

        // 4. Check kitchen ticket print
        $printResponse = $this->get('/cashier/print/kitchen/' . $order->id);
        $printResponse->assertStatus(200);
        $printResponse->assertSee('TIKET BUNGKUS / TAKE AWAY');
        $printResponse->assertSee('Bpk. Hendra Take Away');

        // 5. Cashier can mark order completed directly with 1 click
        $completeResponse = $this->post("/cashier/order/{$order->id}/status", ['status' => 'completed']);
        $completeResponse->assertRedirect();
        $this->assertEquals('completed', $order->fresh()->status);
    }

    public function test_admin_can_view_and_manage_users(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // 1. Admin can view users list
        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Kelola Staf &amp; Akun Pengguna', false);
        $response->assertSee($admin->name);
        $response->assertSee('Najwa (Kasir)');

        // 2. Admin can create new cashier staff
        $storeResponse = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Budi Santoso',
            'email' => 'budi@marmoyo.com',
            'role' => 'kasir',
            'password' => 'password123',
        ]);
        $storeResponse->assertRedirect('/admin/users');
        $storeResponse->assertSessionHas('success');

        $newUser = User::where('email', 'budi@marmoyo.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('Budi Santoso', $newUser->name);
        $this->assertEquals('kasir', $newUser->role);

        // 3. Admin can update staff profile and reset password
        $updateResponse = $this->actingAs($admin)->put('/admin/users/' . $newUser->id, [
            'name' => 'Budi S. Kasir Shift Malam',
            'email' => 'budi@marmoyo.com',
            'role' => 'kasir',
            'password' => 'newpassword123',
        ]);
        $updateResponse->assertRedirect('/admin/users');
        $this->assertEquals('Budi S. Kasir Shift Malam', $newUser->fresh()->name);

        // 4. Admin cannot delete their own active account
        $deleteSelfResponse = $this->actingAs($admin)->delete('/admin/users/' . $admin->id);
        $deleteSelfResponse->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        // 5. Admin can delete the cashier staff account
        $deleteResponse = $this->actingAs($admin)->delete('/admin/users/' . $newUser->id);
        $deleteResponse->assertRedirect('/admin/users');
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('users', ['id' => $newUser->id]);
    }

    public function test_cashier_cannot_access_user_management(): void
    {
        $cashier = User::where('role', 'kasir')->first();
        $this->assertNotNull($cashier);

        $response = $this->actingAs($cashier)->get('/admin/users');
        $response->assertStatus(403);
    }

    public function test_admin_can_view_and_update_store_settings(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // 1. Admin can view store settings screen
        $response = $this->actingAs($admin)->get('/admin/settings');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Kedai &amp; Format Struk', false);
        $response->assertSee('Marmoyo Smart-Dine');

        // 2. Admin can update store identity & pricing
        $updateResponse = $this->actingAs($admin)->put('/admin/settings', [
            'store_name' => 'Kedai Mie Marmoyo Surabaya Pusat',
            'store_tagline' => 'Rajanya Pedas Surabaya',
            'store_address' => 'Jl. Marmoyo No. 88, Wonokromo, Surabaya',
            'store_phone' => '0812-9999-8888',
            'receipt_footer' => "Matur nuwun sanget!\nIG: @miemarmoyo",
            'packaging_fee' => 2500,
            'tax_pb1_percent' => 10,
            'promo_active' => true,
            'promo_discount' => 3000,
        ]);

        $updateResponse->assertRedirect('/admin/settings');
        $updateResponse->assertSessionHas('success');

        $settings = \App\Models\StoreSetting::current();
        $this->assertEquals('Kedai Mie Marmoyo Surabaya Pusat', $settings->store_name);
        $this->assertEquals(2500, (float) $settings->packaging_fee);

        // 3. Cashier cannot access admin settings
        $cashier = User::where('role', 'kasir')->first();
        $this->actingAs($cashier)->get('/admin/settings')->assertStatus(403);
    }

    public function test_customer_can_update_cart_and_switch_payment_and_download_receipt(): void
    {
        $table = RestaurantTable::where('table_number', '08')->first();
        $menu = Menu::first();

        $cartKey = 'test_key_item_1';
        $cartItem = [
            'cart_key' => $cartKey,
            'menu_id' => $menu->id,
            'name' => $menu->name,
            'unit_price' => 15000,
            'quantity' => 2,
            'subtotal_price' => 30000,
        ];

        // 1. Update Cart: Increment
        $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->table_number,
            'cart' => [$cartKey => $cartItem],
        ])->post('/cart/update', [
            'cart_key' => $cartKey,
            'action' => 'increment',
        ])->assertRedirect(route('customer.cart'));

        // 2. Checkout
        $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->table_number,
            'cart' => [$cartKey => $cartItem],
        ])->post('/checkout', [
            'payment_method' => 'qris',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);

        // 3. Switch Payment Method to Cash
        $switchResponse = $this->post(route('customer.payment.switch', ['orderNumber' => $order->order_number]), [
            'method' => 'cash',
        ]);
        $switchResponse->assertRedirect(route('customer.payment', ['orderNumber' => $order->order_number]));
        $order->refresh();
        $this->assertEquals('cash', $order->payment_method);

        // 4. Download / View Receipt
        $receiptResponse = $this->get(route('customer.download-receipt', ['orderNumber' => $order->order_number]));
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee($order->order_number);
    }

    public function test_cashier_order_status_print_and_settlement(): void
    {
        $cashier = User::where('role', 'kasir')->first();
        $table = RestaurantTable::first();
        $menu = Menu::first();

        $order = Order::create([
            'order_number' => 'MRM-0899',
            'table_id' => $table->id,
            'customer_name' => 'Pelanggan Meja 08',
            'subtotal' => 25000,
            'final_amount' => 25000,
            'status' => 'pending_payment',
            'payment_status' => 'unpaid',
            'payment_method' => 'cash',
        ]);

        // 1. Update Order Status
        $statusResponse = $this->actingAs($cashier)->post(route('cashier.order.status', ['id' => $order->id]), [
            'status' => 'ready',
        ]);
        $statusResponse->assertRedirect(route('cashier.live-orders'));
        $order->refresh();
        $this->assertEquals('ready', $order->status);

        // 2. Print Thermal Customer Receipt
        $receiptPrint = $this->actingAs($cashier)->get(route('cashier.print.receipt', ['id' => $order->id]));
        $receiptPrint->assertStatus(200);
        $receiptPrint->assertSee('MARMOYO SMART-DINE');

        // 3. Print Kitchen Ticket
        $kitchenPrint = $this->actingAs($cashier)->get(route('cashier.print.kitchen', ['id' => $order->id]));
        $kitchenPrint->assertStatus(200);
        $kitchenPrint->assertSee('TIKET PESANAN');

        // 4. View Settlement & Close Shift
        $settlementView = $this->actingAs($cashier)->get(route('cashier.settlement'));
        $settlementView->assertStatus(200);

        $closeShiftResponse = $this->actingAs($cashier)->post(route('cashier.settlement.close'), [
            'starting_cash' => 100000,
            'actual_physical_cash' => 125000,
            'notes' => 'Shift kasir malam ditutup',
        ]);
        $closeShiftResponse->assertRedirect(route('cashier.settlement'));
        $closeShiftResponse->assertSessionHas('success');
    }

    public function test_admin_tables_menus_reconciliation_and_auth(): void
    {
        $admin = User::where('role', 'admin')->first();

        // 1. Root redirect
        $this->get('/')->assertRedirect(route('login'));

        // 2. Health check
        $this->get('/up')->assertStatus(200);

        // 3. Admin Tables: Create & Print Sheet & Delete
        $tablesView = $this->actingAs($admin)->get(route('admin.tables.index'));
        $tablesView->assertStatus(200);

        $newTableResponse = $this->actingAs($admin)->post(route('admin.tables.store'), [
            'table_number' => '99',
        ]);
        $newTableResponse->assertRedirect(route('admin.tables.index'));
        $newTable = RestaurantTable::where('table_number', '99')->first();
        $this->assertNotNull($newTable);

        $printSheet = $this->actingAs($admin)->get(route('admin.tables.print-sheet'));
        $printSheet->assertStatus(200);
        $printSheet->assertSee('MARMOYO SMART-DINE');

        $deleteTableResponse = $this->actingAs($admin)->delete(route('admin.tables.destroy', ['id' => $newTable->id]));
        $deleteTableResponse->assertRedirect(route('admin.tables.index'));
        $this->assertDatabaseMissing('restaurant_tables', ['table_number' => '99']);

        // 4. Admin Menu Store & Delete
        $category = \App\Models\Category::first();
        $createMenuResponse = $this->actingAs($admin)->post(route('admin.menus.store'), [
            'name' => 'Mie Spesial Test Unit',
            'category_id' => $category->id,
            'base_price' => 18000,
            'description' => 'Menu baru untuk pengujian otomatis',
            'is_available' => 1,
        ]);
        $createMenuResponse->assertRedirect(route('admin.menus.index'));
        $createdMenu = Menu::where('name', 'Mie Spesial Test Unit')->first();
        $this->assertNotNull($createdMenu);

        $deleteMenuResponse = $this->actingAs($admin)->delete(route('admin.menus.destroy', ['id' => $createdMenu->id]));
        $deleteMenuResponse->assertRedirect(route('admin.menus.index'));
        $this->assertDatabaseMissing('menus', ['id' => $createdMenu->id]);

        // 5. Admin Reconciliation Report View
        $reconView = $this->actingAs($admin)->get(route('admin.reconciliation.index'));
        $reconView->assertStatus(200);

        // 6. Auth Logout
        $logoutResponse = $this->actingAs($admin)->post(route('logout'));
        $logoutResponse->assertRedirect(route('login'));
        $this->assertGuest();
    }

    /**
     * Test concurrent orders from multiple tables simultaneously.
     */
    public function test_concurrent_orders_from_multiple_different_tables(): void
    {
        $tables = RestaurantTable::take(4)->get();
        $menu = Menu::where('is_available', true)->first();
        $this->assertCount(4, $tables);
        $this->assertNotNull($menu);

        $createdOrderNumbers = [];

        // Simulate 4 tables ordering concurrently
        foreach ($tables as $index => $table) {
            // Each table session is independent
            $cartKey = 'menu_' . $menu->id . '_idx_' . $index;
            $cartData = [
                $cartKey => [
                    'cart_key' => $cartKey,
                    'menu_id' => $menu->id,
                    'name' => $menu->name,
                    'image_url' => $menu->image_url,
                    'unit_price' => (float) $menu->base_price,
                    'quantity' => $index + 1,
                    'subtotal_price' => (float) $menu->base_price * ($index + 1),
                    'spice_level' => 'Level 2',
                    'noodle_type' => 'Mie Keriting',
                    'toppings' => [],
                    'special_notes' => 'Catatan Meja ' . $table->table_number,
                ]
            ];

            $response = $this->withSession([
                'table_id' => $table->id,
                'table_number' => $table->table_number,
                'table_token' => $table->qr_token,
                'cart' => $cartData,
            ])->post(route('customer.checkout'), [
                'payment_method' => $index % 2 === 0 ? 'qris' : 'cash',
                'kitchen_notes' => 'Pesanan serentak meja ' . $table->table_number,
            ]);

            $response->assertStatus(302);
            $this->assertTrue(str_contains($response->headers->get('Location'), '/payment/MRM-'));

            // Extract order number from redirect location
            preg_match('/\/payment\/(MRM-[^\/]+)/', $response->headers->get('Location'), $matches);
            $this->assertNotEmpty($matches[1]);
            $orderNumber = $matches[1];

            $this->assertNotContains($orderNumber, $createdOrderNumbers, "Order number {$orderNumber} must be completely unique");
            $createdOrderNumbers[] = $orderNumber;

            // Assert database integrity
            $order = Order::with('payment', 'items')->where('order_number', $orderNumber)->first();
            $this->assertNotNull($order);
            $this->assertEquals($table->id, $order->table_id);
            $this->assertEquals($index + 1, $order->items->first()->quantity);
            $this->assertNotNull($order->payment);
        }

        // Verify all 4 distinct orders were created without any collision
        $this->assertCount(4, array_unique($createdOrderNumbers));

        // Verify cashier can see all concurrent orders in live-orders dashboard
        $cashier = User::where('role', 'kasir')->first();
        $liveOrders = $this->actingAs($cashier)->get(route('cashier.live-orders'));
        $liveOrders->assertStatus(200);
        foreach ($createdOrderNumbers as $ordNumber) {
            $liveOrders->assertSee($ordNumber);
        }
    }

    /**
     * Test single table QR code can be used multiple times for different orders (e.g. re-orders, add-ons, or subsequent customers).
     */
    public function test_single_table_qr_code_can_be_used_multiple_times_for_different_orders(): void
    {
        $table = RestaurantTable::where('table_number', '01')->first() ?? RestaurantTable::first();
        $this->assertNotNull($table);
        $menus = Menu::where('is_available', true)->take(3)->get();
        $this->assertGreaterThanOrEqual(2, $menus->count());

        $menu1 = $menus[0];
        $menu2 = $menus[1];

        // --- ORDER 1: Pelanggan scan Meja 01 dan order menu makanan utama ---
        $scanResponse1 = $this->get('/scan?table=' . $table->table_number . '&token=' . $table->qr_token);
        $scanResponse1->assertRedirect('/menu');

        $order1Response = $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->table_number,
            'table_token' => $table->qr_token,
            'cart' => [
                'item_1' => [
                    'cart_key' => 'item_1',
                    'menu_id' => $menu1->id,
                    'name' => $menu1->name,
                    'unit_price' => (float) $menu1->base_price,
                    'quantity' => 2,
                    'subtotal_price' => (float) $menu1->base_price * 2,
                ]
            ],
        ])->post(route('customer.checkout'), [
            'payment_method' => 'qris',
            'kitchen_notes' => 'Pesanan Pertama Meja ' . $table->table_number,
        ]);

        $order1Response->assertStatus(302);
        preg_match('/\/payment\/(MRM-[^\/]+)/', $order1Response->headers->get('Location'), $matches1);
        $orderNumber1 = $matches1[1];
        $this->assertNotEmpty($orderNumber1);

        // Pelanggan bayar Order 1
        $this->post(route('customer.verify-qris', ['orderNumber' => $orderNumber1]));
        $order1 = Order::where('order_number', $orderNumber1)->first();
        $this->assertEquals('paid', $order1->payment_status);

        // --- ORDER 2: Pelanggan di meja yang sama ingin nambah pesanan (misal: minuman tambahan / dessert) ---
        // Pelanggan tetap duduk di Meja 01, buka katalog menu lagi, session meja masih aktif
        $order2Response = $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->table_number,
            'table_token' => $table->qr_token,
            'cart' => [
                'item_2' => [
                    'cart_key' => 'item_2',
                    'menu_id' => $menu2->id,
                    'name' => $menu2->name,
                    'unit_price' => (float) $menu2->base_price,
                    'quantity' => 1,
                    'subtotal_price' => (float) $menu2->base_price,
                ]
            ],
        ])->post(route('customer.checkout'), [
            'payment_method' => 'cash',
            'kitchen_notes' => 'Pesanan Tambahan Kedua di Meja ' . $table->table_number,
        ]);

        $order2Response->assertStatus(302);
        preg_match('/\/payment\/(MRM-[^\/]+)/', $order2Response->headers->get('Location'), $matches2);
        $orderNumber2 = $matches2[1];
        $this->assertNotEmpty($orderNumber2);

        // Assert Order 1 dan Order 2 berbeda total meskipun dari meja yang sama
        $this->assertNotEquals($orderNumber1, $orderNumber2);
        $order2 = Order::where('order_number', $orderNumber2)->first();
        $this->assertEquals($table->id, $order2->table_id);
        $this->assertEquals('pending_payment', $order2->status);
        $this->assertEquals('cash', $order2->payment_method);

        // --- ORDER 3: Pelanggan berikutnya datang di lain waktu, scan QR Code Meja 01 yang sama ---
        $scanResponse2 = $this->get('/scan?table=' . $table->table_number . '&token=' . $table->qr_token);
        $scanResponse2->assertRedirect('/menu');
        $scanResponse2->assertSessionHas('table_number', $table->table_number);

        $order3Response = $this->withSession([
            'table_id' => $table->id,
            'table_number' => $table->table_number,
            'table_token' => $table->qr_token,
            'cart' => [
                'item_3' => [
                    'cart_key' => 'item_3',
                    'menu_id' => $menu1->id,
                    'name' => $menu1->name,
                    'unit_price' => (float) $menu1->base_price,
                    'quantity' => 3,
                    'subtotal_price' => (float) $menu1->base_price * 3,
                ]
            ],
        ])->post(route('customer.checkout'), [
            'payment_method' => 'qris',
            'kitchen_notes' => 'Pesanan Pelanggan Baru Meja ' . $table->table_number,
        ]);

        $order3Response->assertStatus(302);
        preg_match('/\/payment\/(MRM-[^\/]+)/', $order3Response->headers->get('Location'), $matches3);
        $orderNumber3 = $matches3[1];
        $this->assertNotEmpty($orderNumber3);

        // Assert ketiga order di Meja 01 tersimpan berdampingan dan independen
        $this->assertNotEquals($orderNumber1, $orderNumber3);
        $this->assertNotEquals($orderNumber2, $orderNumber3);

        $allOrdersTable1 = Order::where('table_id', $table->id)->pluck('order_number')->toArray();
        $this->assertContains($orderNumber1, $allOrdersTable1);
        $this->assertContains($orderNumber2, $allOrdersTable1);
        $this->assertContains($orderNumber3, $allOrdersTable1);
    }
}
