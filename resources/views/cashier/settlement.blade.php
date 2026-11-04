@extends('layouts.cashier')

@section('title', 'Rekonsiliasi Kas Harian - Kedai Mie Marmoyo')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between pb-3 border-b border-stone-800">
        <div>
            <h2 class="text-base font-extrabold text-white">Rekonsiliasi Kas & Tutup Shift</h2>
            <p class="text-xs text-stone-400">Pencocokan Uang Fisik Kasir dengan Rekap Sistem (Target Selisih: Rp 0)</p>
        </div>
        <span class="text-xs font-mono bg-stone-800 text-stone-300 px-3 py-1 rounded-lg">
            {{ now()->translatedFormat('d F Y') }}
        </span>
    </div>

    <!-- Ringkasan Penjualan Shift Berjalan -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-4">
            <p class="text-xs font-bold text-stone-400 uppercase">Total Penjualan Tunai (Sistem)</p>
            <p class="text-xl font-black text-emerald-400 mt-1">Rp{{ number_format($totalCashSales, 0, ',', '.') }}</p>
            <span class="text-[10px] text-stone-500">Akumulasi uang tunai valid dari pesanan meja</span>
        </div>

        <div class="bg-stone-900 border border-stone-800 rounded-2xl p-4">
            <p class="text-xs font-bold text-stone-400 uppercase">Total Penjualan QRIS (Sistem)</p>
            <p class="text-xl font-black text-sky-400 mt-1">Rp{{ number_format($totalQrisSales, 0, ',', '.') }}</p>
            <span class="text-[10px] text-stone-500">Otomatis masuk ke rekening bank kedai</span>
        </div>
    </div>

    @if($existingReconciliation && $existingReconciliation->status === 'closed')
        <div class="bg-emerald-950/70 border border-emerald-800 p-5 rounded-2xl space-y-3">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
                <h3 class="font-extrabold text-emerald-300 text-sm">Shift Hari Ini Telah Ditutup</h3>
            </div>
            <p class="text-xs text-emerald-200">
                Shift ditutup pada: {{ $existingReconciliation->closed_at->format('d/m/Y H:i') }} WIB oleh {{ $existingReconciliation->cashier->name ?? 'Kasir' }}
            </p>
            <div class="bg-stone-950 p-3 rounded-xl border border-stone-800 grid grid-cols-3 gap-2 text-xs text-center">
                <div>
                    <span class="text-stone-400 text-[10px]">Modal Awal:</span>
                    <p class="font-mono font-bold text-white">Rp{{ number_format($existingReconciliation->starting_cash, 0, ',', '.') }}</p>
                </div>
                <div>
                    <span class="text-stone-400 text-[10px]">Uang Fisik Dihitung:</span>
                    <p class="font-mono font-bold text-emerald-400">Rp{{ number_format($existingReconciliation->actual_physical_cash, 0, ',', '.') }}</p>
                </div>
                <div>
                    <span class="text-stone-400 text-[10px]">Selisih Kas:</span>
                    <p class="font-mono font-bold {{ $existingReconciliation->cash_difference == 0 ? 'text-emerald-400' : 'text-rose-400' }}">
                        Rp{{ number_format($existingReconciliation->cash_difference, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Formulir Input Hitung Fisik Kasir -->
        <form action="{{ route('cashier.settlement.close') }}" method="POST" class="bg-stone-900 border border-stone-800 rounded-3xl p-6 shadow-xl space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-300">Modal Awal Laci Kasir / Kembalian (Rp)</label>
                <input type="number" name="starting_cash" id="starting-cash" oninput="calculateAuditDifference()" value="200000" min="0" required class="w-full bg-stone-950 border border-stone-700 rounded-xl px-4 py-2.5 font-mono text-sm text-white outline-none focus:border-red-500">
                <p class="text-[10px] text-stone-500">Uang receh/modal yang ada di laci sebelum kedai dibuka.</p>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-300">Total Uang Fisik Hasil Hitung Manual (Rp)</label>
                <input type="number" name="actual_physical_cash" id="actual-cash" oninput="calculateAuditDifference()" min="0" required placeholder="Contoh: 1620000" class="w-full bg-stone-950 border border-stone-700 rounded-xl px-4 py-2.5 font-mono text-base font-bold text-white outline-none focus:border-emerald-500">
                <p class="text-[10px] text-stone-500">Hitung seluruh lembaran & koin fisik yang ada di laci saat ini.</p>
            </div>

            <!-- Panel Deteksi Selisih Real-time -->
            <div class="bg-stone-950 p-4 rounded-2xl border border-stone-800 space-y-2">
                <div class="flex justify-between text-xs text-stone-400">
                    <span>Target Uang Fisik Seharusnya:</span>
                    <span id="expected-cash-display" class="font-mono text-white font-bold">Rp 0</span>
                </div>
                <div class="flex justify-between items-baseline pt-2 border-t border-stone-800">
                    <span class="text-xs font-bold text-stone-300">Status Selisih (Selaras / Tidak):</span>
                    <span id="diff-status-display" class="text-base font-black text-stone-400">Rp 0</span>
                </div>
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-stone-300">Catatan Penutupan Kasir (Opsional)</label>
                <textarea name="notes" rows="2" placeholder="Catatan shift atau keterangan jika terdapat selisih fisik..." class="w-full bg-stone-950 border border-stone-700 rounded-xl p-3 text-xs text-white outline-none focus:border-red-500 resize-none"></textarea>
            </div>

            <button type="submit" class="w-full bg-red-600 hover:bg-red-500 text-white text-xs font-black py-3.5 rounded-xl transition shadow-lg active:scale-95 flex items-center justify-center space-x-2">
                <span>🔒 Tutup Shift & Rekonsiliasi Kas</span>
            </button>
        </form>
    @endif
</div>

<script>
    const totalCashSales = {{ (float) $totalCashSales }};

    function calculateAuditDifference() {
        const starting = parseFloat(document.getElementById('starting-cash').value || 0);
        const actual = parseFloat(document.getElementById('actual-cash').value || 0);
        const expected = starting + totalCashSales;

        document.getElementById('expected-cash-display').innerText = 'Rp ' + expected.toLocaleString('id-ID');

        const diff = actual - expected;
        const diffDisplay = document.getElementById('diff-status-display');

        if (diff === 0) {
            diffDisplay.innerText = '✓ Klop Sempurna (Rp 0)';
            diffDisplay.className = 'text-base font-black text-emerald-400';
        } else if (diff > 0) {
            diffDisplay.innerText = '+Rp ' + diff.toLocaleString('id-ID') + ' (Kelebihan Uang)';
            diffDisplay.className = 'text-base font-black text-amber-400';
        } else {
            diffDisplay.innerText = '-Rp ' + Math.abs(diff).toLocaleString('id-ID') + ' (Kurang / Selisih)';
            diffDisplay.className = 'text-base font-black text-rose-400';
        }
    }

    // Run initial calculation
    calculateAuditDifference();
</script>
@endsection

