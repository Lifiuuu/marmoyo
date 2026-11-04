<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Cashier\CashierController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Marmoyo Smart-Dine Web Routes
|--------------------------------------------------------------------------
*/

// Root redirect to login page (staff/admin)
Route::get('/', function () {
    return redirect()->route('login');
});

// ==============================
// 1. Pelanggan (Customer) Self-Ordering Routes
// ==============================
Route::get('/scan', [CustomerOrderController::class, 'scan'])->name('customer.scan');
Route::get('/menu', [CustomerOrderController::class, 'menu'])->name('customer.menu');
Route::get('/cart', [CustomerOrderController::class, 'cart'])->name('customer.cart');
Route::post('/cart/add', [CustomerOrderController::class, 'addToCart'])->name('customer.cart.add');
Route::post('/cart/update', [CustomerOrderController::class, 'updateCart'])->name('customer.cart.update');
Route::post('/checkout', [CustomerOrderController::class, 'checkout'])->name('customer.checkout');
Route::get('/payment/{orderNumber}', [CustomerOrderController::class, 'payment'])->name('customer.payment');
Route::post('/payment/{orderNumber}/verify', [CustomerOrderController::class, 'verifyQris'])->name('customer.verify-qris');
Route::post('/payment/{orderNumber}/switch', [CustomerOrderController::class, 'switchPaymentMethod'])->name('customer.payment.switch');
Route::get('/order/{orderNumber}/status', [CustomerOrderController::class, 'orderStatus'])->name('customer.order-status');
Route::get('/order/{orderNumber}/receipt', [CustomerOrderController::class, 'downloadReceipt'])->name('customer.download-receipt');

// ==============================
// 2. Authentication Routes
// ==============================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==============================
// 3. Kasir / Staf Kedai Routes (POS)
// ==============================
Route::prefix('cashier')->middleware(['auth'])->group(function () {
    Route::get('/live-orders', [CashierController::class, 'liveOrders'])->name('cashier.live-orders');
    Route::get('/take-away', [CashierController::class, 'takeAwayCreate'])->name('cashier.take-away.create');
    Route::post('/take-away', [CashierController::class, 'takeAwayStore'])->name('cashier.take-away.store');
    Route::post('/validate-cash', [CashierController::class, 'validateCash'])->name('cashier.validate-cash');
    Route::post('/order/{id}/status', [CashierController::class, 'updateStatus'])->name('cashier.order.status');
    Route::get('/menus', [CashierController::class, 'menuStock'])->name('cashier.menus.index');
    Route::post('/menus/{id}/toggle-stock', [CashierController::class, 'toggleStock'])->name('cashier.menus.toggle-stock');
    Route::get('/print/kitchen/{id}', [CashierController::class, 'printKitchen'])->name('cashier.print.kitchen');
    Route::get('/print/receipt/{id}', [CashierController::class, 'printReceipt'])->name('cashier.print.receipt');
    Route::get('/settlement', [CashierController::class, 'settlement'])->name('cashier.settlement');
    Route::post('/settlement/close', [CashierController::class, 'closeShift'])->name('cashier.settlement.close');
});

// ==============================
// 4. Admin / Pemilik (Owner) Routes
// ==============================
Route::prefix('admin')->middleware(['auth', RoleMiddleware::class . ':admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/menus', [AdminDashboardController::class, 'menusIndex'])->name('admin.menus.index');
    Route::get('/menus/create', [AdminDashboardController::class, 'menusCreate'])->name('admin.menus.create');
    Route::post('/menus/store', [AdminDashboardController::class, 'menusStore'])->name('admin.menus.store');
    Route::get('/menus/{id}/edit', [AdminDashboardController::class, 'menusEdit'])->name('admin.menus.edit');
    Route::put('/menus/{id}', [AdminDashboardController::class, 'menusUpdate'])->name('admin.menus.update');
    Route::post('/menus/{id}/toggle-stock', [AdminDashboardController::class, 'toggleMenuStock'])->name('admin.menus.toggle-stock');
    Route::delete('/menus/{id}', [AdminDashboardController::class, 'menusDestroy'])->name('admin.menus.destroy');

    Route::get('/tables', [AdminDashboardController::class, 'tablesIndex'])->name('admin.tables.index');
    Route::post('/tables', [AdminDashboardController::class, 'tablesStore'])->name('admin.tables.store');
    Route::delete('/tables/{id}', [AdminDashboardController::class, 'tablesDestroy'])->name('admin.tables.destroy');
    Route::get('/tables/print-sheet', [AdminDashboardController::class, 'tablesPrintAll'])->name('admin.tables.print-sheet');

    Route::get('/reconciliation', [AdminDashboardController::class, 'reconciliationIndex'])->name('admin.reconciliation.index');
    Route::get('/users', [AdminDashboardController::class, 'usersIndex'])->name('admin.users.index');
    Route::post('/users', [AdminDashboardController::class, 'usersStore'])->name('admin.users.store');
    Route::put('/users/{id}', [AdminDashboardController::class, 'usersUpdate'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminDashboardController::class, 'usersDestroy'])->name('admin.users.destroy');
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('admin.settings');
    Route::put('/settings', [AdminDashboardController::class, 'settingsUpdate'])->name('admin.settings.update');
});
