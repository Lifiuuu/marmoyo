@extends('layouts.cashier')

@section('title', 'Rekonsiliasi Kas Harian - Marmoyo Smart-Dine')

@section('content')
<div class="max-w-2xl mx-auto space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-stone-800">
        <div>
            <h2 class="text-sm sm:text-base font-extrabold text-white">Rekonsiliasi Kas &amp; Tutup Shift</h2>
            <p class="text-[11px] sm:text-xs text-stone-400">Pencocokan Uang Fisik Kasir dengan Rekap Sistem (Target Selisih: Rp 0)</p>
        </div>
        <span class="text-xs font-mono bg-stone-800 text-stone-300 px-3 py-1 rounded-xl self-start sm:self-auto">
            {{ now()->translatedFormat('d F Y') }}
        </span>
    </div>

    <!-- Ringkasan Penjualan Shift Berjalan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-4">
            <p class="text-[10px] sm:text-xs font-bold text-stone-400 uppercase">Total Penjualan Tunai (Sistem)</p>
            <p class="text-xl sm:text-2xl font-black text-emerald-400 mt-1">Rp{{ number_format($totalCashSales, 0, ',', '.') }}</p>
            <span class="text-[10px] text-stone-500">Akumulasi uang tunai valid dari pesanan meja &amp; take away</span>
        </div>

        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-4">
            <p class="text-[10px] sm:text-xs font-bold text-stone-400 uppercase">Total Penjualan QRIS (Sistem)</p>
            <p class="text-xl sm:text-2xl font-black text-sky-400 mt-1">Rp{{ number_format($totalQrisSales, 0, ',', '.') }}</p>
            <span class="text-[10px] text-stone-500">Otomatis masuk ke rekening bank kedai</span>
        </div>
    </div>

    @if($existingReconciliation && $existingReconciliation->status === 'closed')
        <div class="bg-emerald-950/70 border border-emerald-800 p-4 sm:p-5 rounded-2xl space-y-3">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                <h3 class="font-extrabold text-emerald-300 text-sm">Shift Hari Ini Telah Ditutup</h3>
            </div>
            <p class="text-xs text-emerald-200">
                Shift ditutup pada: {{ $existingReconciliation->closed_at->format('d/m/Y H:i') }} WIB oleh {{ $existingReconciliation->cashier->name ?? 'Kasir' }}
            </p>
            <div class="bg-stone-950 p-3.5 rounded-xl border border-stone-800 grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs text-center">
                <div class="p-1">
                    <span class="text-stone-400 text-[10px] block">Modal Awal:</span>
                    <p class="font-mono font-bold text-white text-sm">Rp{{ number_format($existingReconciliation->starting_cash, 0, ',', '.') }}</p>
                </div>
                <div class="p-1 border-t sm:border-t-0 sm:border-l border-stone-800 pt-2 sm:pt-1">
                    <span class="text-stone-400 text-[10px] block">Uang Fisik Dihitung:</span>
                    <p class="font-mono font-bold text-emerald-400 text-sm">Rp{{ number_format($existingReconciliation->actual_physical_cash, 0, ',', '.') }}</p>
                </div>
                <div class="p-1 border-t sm:border-t-0 sm:border-l border-stone-800 pt-2 sm:pt-1">
                    <span class="text-stone-400 text-[10px] block">Selisih Kas:</span>
                    <p class="font-mono font-bold text-sm {{ $existingReconciliation->cash_difference == 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        Rp{{ number_format($existingReconciliation->cash_difference, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Formulir Input Hitung Fisik Kasir -->
        <form action="{{ route('cashier.settlement.close') }}" method="POST" class="bg-stone-900 border border-stone-800 rounded-3xl p-4 sm:p-6 shadow-xl space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-300">Modal Awal Laci Kasir / Kembalian (Rp)</label>
                <input type="number" name="starting_cash" id="starting-cash" oninput="calculateAuditDifference()" value="200000" min="0" required class="w-full bg-stone-950 border border-stone-700 rounded-xl px-4 py-2.5 font-mono text-sm text-white outline-none focus:border-red-500">
                <p class="text-[10px] text-stone-500">Uang receh/modal yang ada di laci sebelum kedai dibuka.</p>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-300">Total Uang Fisik Hasil Hitung Manual (Rp)</label>
                <input type="number" name="actual_physical_cash" id="actual-cash" oninput="calculateAuditDifference()" min="0" required placeholder="Contoh: 1620000" class="w-full bg-stone-950 border border-stone-700 rounded-xl px-4 py-2.5 font-mono text-base font-bold text-white outline-none focus:border-emerald-500">
                <p class="text-[10px] text-stone-500">Hitung seluruh lembaran &amp; koin fisik yang ada di laci saat ini.</p>
            </div>

            <!-- Panel Deteksi Selisih Real-time -->
            <div class="bg-stone-950 p-3.5 sm:p-4 rounded-2xl border border-stone-800 space-y-2">
                <div class="flex justify-between text-xs text-stone-400">
                    <span>Target Uang Fisik Seharusnya:</span>
                    <span id="expected-cash-display" class="font-mono text-white font-bold">Rp 0</span>
                </div>
                <div class="flex justify-between items-baseline pt-2 border-t border-stone-800">
                    <span class="text-xs font-bold text-stone-300">Status Selisih (Selaras / Tidak):</span>
                    <span id="diff-status-display" class="text-sm sm:text-base font-black text-stone-400">Rp 0</span>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-300">Catatan Penutupan Kasir (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Catatan shift atau keterangan jika terdapat selisih fisik..." class="w-full bg-stone-950 border border-stone-700 rounded-xl p-3 text-xs text-white outline-none focus:border-red-500 resize-none"></textarea>
            </div>

            <button type="submit" class="w-full bg-red-600 hover:bg-red-500 text-white text-xs font-black py-3 sm:py-3.5 rounded-xl transition shadow-lg active:scale-95 flex items-center justify-center space-x-2">
                <span>🔒 Tutup Shift &amp; Rekonsiliasi Kas</span>
            </button>
        </form>
    @endif
</div>

<script>
    const totalCashSales = {{ (float) $totalCashSales }};

    function calculateAuditDifference() {
        const startingCash = parseFloat(document.getElementById('starting-cash').value || 0);
        const actualCash = parseFloat(document.getElementById('actual-cash').value || 0);

        const expectedCash = startingCash + totalCashSales;
        const diff = actualCash - expectedCash;

        document.getElementById('expected-cash-display').innerText = 'Rp ' + expectedCash.toLocaleString('id-ID');

        const diffDisplay = document.getElementById('diff-status-display');
        if (diff === 0) {
            diffDisplay.innerText = 'Rp 0 (Sempurna / Klop)';
            diffDisplay.className = 'text-sm sm:text-base font-black text-emerald-400';
        } else if (diff > 0) {
            diffDisplay.innerText = '+Rp ' + diff.toLocaleString('id-ID') + ' (Kelebihan Fisik)';
            diffDisplay.className = 'text-sm sm:text-base font-black text-sky-400';
        } else {
            diffDisplay.innerText = '-Rp ' + Math.abs(diff).toLocaleString('id-ID') + ' (Kurang Fisik)';
            diffDisplay.className = 'text-sm sm:text-base font-black text-rose-400';
        }
    }

    // Auto-calculate on initial page render
    if (document.getElementById('starting-cash')) {
        calculateAuditDifference();
    }
</script>
@endsection
