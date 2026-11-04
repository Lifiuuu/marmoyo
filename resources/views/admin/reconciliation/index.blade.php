@extends('layouts.admin')

@section('page-title', 'Laporan Rekonsiliasi Kas Harian')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between pb-3 border-b border-stone-200">
        <div>
            <h3 class="text-sm font-extrabold text-stone-900">Buku Mutasi & Rekonsiliasi Kasir</h3>
            <p class="text-xs text-stone-500">Pencocokan penerimaan kasir harian dengan uang fisik untuk mencegah selisih</p>
        </div>
        <span class="text-xs bg-emerald-50 text-emerald-700 font-bold px-3 py-1.5 rounded-full border border-emerald-200">
            ✓ Target Selisih: Rp 0 (Klop)
        </span>
    </div>

    <div class="bg-white border border-stone-200 rounded-2xl shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-500 uppercase font-semibold">
                        <th class="p-3.5">Tanggal Shift</th>
                        <th class="p-3.5">Kasir</th>
                        <th class="p-3.5 text-right">Modal Awal</th>
                        <th class="p-3.5 text-right">Penjualan Tunai</th>
                        <th class="p-3.5 text-right">Penjualan QRIS</th>
                        <th class="p-3.5 text-right">Fisik Dihitung</th>
                        <th class="p-3.5 text-right">Selisih</th>
                        <th class="p-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($reconciliations as $rec)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="p-3.5 font-bold text-stone-900">{{ $rec->shift_date->format('d/m/Y') }}</td>
                            <td class="p-3.5 text-stone-700 font-medium">{{ $rec->cashier->name ?? 'Kasir' }}</td>
                            <td class="p-3.5 text-right font-mono">Rp{{ number_format($rec->starting_cash, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono font-bold text-stone-900">Rp{{ number_format($rec->total_cash_sales, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono text-sky-700">Rp{{ number_format($rec->total_qris_sales, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono font-bold text-emerald-700">Rp{{ number_format($rec->actual_physical_cash, 0, ',', '.') }}</td>
                            <td class="p-3.5 text-right font-mono font-black {{ $rec->cash_difference == 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $rec->cash_difference == 0 ? 'Rp 0 (Klop)' : 'Rp ' . number_format($rec->cash_difference, 0, ',', '.') }}
                            </td>
                            <td class="p-3.5">
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

