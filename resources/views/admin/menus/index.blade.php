@extends('layouts.admin')

@section('page-title', 'Katalog Menu & Kontrol Stok Bahan')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-stone-200">
        <div>
            <h3 class="text-xs sm:text-sm font-extrabold text-stone-900">Katalog Menu &amp; AI Smart Upsell</h3>
            <p class="text-[11px] sm:text-xs text-stone-500">Kelola menu, harga, tingkat kepedasan, dan sakelar ketersediaan stok</p>
        </div>
        <a href="{{ route('admin.menus.create') }}" class="self-start sm:self-auto bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3.5 py-2 rounded-xl shadow transition flex items-center space-x-1.5 active:scale-95">
            <span>+ Tambah Menu Baru</span>
        </a>
    </div>

    @foreach($categories as $category)
        <div class="bg-white border border-stone-200 rounded-2xl p-3.5 sm:p-5 shadow-xs space-y-3 sm:space-y-4">
            <div class="flex items-center justify-between border-b border-stone-100 pb-2.5">
                <div class="flex items-center space-x-2">
                    <span class="text-base sm:text-lg">{{ $category->icon }}</span>
                    <h4 class="text-xs sm:text-sm font-extrabold text-stone-900">{{ $category->name }}</h4>
                    <span class="text-[10px] sm:text-xs bg-stone-100 text-stone-600 font-bold px-2 py-0.5 rounded-full">{{ $category->menus->count() }} Menu</span>
                </div>
            </div>

            <!-- Mobile Card Layout (< 640px) -->
            <div class="grid grid-cols-1 gap-2.5 sm:hidden">
                @forelse($category->menus as $menu)
                    <div class="bg-stone-50 border border-stone-200 rounded-xl p-3 space-y-2.5">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center space-x-2.5 min-w-0">
                                <img src="{{ $menu->image_url ?? 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=100' }}" alt="{{ $menu->name }}" class="w-12 h-12 rounded-lg object-cover border border-stone-200 shrink-0">
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-900 text-xs truncate">{{ $menu->name }}</p>
                                    <p class="font-black text-red-600 text-xs">Rp{{ number_format($menu->base_price, 0, ',', '.') }}</p>
                                    <div class="flex items-center space-x-1 mt-0.5">
                                        @if($menu->is_spicy)
                                            <span class="text-[9px] bg-red-50 text-red-700 font-bold px-1.5 py-0.5 rounded">🌶️ Lv.{{ $menu->default_spiciness_level }}</span>
                                        @endif
                                        @if($menu->is_bestseller)
                                            <span class="text-[8px] bg-amber-100 text-amber-800 font-bold px-1 rounded uppercase">Bestseller</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions & Toggle on Mobile -->
                        <div class="pt-2 border-t border-stone-200/80 flex items-center justify-between gap-2">
                            <form action="{{ route('admin.menus.toggle-stock', $menu->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center space-x-1.5 px-2.5 py-1.5 rounded-full text-[10px] font-extrabold transition {{ $menu->is_available ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $menu->is_available ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span>{{ $menu->is_available ? 'Tersedia' : 'Habis' }}</span>
                                </button>
                            </form>

                            <div class="flex items-center space-x-1.5">
                                <a href="{{ route('admin.menus.edit', $menu->id) }}" class="inline-flex items-center space-x-1 bg-white border border-stone-200 text-stone-700 font-bold text-[11px] px-2.5 py-1.5 rounded-lg hover:bg-stone-100 transition shadow-2xs">
                                    <span>Edit</span>
                                </a>

                                <form action="{{ route('admin.menus.destroy', $menu->id) }}" method="POST" onsubmit="return confirm('Hapus menu ini dari katalog?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center text-rose-600 font-bold text-[11px] px-2 py-1.5 rounded-lg hover:bg-rose-50 transition">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-4 text-center text-stone-400 text-xs">Belum ada menu di kategori ini.</div>
                @endforelse
            </div>

            <!-- Desktop & Tablet Table View (>= 640px) -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-stone-100 text-stone-400 uppercase font-semibold">
                            <th class="pb-2">Menu</th>
                            <th class="pb-2">Harga</th>
                            <th class="pb-2">Level Pedas</th>
                            <th class="pb-2">Status Stok (Toggle 1-Klik)</th>
                            <th class="pb-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($category->menus as $menu)
                            <tr class="hover:bg-stone-50 transition">
                                <td class="py-3">
                                    <div class="flex items-center space-x-3">
                                        <img src="{{ $menu->image_url ?? 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=100' }}" alt="{{ $menu->name }}" class="w-10 h-10 rounded-lg object-cover shrink-0">
                                        <div>
                                            <p class="font-bold text-stone-900">{{ $menu->name }}</p>
                                            @if($menu->is_bestseller)
                                                <span class="text-[9px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded uppercase">Bestseller</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 font-black text-stone-900">
                                    Rp{{ number_format($menu->base_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3">
                                    @if($menu->is_spicy)
                                        <span class="bg-red-50 text-red-700 font-bold px-2 py-0.5 rounded text-[10px]">
                                            🌶️ Level {{ $menu->default_spiciness_level }}
                                        </span>
                                    @else
                                        <span class="text-stone-400 text-[10px]">Tidak Pedas</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <!-- Stock Availability Toggle (Page 30 SRS) -->
                                    <form action="{{ route('admin.menus.toggle-stock', $menu->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="flex items-center space-x-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold transition {{ $menu->is_available ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}">
                                            <span class="w-2 h-2 rounded-full {{ $menu->is_available ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                            <span>{{ $menu->is_available ? 'Tersedia' : 'Habis (Sold Out)' }}</span>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 text-right space-x-1.5 whitespace-nowrap">
                                    <a href="{{ route('admin.menus.edit', $menu->id) }}" class="inline-flex items-center space-x-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs px-2.5 py-1.5 rounded-lg transition">
                                        <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>Edit</span>
                                    </a>

                                    <form action="{{ route('admin.menus.destroy', $menu->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus menu ini dari katalog?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center text-stone-400 hover:text-rose-600 font-bold text-xs px-2 py-1.5 rounded-lg hover:bg-rose-50 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-stone-400">Belum ada menu di kategori ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection
