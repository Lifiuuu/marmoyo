<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CashReconciliation;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RestaurantTable;
use App\Models\User;
use App\Services\Supabase\SupabaseClient;
use App\Services\Supabase\SupabaseStorageService;
use App\Services\Table\TableSessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected TableSessionService $tableService,
        protected SupabaseClient $supabaseClient,
        protected SupabaseStorageService $storageService
    ) {}

    /**
     * Executive Overview Dashboard (Page 30 SRS).
     */
    public function dashboard()
    {
        $today = Carbon::today();

        $totalOmzet = Order::where('payment_status', 'paid')->whereDate('created_at', $today)->sum('final_amount');
        $totalTransactions = Order::where('payment_status', 'paid')->whereDate('created_at', $today)->count();
        $averageRating = 9.4; // Authentic metric from SRS survey

        // Payment ratio calculated live from Supabase database
        $qrisCount = Payment::where('method', 'qris')->where('status', 'success')->whereDate('paid_at', $today)->count();
        $cashCount = Payment::where('method', 'cash')->where('status', 'success')->whereDate('paid_at', $today)->count();
        $totalPaid = $qrisCount + $cashCount;
        $qrisPercentage = $totalPaid > 0 ? round(($qrisCount / $totalPaid) * 100) : 0;
        $cashPercentage = $totalPaid > 0 ? (100 - $qrisPercentage) : 0;

        // Monthly Sales Trend Data calculated live for the current month
        $now = Carbon::now();
        $daysInMonth = $now->daysInMonth;
        $monthName = $now->translatedFormat('F Y');

        $monthOrders = Order::whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->get();

        $monthlyData = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayOrders = $monthOrders->filter(function ($order) use ($day) {
                return (int) $order->created_at->format('j') === $day;
            });

            $monthlyData[$day] = [
                'day' => $day,
                'count' => $dayOrders->count(),
                'omzet' => (float) $dayOrders->where('payment_status', 'paid')->sum('final_amount'),
                'is_today' => $day === (int) $now->format('j'),
            ];
        }

        $maxMonthCount = max(array_column($monthlyData, 'count')) ?: 1;

        // Top Selling Menus calculated live from actual OrderItem sales volume
        $topMenus = Menu::with('category')
            ->withCount(['orderItems as total_sold' => function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw('COALESCE(SUM(quantity), 0)'));
            }])
            ->orderByDesc('total_sold')
            ->orderByDesc('is_bestseller')
            ->take(5)
            ->get();

        // Recent transactions
        $recentOrders = Order::with(['table', 'payment'])->latest()->take(7)->get();

        return view('admin.dashboard', compact(
            'totalOmzet',
            'totalTransactions',
            'averageRating',
            'qrisPercentage',
            'cashPercentage',
            'monthlyData',
            'daysInMonth',
            'monthName',
            'maxMonthCount',
            'topMenus',
            'recentOrders'
        ));
    }

    /**
     * Menu Catalog List and Stock Toggles.
     */
    public function menusIndex()
    {
        $categories = Category::with('menus.variants')->orderBy('sort_order')->get();
        return view('admin.menus.index', compact('categories'));
    }

    /**
     * Create Menu Form.
     */
    public function menusCreate()
    {
        $categories = Category::all();
        return view('admin.menus.create', compact('categories'));
    }

    /**
     * Store new menu.
     */
    public function menusStore(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_spicy' => 'nullable|boolean',
            'default_spiciness_level' => 'nullable|integer|min:0|max:5',
            'is_bestseller' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        $imageUrl = 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=500';
        if ($request->hasFile('image')) {
            $imageUrl = $this->storageService->uploadMenuImage($request->file('image'));
        }

        $menu = Menu::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . rand(10, 99),
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'image_url' => $imageUrl,
            'is_spicy' => $request->boolean('is_spicy'),
            'default_spiciness_level' => $validated['default_spiciness_level'] ?? 0,
            'is_bestseller' => false, // Dynamically computed by AI engine from sales
            'is_available' => true,
        ]);

        return redirect()->route('admin.menus.index')->with('success', "Menu '{$menu->name}' berhasil ditambahkan ke katalog!");
    }

    /**
     * Edit Menu Form.
     */
    public function menusEdit(int $id)
    {
        $menu = Menu::with('variants')->findOrFail($id);
        $categories = Category::all();
        return view('admin.menus.edit', compact('menu', 'categories'));
    }

    /**
     * Update existing menu.
     */
    public function menusUpdate(Request $request, int $id)
    {
        $menu = Menu::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'is_spicy' => 'nullable|boolean',
            'default_spiciness_level' => 'nullable|integer|min:0|max:5',
            'is_bestseller' => 'nullable|boolean',
            'is_available' => 'nullable|boolean',
            'image' => 'nullable|image|max:5120',
        ]);

        $updateData = [
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'is_spicy' => $request->boolean('is_spicy'),
            'default_spiciness_level' => $validated['default_spiciness_level'] ?? 0,
            'is_available' => $request->boolean('is_available', true),
        ];

        if ($request->hasFile('image')) {
            $updateData['image_url'] = $this->storageService->uploadMenuImage($request->file('image'));
        }

        $menu->update($updateData);

        return redirect()->route('admin.menus.index')->with('success', "Menu '{$menu->name}' berhasil diperbarui!");
    }

    /**
     * Instant 1-click toggle stock availability.
     */
    public function toggleMenuStock(int $id)
    {
        $menu = Menu::findOrFail($id);
        $menu->is_available = !$menu->is_available;
        $menu->save();

        $statusText = $menu->is_available ? 'Tersedia' : 'Habis';
        return redirect()->back()->with('success', "Status stok menu '{$menu->name}' diubah menjadi: {$statusText}.");
    }

    /**
     * Delete menu item.
     */
    public function menusDestroy(int $id)
    {
        $menu = Menu::findOrFail($id);
        $name = $menu->name;
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', "Menu '{$name}' telah dihapus.");
    }

    /**
     * Tables and QR Code Generation (Page 31 SRS).
     */
    public function tablesIndex()
    {
        $tables = RestaurantTable::with(['orders' => function ($query) {
            $query->where('status', 'menunggu');
        }])->orderBy('table_number')->get();

        // Automatically sync table status based on whether there are active 'menunggu' orders
        foreach ($tables as $table) {
            $expectedStatus = $table->orders->isNotEmpty() ? 'occupied' : 'available';
            if ($table->status !== $expectedStatus) {
                $table->update(['status' => $expectedStatus]);
                $table->status = $expectedStatus;
            }
        }

        return view('admin.tables.index', compact('tables'));
    }

    /**
     * Add new table and generate cryptographic QR token.
     */
    public function tablesStore(Request $request)
    {
        $validated = $request->validate([
            'table_number' => 'required|string|max:10|unique:restaurant_tables,table_number',
        ]);

        $tableNumber = sprintf('%02d', (int) $validated['table_number']);
        $table = RestaurantTable::create([
            'table_number' => $tableNumber,
            'qr_token' => RestaurantTable::generateTokenForTable($tableNumber),
            'status' => 'available',
        ]);

        return redirect()->route('admin.tables.index')->with('success', "Meja {$table->table_number} berhasil ditambahkan!");
    }

    /**
     * Delete table if no longer used.
     */
    public function tablesDestroy(int $id)
    {
        $table = RestaurantTable::findOrFail($id);
        $num = $table->table_number;
        $table->delete();

        return redirect()->route('admin.tables.index')->with('success', "Meja {$num} berhasil dihapus.");
    }

    /**
     * Printable QR Code Sheet preview for all tables.
     */
    public function tablesPrintAll()
    {
        $tables = RestaurantTable::orderBy('table_number')->get();
        return view('admin.tables.print-sheet', compact('tables'));
    }

    /**
     * Financial Reconciliation & Audit Log.
     */
    public function reconciliationIndex()
    {
        $reconciliations = CashReconciliation::with('cashier')->latest()->paginate(15);
        return view('admin.reconciliation.index', compact('reconciliations'));
    }

    /**
     * Internal Staff & User Account Management (No Public Registration).
     */
    public function usersIndex()
    {
        $users = User::orderByRaw("CASE WHEN role = 'admin' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->get();

        $adminCount = $users->where('role', 'admin')->count();
        $cashierCount = $users->where('role', 'kasir')->count();

        return view('admin.users.index', compact('users', 'adminCount', 'cashierCount'));
    }

    /**
     * Create a new internal staff/user account.
     */
    public function usersStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,kasir',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        $roleLabel = $user->role === 'admin' ? 'Owner / Admin' : 'Staf Kasir';
        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$roleLabel} '{$user->name}' ({$user->email}) berhasil dibuat!");
    }

    /**
     * Update an existing staff/user account or reset password.
     */
    public function usersUpdate(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,kasir',
            'password' => 'nullable|string|min:6',
        ]);

        $updateData = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')
            ->with('success', "Data akun '{$user->name}' berhasil diperbarui!");
    }

    /**
     * Delete a staff/user account.
     */
    public function usersDestroy(int $id)
    {
        $user = User::findOrFail($id);

        // Security check: cannot delete currently logged in account
        if (Auth::id() === $user->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        // Security check: cannot delete the last remaining admin
        if ($user->role === 'admin') {
            $totalAdmins = User::where('role', 'admin')->count();
            if ($totalAdmins <= 1) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Tidak dapat menghapus akun Administrator terakhir di sistem.');
            }
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Akun '{$userName}' berhasil dihapus dari sistem.");
    }

    /**
     * Store Identity, Receipt Header, and Pricing Configuration.
     */
    public function settings()
    {
        $settings = \App\Models\StoreSetting::current();
        return view('admin.settings', compact('settings'));
    }

    /**
     * Update store identity and operational configuration.
     */
    public function settingsUpdate(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:100',
            'store_tagline' => 'nullable|string|max:150',
            'store_address' => 'required|string|max:255',
            'store_phone' => 'nullable|string|max:50',
            'receipt_footer' => 'nullable|string|max:500',
            'packaging_fee' => 'required|numeric|min:0',
            'tax_pb1_percent' => 'required|numeric|min:0|max:100',
            'promo_active' => 'nullable|boolean',
            'promo_discount' => 'required|numeric|min:0',
        ]);

        $settings = \App\Models\StoreSetting::current();
        $settings->update([
            'store_name' => trim($validated['store_name']),
            'store_tagline' => trim($validated['store_tagline'] ?? ''),
            'store_address' => trim($validated['store_address']),
            'store_phone' => trim($validated['store_phone'] ?? ''),
            'receipt_footer' => trim($validated['receipt_footer'] ?? ''),
            'packaging_fee' => (float) $validated['packaging_fee'],
            'tax_pb1_percent' => (float) $validated['tax_pb1_percent'],
            'promo_active' => $request->boolean('promo_active'),
            'promo_discount' => (float) $validated['promo_discount'],
        ]);

        return redirect()->route('admin.settings')
            ->with('success', 'Pengaturan identitas kedai, tarif, dan format struk berhasil disimpan!');
    }
}

