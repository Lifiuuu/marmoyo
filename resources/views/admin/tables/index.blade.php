@extends('layouts.admin')

@section('page-title', 'Pengaturan Meja & QR Code Generator')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-stone-200">
        <div>
            <h3 class="text-xs sm:text-sm font-extrabold text-stone-900">Manajemen Meja &amp; QR Code Meja</h3>
            <p class="text-[11px] sm:text-xs text-stone-500">QR Code bersifat <b>statis &amp; permanen</b> seumur hidup meja. Sekali dicetak dan ditempel di meja fisik, tidak akan pernah berubah atau kedaluwarsa.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto shrink-0">
            <a href="{{ route('admin.tables.print-sheet') }}" target="_blank" class="bg-stone-800 hover:bg-stone-900 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow transition flex items-center space-x-1.5 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Cetak Stiker QR</span>
            </a>
            <button onclick="document.getElementById('add-table-modal').classList.remove('hidden')" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow transition active:scale-95">
                + Tambah Meja
            </button>
        </div>
    </div>

    <!-- Table Grid (Page 31 SRS) - Responsive for Phone, Tablet & Desktop -->
    <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-4">
        @foreach($tables as $table)
            @php $qrUrl = url("/scan?table={$table->table_number}&token={$table->qr_token}"); @endphp
            <div class="bg-white border border-stone-200 rounded-2xl p-4 shadow-xs space-y-3 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between border-b border-stone-100 pb-2">
                        <span class="text-sm font-black text-stone-900">Meja {{ $table->table_number }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $table->status === 'available' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                            {{ $table->status === 'available' ? 'Kosong' : 'Terisi' }}
                        </span>
                    </div>

                    <!-- Visual QR Code -->
                    <div class="bg-stone-50 p-3 rounded-xl border border-stone-200 text-center my-3">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($qrUrl) }}" alt="QR Meja {{ $table->table_number }}" class="w-28 h-28 mx-auto rounded shadow-xs">
                        <div class="mt-2 flex items-center justify-center space-x-1 text-[10px] text-emerald-700 bg-emerald-50 py-1 px-2 rounded-md font-bold">
                            <span>QR Permanen &amp; Siap Cetak</span>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <a href="{{ $qrUrl }}" target="_blank" class="block w-full bg-stone-100 hover:bg-stone-200 text-stone-800 text-center py-2 rounded-xl font-bold transition">
                        Uji Buka Menu Meja
                    </a>
                    
                    <form action="{{ route('admin.tables.destroy', $table->id) }}" method="POST" onsubmit="return confirm('Hapus Meja {{ $table->table_number }} dari sistem?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-stone-400 hover:text-red-600 text-[11px] font-medium py-1 text-center transition">
                            Hapus Meja
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal Tambah Meja (Responsive for Touch) -->
<div id="add-table-modal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-xs w-full p-5 sm:p-6 shadow-2xl space-y-3.5">
        <h4 class="font-extrabold text-stone-900 text-sm">Tambah Nomor Meja Baru</h4>
        <p class="text-[11px] text-stone-500">QR Code statis permanen akan langsung dibuat secara otomatis untuk nomor meja ini.</p>
        <form action="{{ route('admin.tables.store') }}" method="POST" class="space-y-3 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-stone-700 mb-1">Nomor Meja</label>
                <input type="text" name="table_number" required placeholder="Contoh: 13" class="w-full bg-stone-50 border border-stone-200 rounded-xl p-2.5 outline-none focus:border-red-500 font-bold">
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('add-table-modal').classList.add('hidden')" class="px-3.5 py-2 rounded-xl text-stone-500 hover:bg-stone-100 font-semibold transition">Batal</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-xl shadow active:scale-95 transition">Simpan &amp; Buat QR</button>
            </div>
        </form>
    </div>
</div>
@endsection
