@extends('layouts.customer')

@section('title', 'Status Pesanan - Marmoyo Smart-Dine')

@section('content')
<div class="space-y-4 pb-8">
    <!-- Success / Verification Banner (Page 28 SRS) -->
    @if($order->isPaid())
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-center space-y-2">
            <div class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center mx-auto shadow-md">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-sm font-extrabold text-emerald-900">Pembayaran Berhasil!</h3>
            <p class="text-[11px] text-emerald-700">Terima kasih, pembayaran Anda telah diverifikasi secara sah.</p>
            <div class="inline-block bg-white px-3 py-1 rounded-full text-xs font-black text-emerald-800 border border-emerald-200">
                Total Bayar Lunas: Rp{{ number_format($order->final_amount, 0, ',', '.') }}
            </div>
        </div>

        <!-- Kitchen Status Tracker -->
        <div class="bg-white border border-stone-200 rounded-2xl p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <span id="kitchen-status-dot" class="w-2.5 h-2.5 rounded-full {{ in_array($order->status, ['selesai', 'completed']) ? 'bg-emerald-500' : 'bg-emerald-500 animate-pulse' }}"></span>
                    <h4 id="kitchen-status-heading" class="text-xs font-extrabold text-stone-900">{{ in_array($order->status, ['selesai', 'completed']) ? 'Pesanan Telah Selesai' : 'Pesanan Dikonfirmasi' }}</h4>
                </div>
                <span id="kitchen-status-badge" class="text-[10px] {{ in_array($order->status, ['selesai', 'completed']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }} font-black px-2.5 py-0.5 rounded-full uppercase">
                    {{ in_array($order->status, ['selesai', 'completed']) ? 'Selesai' : 'Menunggu Disajikan' }}
                </span>
            </div>
            <p id="kitchen-status-desc" class="text-[11px] text-stone-500">
                {{ in_array($order->status, ['selesai', 'completed']) ? 'Pesanan Anda telah selesai disiapkan dan disajikan ke meja Anda. Selamat menikmati!' : 'Pesanan Anda sudah masuk dan sedang disiapkan oleh tim Marmoyo Smart-Dine.' }}
            </p>

            <div class="bg-stone-50 border border-stone-200 rounded-xl p-2.5 flex items-center justify-between text-xs">
                <span class="text-stone-600 font-bold">Estimasi Penyajian:</span>
                <span id="kitchen-status-eta" class="font-extrabold text-stone-900">{{ in_array($order->status, ['selesai', 'completed']) ? 'Telah Disajikan' : '10 - 15 Menit' }}</span>
            </div>
        </div>

    @else
        <!-- Pending Payment Status -->
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-center space-y-2">
            <div class="w-12 h-12 rounded-full bg-amber-400 text-white flex items-center justify-center mx-auto shadow-md">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-sm font-extrabold text-amber-900">Menunggu Pembayaran</h3>
            <p class="text-[11px] text-amber-700">Pesanan akan segera disiapkan setelah pembayaran divalidasi di kasir.</p>
            <a href="{{ route('customer.payment', ['orderNumber' => $order->order_number]) }}" class="inline-block bg-red-600 text-white text-xs font-bold px-4 py-2 rounded-xl shadow">
                Selesaikan Pembayaran Sekarang &rarr;
            </a>
        </div>
    @endif

    <!-- Struk Digital Card (Page 28 SRS) -->
    <div class="bg-stone-50 border border-stone-200 rounded-2xl p-4 space-y-3 text-xs">
        <div class="flex items-center justify-between border-b border-stone-200 pb-2">
            <div>
                <span class="text-[10px] font-bold text-stone-400 uppercase">Struk Digital</span>
                <h5 class="font-mono font-black text-stone-900">ID #{{ $order->order_number }}</h5>
            </div>
            <div class="text-right">
                <span class="text-[10px] bg-stone-200 text-stone-700 font-bold px-2 py-0.5 rounded">
                    Meja {{ $order->table->table_number ?? '08' }}
                </span>
                <p class="text-[10px] text-stone-400 mt-0.5">{{ $order->created_at->format('d M Y, H:i') }} WIB</p>
            </div>
        </div>

        <div class="space-y-1.5 divide-y divide-stone-100">
            @foreach($order->items as $item)
                <div class="pt-1.5 flex justify-between items-start">
                    <div>
                        <p class="font-bold text-stone-800">{{ $item->quantity }}x {{ $item->menu_name }}</p>
                        @if(!empty($item->spice_level))
                            <p class="text-[10px] text-red-600 font-semibold">• {{ $item->spice_level }}</p>
                        @endif
                        @if(!empty($item->noodle_type))
                            <p class="text-[10px] text-stone-500">• {{ $item->noodle_type }}</p>
                        @endif
                    </div>
                    <span class="font-bold text-stone-900">Rp{{ number_format($item->subtotal_price, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>

        <div class="pt-2 border-t border-stone-200 space-y-1 text-stone-600">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-700 font-bold">
                    <span>Diskon Promo</span>
                    <span>-Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between font-black text-stone-900 pt-1 border-t border-stone-200">
                <span>Total Bayar</span>
                <span class="text-red-600">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Download Digital Receipt Button (Page 28 SRS) -->
        <a href="{{ route('customer.download-receipt', ['orderNumber' => $order->order_number]) }}" class="w-full bg-stone-200 hover:bg-stone-300 text-stone-800 font-bold py-2 rounded-xl text-center text-xs block transition">
            📥 Unduh Bukti Pembayaran / Struk Digital
        </a>
    </div>
</div>

<script>
    // Realtime Kitchen Tracker: automatically tracks order status without manual refresh
    (function() {
        let currentStatus = '{{ $order->status }}';
        let wasPaid = {{ $order->isPaid() ? 'true' : 'false' }};

        // If order is already completed, do NOT poll to keep Vercel serverless load zero
        if (currentStatus === 'selesai' || currentStatus === 'completed') {
            return;
        }

        const statusApiUrl = '{{ route("customer.payment.status-api", ["orderNumber" => $order->order_number]) }}';
        let pollTimer = null;
        let isChecking = false;

        const checkOrderStatus = async () => {
            if (isChecking) return;
            isChecking = true;

            try {
                const response = await fetch(statusApiUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) return;
                const data = await response.json();

                // If order was unpaid and now paid, reload page to show full success layout
                if (!wasPaid && data.is_paid) {
                    window.location.reload();
                    return;
                }

                // If status changed to completed, update tracker immediately in-place and stop polling
                if (data.order_status !== currentStatus) {
                    currentStatus = data.order_status;

                    if (currentStatus === 'selesai' || currentStatus === 'completed') {
                        if (pollTimer) clearTimeout(pollTimer);

                        const heading = document.getElementById('kitchen-status-heading');
                        const badge = document.getElementById('kitchen-status-badge');
                        const desc = document.getElementById('kitchen-status-desc');
                        const eta = document.getElementById('kitchen-status-eta');
                        const dot = document.getElementById('kitchen-status-dot');

                        if (heading) heading.textContent = 'Pesanan Telah Selesai';
                        if (badge) {
                            badge.textContent = 'Selesai';
                            badge.className = 'text-[10px] bg-emerald-100 text-emerald-800 font-black px-2.5 py-0.5 rounded-full uppercase';
                        }
                        if (desc) desc.textContent = 'Pesanan Anda telah selesai disiapkan dan disajikan ke meja Anda. Selamat menikmati!';
                        if (eta) eta.textContent = 'Telah Disajikan';
                        if (dot) dot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500';
                        return; // Cease further polling
                    }
                }
            } catch (err) {
                // Silently ignore transient fetch failures
            } finally {
                isChecking = false;
                scheduleNextCheck();
            }
        };

        function scheduleNextCheck() {
            if (currentStatus === 'selesai' || currentStatus === 'completed') return;
            if (pollTimer) clearTimeout(pollTimer);

            // Serverless efficiency: slow down interval to 15s when tab is in background
            const interval = document.hidden ? 15000 : 3500;
            pollTimer = setTimeout(checkOrderStatus, interval);
        }

        document.addEventListener('visibilitychange', function() {
            if (!document.hidden && currentStatus !== 'selesai' && currentStatus !== 'completed') {
                checkOrderStatus();
            }
        });

        scheduleNextCheck();
    })();
</script>
@endsection

