@extends('layouts.admin')

@section('page-title', 'Edit Menu - ' . $menu->name)

@section('content')
<div class="max-w-xl mx-auto bg-white border border-stone-200 rounded-2xl sm:rounded-3xl p-4 sm:p-6 shadow-xs space-y-4 sm:space-y-5">
    <div class="pb-2.5 sm:pb-3 border-b border-stone-100 flex items-center justify-between">
        <div>
            <h3 class="font-extrabold text-stone-900 text-xs sm:text-sm">Edit Data Menu</h3>
            <p class="text-[11px] sm:text-xs text-stone-400">Perbarui informasi harga, kategori, foto, dan ketersediaan menu</p>
        </div>
        <a href="{{ route('admin.menus.index') }}" class="text-xs font-bold text-stone-500 hover:text-stone-800 transition">Batal &amp; Kembali</a>
    </div>

    <form action="{{ route('admin.menus.update', $menu->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3.5 sm:space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div class="space-y-1">
            <label class="block font-bold text-stone-700">Kategori Menu</label>
            <select name="category_id" required class="w-full bg-stone-50 border border-stone-200 rounded-xl p-2.5 outline-none focus:border-red-500 font-semibold">
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ $menu->category_id == $cat->id ? 'selected' : '' }}>
                        {{ $cat->icon }} {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="space-y-1">
            <label class="block font-bold text-stone-700">Nama Menu</label>
            <input type="text" name="name" value="{{ old('name', $menu->name) }}" required placeholder="Contoh: Mie Mala Pedas Nampol" class="w-full bg-stone-50 border border-stone-200 rounded-xl p-2.5 font-bold outline-none focus:border-red-500">
        </div>

        <div class="space-y-1">
            <label class="block font-bold text-stone-700">Harga Pokok (Rp)</label>
            <input type="number" name="base_price" value="{{ old('base_price', (int) $menu->base_price) }}" required min="0" placeholder="15000" class="w-full bg-stone-50 border border-stone-200 rounded-xl p-2.5 font-mono font-bold outline-none focus:border-red-500">
        </div>

        <div class="space-y-1">
            <label class="block font-bold text-stone-700">Deskripsi Cita Rasa</label>
            <textarea name="description" rows="2" placeholder="Jelaskan aroma rempah, tingkat gurih, dan tekstur menu..." class="w-full bg-stone-50 border border-stone-200 rounded-xl p-2.5 outline-none focus:border-red-500 resize-none">{{ old('description', $menu->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-3 bg-stone-50 rounded-xl border border-stone-200">
            <label class="flex items-center space-x-2 cursor-pointer">
                <input type="checkbox" name="is_spicy" value="1" {{ $menu->is_spicy ? 'checked' : '' }} class="rounded text-red-600 focus:ring-red-500">
                <span class="font-bold text-stone-800">Menu Bercita Rasa Pedas</span>
            </label>

            <div class="space-y-1">
                <label class="block font-bold text-stone-700">Level Pedas Default</label>
                <select name="default_spiciness_level" class="w-full bg-white border border-stone-200 rounded-lg p-1.5 font-medium">
                    <option value="0" {{ $menu->default_spiciness_level == 0 ? 'selected' : '' }}>Level 0 (Tidak Pedas)</option>
                    <option value="1" {{ $menu->default_spiciness_level == 1 ? 'selected' : '' }}>Level 1 (Santai)</option>
                    <option value="2" {{ $menu->default_spiciness_level == 2 ? 'selected' : '' }}>Level 2 (Sedang)</option>
                    <option value="3" {{ $menu->default_spiciness_level == 3 ? 'selected' : '' }}>Level 3 (Pedas Nampol)</option>
                    <option value="5" {{ $menu->default_spiciness_level == 5 ? 'selected' : '' }}>Level 5 (Super Pedas)</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="p-3 bg-stone-50 rounded-xl border border-stone-200 flex items-center justify-between">
                <div>
                    <span class="font-bold text-stone-900 flex items-center space-x-1">
                        <span>Status Bestseller</span>
                    </span>
                    <p class="text-[10px] text-stone-500">Dihitung otomatis dari volume penjualan riil pelanggan</p>
                </div>
                @php $soldCount = $menu->orderItems()->sum('quantity'); @endphp
                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $menu->is_bestseller ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-stone-200 text-stone-600' }}">
                    {{ $soldCount }} Porsi
                </span>
            </div>

            <label class="flex items-center space-x-2 cursor-pointer p-3 bg-emerald-50 rounded-xl border border-emerald-200">
                <input type="checkbox" name="is_available" value="1" {{ $menu->is_available ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                <div>
                    <span class="font-bold text-emerald-900">Stok Tersedia</span>
                    <p class="text-[10px] text-emerald-700">Tampil di pemesanan pelanggan</p>
                </div>
            </label>
        </div>

        <!-- Foto Menu Saat Ini & Ganti Foto -->
        <div class="space-y-2 p-3 bg-stone-50 rounded-xl border border-stone-200">
            <label class="block font-bold text-stone-700">Foto Menu (Supabase Storage)</label>
            @if($menu->image_url)
                <div class="flex items-center space-x-3 pb-2 border-b border-stone-200">
                    <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}" class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl object-cover border border-stone-200 shadow-2xs shrink-0">
                    <div>
                        <p class="text-[11px] font-bold text-stone-800">Foto Saat Ini Aktif</p>
                        <span class="text-[10px] text-emerald-700 bg-emerald-100 font-bold px-1.5 py-0.5 rounded">Tersimpan di Supabase Cloud</span>
                    </div>
                </div>
            @endif

            <div class="pt-1">
                <span class="block text-[11px] text-stone-500 mb-1">Unggah Foto Baru (Akan otomatis menimpa di bucket `marmoyo-assets`):</span>
                <input type="file" name="image" accept="image/*" class="w-full text-stone-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
            </div>
        </div>

        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3">
            <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-extrabold py-3 rounded-xl shadow transition active:scale-95 text-center">
                Simpan Perubahan Menu ✓
            </button>
            <a href="{{ route('admin.menus.index') }}" class="px-5 py-3 rounded-xl border border-stone-300 font-bold text-stone-600 hover:bg-stone-100 text-center transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
