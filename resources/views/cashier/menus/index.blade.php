@extends('layouts.cashier')

@section('title', 'Kontrol Stok Menu - Kasir POS')

@section('content')
<div class="space-y-6">
    <!-- Header & Info Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-stone-800">
        <div>
            <h2 class="text-base font-extrabold text-white flex items-center space-x-2">
                <span>Kontrol Ketersediaan Stok Menu</span>
                <span class="text-xs bg-stone-800 text-stone-300 px-2.5 py-0.5 rounded-full font-bold">1-Klik Sakelar</span>
            </h2>
            <p class="text-xs text-stone-400 mt-0.5">Ubah status menu menjadi <b>Habis (Sold Out)</b> saat stok menu habis agar pelanggan tidak dapat memesannya.</p>
        </div>
        <div class="bg-amber-950/60 border border-amber-800/80 rounded-xl px-3.5 py-2 text-[11px] text-amber-300">
            Kasir hanya berwenang mengubah ketersediaan stok. Tambah/Edit menu hanya oleh Owner.
        </div>
    </div>

    <!-- Category Sections -->
    <div class="space-y-6">
        @foreach($categories as $category)
            <div class="bg-stone-900 border border-stone-800 rounded-2xl p-5 shadow-lg space-y-4">
                <div class="flex items-center justify-between border-b border-stone-800 pb-3">
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">{{ $category->icon }}</span>
                        <h3 class="text-sm font-extrabold text-white">{{ $category->name }}</h3>
                        <span class="text-xs bg-stone-800 text-stone-400 font-bold px-2 py-0.5 rounded-full">{{ $category->menus->count() }} Menu</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-stone-800 text-stone-400 uppercase font-semibold">
                                <th class="pb-2.5">Menu</th>
                                <th class="pb-2.5">Harga</th>
                                <th class="pb-2.5">Cita Rasa</th>
                                <th class="pb-2.5 text-right">Status Ketersediaan (Toggle Kasir)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-800/60">
                            @forelse($category->menus as $menu)
                                <tr class="hover:bg-stone-800/30 transition">
                                    <td class="py-3">
                                        <div class="flex items-center space-x-3">
                                            <img src="{{ $menu->image_url ?? 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=100' }}" alt="{{ $menu->name }}" class="w-11 h-11 rounded-lg object-cover border border-stone-800 shrink-0">
                                            <div>
                                                <p class="font-bold text-white text-xs">{{ $menu->name }}</p>
                                                @if($menu->is_bestseller)
                                                    <span class="text-[9px] bg-amber-950 text-amber-400 border border-amber-800/60 font-bold px-1.5 py-0.5 rounded uppercase">Bestseller</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 font-mono font-bold text-stone-200">
                                        Rp{{ number_format($menu->base_price, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3">
                                        @if($menu->is_spicy)
                                            <span class="bg-red-950 text-red-400 border border-red-800/60 font-bold px-2 py-0.5 rounded text-[10px]">
                                                🌶️ Level {{ $menu->default_spiciness_level }}
                                            </span>
                                        @else
                                            <span class="text-stone-500 text-[10px]">Original</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right">
                                        <!-- Cashier 1-Click Stock Toggle -->
                                        <form action="{{ route('cashier.menus.toggle-stock', $menu->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center space-x-2 px-3.5 py-1.5 rounded-full text-xs font-bold transition active:scale-95 shadow-sm {{ $menu->is_available ? 'bg-emerald-950 border border-emerald-700 text-emerald-300 hover:bg-emerald-900' : 'bg-rose-950 border border-rose-700 text-rose-300 hover:bg-rose-900' }}">
                                                <span class="w-2 h-2 rounded-full {{ $menu->is_available ? 'bg-emerald-400 animate-pulse' : 'bg-rose-500' }}"></span>
                                                <span>{{ $menu->is_available ? 'Tersedia' : 'Habis (Sold Out)' }}</span>
                                                <span class="text-[10px] text-stone-400 font-normal ml-1">⇄ Ganti</span>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-4 text-center text-stone-500">Belum ada menu di kategori ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

