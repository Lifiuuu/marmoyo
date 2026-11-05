@extends('layouts.cashier')

@section('title', 'Input Pesanan Take Away (Bungkus) - Marmoyo Smart-Dine')

@section('content')
<div class="space-y-4 sm:space-y-6 pb-20 lg:pb-16">
    <!-- Top Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 sm:pb-4 border-b border-stone-800">
        <div>
            <h2 class="text-sm sm:text-base font-extrabold text-white flex items-center space-x-2">
                <span>Input Pesanan Take Away</span>
                <span class="text-[10px] sm:text-[11px] bg-amber-950 text-amber-300 border border-amber-800 px-2 py-0.5 rounded-md font-bold">Bungkus</span>
            </h2>
            <p class="text-[11px] sm:text-xs text-stone-400 mt-0.5">Pilih menu, input nama pemesan, dan proses pembayaran langsung di kasir.</p>
        </div>
        <div class="shrink-0">
            <a href="{{ route('cashier.live-orders') }}" class="inline-flex items-center space-x-1 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-xl border border-stone-700 hover:bg-stone-800 text-stone-300 text-xs font-bold transition">
                <span>&larr;</span>
                <span>Kembali ke Live Orders</span>
            </a>
        </div>
    </div>

    <!-- Main Two-Column POS Layout (Responsive Stack on Mobile/Tablet, Side-by-Side on Desktop) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-6 items-start">
        <!-- Left: Menu Selector & Categories (Cols 7 on Desktop, Full Width on Mobile/Tab) -->
        <div class="lg:col-span-7 space-y-3.5 sm:space-y-4">
            <!-- Filter Categories & Search -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                <div class="relative flex-1">
                    <input type="text" id="menu-search" oninput="filterMenuCards()" placeholder="Cari nama menu..." class="w-full bg-stone-900 border border-stone-800 rounded-xl px-3.5 py-2 sm:py-2.5 text-xs text-stone-200 placeholder-stone-500 focus:outline-none focus:border-amber-500">
                </div>
                <div class="flex items-center space-x-1.5 overflow-x-auto pb-1 scrollbar-none" id="category-pills">
                    <button type="button" onclick="selectCategory('all')" class="cat-pill shrink-0 px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-bold transition bg-amber-500 text-stone-950 shadow" data-cat="all">
                        Semua Menu
                    </button>
                    @foreach($categories as $cat)
                        <button type="button" onclick="selectCategory('{{ $cat->id }}')" class="cat-pill shrink-0 px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition bg-stone-900 border border-stone-800 text-stone-400 hover:text-stone-200" data-cat="{{ $cat->id }}">
                            {{ $cat->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Menus Grid (2 cols mobile, 3 cols tablet & desktop) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 sm:gap-3.5" id="menu-items-grid">
                @foreach($categories as $category)
                    @foreach($category->menus as $menu)
                        <div class="menu-card bg-stone-950 border border-stone-800/80 hover:border-amber-500/70 rounded-2xl p-2.5 sm:p-3 flex flex-col justify-between transition group {{ !$menu->is_available ? 'opacity-40 pointer-events-none' : 'cursor-pointer hover:shadow-lg hover:shadow-amber-950/20 active:scale-[0.98]' }}"
                             data-cat-id="{{ $category->id }}"
                             data-name="{{ strtolower($menu->name) }}"
                             onclick="promptAddMenu({{ $menu->id }}, '{{ addslashes($menu->name) }}', {{ $menu->base_price }}, {{ $menu->is_spicy ? 'true' : 'false' }}, {{ $menu->default_spiciness_level ?? 2 }})">
                            <div>
                                <div class="w-full h-24 sm:h-28 rounded-xl bg-stone-900 border border-stone-800/60 overflow-hidden relative mb-2 flex items-center justify-center">
                                    @if($menu->image_url)
                                        <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    @else
                                        <span class="text-xs text-stone-600 font-semibold tracking-wide">Marmoyo</span>
                                    @endif

                                    @if(!$menu->is_available)
                                        <div class="absolute inset-0 bg-stone-950/80 backdrop-blur-xs flex items-center justify-center">
                                            <span class="text-[9px] sm:text-[10px] font-black uppercase text-rose-400 bg-rose-950/80 px-2 py-0.5 rounded border border-rose-800">Habis</span>
                                        </div>
                                    @elseif($menu->is_spicy)
                                        <span class="absolute top-1.5 right-1.5 text-[9px] sm:text-[10px] bg-red-600 text-white font-extrabold px-1.5 py-0.5 rounded shadow">
                                            Pedas
                                        </span>
                                    @endif
                                </div>
                                <h4 class="text-[11px] sm:text-xs font-bold text-white line-clamp-2 leading-tight group-hover:text-amber-400 transition">{{ $menu->name }}</h4>
                            </div>
                            <div class="pt-2 mt-2 border-t border-stone-900 flex items-center justify-between">
                                <span class="text-[11px] sm:text-xs font-black text-amber-400">Rp{{ number_format($menu->base_price, 0, ',', '.') }}</span>
                                <span class="w-6 h-6 rounded-lg bg-stone-900 group-hover:bg-amber-500 group-hover:text-stone-950 text-stone-400 flex items-center justify-center text-xs font-black transition">
                                    +
                                </span>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>

        <!-- Right: Take Away Bill & Checkout Form (Cols 5 on Desktop, Pinned/Stacked on Tab & Mobile) -->
        <div id="take-away-bill-section" class="lg:col-span-5 bg-stone-950 border border-stone-800 rounded-3xl p-4 sm:p-5 shadow-2xl space-y-4 lg:sticky lg:top-20">
            <form id="take-away-form" action="{{ route('cashier.take-away.store') }}" method="POST" class="space-y-4" onsubmit="return validateBeforeSubmit(event)">
                @csrf

                <!-- Bill Header & Customer Details -->
                <div class="space-y-3 pb-3 border-b border-stone-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xs font-black uppercase tracking-wider text-stone-300 flex items-center space-x-1.5">
                            <span>Keranjang Take Away</span>
                            <span id="cart-count-badge" class="bg-amber-500/20 text-amber-400 text-[10px] font-bold px-2 py-0.5 rounded-full border border-amber-500/40">0 Item</span>
                        </h3>
                        <button type="button" onclick="clearCart()" class="text-[11px] text-stone-500 hover:text-rose-400 font-semibold transition">
                            Kosongkan
                        </button>
                    </div>

                    <!-- Customer Name (Wajib) -->
                    <div>
                        <label class="block text-[11px] font-bold text-stone-400 mb-1">
                            Nama Pelanggan / Pemesan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="customer_name" id="input-customer-name" required placeholder="Contoh: Bpk. Budi / Kak Sarah" class="w-full bg-stone-900 border border-stone-800 focus:border-amber-500 rounded-xl px-3 py-2 sm:py-2.5 text-xs text-stone-100 placeholder-stone-600 focus:outline-none">
                    </div>
                </div>

                <!-- Cart Items List (Scrollable if many items) -->
                <div class="space-y-2 max-h-56 overflow-y-auto pr-1 divide-y divide-stone-900" id="cart-items-container">
                    <!-- Empty State -->
                    <div id="cart-empty-state" class="flex flex-col items-center justify-center text-center py-6 text-stone-600">
                        <span class="text-2xl mb-1">🛒</span>
                        <p class="text-xs font-semibold">Keranjang masih kosong</p>
                        <p class="text-[10px] text-stone-600 mt-0.5">Pilih menu di atas/samping untuk menambahkan</p>
                    </div>
                </div>

                <!-- Fees, Packaging, & Totals Breakdown -->
                <div class="pt-3 border-t border-stone-800 space-y-3">
                    <!-- Packaging Fee Option -->
                    <div class="bg-stone-900/80 border border-stone-800/80 rounded-2xl p-2.5 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" id="check-packaging-fee" checked onchange="togglePackagingFee()" class="w-4 h-4 rounded text-amber-500 focus:ring-0 bg-stone-950 border-stone-700">
                            <div>
                                <label for="check-packaging-fee" class="font-bold text-stone-200 cursor-pointer block text-xs">Biaya Box &amp; Tas Bungkus</label>
                                <span class="text-[10px] text-stone-500 block">Kemasan Food Grade Take Away</span>
                            </div>
                        </div>
                        <span id="packaging-fee-label" class="font-black text-amber-400">Rp2.000</span>
                        <input type="hidden" name="packaging_fee" id="input-packaging-fee" value="2000">
                    </div>

                    <!-- Financial Summary Display -->
                    <div class="space-y-1.5 text-xs text-stone-400">
                        <div class="flex justify-between">
                            <span>Subtotal Menu</span>
                            <span id="display-subtotal" class="font-semibold text-stone-200">Rp0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Biaya Kemasan</span>
                            <span id="display-packaging" class="font-semibold text-stone-200">Rp2.000</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pajak Resto PB1 (10%)</span>
                            <span class="font-semibold text-emerald-400 text-[11px]">Sudah Termasuk</span>
                        </div>
                        <div class="flex justify-between items-baseline pt-2 border-t border-stone-900">
                            <span class="text-xs font-black uppercase text-white">Total Bayar</span>
                            <span id="display-final-amount" class="text-lg sm:text-xl font-black text-amber-400">Rp2.000</span>
                        </div>
                    </div>

                    <!-- Pilih Metode Pembayaran -->
                    <div class="pt-2">
                        <label class="block text-[11px] font-bold text-stone-400 mb-1.5">Pilih Metode Pembayaran:</label>
                        <div class="grid grid-cols-2 gap-2 p-1 bg-stone-900 rounded-xl border border-stone-800 text-xs font-bold">
                            <label class="flex items-center justify-center space-x-1.5 py-2 rounded-lg cursor-pointer transition has-checked:bg-amber-500 has-checked:text-stone-950 text-stone-400">
                                <input type="radio" name="payment_method" value="cash" checked onchange="handlePaymentMethodChange()" class="sr-only">
                                <span>Tunai (Cash)</span>
                            </label>
                            <label class="flex items-center justify-center space-x-1.5 py-2 rounded-lg cursor-pointer transition has-checked:bg-sky-600 has-checked:text-white text-stone-400">
                                <input type="radio" name="payment_method" value="qris" onchange="handlePaymentMethodChange()" class="sr-only">
                                <span>QRIS Dinamis</span>
                            </label>
                        </div>
                    </div>

                    <!-- Input Pembayaran Tunai & Kembalian Real-time -->
                    <div id="cash-payment-section" class="space-y-2 bg-stone-900/60 p-3 rounded-2xl border border-stone-800/80">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between text-[11px] gap-1.5">
                            <span class="text-stone-300 font-bold">Uang Cash Diterima:</span>
                            <div class="flex flex-wrap gap-1">
                                <button type="button" onclick="setQuickCash('exact')" class="px-2 py-1 rounded-md bg-stone-800 hover:bg-stone-700 active:bg-stone-600 text-stone-200 font-bold text-[10px]">Uang Pas</button>
                                <button type="button" onclick="setQuickCash(20000)" class="px-2 py-1 rounded-md bg-stone-800 hover:bg-stone-700 active:bg-stone-600 text-stone-200 font-bold text-[10px]">20k</button>
                                <button type="button" onclick="setQuickCash(50000)" class="px-2 py-1 rounded-md bg-stone-800 hover:bg-stone-700 active:bg-stone-600 text-stone-200 font-bold text-[10px]">50k</button>
                                <button type="button" onclick="setQuickCash(100000)" class="px-2 py-1 rounded-md bg-stone-800 hover:bg-stone-700 active:bg-stone-600 text-stone-200 font-bold text-[10px]">100k</button>
                            </div>
                        </div>
                        <input type="number" name="cash_received" id="input-cash-received" oninput="calculateTakeAwayChange()" placeholder="Masukkan nominal uang cash..." class="w-full bg-stone-950 border border-stone-800 focus:border-amber-500 rounded-xl px-3 py-2 text-sm font-bold text-emerald-400 focus:outline-none">
                        <div class="flex justify-between items-center text-xs pt-1.5 border-t border-stone-900">
                            <span class="text-stone-400 font-semibold">Uang Kembalian:</span>
                            <span id="display-cash-change" class="text-sm font-black text-stone-400">Rp0</span>
                        </div>
                    </div>

                    <!-- Catatan & Tombol Buat Pesanan -->
                    <div class="space-y-2 pt-1">
                        <input type="text" name="kitchen_notes" placeholder="Catatan Pesanan (opsional: sendok, plastik, kuah pisah)..." class="w-full bg-stone-900 border border-stone-800 focus:border-amber-500 rounded-xl px-3 py-2 sm:py-2.5 text-xs text-stone-300 placeholder-stone-600 focus:outline-none">
                        
                        <!-- TOMBOL BUAT PESANAN -->
                        <button type="submit" id="btn-submit-order" class="w-full bg-amber-500 hover:bg-amber-400 text-stone-950 font-black py-3 sm:py-3.5 rounded-2xl text-xs uppercase tracking-wider transition shadow-lg shadow-amber-950/40 active:scale-[0.99] flex items-center justify-center">
                            Simpan Pesanan &amp; Cetak
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Floating Action Bar on Mobile/Tablet when items in cart -->
<div id="mobile-cart-floater" onclick="scrollToTakeAwayBill()" class="hidden fixed bottom-14 sm:bottom-16 left-3 right-3 lg:hidden z-30 bg-amber-500 hover:bg-amber-400 text-stone-950 px-4 py-3 rounded-2xl shadow-2xl flex items-center justify-between font-black text-xs active:scale-95 transition cursor-pointer">
    <div class="flex items-center space-x-2">
        <span class="text-sm">🛒</span>
        <span id="floater-count-text">0 Menu Dipilih</span>
    </div>
    <div class="flex items-center space-x-1.5">
        <span id="floater-amount-text" class="text-sm">Rp0</span>
        <span>&rarr; Bayar</span>
    </div>
</div>

<!-- Modal Customize Menu (Pedas, Tipe Mie, Catatan) Responsive for Touch -->
<div id="modal-customize" class="fixed inset-0 z-50 bg-stone-950/80 backdrop-blur-sm hidden flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-stone-900 border border-stone-800 rounded-3xl max-w-sm w-full p-4 sm:p-6 space-y-3.5 sm:space-y-4 shadow-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between border-b border-stone-800 pb-2.5">
            <div>
                <h3 id="modal-menu-title" class="text-xs sm:text-sm font-black text-white">Sesuaikan Menu</h3>
                <p id="modal-menu-price" class="text-xs font-bold text-amber-400 mt-0.5">Rp15.000</p>
            </div>
            <button type="button" onclick="closeCustomizeModal()" class="text-stone-400 hover:text-white p-1 text-lg font-bold">&times;</button>
        </div>

        <div id="modal-spice-container" class="space-y-2">
            <label class="block text-xs font-bold text-stone-300">Pilih Level Pedas:</label>
            <div class="grid grid-cols-3 gap-1.5 sm:gap-2 text-[11px] sm:text-xs">
                <label class="p-2 rounded-xl border border-stone-800 bg-stone-950 text-center cursor-pointer has-checked:border-amber-500 has-checked:bg-amber-500/20 has-checked:text-amber-400 font-bold transition">
                    <input type="radio" name="modal_spice" value="Level 0 (Tidak Pedas)" class="sr-only">
                    Level 0
                </label>
                <label class="p-2 rounded-xl border border-stone-800 bg-stone-950 text-center cursor-pointer has-checked:border-amber-500 has-checked:bg-amber-500/20 has-checked:text-amber-400 font-bold transition">
                    <input type="radio" name="modal_spice" value="Level 1 (Pedas Santai)" class="sr-only">
                    Level 1
                </label>
                <label class="p-2 rounded-xl border border-stone-800 bg-stone-950 text-center cursor-pointer has-checked:border-amber-500 has-checked:bg-amber-500/20 has-checked:text-amber-400 font-bold transition">
                    <input type="radio" name="modal_spice" value="Level 2 (Pedas Sedang)" checked class="sr-only">
                    Level 2
                </label>
                <label class="p-2 rounded-xl border border-stone-800 bg-stone-950 text-center cursor-pointer has-checked:border-amber-500 has-checked:bg-amber-500/20 has-checked:text-amber-400 font-bold transition">
                    <input type="radio" name="modal_spice" value="Level 3 (Pedas Nampol)" class="sr-only">
                    Level 3
                </label>
                <label class="p-2 rounded-xl border border-stone-800 bg-stone-950 text-center cursor-pointer has-checked:border-amber-500 has-checked:bg-amber-500/20 has-checked:text-amber-400 font-bold transition">
                    <input type="radio" name="modal_spice" value="Level 4 (Pedas Mampus)" class="sr-only">
                    Level 4
                </label>
                <label class="p-2 rounded-xl border border-stone-800 bg-stone-950 text-center cursor-pointer has-checked:border-amber-500 has-checked:bg-amber-500/20 has-checked:text-amber-400 font-bold transition">
                    <input type="radio" name="modal_spice" value="Level 5 (Gila Pedas)" class="sr-only">
                    Level 5
                </label>
            </div>
        </div>

        <div id="modal-noodle-container" class="space-y-2">
            <label class="block text-xs font-bold text-stone-300">Tipe Mie:</label>
            <div class="grid grid-cols-2 gap-2 text-[11px] sm:text-xs">
                <label class="p-2 rounded-xl border border-stone-800 bg-stone-950 text-center cursor-pointer has-checked:border-amber-500 has-checked:bg-amber-500/20 has-checked:text-amber-400 font-bold transition">
                    <input type="radio" name="modal_noodle" value="Mie Keriting Kenyal" checked class="sr-only">
                    Keriting Kenyal
                </label>
                <label class="p-2 rounded-xl border border-stone-800 bg-stone-950 text-center cursor-pointer has-checked:border-amber-500 has-checked:bg-amber-500/20 has-checked:text-amber-400 font-bold transition">
                    <input type="radio" name="modal_noodle" value="Mie Lebar Lembut" class="sr-only">
                    Mie Lebar
                </label>
            </div>
        </div>

        <div class="space-y-1.5">
            <label class="block text-xs font-bold text-stone-300">Catatan Khusus Porsi:</label>
            <input type="text" id="modal-notes" placeholder="Misal: jangan pakai daun bawang" class="w-full bg-stone-950 border border-stone-800 rounded-xl px-3 py-2 text-xs text-stone-200 focus:outline-none focus:border-amber-500">
        </div>

        <button type="button" onclick="confirmCustomizeAdd()" class="w-full bg-amber-500 hover:bg-amber-400 text-stone-950 font-black py-3 rounded-xl text-xs uppercase tracking-wider transition active:scale-95 shadow">
            Tambahkan ke Keranjang
        </button>
    </div>
</div>

<script>
    let cart = [];
    let packagingFee = 2000;
    let pendingCustomMenu = null;

    function scrollToTakeAwayBill() {
        const el = document.getElementById('take-away-bill-section');
        if (el) {
            el.scrollIntoView({ behavior: 'smooth' });
        }
    }

    function selectCategory(catId) {
        document.querySelectorAll('.cat-pill').forEach(pill => {
            if (pill.getAttribute('data-cat') === catId) {
                pill.className = 'cat-pill shrink-0 px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-bold transition bg-amber-500 text-stone-950 shadow';
            } else {
                pill.className = 'cat-pill shrink-0 px-3 sm:px-3.5 py-1.5 sm:py-2 rounded-xl text-xs font-semibold transition bg-stone-900 border border-stone-800 text-stone-400 hover:text-stone-200';
            }
        });

        const cards = document.querySelectorAll('.menu-card');
        cards.forEach(card => {
            if (catId === 'all' || card.getAttribute('data-cat-id') === catId) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    function filterMenuCards() {
        const query = document.getElementById('menu-search').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.menu-card');
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            if (name.includes(query)) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });
    }

    function promptAddMenu(id, name, price, isSpicy, defaultSpice) {
        if (isSpicy) {
            pendingCustomMenu = { id, name, price, isSpicy };
            document.getElementById('modal-menu-title').innerText = name;
            document.getElementById('modal-menu-price').innerText = 'Rp ' + price.toLocaleString('id-ID');
            document.getElementById('modal-spice-container').classList.remove('hidden');
            document.getElementById('modal-noodle-container').classList.remove('hidden');
            document.getElementById('modal-notes').value = '';
            document.getElementById('modal-customize').classList.remove('hidden');
        } else {
            addToCart({
                menu_id: id,
                name: name,
                price: price,
                quantity: 1,
                spice_level: null,
                noodle_type: null,
                special_notes: ''
            });
        }
    }

    function closeCustomizeModal() {
        document.getElementById('modal-customize').classList.add('hidden');
        pendingCustomMenu = null;
    }

    function confirmCustomizeAdd() {
        if (!pendingCustomMenu) return;
        const spice = document.querySelector('input[name="modal_spice"]:checked')?.value || 'Level 2 (Pedas Sedang)';
        const noodle = document.querySelector('input[name="modal_noodle"]:checked')?.value || 'Mie Keriting Kenyal';
        const notes = document.getElementById('modal-notes').value.trim();

        addToCart({
            menu_id: pendingCustomMenu.id,
            name: pendingCustomMenu.name,
            price: pendingCustomMenu.price,
            quantity: 1,
            spice_level: spice,
            noodle_type: noodle,
            special_notes: notes
        });

        closeCustomizeModal();
    }

    function addToCart(newItem) {
        const existingIdx = cart.findIndex(it => 
            it.menu_id === newItem.menu_id && 
            it.spice_level === newItem.spice_level && 
            it.noodle_type === newItem.noodle_type && 
            it.special_notes === newItem.special_notes
        );

        if (existingIdx >= 0) {
            cart[existingIdx].quantity += newItem.quantity;
        } else {
            cart.push(newItem);
        }

        renderCart();
    }

    function updateCartQty(idx, delta) {
        if (!cart[idx]) return;
        cart[idx].quantity += delta;
        if (cart[idx].quantity <= 0) {
            cart.splice(idx, 1);
        }
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function togglePackagingFee() {
        const checked = document.getElementById('check-packaging-fee').checked;
        packagingFee = checked ? 2000 : 0;
        document.getElementById('input-packaging-fee').value = packagingFee;
        document.getElementById('packaging-fee-label').innerText = checked ? 'Rp2.000' : 'Rp0 (Gratis)';
        renderCart();
    }

    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const countBadge = document.getElementById('cart-count-badge');
        const floater = document.getElementById('mobile-cart-floater');
        const floaterCount = document.getElementById('floater-count-text');
        const floaterAmount = document.getElementById('floater-amount-text');

        const totalItemsCount = cart.reduce((sum, it) => sum + it.quantity, 0);
        countBadge.innerText = totalItemsCount + ' Item';

        if (cart.length === 0) {
            container.innerHTML = `
                <div id="cart-empty-state" class="flex flex-col items-center justify-center text-center py-6 text-stone-600">
                    <span class="text-2xl mb-1">🛒</span>
                    <p class="text-xs font-semibold">Keranjang masih kosong</p>
                    <p class="text-[10px] text-stone-600 mt-0.5">Sentuh menu di atas/samping untuk menambahkan</p>
                </div>
            `;
            if (floater) floater.classList.add('hidden');
            updateFinancials(0);
            return;
        }

        let html = '';
        let subtotal = 0;

        cart.forEach((item, idx) => {
            const itemTotal = item.price * item.quantity;
            subtotal += itemTotal;

            html += `
                <div class="py-2 flex items-start justify-between text-xs group">
                    <div class="flex-1 pr-2 min-w-0">
                        <p class="font-bold text-stone-100 truncate">${item.name}</p>
                        ${item.spice_level ? `<p class="text-[10px] text-red-400 font-semibold">${item.spice_level}</p>` : ''}
                        ${item.noodle_type ? `<p class="text-[10px] text-stone-400">${item.noodle_type}</p>` : ''}
                        ${item.special_notes ? `<p class="text-[10px] text-amber-300 italic">*${item.special_notes}</p>` : ''}
                        <p class="text-[11px] font-black text-amber-400 mt-0.5">Rp${(item.price).toLocaleString('id-ID')}</p>
                        
                        <!-- Hidden Form Inputs for Laravel POST -->
                        <input type="hidden" name="items[${idx}][menu_id]" value="${item.menu_id}">
                        <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">
                        <input type="hidden" name="items[${idx}][spice_level]" value="${item.spice_level || ''}">
                        <input type="hidden" name="items[${idx}][noodle_type]" value="${item.noodle_type || ''}">
                        <input type="hidden" name="items[${idx}][special_notes]" value="${item.special_notes || ''}">
                    </div>
                    <div class="flex items-center space-x-1.5 sm:space-x-2 shrink-0">
                        <div class="flex items-center space-x-1 bg-stone-900 border border-stone-800 rounded-lg p-0.5">
                            <button type="button" onclick="updateCartQty(${idx}, -1)" class="w-6 h-6 rounded bg-stone-800 hover:bg-stone-700 text-stone-300 font-bold flex items-center justify-center">-</button>
                            <span class="w-5 text-center font-bold text-xs text-white">${item.quantity}</span>
                            <button type="button" onclick="updateCartQty(${idx}, 1)" class="w-6 h-6 rounded bg-stone-800 hover:bg-stone-700 text-stone-300 font-bold flex items-center justify-center">+</button>
                        </div>
                        <span class="font-black text-white text-xs w-16 text-right">Rp${itemTotal.toLocaleString('id-ID')}</span>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        updateFinancials(subtotal);

        // Update mobile floating cart pill
        if (floater) {
            floater.classList.remove('hidden');
            if (floaterCount) floaterCount.innerText = totalItemsCount + ' Menu Dipilih';
            if (floaterAmount) floaterAmount.innerText = 'Rp' + Math.max(0, subtotal + packagingFee).toLocaleString('id-ID');
        }
    }

    function updateFinancials(subtotal) {
        const finalAmount = Math.max(0, subtotal + packagingFee);

        document.getElementById('display-subtotal').innerText = 'Rp' + subtotal.toLocaleString('id-ID');
        document.getElementById('display-packaging').innerText = 'Rp' + packagingFee.toLocaleString('id-ID');
        document.getElementById('display-final-amount').innerText = 'Rp' + finalAmount.toLocaleString('id-ID');

        calculateTakeAwayChange();
    }

    function getFinalAmount() {
        const subtotal = cart.reduce((sum, it) => sum + (it.price * it.quantity), 0);
        return Math.max(0, subtotal + packagingFee);
    }

    function handlePaymentMethodChange() {
        const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
        const cashSection = document.getElementById('cash-payment-section');
        if (method === 'cash') {
            cashSection.classList.remove('hidden');
        } else {
            cashSection.classList.add('hidden');
        }
    }

    function setQuickCash(val) {
        const finalAmount = getFinalAmount();
        const input = document.getElementById('input-cash-received');
        if (val === 'exact') {
            input.value = finalAmount;
        } else {
            input.value = val;
        }
        calculateTakeAwayChange();
    }

    function calculateTakeAwayChange() {
        const finalAmount = getFinalAmount();
        const received = parseFloat(document.getElementById('input-cash-received').value || 0);
        const change = received - finalAmount;
        const display = document.getElementById('display-cash-change');

        if (change >= 0) {
            display.innerText = 'Rp' + change.toLocaleString('id-ID');
            display.className = 'text-sm font-black text-emerald-400';
        } else {
            display.innerText = '-Rp' + Math.abs(change).toLocaleString('id-ID') + ' (Kurang)';
            display.className = 'text-xs font-black text-rose-400';
        }
    }

    function validateBeforeSubmit(e) {
        if (cart.length === 0) {
            alert('Silakan pilih minimal 1 menu untuk pesanan take away.');
            return false;
        }

        const name = document.getElementById('input-customer-name').value.trim();
        if (!name) {
            alert('Nama Pelanggan wajib diisi.');
            document.getElementById('input-customer-name').focus();
            return false;
        }

        const method = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
        if (method === 'cash') {
            const finalAmount = getFinalAmount();
            const received = parseFloat(document.getElementById('input-cash-received').value || 0);
            if (received < finalAmount) {
                alert('Nominal uang cash yang diterima kurang dari total bayar (Rp' + finalAmount.toLocaleString('id-ID') + ').');
                document.getElementById('input-cash-received').focus();
                return false;
            }
        }

        return true;
    }
</script>
@endsection
