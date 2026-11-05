@extends('layouts.customer')

@section('title', 'Katalog Menu - Marmoyo Smart-Dine')

@section('content')
<div class="space-y-4">
    <!-- Featured AI Hero Banner (Dynamic from Supabase) -->
    @if($featuredMenu)
        <div class="relative bg-gradient-to-r from-red-600 to-amber-600 rounded-2xl p-4 text-white shadow-lg overflow-hidden">
            <div class="relative z-10 max-w-[65%]">
                <span class="inline-block bg-white/20 backdrop-blur-sm px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase mb-1">
                    Rekomendasi Terlaris
                </span>
                <h2 class="text-base font-extrabold leading-tight">{{ $featuredMenu->name }}</h2>
                <p class="text-[11px] text-red-100 mt-1 line-clamp-2">{{ $featuredMenu->description }}</p>
                <div class="mt-2.5 flex items-center space-x-2">
                    <span class="text-sm font-black">Rp{{ number_format($featuredMenu->base_price, 0, ',', '.') }}</span>
                    <button type="button" onclick="openMenuModal({{ $featuredMenu->id }})" class="bg-white text-red-600 text-xs font-bold px-3 py-1 rounded-full shadow hover:bg-amber-50 active:scale-95 transition">
                        + Pesan Sekarang
                    </button>
                </div>
            </div>
            @if($featuredMenu->image_url)
                <img src="{{ $featuredMenu->image_url }}" alt="{{ $featuredMenu->name }}" class="absolute -right-4 -bottom-4 w-32 h-32 object-cover rounded-full border-4 border-white/20 shadow-inner">
            @endif
        </div>
    @endif

    <!-- Category Tabs Navigation -->
    <div class="flex items-center space-x-2 overflow-x-auto pb-1 scrollbar-none text-xs font-bold">
        <a href="#cat-all" class="px-3.5 py-1.5 rounded-full bg-red-600 text-white shrink-0 shadow-sm">Semua Menu</a>
        @foreach($categories as $category)
            <a href="#cat-{{ $category->slug }}" class="px-3.5 py-1.5 rounded-full bg-stone-100 text-stone-700 hover:bg-stone-200 shrink-0 border border-stone-200 transition">
                <span>{{ $category->icon }}</span>
                <span>{{ $category->name }}</span>
            </a>
        @endforeach
    </div>

    <!-- Menu Sections grouped by category -->
    @foreach($categories as $category)
        <div id="cat-{{ $category->slug }}" class="pt-2">
            <div class="flex items-center space-x-2 mb-2.5">
                <span class="text-base">{{ $category->icon }}</span>
                <h3 class="text-sm font-extrabold text-stone-900">{{ $category->name }}</h3>
                <span class="text-[10px] bg-stone-100 text-stone-500 font-bold px-1.5 py-0.5 rounded-md">{{ $category->menus->count() }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($category->menus as $menu)
                    <div class="bg-white border border-stone-200 rounded-xl p-3 flex space-x-3 shadow-xs hover:border-red-300 transition">
                        <img src="{{ $menu->image_url ?? 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=200' }}" alt="{{ $menu->name }}" class="w-20 h-20 rounded-lg object-cover shrink-0 border border-stone-100">
                        
                        <div class="flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-1">
                                    <h4 class="text-xs font-extrabold text-stone-900 leading-snug">{{ $menu->name }}</h4>
                                    @if($menu->is_bestseller)
                                        <span class="shrink-0 text-[9px] font-black uppercase bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">Terlaris</span>
                                    @endif
                                </div>
                                <p class="text-[10px] text-stone-500 line-clamp-2 mt-0.5">{{ $menu->description }}</p>

                                @if($menu->is_spicy)
                                    <div class="mt-1 flex items-center space-x-1">
                                        <span class="text-[10px] text-red-600 font-bold">🌶️ Level {{ $menu->default_spiciness_level > 0 ? $menu->default_spiciness_level : '1-5' }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center justify-between mt-2 pt-1 border-t border-stone-100">
                                <span class="text-xs font-black text-stone-900">Rp{{ number_format($menu->base_price, 0, ',', '.') }}</span>
                                <button type="button" onclick="openMenuModal({{ $menu->id }})" class="bg-red-50 hover:bg-red-600 text-red-600 hover:text-white text-[11px] font-extrabold px-3 py-1 rounded-lg border border-red-200 hover:border-red-600 transition active:scale-95">
                                    + Tambah
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<!-- Sticky Bottom Cart Summary Bar (Adaptive for standard phones and foldables) -->
@if($cartCount > 0)
    <div class="fixed bottom-0 left-1/2 -translate-x-1/2 max-w-md sm:max-w-xl md:max-w-2xl w-full bg-white/95 backdrop-blur border-t border-stone-200 p-3 sm:px-6 shadow-2xl z-30 flex items-center justify-between">
        <div>
            <p class="text-[10px] sm:text-xs text-stone-500 font-medium">{{ $cartCount }} Menu Dipilih</p>
            <p class="text-sm sm:text-base font-black text-red-600">Rp{{ number_format($cartSubtotal, 0, ',', '.') }}</p>
        </div>
        <a href="{{ route('customer.cart') }}" class="bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-bold px-4 sm:px-6 py-2.5 rounded-xl shadow flex items-center space-x-1.5 active:scale-95 transition">
            <span>Lihat Keranjang</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
            </svg>
        </a>
    </div>
@endif

<!-- Customization Modal Dialog (100% Dynamic from Supabase variants) -->
<div id="customize-modal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-end sm:items-center justify-center p-0 sm:p-4 transition-opacity">
    <div class="bg-white rounded-t-3xl sm:rounded-2xl max-w-md sm:max-w-lg w-full max-h-[85vh] overflow-y-auto p-4 sm:p-6 shadow-2xl flex flex-col justify-between">
        <div>
            <div class="flex items-center justify-between pb-3 border-b border-stone-200">
                <div>
                    <h3 id="modal-menu-name" class="font-extrabold text-stone-900 text-sm">Kustomisasi Menu</h3>
                    <p id="modal-menu-price" class="text-xs text-red-600 font-black">Rp0</p>
                </div>
                <button type="button" onclick="closeCustomizeModal()" class="w-7 h-7 rounded-full bg-stone-100 text-stone-500 hover:bg-stone-200 flex items-center justify-center text-xs font-bold">&times;</button>
            </div>

            <form id="customize-form" action="{{ route('customer.cart.add') }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="menu_id" id="modal-menu-id">

                <!-- Dynamic Variant Sections (Injected via JS from Supabase) -->
                <div id="dynamic-variants-container" class="space-y-4"></div>

                <!-- Tambah Topping Kenyang (Dynamic from Supabase) -->
                @if($allToppings->isNotEmpty())
                    <div id="toppings-section" class="space-y-2">
                        <label class="block text-xs font-bold text-stone-800">Tambah Topping Kenyang</label>
                        <div class="space-y-1.5 text-xs">
                            @foreach($allToppings as $topping)
                                <label class="flex items-center justify-between p-2 rounded-lg border border-stone-200 hover:bg-stone-50 cursor-pointer">
                                    <span class="flex items-center space-x-2">
                                        <input type="checkbox" name="toppings[]" value="{{ $topping->id }}" class="text-red-600 rounded focus:ring-red-500">
                                        <span>{{ $topping->name }}</span>
                                    </span>
                                    <span class="font-bold text-stone-700">+Rp{{ number_format($topping->price, 0, ',', '.') }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Catatan Khusus Pesanan -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold text-stone-800">Catatan Khusus Pesanan</label>
                    <input type="text" name="special_notes" id="modal-special-notes" placeholder="Contoh: Jangan terlalu berminyak ya kak, kuah banyakin" class="w-full text-xs p-2.5 rounded-lg border border-stone-200 focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none">
                </div>

                <!-- Jumlah Porsi & Submit Button -->
                <div class="pt-3 border-t border-stone-200 flex items-center justify-between">
                    <div class="flex items-center space-x-2 border border-stone-200 rounded-lg p-1 bg-stone-50">
                        <button type="button" onclick="decrementQty()" class="w-6 h-6 rounded bg-white text-stone-700 font-bold text-xs shadow-xs hover:bg-stone-100">-</button>
                        <input type="number" name="quantity" id="modal-qty" value="1" min="1" class="w-8 text-center text-xs font-bold bg-transparent outline-none">
                        <button type="button" onclick="incrementQty()" class="w-6 h-6 rounded bg-white text-stone-700 font-bold text-xs shadow-xs hover:bg-stone-100">+</button>
                    </div>

                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-6 py-2.5 rounded-xl shadow active:scale-95 transition">
                        + Tambah ke Keranjang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Injected live from Supabase database
    const menusDatabase = @json($categories->pluck('menus')->flatten(1)->keyBy('id'));

    function openMenuModal(menuId) {
        const menu = menusDatabase[menuId];
        if (!menu) return;

        document.getElementById('modal-menu-id').value = menu.id;
        document.getElementById('modal-menu-name').innerText = menu.name;
        document.getElementById('modal-menu-price').innerText = 'Rp ' + Number(menu.base_price).toLocaleString('id-ID');
        document.getElementById('modal-qty').value = 1;
        document.getElementById('modal-special-notes').value = '';

        // Reset all topping checkboxes
        document.querySelectorAll('#toppings-section input[type="checkbox"]').forEach(cb => cb.checked = false);

        // Build dynamic variants container
        const variantsContainer = document.getElementById('dynamic-variants-container');
        variantsContainer.innerHTML = '';

        if (menu.variants && menu.variants.length > 0) {
            // Group variants by group_name
            const groupedVariants = {};
            menu.variants.forEach(variant => {
                const group = variant.group_name || 'Pilihan';
                if (!groupedVariants[group]) {
                    groupedVariants[group] = [];
                }
                groupedVariants[group].push(variant);
            });

            // Render each group
            Object.keys(groupedVariants).forEach(groupName => {
                const variants = groupedVariants[groupName];
                const inputName = groupName === 'Tingkat Kepedasan' ? 'spice_level' : (groupName === 'Jenis Mie' ? 'noodle_type' : 'variant_' + groupName.toLowerCase().replace(/\s+/g, '_'));

                const groupWrapper = document.createElement('div');
                groupWrapper.className = 'space-y-2';

                const label = document.createElement('label');
                label.className = 'block text-xs font-bold text-stone-800';
                label.innerText = groupName;
                groupWrapper.appendChild(label);

                const optionsList = document.createElement('div');
                optionsList.className = groupName === 'Jenis Mie' ? 'grid grid-cols-2 gap-2 text-xs' : 'space-y-1.5 text-xs';

                variants.forEach((variant, index) => {
                    const optionLabel = document.createElement('label');
                    const isDefault = index === 0 || (groupName === 'Tingkat Kepedasan' && variant.name.includes('Level 2'));
                    
                    optionLabel.className = 'flex items-center justify-between p-2 rounded-lg border border-stone-200 hover:bg-stone-50 cursor-pointer';

                    const priceText = Number(variant.additional_price) > 0 
                        ? '+Rp' + Number(variant.additional_price).toLocaleString('id-ID') 
                        : 'Free';

                    optionLabel.innerHTML = `
                        <span class="flex items-center space-x-2">
                            <input type="radio" name="${inputName}" value="${variant.name}" ${isDefault ? 'checked' : ''} class="text-red-600 focus:ring-red-500">
                            <span class="${isDefault && groupName === 'Tingkat Kepedasan' ? 'font-bold text-stone-900' : ''}">${variant.name}</span>
                        </span>
                        <span class="${Number(variant.additional_price) > 0 ? 'text-red-600 font-bold' : 'text-stone-400 font-medium'}">${priceText}</span>
                    `;

                    optionsList.appendChild(optionLabel);
                });

                groupWrapper.appendChild(optionsList);
                variantsContainer.appendChild(groupWrapper);
            });
        }

        // Show/hide toppings: Hide for drinks, show for noodles & snacks
        const toppingsSection = document.getElementById('toppings-section');
        if (toppingsSection) {
            if (menu.category_id === 3) { // Minuman
                toppingsSection.classList.add('hidden');
            } else {
                toppingsSection.classList.remove('hidden');
            }
        }

        document.getElementById('customize-modal').classList.remove('hidden');
    }

    function closeCustomizeModal() {
        document.getElementById('customize-modal').classList.add('hidden');
    }

    function incrementQty() {
        const input = document.getElementById('modal-qty');
        input.value = parseInt(input.value || 1) + 1;
    }

    function decrementQty() {
        const input = document.getElementById('modal-qty');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    }
</script>
@endsection
