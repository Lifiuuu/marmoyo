@extends('layouts.admin')

@section('page-title', 'Executive Overview & Analisis Penjualan')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Metric Cards (Page 30 SRS) - Responsive Grid for Phone, Tab, & Desktop -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
        <div class="bg-white border border-stone-200 rounded-2xl p-4 sm:p-5 shadow-xs">
            <span class="text-[10px] sm:text-xs font-bold text-stone-400 uppercase tracking-wider">Omzet Penjualan Hari Ini</span>
            <p class="text-xl sm:text-2xl font-black text-stone-900 mt-1">Rp{{ number_format($totalOmzet, 0, ',', '.') }}</p>
            <span class="text-[10px] sm:text-[11px] text-emerald-600 font-semibold mt-0.5 inline-block">Terhitung otomatis dari transaksi lunas</span>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl p-4 sm:p-5 shadow-xs">
            <span class="text-[10px] sm:text-xs font-bold text-stone-400 uppercase tracking-wider">Volume Transaksi</span>
            <p class="text-xl sm:text-2xl font-black text-stone-900 mt-1">{{ $totalTransactions }}</p>
            <span class="text-[10px] sm:text-[11px] text-stone-500 mt-0.5 inline-block">Pesanan mandiri meja QR &amp; Take Away</span>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl p-4 sm:p-5 shadow-xs">
            <span class="text-[10px] sm:text-xs font-bold text-stone-400 uppercase tracking-wider">Kepuasan Pelanggan</span>
            <p class="text-xl sm:text-2xl font-black text-amber-600 mt-1">{{ $averageRating }} / 10</p>
            <span class="text-[10px] sm:text-[11px] text-stone-500 mt-0.5 inline-block">Riset UTS Design Thinking</span>
        </div>

        <div class="bg-white border border-stone-200 rounded-2xl p-4 sm:p-5 shadow-xs">
            <span class="text-[10px] sm:text-xs font-bold text-stone-400 uppercase tracking-wider">Rasio Pembayaran</span>
            <p class="text-xl sm:text-2xl font-black text-stone-900 mt-1">{{ $qrisPercentage }}% QRIS</p>
            <div class="w-full bg-stone-100 rounded-full h-2 mt-2 overflow-hidden flex">
                <div class="bg-red-600 h-2" style="width: {{ $qrisPercentage }}%"></div>
                <div class="bg-stone-400 h-2" style="width: {{ $cashPercentage }}%"></div>
            </div>
            <span class="text-[10px] text-stone-500 mt-1 inline-block">{{ $qrisPercentage }}% Digital vs {{ $cashPercentage }}% Tunai</span>
        </div>
    </div>

    <!-- Charts & Top Menus Row (Page 30 SRS) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
        <!-- Monthly Sales Trend Graph (Adjusted to Days in Month) -->
        <div class="lg:col-span-2 bg-white border border-stone-200 rounded-2xl p-4 sm:p-5 shadow-xs space-y-3 sm:space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5">
                <div>
                    <h3 class="font-extrabold text-stone-900 text-xs sm:text-sm">Tren Penjualan Harian ({{ $monthName }})</h3>
                    <p class="text-[11px] sm:text-xs text-stone-400">Total volume transaksi harian selama {{ $daysInMonth }} hari pada bulan ini</p>
                </div>
                <span class="text-[10px] sm:text-xs bg-stone-100 text-stone-700 font-bold px-2.5 py-1 rounded-full self-start sm:self-auto shrink-0">{{ $daysInMonth }} Hari Aktif</span>
            </div>

            <!-- Responsive Chart Container with Horizontal Scroll for 28-31 days -->
            <div class="overflow-x-auto pb-1 scrollbar-none">
                <div class="min-w-[580px] sm:min-w-0 h-48 sm:h-56 flex items-end justify-between gap-1 pt-6 pb-2 px-1 border-b border-stone-100">
                    @foreach($monthlyData as $day => $val)
                        @php 
                            $count = $val['count'];
                            $omzet = $val['omzet'];
                            $isToday = $val['is_today'];
                            $heightPercent = $count > 0 ? max(10, round(($count / $maxMonthCount) * 100)) : 4;
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-1 group relative">
                            <div class="absolute -top-10 opacity-0 group-hover:opacity-100 transition bg-stone-900 text-white text-[10px] py-1 px-2 rounded-lg pointer-events-none whitespace-nowrap z-20 shadow-lg text-center">
                                <p class="font-bold">Tgl {{ $day }} {{ $monthName }}</p>
                                <p class="text-[9px] text-stone-300">{{ $count }} Pesanan • Rp{{ number_format($omzet, 0, ',', '.') }}</p>
                            </div>
                            <div class="w-full rounded-t-sm sm:rounded-t transition-all {{ $isToday ? 'bg-red-600 ring-2 ring-red-300' : ($count > 0 ? 'bg-stone-800 hover:bg-stone-700' : 'bg-stone-200 hover:bg-stone-300') }}" style="height: {{ $heightPercent }}%;"></div>
                            <span class="text-[8px] sm:text-[9px] font-semibold {{ $isToday ? 'text-red-600 font-black' : 'text-stone-400' }} mt-0.5">{{ $day }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-[11px] sm:text-xs text-stone-500 pt-1">
                <span class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 bg-red-600 rounded"></span>
                    <span>Hari Ini (Aktif)</span>
                </span>
                <span class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 bg-stone-800 rounded"></span>
                    <span>Ada Transaksi</span>
                </span>
                <span class="flex items-center space-x-1.5">
                    <span class="w-3 h-3 bg-stone-200 rounded"></span>
                    <span>Kosong</span>
                </span>
            </div>
        </div>

        <!-- Top Menu Terlaris Ranking (Page 30 SRS) -->
        <div class="bg-white border border-stone-200 rounded-2xl p-4 sm:p-5 shadow-xs space-y-3">
            <div>
                <h3 class="font-extrabold text-stone-900 text-xs sm:text-sm">Top Menu Terlaris</h3>
                <p class="text-[11px] sm:text-xs text-stone-400">Menu dengan konversi pesanan tertinggi</p>
            </div>

            <div class="divide-y divide-stone-100 pt-1">
                @foreach($topMenus as $idx => $menu)
                    <div class="py-2.5 flex items-center justify-between gap-2">
                        <div class="flex items-center space-x-2.5 min-w-0">
                            <span class="w-6 h-6 rounded-full {{ $idx === 0 ? 'bg-amber-100 text-amber-700 font-black' : 'bg-stone-100 text-stone-500' }} text-xs flex items-center justify-center font-bold shrink-0">
                                #{{ $idx + 1 }}
                            </span>
                            <div class="min-w-0">
                                <h4 class="text-xs font-bold text-stone-900 truncate">{{ $menu->name }}</h4>
                                <span class="text-[10px] text-stone-400 truncate block">{{ $menu->category->name ?? 'Aneka Menu' }} • {{ $menu->total_sold ?? 0 }} terjual</span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs font-black text-stone-900">Rp{{ number_format($menu->base_price, 0, ',', '.') }}</p>
                            @if($menu->is_spicy)
                                <span class="text-[9px] text-red-600 font-bold">🌶️ Lv.{{ $menu->default_spiciness_level }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Transactions Table -->
    <div class="bg-white border border-stone-200 rounded-2xl p-4 sm:p-5 shadow-xs space-y-3 sm:space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-extrabold text-stone-900 text-xs sm:text-sm">Transaksi Terakhir Masuk</h3>
            <a href="{{ route('cashier.live-orders') }}" class="text-xs font-bold text-red-600 hover:underline">Buka Live POS</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-stone-200 text-stone-400 uppercase font-semibold">
                        <th class="pb-2.5 whitespace-nowrap">No. Pesanan</th>
                        <th class="pb-2.5 whitespace-nowrap">Meja / Tipe</th>
                        <th class="pb-2.5 whitespace-nowrap">Waktu</th>
                        <th class="pb-2.5 whitespace-nowrap">Metode Bayar</th>
                        <th class="pb-2.5 whitespace-nowrap">Status</th>
                        <th class="pb-2.5 text-right whitespace-nowrap">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="py-3 font-mono font-bold text-stone-900 whitespace-nowrap">#{{ $order->order_number }}</td>
                            <td class="py-3 font-semibold text-stone-800 whitespace-nowrap">
                                @if($order->isTakeAway())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 text-[10px] font-bold">
                                        Take Away
                                    </span>
                                @else
                                    <span>Meja {{ $order->table->table_number ?? '00' }}</span>
                                @endif
                            </td>
                            <td class="py-3 text-stone-500 whitespace-nowrap">{{ $order->created_at->format('H:i') }}</td>
                            <td class="py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $order->payment_method === 'qris' ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ $order->payment_method ?? 'tunai' }}
                                </span>
                            </td>
                            <td class="py-3 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ in_array($order->status, ['selesai', 'completed']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ in_array($order->status, ['selesai', 'completed']) ? 'Selesai' : 'Menunggu' }}
                                </span>
                            </td>
                            <td class="py-3 text-right font-black text-stone-900 whitespace-nowrap">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</td>
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
