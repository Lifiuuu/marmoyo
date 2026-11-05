@extends('layouts.cashier')

@section('title', 'Live Order POS - Marmoyo Smart-Dine')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Realtime Toast Notification (Hidden by default, shows on new order) -->
    <div id="new-order-toast" class="fixed top-16 right-4 sm:right-6 z-50 bg-emerald-600 text-white px-4 py-3 rounded-2xl shadow-2xl flex items-center space-x-3 transition-all duration-300 transform translate-y-[-150%] opacity-0 pointer-events-none">
        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
        </div>
        <div>
            <p class="text-xs font-black" id="toast-title">Pesanan Baru Masuk!</p>
            <p class="text-[10px] text-emerald-100" id="toast-subtitle">Daftar antrean otomatis diperbarui.</p>
        </div>
    </div>

    <!-- Top Metric Overview: Minimalist & Clean Responsive Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4">
        <!-- 1. Omzet Hari Ini -->
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-4 sm:p-5 shadow-sm">
            <p class="text-[10px] sm:text-[11px] font-bold text-stone-400 uppercase tracking-wider">Omzet Penjualan Hari Ini</p>
            <p id="metric-omzet" class="text-xl sm:text-2xl font-black text-white mt-1">Rp{{ number_format($todayOmzet, 0, ',', '.') }}</p>
            <span class="text-[10px] text-emerald-400 font-semibold mt-0.5 inline-block">Pesanan Sah &amp; Lunas</span>
        </div>

        <!-- 2. Antrean Menunggu -->
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-4 sm:p-5 shadow-sm">
            <p class="text-[10px] sm:text-[11px] font-bold text-stone-400 uppercase tracking-wider">Antrean Menunggu</p>
            <p id="metric-waiting" class="text-xl sm:text-2xl font-black text-amber-400 mt-1">{{ $waitingOrdersCount }}</p>
            <span class="text-[10px] text-stone-400 mt-0.5 inline-block">Sedang Disiapkan</span>
        </div>

        <!-- 3. Pesanan Selesai Hari Ini -->
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-4 sm:p-5 shadow-sm">
            <p class="text-[10px] sm:text-[11px] font-bold text-stone-400 uppercase tracking-wider">Pesanan Selesai Hari Ini</p>
            <p id="metric-completed" class="text-xl sm:text-2xl font-black text-stone-200 mt-1">{{ $completedOrdersCount }}</p>
            <span class="text-[10px] text-emerald-400 font-semibold mt-0.5 inline-block">Dine In &amp; Take Away Selesai</span>
        </div>
    </div>

    <!-- Live Orders Cards Grid -->
    <div>
        <div class="flex flex-col xs:flex-row xs:items-center justify-between gap-2 mb-3 sm:mb-4">
            <div class="flex items-center space-x-2">
                <h3 class="text-xs sm:text-sm font-extrabold text-white flex items-center space-x-2">
                    <span>Daftar Antrean Pesanan Aktif</span>
                    <span id="badge-active-count" class="text-xs bg-stone-800 text-stone-300 px-2 py-0.5 rounded-full font-bold">{{ $activeOrders->count() }}</span>
                </h3>
            </div>
            <div class="flex items-center space-x-2 text-[11px] text-stone-400">
                <span id="realtime-status-dot" class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span id="realtime-status-text">Sinkronisasi Realtime Terhubung</span>
                <button type="button" onclick="triggerManualSync()" title="Sinkronkan Sekarang" class="p-1 hover:text-white rounded transition active:rotate-180">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Dynamic Live Order Queue Container -->
        <div id="active-orders-container">
            @include('cashier.partials.active-orders-grid', ['activeOrders' => $activeOrders])
        </div>
    </div>
</div>

<script>
    // Global live cash orders map for instant bill detection when cashier scans QR
    window.pendingOrdersMap = @json($pendingOrdersMap ?? []);
    var pendingOrdersMap = window.pendingOrdersMap;

    let currentHash = null;
    let currentActiveCount = {{ $activeOrders->count() }};
    let isPolling = false;

    // Web Audio API chime generator for pleasant audible notification without external files
    function playOrderChime() {
        try {
            const AudioCtx = window.AudioContext || window.webkitAudioContext;
            if (!AudioCtx) return;
            const ctx = new AudioCtx();
            
            const now = ctx.currentTime;
            
            // First chime tone (E5 ~ 659 Hz)
            const osc1 = ctx.createOscillator();
            const gain1 = ctx.createGain();
            osc1.type = 'sine';
            osc1.frequency.setValueAtTime(659.25, now);
            gain1.gain.setValueAtTime(0.15, now);
            gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc1.connect(gain1);
            gain1.connect(ctx.destination);
            osc1.start(now);
            osc1.stop(now + 0.35);

            // Second chime tone (G#5 ~ 830 Hz)
            const osc2 = ctx.createOscillator();
            const gain2 = ctx.createGain();
            osc2.type = 'sine';
            osc2.frequency.setValueAtTime(830.61, now + 0.15);
            gain2.gain.setValueAtTime(0.18, now + 0.15);
            gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.6);
            osc2.connect(gain2);
            gain2.connect(ctx.destination);
            osc2.start(now + 0.15);
            osc2.stop(now + 0.6);
        } catch (e) {
            console.log('Audio notification skipped:', e);
        }
    }

    // Show floating toast notification
    function showNewOrderToast(count) {
        const toast = document.getElementById('new-order-toast');
        if (!toast) return;

        document.getElementById('toast-title').textContent = `${count} Pesanan Masuk!`;
        document.getElementById('toast-subtitle').textContent = 'Antrean dapur telah diperbarui secara realtime.';
        
        toast.classList.remove('translate-y-[-150%]', 'opacity-0', 'pointer-events-none');
        toast.classList.add('translate-y-0', 'opacity-100');

        setTimeout(() => {
            toast.classList.remove('translate-y-0', 'opacity-100');
            toast.classList.add('translate-y-[-150%]', 'opacity-0', 'pointer-events-none');
        }, 4000);
    }

    async function fetchLiveOrdersFeed() {
        if (isPolling) return;
        isPolling = true;

        const feedUrl = `{{ route('cashier.live-orders.feed') }}` + (currentHash ? `?hash=${encodeURIComponent(currentHash)}` : '');

        try {
            const response = await fetch(feedUrl, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            // Set status to connected
            const statusDot = document.getElementById('realtime-status-dot');
            const statusText = document.getElementById('realtime-status-text');
            if (statusDot) statusDot.className = 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse';
            if (statusText) statusText.textContent = 'Sinkronisasi Realtime Terhubung';

            if (data.changed) {
                // If new order came in (active count increased)
                if (data.active_orders_count > currentActiveCount) {
                    playOrderChime();
                    showNewOrderToast(data.active_orders_count - currentActiveCount);
                }

                currentActiveCount = data.active_orders_count;
                currentHash = data.hash;

                // Update metrics
                const elOmzet = document.getElementById('metric-omzet');
                if (elOmzet) elOmzet.textContent = data.omzet_formatted;

                const elWaiting = document.getElementById('metric-waiting');
                if (elWaiting) elWaiting.textContent = data.waiting_orders_count;

                const elCompleted = document.getElementById('metric-completed');
                if (elCompleted) elCompleted.textContent = data.completed_orders_count;

                const elBadge = document.getElementById('badge-active-count');
                if (elBadge) elBadge.textContent = data.active_orders_count;

                // Seamlessly swap cards container without page flash
                const elContainer = document.getElementById('active-orders-container');
                if (elContainer && data.orders_html) {
                    elContainer.innerHTML = data.orders_html;
                }

                // Update cash validation lookup map
                if (data.pending_orders_map) {
                    window.pendingOrdersMap = data.pending_orders_map;
                    pendingOrdersMap = data.pending_orders_map;
                }
            } else {
                if (data.hash) currentHash = data.hash;
            }
            consecutiveErrors = 0;
        } catch (err) {
            consecutiveErrors++;
            const statusDot = document.getElementById('realtime-status-dot');
            const statusText = document.getElementById('realtime-status-text');
            if (statusDot) statusDot.className = 'w-2 h-2 rounded-full bg-amber-500';
            if (statusText) statusText.textContent = 'Menghubungkan kembali...';
        } finally {
            isPolling = false;
        }
    }

    let pollTimer = null;
    let consecutiveErrors = 0;

    function scheduleNextPoll() {
        if (pollTimer) clearTimeout(pollTimer);

        let delay = document.hidden ? 15000 : 3500;
        if (consecutiveErrors > 0) {
            // Exponential backoff to avoid hammering Vercel serverless during connection hiccups
            delay = Math.min(20000, delay * Math.pow(1.5, consecutiveErrors));
        }

        pollTimer = setTimeout(async () => {
            await fetchLiveOrdersFeed();
            scheduleNextPoll();
        }, delay);
    }

    function triggerManualSync() {
        currentHash = null; // force fetch fresh data
        fetchLiveOrdersFeed();
        scheduleNextPoll();
    }

    // Refresh immediately when cashier switches back to active tab
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden) {
            consecutiveErrors = 0;
            fetchLiveOrdersFeed();
            scheduleNextPoll();
        }
    });

    // Start scheduling
    setTimeout(scheduleNextPoll, 1000);
</script>
@endsection
