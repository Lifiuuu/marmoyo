@extends('layouts.admin')

@section('page-title', 'Laporan Rekonsiliasi Kas Harian')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-3 border-b border-stone-200">
        <div>
            <h3 class="text-xs sm:text-sm font-extrabold text-stone-900">Buku Mutasi &amp; Rekonsiliasi Kasir</h3>
            <p class="text-[11px] sm:text-xs text-stone-500">Pencocokan penerimaan kasir harian dengan uang fisik untuk mencegah selisih</p>
        </div>
        <span class="text-xs bg-emerald-50 text-emerald-700 font-bold px-3 py-1.5 rounded-full border border-emerald-200 self-start sm:self-auto shrink-0">
            ✓ Target Selisih: Rp 0 (Klop)
        </span>
    </div>

    <div class="bg-white border border-stone-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 uppercase font-semibold">
                        <th class="p-3 sm:p-3.5 whitespace-nowrap">Tanggal Shift</th>
                        <th class="p-3 sm:p-3.5 whitespace-nowrap">Kasir</th>
                        <th class="p-3 sm:p-3.5 text-right whitespace-nowrap">Modal Awal</th>
                        <th class="p-3 sm:p-3.5 text-right whitespace-nowrap">Penjualan Tunai</th>
                        <th class="p-3 sm:p-3.5 text-right whitespace-nowrap">Penjualan QRIS</th>
                        <th class="p-3 sm:p-3.5 text-right whitespace-nowrap">Fisik Dihitung</th>
                        <th class="p-3 sm:p-3.5 text-right whitespace-nowrap">Selisih</th>
                        <th class="p-3 sm:p-3.5 whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($reconciliations as $rec)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="p-3 sm:p-3.5 font-bold text-stone-900 whitespace-nowrap">{{ $rec->shift_date->format('d/m/Y') }}</td>
                            <td class="p-3 sm:p-3.5 text-stone-700 font-medium whitespace-nowrap">{{ $rec->cashier->name ?? 'Kasir' }}</td>
                            <td class="p-3 sm:p-3.5 text-right font-mono whitespace-nowrap">Rp{{ number_format($rec->starting_cash, 0, ',', '.') }}</td>
                            <td class="p-3 sm:p-3.5 text-right font-mono font-bold text-stone-900 whitespace-nowrap">Rp{{ number_format($rec->total_cash_sales, 0, ',', '.') }}</td>
                            <td class="p-3 sm:p-3.5 text-right font-mono text-sky-700 whitespace-nowrap">Rp{{ number_format($rec->total_qris_sales, 0, ',', '.') }}</td>
                            <td class="p-3 sm:p-3.5 text-right font-mono font-bold text-emerald-700 whitespace-nowrap">Rp{{ number_format($rec->actual_physical_cash, 0, ',', '.') }}</td>
                            <td class="p-3 sm:p-3.5 text-right font-mono font-black whitespace-nowrap {{ $rec->cash_difference == 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $rec->cash_difference == 0 ? 'Rp 0 (Klop)' : 'Rp ' . number_format($rec->cash_difference, 0, ',', '.') }}
                            </td>
                            <td class="p-3 sm:p-3.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $rec->status === 'closed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $rec->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-stone-400">
                                Belum ada riwayat tutup shift kasir yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
