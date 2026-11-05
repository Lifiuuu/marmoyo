@extends('layouts.customer')

@section('title', 'Keranjang Pesanan - Marmoyo Smart-Dine')

@section('content')
<div class="space-y-4 pb-6">
    <!-- Top Bar Navigation -->
    <div class="flex items-center justify-between pb-2 border-b border-stone-200">
        <a href="{{ route('customer.menu') }}" class="text-xs font-bold text-stone-600 hover:text-stone-900 flex items-center space-x-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>Tambah Menu Lain</span>
        </a>
        <div class="flex items-center space-x-1 text-xs font-bold text-red-600 bg-red-50 px-2.5 py-1 rounded-full">
            <span>🍽️ Makan di Tempat (Dine-in)</span>
        </div>
    </div>

    @if(empty($cart))
        <div class="py-12 text-center">
            <div class="w-16 h-16 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <h3 class="font-extrabold text-stone-800 text-sm mb-1">Keranjang Masih Kosong</h3>
            <p class="text-xs text-stone-500 mb-5">Pilih menu favorit Anda dari katalog sekarang!</p>
            <a href="{{ route('customer.menu') }}" class="bg-red-600 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow hover:bg-red-700">
                Lihat Menu
            </a>
        </div>
    @else
        <!-- Cart Items List (Page 27 SRS) -->
        <div class="space-y-3">
            <h3 class="text-xs font-extrabold text-stone-900 flex items-center justify-between">
                <span>Daftar Menu ({{ count($cart) }})</span>
                <span class="text-[10px] text-stone-400 font-normal">Self-Order Meja {{ session('table_number', '08') }}</span>
            </h3>

            @foreach($cart as $item)
                <div class="bg-white border border-stone-200 rounded-xl p-3 shadow-xs space-y-2">
                    <div class="flex justify-between items-start">
                        <div class="flex space-x-2.5">
                            <img src="{{ $item['image_url'] ?? 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=150' }}" alt="{{ $item['name'] }}" class="w-12 h-12 rounded-lg object-cover shrink-0">
                            <div>
                                <h4 class="text-xs font-extrabold text-stone-900">{{ $item['name'] }}</h4>
                                <p class="text-xs font-black text-red-600">Rp{{ number_format($item['unit_price'], 0, ',', '.') }}</p>
                                
                                @if(!empty($item['spice_level']))
                                    <span class="inline-block text-[10px] bg-red-50 text-red-700 font-bold px-1.5 py-0.5 rounded mt-0.5">
                                        🌶️ {{ $item['spice_level'] }}
                                    </span>
                                @endif

                                @if(!empty($item['noodle_type']))
                                    <p class="text-[10px] text-stone-500">• {{ $item['noodle_type'] }}</p>
                                @endif

                                @if(!empty($item['toppings']))
                                    <div class="text-[10px] text-stone-500">
                                        @foreach($item['toppings'] as $top)
                                            <span>+ {{ $top['name'] }} </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if(!empty($item['special_notes']))
                                    <p class="text-[10px] text-amber-700 italic bg-amber-50 px-1.5 py-0.5 rounded mt-1">
                                        "{{ $item['special_notes'] }}"
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Quantity Stepper -->
                        <div class="flex items-center space-x-1.5 border border-stone-200 rounded-lg p-1 bg-stone-50">
                            <form action="{{ route('customer.cart.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="cart_key" value="{{ $item['cart_key'] }}">
                                <input type="hidden" name="action" value="decrement">
                                <button type="submit" class="w-5 h-5 rounded bg-white text-stone-700 font-black text-xs flex items-center justify-center hover:bg-stone-100 shadow-xs">-</button>
                            </form>
                            <span class="text-xs font-bold text-stone-900 px-1">{{ $item['quantity'] }}</span>
                            <form action="{{ route('customer.cart.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="cart_key" value="{{ $item['cart_key'] }}">
                                <input type="hidden" name="action" value="increment">
                                <button type="submit" class="w-5 h-5 rounded bg-white text-stone-700 font-black text-xs flex items-center justify-center hover:bg-stone-100 shadow-xs">+</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- AI Smart Pairing Recommendation Card (Page 27 SRS High Fidelity) -->
        @if(!empty($aiRecommendation['active']) && !empty($aiRecommendation['items']))
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-3.5 shadow-sm space-y-2.5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-1.5">
                        <span class="text-amber-600 font-bold text-xs">✨ {{ $aiRecommendation['title'] }}</span>
                    </div>
                    <span class="text-[9px] font-black uppercase bg-amber-200 text-amber-900 px-1.5 py-0.5 rounded">
                        {{ $aiRecommendation['badge'] }}
                    </span>
                </div>
                <p class="text-[11px] text-stone-600 leading-snug">{{ $aiRecommendation['message'] }}</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                    @foreach($aiRecommendation['items'] as $rec)
                        <div class="bg-white border border-amber-100 rounded-xl p-2.5 flex items-center justify-between shadow-xs">
                            <div class="flex items-center space-x-2 min-w-0">
                                <img src="{{ $rec->image_url ?? 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=120' }}" alt="{{ $rec->name }}" class="w-10 h-10 rounded-lg object-cover shrink-0">
                                <div class="min-w-0">
                                    <h5 class="text-[11px] font-extrabold text-stone-900 leading-tight truncate">{{ $rec->name }}</h5>
                                    <span class="text-[10px] text-red-600 font-bold">Rp{{ number_format($rec->base_price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            <form action="{{ route('customer.cart.add') }}" method="POST" class="shrink-0 ml-2">
                                @csrf
                                <input type="hidden" name="menu_id" value="{{ $rec->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-[10px] font-bold px-2.5 py-1.5 rounded-lg transition active:scale-95">
                                    + Tambah
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Form Checkout with Notes and Payment Method Selection -->
        <form action="{{ route('customer.checkout') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Catatan Tambahan Pesanan -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-800">Catatan Tambahan Pesanan</label>
                <textarea name="kitchen_notes" rows="2" placeholder="Contoh: Alat makan disiapkan ya kak, atau sambal minta dipisah" class="w-full text-xs p-2.5 rounded-xl border border-stone-200 focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none resize-none"></textarea>
            </div>

            <!-- Promo Voucher Card (Page 27 SRS) -->
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span class="text-base">🏷️</span>
                    <div>
                        <p class="text-xs font-extrabold text-emerald-900">Promo "MAKANHEMAT" Aktif!</p>
                        <p class="text-[10px] text-emerald-700">Potongan langsung Rp3.000 untuk transaksi Anda</p>
                    </div>
                </div>
                <span class="text-xs font-black text-emerald-700">-Rp3.000</span>
            </div>

            <!-- Ringkasan Biaya (Page 27 SRS) -->
            <div class="bg-white border border-stone-200 rounded-2xl p-4 shadow-xs space-y-2 text-xs">
                <h4 class="font-extrabold text-stone-900 pb-1 border-b border-stone-100">Ringkasan Biaya</h4>
                <div class="flex justify-between text-stone-600">
                    <span>Subtotal ({{ count($cart) }} Item)</span>
                    <span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @if($discount > 0)
                    <div class="flex justify-between text-emerald-700 font-bold">
                        <span>Diskon Spesial</span>
                        <span>-Rp{{ number_format($discount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-stone-600">
                    <span class="flex items-center space-x-1">
                        <span>Pajak Resto (PB1 10%)</span>
                        <span class="text-[10px] text-stone-400">ⓘ</span>
                    </span>
                    <span class="text-stone-500 font-medium">Sudah Termasuk</span>
                </div>
                <div class="flex justify-between text-stone-600">
                    <span>Biaya Layanan Self-Order</span>
                    <span class="text-emerald-700 font-black">GRATIS</span>
                </div>
                <div class="pt-2 border-t border-stone-200 flex justify-between items-baseline font-black">
                    <span class="text-stone-900">Total Pembayaran</span>
                    <span class="text-base text-red-600">Rp{{ number_format($finalAmount, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Pilih Metode Pembayaran (Wajib Dipilih oleh Pelanggan) -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-stone-800">
                        Pilih Metode Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <span class="text-[10px] text-stone-500 font-medium">Wajib pilih salah satu</span>
                </div>

                @if($errors->has('payment_method'))
                    <div class="p-2.5 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs flex items-center space-x-2">
                        <svg class="w-4 h-4 shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-bold text-[11px]">{{ $errors->first('payment_method') }}</span>
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-2.5 text-xs">
                    <!-- Opsi 1: QRIS Instan -->
                    <label id="payment-card-qris" onclick="selectPaymentMethod('qris')" class="payment-method-card p-3 rounded-2xl border-2 {{ old('payment_method') === 'qris' ? 'border-red-500 bg-red-50/50 ring-2 ring-red-500/20' : 'border-stone-200 hover:border-red-300 bg-white' }} cursor-pointer flex flex-col justify-between space-y-2 transition-all shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-stone-900 flex items-center space-x-1">
                                <span>📱</span>
                                <span>QRIS Instan</span>
                            </span>
                            <input type="radio" name="payment_method" value="qris" {{ old('payment_method') === 'qris' ? 'checked' : '' }} required class="text-red-600 focus:ring-red-500">
                        </div>
                        <p class="text-[10px] text-stone-500 leading-snug">Verifikasi otomatis tanpa perlu antre di kasir</p>
                    </label>

                    <!-- Opsi 2: Tunai di Kasir -->
                    <label id="payment-card-cash" onclick="selectPaymentMethod('cash')" class="payment-method-card p-3 rounded-2xl border-2 {{ old('payment_method') === 'cash' ? 'border-red-500 bg-red-50/50 ring-2 ring-red-500/20' : 'border-stone-200 hover:border-red-300 bg-white' }} cursor-pointer flex flex-col justify-between space-y-2 transition-all shadow-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-stone-900 flex items-center space-x-1">
                                <span>💵</span>
                                <span>Tunai di Kasir</span>
                            </span>
                            <input type="radio" name="payment_method" value="cash" {{ old('payment_method') === 'cash' ? 'checked' : '' }} required class="text-red-600 focus:ring-red-500">
                        </div>
                        <p class="text-[10px] text-stone-500 leading-snug">Serahkan uang fisik langsung ke meja kasir</p>
                    </label>
                </div>
            </div>

            <!-- Submit Button (Page 27 SRS) -->
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-xs font-extrabold py-3.5 rounded-xl shadow-lg flex items-center justify-center space-x-2 active:scale-95 transition">
                <span>Konfirmasi & Lanjutkan Pesanan</span>
                <span>&rarr;</span>
            </button>
        </form>

        <script>
            function selectPaymentMethod(method) {
                document.querySelectorAll('.payment-method-card').forEach(card => {
                    card.classList.remove('border-red-500', 'bg-red-50/50', 'ring-2', 'ring-red-500/20');
                    card.classList.add('border-stone-200', 'bg-white');
                });

                const selectedCard = document.getElementById('payment-card-' + method);
                if (selectedCard) {
                    selectedCard.classList.remove('border-stone-200', 'bg-white');
                    selectedCard.classList.add('border-red-500', 'bg-red-50/50', 'ring-2', 'ring-red-500/20');
                    const radio = selectedCard.querySelector('input[type="radio"]');
                    if (radio) radio.checked = true;
                }
            }
        </script>
    @endif
</div>
@endsection

