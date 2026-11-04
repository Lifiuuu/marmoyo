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
        $menuResponse->assertSee('Kedai Mie Marmoyo');
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
        $liveOrdersResponse->assertSee('Marmoyo POS Kasir');
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
        $response->assertSee('Kedai Mie Marmoyo');

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
}
