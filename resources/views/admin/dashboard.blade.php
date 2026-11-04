@extends('layouts.admin')

@section('page-title', 'Executive Overview & Analisis Penjualan')

@section('content')
<div class="space-y-6">
    <!-- Metric Cards (Page 30 SRS) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-xs">
            <span class="text-xs font-bold text-stone-400 uppercase tracking-wider">Omzet Penjualan Hari Ini</span>
            <p class="text-2xl font-black text-stone-900 mt-1">Rp{{ number_format($totalOmzet, 0, ',', '.') }}</p>
            <span class="text-[11px] text-emerald-600 font-semibold mt-1 inline-block">↑ Terhitung otomatis dari transaksi lunas</span>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-xs">
            <span class="text-xs font-bold text-stone-400 uppercase tracking-wider">Volume Transaksi</span>
            <p class="text-2xl font-black text-stone-900 mt-1">{{ $totalTransactions }}</p>
            <span class="text-[11px] text-stone-500 mt-1 inline-block">Pesanan mandiri meja QR</span>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-xs">
            <span class="text-xs font-bold text-stone-400 uppercase tracking-wider">Kepuasan Pelanggan</span>
            <p class="text-2xl font-black text-amber-500 mt-1">⭐ {{ $averageRating }} / 10</p>
            <span class="text-[11px] text-stone-500 mt-1 inline-block">Riset UTS Design Thinking</span>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-xs">
            <span class="text-xs font-bold text-stone-400 uppercase tracking-wider">Rasio Pembayaran</span>
            <p class="text-2xl font-black text-stone-900 mt-1">{{ $qrisPercentage }}% QRIS</p>
            <div class="w-full bg-stone-100 rounded-full h-2 mt-2 overflow-hidden flex">
                <div class="bg-red-600 h-2" style="width: {{ $qrisPercentage }}%"></div>
                <div class="bg-stone-400 h-2" style="width: {{ $cashPercentage }}%"></div>
            </div>
            <span class="text-[10px] text-stone-500 mt-1 inline-block">{{ $qrisPercentage }}% Digital vs {{ $cashPercentage }}% Tunai</span>
        </div>
    </div>

    <!-- Charts & Top Menus Row (Page 30 SRS) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Hourly Peak Rush Hour Graph (17:00 - 21:00 Peak) -->
        <div class="lg:col-span-2 bg-white border border-stone-200 rounded-2xl p-5 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-stone-900 text-sm">Tren Penjualan Per Jam Operasional</h3>
                    <p class="text-xs text-stone-400">Lonjakan antrean terbukti pada jam sibuk sore-malam (17.00 - 21.00 WIB)</p>
                </div>
                <span class="text-xs bg-red-50 text-red-700 font-bold px-2.5 py-1 rounded-full">Peak Hour Alert</span>
            </div>

            <div class="h-56 flex items-end justify-between gap-2 pt-6 pb-2 px-2 border-b border-stone-100">
                @php $maxVal = max(array_values($hourlyData)) ?: 1; @endphp
                @foreach($hourlyData as $hour => $val)
                    @php 
                        $isPeak = in_array($hour, ['17:00', '18:00', '19:00', '20:00']); 
                        $heightPercent = $val > 0 ? max(8, round(($val / $maxVal) * 100)) : 4;
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 group relative">
                        <div class="absolute -top-7 opacity-0 group-hover:opacity-100 transition bg-stone-900 text-white text-[10px] py-0.5 px-1.5 rounded pointer-events-none whitespace-nowrap z-10">
                            {{ $val }} pesanan
                        </div>
                        <div class="w-full rounded-t-lg transition-all {{ $isPeak ? 'bg-red-600 hover:bg-red-700' : 'bg-stone-200 hover:bg-stone-300' }}" style="height: {{ $heightPercent }}%;"></div>
                        <span class="text-[10px] font-semibold text-stone-500 transform -rotate-45 md:rotate-0 mt-1">{{ $hour }}</span>
                    </div>
                @endforeach
            </div>
            <div class="flex items-center space-x-4 text-xs text-stone-500">
                <span class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 bg-red-600 rounded"></span>
                    <span>Jam Sibuk (Antrean Kritis)</span>
                </span>
                <span class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 bg-stone-200 rounded"></span>
                    <span>Jam Normal</span>
                </span>
            </div>
        </div>

        <!-- Top Menu Terlaris Ranking (Page 30 SRS) -->
        <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-xs space-y-3">
            <h3 class="font-extrabold text-stone-900 text-sm">Top Menu Terlaris</h3>
            <p class="text-xs text-stone-400">Menu dengan konversi pesanan tertinggi</p>

            <div class="divide-y divide-stone-100 pt-1">
                @foreach($topMenus as $idx => $menu)
                    <div class="py-2.5 flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="w-6 h-6 rounded-full {{ $idx === 0 ? 'bg-amber-100 text-amber-700 font-black' : 'bg-stone-100 text-stone-500' }} text-xs flex items-center justify-center font-bold">
                                #{{ $idx + 1 }}
                            </span>
                            <div>
                                <h4 class="text-xs font-bold text-stone-900">{{ $menu->name }}</h4>
                                <span class="text-[10px] text-stone-400">{{ $menu->category->name ?? 'Aneka Menu' }} • {{ $menu->total_sold ?? 0 }} terjual</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-black text-stone-900">Rp{{ number_format($menu->base_price, 0, ',', '.') }}</p>
                            @if($menu->is_spicy)
                                <span class="text-[9px] text-red-600 font-bold">🌶️ Level {{ $menu->default_spiciness_level }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-white border border-stone-200 rounded-2xl p-5 shadow-xs space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-extrabold text-stone-900 text-sm">Transaksi Terakhir Masuk</h3>
            <a href="{{ route('cashier.live-orders') }}" class="text-xs font-bold text-red-600 hover:underline">Buka Live POS &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-stone-200 text-stone-400 uppercase font-semibold">
                        <th class="pb-2.5">No. Pesanan</th>
                        <th class="pb-2.5">Meja</th>
                        <th class="pb-2.5">Waktu</th>
                        <th class="pb-2.5">Metode Bayar</th>
                        <th class="pb-2.5">Status Pesanan</th>
                        <th class="pb-2.5 text-right">Total Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="py-3 font-mono font-bold text-stone-900">#{{ $order->order_number }}</td>
                            <td class="py-3 font-semibold text-stone-800">
                                @if($order->isTakeAway())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 text-[11px] font-bold">
                                        Take Away
                                    </span>
                                @else
                                    <span>Meja {{ $order->table->table_number ?? '00' }}</span>
                                @endif
                            </td>
                            <td class="py-3 text-stone-500">{{ $order->created_at->format('H:i:s') }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_method === 'qris' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ $order->payment_method ?? 'tunai' }}
                                </span>
                            </td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $order->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($order->status === 'cooking' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $order->status }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-black text-stone-900">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-6 text-center text-stone-400">Belum ada transaksi hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

