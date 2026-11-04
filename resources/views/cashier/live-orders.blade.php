@extends('layouts.cashier')

@section('title', 'Live Order POS - Kedai Mie Marmoyo')

@section('content')
<div class="space-y-6">
    <!-- Top Metric Overview: Minimalist & Clean -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- 1. Omzet Hari Ini -->
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5 shadow-sm">
            <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Omzet Penjualan Hari Ini</p>
            <p class="text-2xl font-black text-white mt-1.5">Rp{{ number_format($todayOmzet, 0, ',', '.') }}</p>
            <span class="text-[10px] text-emerald-400 font-semibold mt-1 inline-block">Pesanan Sah &amp; Lunas</span>
        </div>

        <!-- 2. Antrean Menunggu -->
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5 shadow-sm">
            <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Antrean Menunggu</p>
            <p class="text-2xl font-black text-amber-400 mt-1.5">{{ $waitingOrdersCount }}</p>
            <span class="text-[10px] text-stone-400 mt-1 inline-block">Sedang Disiapkan</span>
        </div>

        <!-- 3. Pesanan Selesai Hari Ini -->
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5 shadow-sm">
            <p class="text-[11px] font-bold text-stone-400 uppercase tracking-wider">Pesanan Selesai Hari Ini</p>
            <p class="text-2xl font-black text-stone-200 mt-1.5">{{ $completedOrdersCount }}</p>
            <span class="text-[10px] text-stone-400 mt-1 inline-block">Telah Disajikan ke Pelanggan</span>
        </div>
    </div>

    <!-- Live Orders Cards Grid (Page 29 SRS) -->
    <div>
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-2">
                <h3 class="text-sm font-extrabold text-white flex items-center space-x-2">
                    <span>Daftar Antrean Pesanan Aktif</span>
                    <span class="text-xs bg-stone-800 text-stone-300 px-2 py-0.5 rounded-full font-bold">{{ $activeOrders->count() }}</span>
                </h3>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-xs text-stone-400">Sinkronisasi Realtime Terhubung</span>
            </div>
        </div>

        @if($activeOrders->isEmpty())
            <div class="py-16 text-center bg-stone-900 border border-stone-800 rounded-2xl">
                <p class="text-stone-400 text-xs">Tidak ada antrean pesanan aktif saat ini. Siap melayani pelanggan baru.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($activeOrders as $order)
                    <div class="bg-stone-900 border border-stone-800 hover:border-amber-500/60 rounded-2xl p-4 shadow-lg flex flex-col justify-between space-y-4 transition">
                        <div>
                            <!-- Card Header: Table Number & Order Number -->
                            <div class="flex items-start justify-between border-b border-stone-800 pb-2.5">
                                <div class="flex items-center space-x-2">
                                    @if($order->isTakeAway())
                                        <span class="text-[11px] font-black px-2.5 py-1 rounded-md bg-amber-500 text-stone-950 font-mono tracking-wider">
                                            TAKE AWAY
                                        </span>
                                        <div>
                                            <p class="font-extrabold text-xs text-white truncate max-w-[140px]">{{ $order->customer_name ?: 'Take Away' }}</p>
                                            <p class="font-mono text-[10px] text-stone-400">#{{ $order->order_number }} &bull; {{ $order->created_at->format('H:i') }}</p>
                                        </div>
                                    @else
                                        <span class="text-sm font-black px-2.5 py-1 rounded-md bg-red-600 text-white">
                                            Meja {{ $order->table->table_number ?? '00' }}
                                        </span>
                                        <div>
                                            <p class="font-mono text-xs font-bold text-stone-200">#{{ $order->order_number }}</p>
                                            <span class="text-[10px] text-stone-500">{{ $order->created_at->format('H:i') }} ({{ $order->created_at->diffForHumans() }})</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Status Badge: Murni Menunggu -->
                                <div>
                                    <span class="inline-block bg-amber-950/80 text-amber-300 border border-amber-800 text-[10px] font-bold px-2 py-0.5 rounded-md uppercase">
                                        Menunggu
                                    </span>
                                </div>
                            </div>

                            <!-- Item List -->
                            <div class="py-2.5 space-y-2 text-xs divide-y divide-stone-800/60">
                                @foreach($order->items as $item)
                                    <div class="pt-1.5 first:pt-0">
                                        <div class="flex justify-between items-baseline">
                                            <span class="font-bold text-stone-200">{{ $item->quantity }}x {{ $item->menu_name }}</span>
                                            <span class="text-stone-400 font-mono text-[11px]">Rp{{ number_format($item->subtotal_price, 0, ',', '.') }}</span>
                                        </div>
                                        @if(!empty($item->spice_level))
                                            <p class="text-[10px] text-red-400 font-bold">• {{ $item->spice_level }}</p>
                                        @endif
                                        @if(!empty($item->noodle_type))
                                            <p class="text-[10px] text-stone-400">• {{ $item->noodle_type }}</p>
                                        @endif
                                        @if(!empty($item->special_notes))
                                            <p class="text-[10px] text-amber-300 italic bg-amber-950/50 border border-amber-900/50 px-1.5 py-0.5 rounded mt-1">
                                                Catatan: {{ $item->special_notes }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if(!empty($order->kitchen_notes))
                                <div class="bg-stone-950 p-2 rounded-lg border border-stone-800 text-[10px] text-amber-200">
                                    <strong>Catatan Khusus:</strong> {{ $order->kitchen_notes }}
                                </div>
                            @endif
                        </div>

                        <!-- Card Footer & Fast Actions (Cukup 1 Tombol Selesai) -->
                        <div class="pt-3 border-t border-stone-800 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center space-x-1.5">
                                    <span class="text-stone-400 font-medium">Total Tagihan:</span>
                                    @if($order->packaging_fee > 0)
                                        <span class="text-[10px] text-amber-400 font-semibold bg-amber-950/60 border border-amber-800/60 px-1.5 py-0.5 rounded">+Kemasan Rp{{ number_format($order->packaging_fee, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                                <span class="text-sm font-black text-white">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</span>
                            </div>

                            <!-- Tindakan Kasir: Cetak Struk & Tandai Selesai -->
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('cashier.print.receipt', $order->id) }}" target="_blank" class="bg-stone-800 hover:bg-stone-700 text-stone-300 text-[11px] font-bold py-2 rounded-xl text-center transition">
                                    Cetak Struk
                                </a>
                                <form action="{{ route('cashier.order.status', $order->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-[11px] font-black py-2 rounded-xl transition shadow active:scale-95">
                                        Selesai
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<script>
    // Live cash orders map for instant bill detection when cashier scans QR
    const pendingOrdersMap = @json($pendingOrdersMap ?? []);
</script>
@endsection

