@extends('layouts.customer')

@section('title', 'Pembayaran Pesanan - Kedai Mie Marmoyo')

@section('content')
<div class="space-y-4 pb-6">
    <!-- Header with Table & Order ID -->
    <div class="bg-stone-50 border border-stone-200 rounded-xl p-3 flex items-center justify-between text-xs">
        <div>
            <p class="text-stone-500 text-[10px]">Ringkasan Pesanan</p>
            <p class="font-extrabold text-stone-900">#{{ $order->order_number }}</p>
        </div>
        <div class="text-right">
            <span class="inline-block bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded text-[10px]">
                Meja {{ $order->table->table_number ?? '08' }}
            </span>
            <p class="text-xs font-black text-red-600 mt-0.5">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</p>
        </div>
    </div>

    @if($order->payment_method === 'qris')
        <!-- ==================== QRIS INSTAN TAB (Page 28 SRS) ==================== -->
        <div class="bg-white border-2 border-red-500 rounded-2xl p-5 shadow-lg text-center space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                <div class="flex items-center space-x-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-xs font-extrabold text-stone-900">QRIS Instan Marmoyo</span>
                </div>
                <!-- Countdown Timer -->
                <div class="bg-red-50 text-red-600 border border-red-200 px-2 py-0.5 rounded-full text-[11px] font-black flex items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span id="countdown">04:59</span>
                </div>
            </div>

            <p class="text-[11px] text-stone-500">Pindai QR ini memakai GoPay, OVO, ShopeePay, BCA, atau mobile banking lainnya:</p>

            <!-- Dynamic QR Canvas / Display -->
            <div class="bg-stone-50 p-4 rounded-xl border border-stone-200 inline-block shadow-inner">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($payment->qris_payload ?? 'QRIS_MARMOYO_'.$order->order_number) }}" alt="QRIS Code" class="w-44 h-44 mx-auto rounded-lg shadow-xs">
                <p class="text-[9px] text-stone-400 font-mono mt-2 tracking-widest uppercase">NMID: ID102026MARMOYO01</p>
            </div>

            <div>
                <p class="text-xs text-stone-500">Total Tagihan:</p>
                <p class="text-xl font-black text-stone-900">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</p>
            </div>

            <!-- Auto-verification simulation button -->
            <form action="{{ route('customer.verify-qris', ['orderNumber' => $order->order_number]) }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-xs font-black py-3 rounded-xl shadow-md active:scale-95 transition flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Saya Sudah Membayar &rarr;</span>
                </button>
            </form>

            <div class="pt-2 border-t border-stone-100 flex items-center justify-between text-[11px]">
                <form action="{{ route('customer.payment.switch', ['orderNumber' => $order->order_number]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="method" value="cash">
                    <button type="submit" class="text-stone-500 hover:text-red-600 font-semibold underline">
                        Ganti ke Pembayaran Tunai di Kasir
                    </button>
                </form>
                <a href="{{ route('customer.cart') }}" class="text-stone-400 hover:text-stone-600">Ubah Pesanan</a>
            </div>
        </div>

        <script>
            let duration = 300;
            const timerDisplay = document.getElementById('countdown');
            const interval = setInterval(function() {
                let minutes = parseInt(duration / 60, 10);
                let seconds = parseInt(duration % 60, 10);
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;
                timerDisplay.textContent = minutes + ":" + seconds;
                if (--duration < 0) {
                    clearInterval(interval);
                    timerDisplay.textContent = "00:00 (Kedaluwarsa)";
                }
            }, 1000);
        </script>

    @else
        <!-- ==================== TUNAI DI KASIR TAB (Page 29 SRS) ==================== -->
        <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-lg text-center space-y-4">
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-2.5 text-center">
                <span class="text-amber-700 text-xs font-bold">Menunggu Pembayaran di Kasir</span>
                <p class="text-[10px] text-amber-600">Pesanan segera disiapkan setelah pembayaran divalidasi kasir</p>
            </div>

            @php 
                $cashCode = $payment->payment_code ?? ('TUNAI-' . str_replace('MRM-', '', $order->order_number)); 
            @endphp

            <!-- Genuine Scannable QR Code for Cashier Camera Scanner -->
            <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200 inline-block shadow-inner">
                <div class="bg-white p-2.5 rounded-xl shadow-xs border border-stone-100 inline-block">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($cashCode) }}" alt="QR Kode Bayar Tunai" class="w-40 h-40 mx-auto">
                </div>
                <p class="text-[10px] text-stone-500 font-bold mt-2.5 text-center">
                    Arahkan QR ini ke kamera scan kasir
                </p>
            </div>

            <div>
                <p class="text-[10px] uppercase font-bold text-stone-400 tracking-wider">KODE ANTREAN TUNAI</p>
                <div class="mt-1 py-2.5 px-4 bg-stone-900 text-white rounded-xl font-mono text-lg font-black tracking-widest shadow-md flex items-center justify-center space-x-2">
                    <span>{{ $cashCode }}</span>
                </div>
                <p class="text-[10px] text-stone-400 mt-1">Dapat disebutkan ke kasir jika tidak membawa HP</p>
            </div>

            <!-- 3 Langkah Bayar Tunai (Page 29 SRS) -->
            <div class="text-left bg-stone-50 rounded-xl p-3.5 border border-stone-200 space-y-2 text-xs">
                <p class="font-extrabold text-stone-800 text-[11px]">3 Langkah Mudah Bayar Tunai:</p>
                <div class="flex items-start space-x-2.5">
                    <span class="w-5 h-5 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0">1</span>
                    <p class="text-stone-600 text-[11px]"><strong class="text-stone-900">Menuju Meja Kasir:</strong> Bawa HP Anda dan tunjukkan kode di atas.</p>
                </div>
                <div class="flex items-start space-x-2.5">
                    <span class="w-5 h-5 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0">2</span>
                    <p class="text-stone-600 text-[11px]"><strong class="text-stone-900">Serahkan Uang:</strong> Siapkan Rp{{ number_format($order->final_amount, 0, ',', '.') }} (uang pas/kembalian).</p>
                </div>
                <div class="flex items-start space-x-2.5">
                    <span class="w-5 h-5 rounded-full bg-red-600 text-white text-[10px] font-bold flex items-center justify-center shrink-0">3</span>
                    <p class="text-stone-600 text-[11px]"><strong class="text-stone-900">Validasi POS:</strong> Kasir men-scan kode untuk memvalidasi pembayaran Anda.</p>
                </div>
            </div>

            <!-- Switch to QRIS Button -->
            <div class="pt-2 border-t border-stone-100 flex items-center justify-between text-[11px]">
                <form action="{{ route('customer.payment.switch', ['orderNumber' => $order->order_number]) }}" method="POST">
                    @csrf
                    <input type="hidden" name="method" value="qris">
                    <button type="submit" class="text-stone-500 hover:text-red-600 font-semibold underline">
                        Ganti ke Pembayaran QRIS Instan
                    </button>
                </form>
                <a href="{{ route('customer.order-status', ['orderNumber' => $order->order_number]) }}" class="text-red-600 font-bold hover:underline">
                    Pantau Status &rarr;
                </a>
            </div>
        </div>
    @endif

    <!-- Rincian Pesanan Singkat -->
    <div class="bg-white border border-stone-200 rounded-xl p-3.5 space-y-2 text-xs">
        <h4 class="font-extrabold text-stone-900">Rincian Menu ({{ $order->items->count() }})</h4>
        <div class="divide-y divide-stone-100">
            @foreach($order->items as $item)
                <div class="py-1.5 flex justify-between items-center">
                    <div>
                        <p class="font-bold text-stone-800">{{ $item->quantity }}x {{ $item->menu_name }}</p>
                        @if(!empty($item->spice_level))
                            <span class="text-[10px] text-stone-500">{{ $item->spice_level }}</span>
                        @endif
                    </div>
                    <span class="font-bold text-stone-900">Rp{{ number_format($item->subtotal_price, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

