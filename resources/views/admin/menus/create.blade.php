@extends('layouts.admin')

@section('page-title', 'Tambah Menu Baru')

@section('content')
<div class="max-w-xl mx-auto bg-white border border-stone-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-xs space-y-4 sm:space-y-5">
    <div class="pb-2.5 sm:pb-3 border-b border-stone-100 flex items-center justify-between">
        <div>
            <h3 class="font-extrabold text-stone-900 text-xs sm:text-sm">Formulir Menu Baru</h3>
            <p class="text-[11px] sm:text-xs text-stone-400">Tambahkan item makanan, minuman, atau dimsum ke katalog Marmoyo</p>
        </div>
        <a href="{{ route('admin.menus.index') }}" class="text-xs font-bold text-stone-500 hover:text-stone-800 transition">&larr; Kembali</a>
    </div>

    <form action="{{ route('admin.menus.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5 sm:space-y-4 text-xs">
        @csrf

        <div class="space-y-1">
            <label class="block font-bold text-stone-700">Kategori Menu</label>
            <select name="category_id" required class="w-full bg-stone-50 border border-stone-200 rounded-xl p-2.5 outline-none focus:border-red-500">
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->icon }} {{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-1">
            <label class="block font-bold text-stone-700">Nama Menu</label>
            <input type="text" name="name" required placeholder="Contoh: Mie Mala Spesial Jamur" class="w-full bg-stone-50 border border-stone-200 rounded-xl p-2.5 outline-none focus:border-red-500">
        </div>

        <div class="space-y-1">
            <label class="block font-bold text-stone-700">Harga Pokok (Rp)</label>
            <input type="number" name="base_price" required min="0" placeholder="15000" class="w-full bg-stone-50 border border-stone-200 rounded-xl p-2.5 font-mono outline-none focus:border-red-500">
        </div>

        <div class="space-y-1">
            <label class="block font-bold text-stone-700">Deskripsi Cita Rasa</label>
            <textarea name="description" rows="2" placeholder="Jelaskan aroma rempah, tingkat gurih, dan tekstur menu..." class="w-full bg-stone-50 border border-stone-200 rounded-xl p-2.5 outline-none focus:border-red-500 resize-none"></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-stone-50 rounded-xl border border-stone-200">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" name="is_spicy" value="1" class="rounded text-red-600 focus:ring-red-500">
                <span class="font-bold text-stone-800">Menu Bercita Rasa Pedas 🌶️</span>
            </label>

            <div class="space-y-1">
                <label class="block font-bold text-stone-700">Level Pedas Default</label>
                <select name="default_spiciness_level" class="w-full bg-white border border-stone-200 rounded-lg p-1.5">
                    <option value="0">Level 0 (Tidak Pedas)</option>
                    <option value="1">Level 1 (Santai)</option>
                    <option value="2">Level 2 (Sedang)</option>
                    <option value="3" selected>Level 3 (Pedas Nampol)</option>
                    <option value="5">Level 5 (Super Pedas)</option>
                </select>
            </div>
        </div>

        <div class="p-3 bg-stone-50 rounded-xl border border-stone-200 flex items-center space-x-2.5">
            <span class="text-base shrink-0">📊</span>
            <div>
                <span class="font-bold text-stone-900 block">Perhitungan Bestseller Otomatis</span>
                <p class="text-[10px] text-stone-500">Menu baru akan otomatis dipromosikan sebagai Bestseller oleh sistem AI setelah mencapai volume transaksi tertinggi.</p>
            </div>
        </div>

        <div class="space-y-1">
            <label class="block font-bold text-stone-700">Foto Menu (Opsional / Siap Supabase Storage)</label>
            <input type="file" name="image" accept="image/*" class="w-full text-stone-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
        </div>

        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-extrabold py-3 rounded-xl shadow transition active:scale-95">
            Simpan &amp; Publikasikan ke Menu Pelanggan &rarr;
        </button>
    </form>
</div>
@endsection
