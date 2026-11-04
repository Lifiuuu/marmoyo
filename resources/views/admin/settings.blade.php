@extends('layouts.admin')

@section('title', 'Pengaturan Kedai & Struk - Marmoyo Smart-Dine')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-black text-stone-900">Pengaturan Kedai &amp; Format Struk</h2>
            <p class="text-xs text-stone-500 mt-1">Kelola identitas kedai yang tercetak di struk kasir thermal, tarif operasional, dan promo diskon.</p>
        </div>
    </div>

    <!-- Alert / Flash Message -->
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold px-4 py-3 rounded-2xl flex items-center justify-between shadow-xs">
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold px-4 py-3 rounded-2xl flex items-center justify-between shadow-xs">
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium px-4 py-3 rounded-2xl shadow-xs">
            <p class="font-bold">Terjadi kesalahan input:</p>
            <ul class="list-disc list-inside mt-1 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Form Settings (Left 7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Card 1: Identitas Kedai & Struk Header -->
                <div class="bg-white border border-stone-200 rounded-3xl p-6 shadow-xs space-y-4">
                    <div class="border-b border-stone-100 pb-3">
                        <h3 class="font-extrabold text-stone-900 text-sm">Profil Kedai &amp; Kepala Struk</h3>
                        <p class="text-[11px] text-stone-500">Informasi ini otomatis tercetak di bagian atas kertas thermal setiap transaksi lunas.</p>
                    </div>

                    <div class="space-y-3.5 text-xs">
                        <div>
                            <label class="block font-bold text-stone-800 mb-1">Nama Usaha / Kedai *</label>
                            <input type="text" name="store_name" id="inp_store_name" value="{{ old('store_name', $settings->store_name) }}" required class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-stone-900 font-bold focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
                        </div>

                        <div>
                            <label class="block font-bold text-stone-800 mb-1">Slogan / Tagline Kedai</label>
                            <input type="text" name="store_tagline" id="inp_store_tagline" value="{{ old('store_tagline', $settings->store_tagline) }}" placeholder="Contoh: Authentic Spicy Noodles & Dimsum" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-stone-800 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-stone-800 mb-1">Alamat Kedai *</label>
                                <input type="text" name="store_address" id="inp_store_address" value="{{ old('store_address', $settings->store_address) }}" required placeholder="Contoh: Jl. Marmoyo No. 12, Surabaya" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-stone-800 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
                            </div>
                            <div>
                                <label class="block font-bold text-stone-800 mb-1">No. Telepon / WhatsApp</label>
                                <input type="text" name="store_phone" id="inp_store_phone" value="{{ old('store_phone', $settings->store_phone) }}" placeholder="Contoh: 0812-3456-7890" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3 py-2.5 text-stone-800 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-stone-800 mb-1">Pesan Kaki Struk (Footer)</label>
                            <textarea name="receipt_footer" id="inp_receipt_footer" rows="2" placeholder="Pesan penutup struk untuk pelanggan..." class="w-full bg-stone-50 border border-stone-200 rounded-xl p-3 text-stone-800 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 resize-none font-mono text-[11px]">{{ old('receipt_footer', $settings->receipt_footer) }}</textarea>
                            <p class="text-[10px] text-stone-400 mt-0.5">Gunakan enter untuk membuat baris baru pada kertas struk thermal.</p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Tarif Operasional & Promo Diskon -->
                <div class="bg-white border border-stone-200 rounded-3xl p-6 shadow-xs space-y-4">
                    <div class="border-b border-stone-100 pb-3">
                        <h3 class="font-extrabold text-stone-900 text-sm">Tarif Operasional &amp; Promo</h3>
                        <p class="text-[11px] text-stone-500">Aturan penghitungan biaya otomatis di checkout kasir dan pemesanan meja.</p>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-3.5 bg-stone-50 rounded-2xl border border-stone-200 space-y-1">
                                <label class="block font-bold text-stone-800">Biaya Bungkus / Take Away (Rp)</label>
                                <input type="number" name="packaging_fee" value="{{ old('packaging_fee', round($settings->packaging_fee)) }}" min="0" step="500" class="w-full bg-white border border-stone-200 rounded-xl p-2 font-black text-stone-900">
                                <span class="text-[10px] text-stone-500 block">Biaya kemasan box food grade &amp; tas</span>
                            </div>

                            <div class="p-3.5 bg-stone-50 rounded-2xl border border-stone-200 space-y-1">
                                <label class="block font-bold text-stone-800">Pajak Restoran PB1 (%)</label>
                                <input type="number" name="tax_pb1_percent" value="{{ old('tax_pb1_percent', round($settings->tax_pb1_percent)) }}" min="0" max="100" class="w-full bg-white border border-stone-200 rounded-xl p-2 font-black text-stone-900">
                                <span class="text-[10px] text-stone-500 block">Standar pajak daerah resto (10%)</span>
                            </div>
                        </div>

                        <!-- Promo Voucher Makan Hemat -->
                        <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-extrabold text-emerald-950">Promo Diskon "MAKANHEMAT"</span>
                                    <p class="text-[10px] text-emerald-800">Potongan langsung otomatis saat pelanggan memesan di keranjang</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="promo_active" value="1" {{ old('promo_active', $settings->promo_active) ? 'checked' : '' }} class="sr-only peer">
                                    <div class="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>

                            <div class="flex items-center space-x-2 pt-1 border-t border-emerald-200/60">
                                <span class="font-bold text-emerald-900 shrink-0">Nominal Diskon: Rp</span>
                                <input type="number" name="promo_discount" value="{{ old('promo_discount', round($settings->promo_discount)) }}" min="0" step="500" class="w-36 bg-white border border-emerald-300 rounded-lg px-2.5 py-1.5 font-black text-emerald-950 focus:outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs px-6 py-3 rounded-2xl shadow-md active:scale-95 transition flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Simpan Pengaturan Kedai</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Thermal Receipt Mockup (Right 5 Cols) -->
        <div class="lg:col-span-5 space-y-3 sticky top-6">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Live Preview Struk Kasir</span>
                <span class="text-[10px] font-mono bg-stone-200 text-stone-700 px-2 py-0.5 rounded font-bold">ESC/POS 58mm</span>
            </div>

            <!-- Stylized Paper Thermal Receipt -->
            <div class="bg-white rounded-2xl shadow-xl border border-stone-200 p-6 text-stone-900 font-mono text-[11px] leading-relaxed space-y-2 select-none relative overflow-hidden">
                <!-- Top Serrated Border Effect -->
                <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-stone-200 via-stone-300 to-stone-200"></div>

                <!-- Header -->
                <div class="text-center space-y-0.5">
                    <p class="font-black text-sm uppercase tracking-wide" id="prev_name">{{ $settings->store_name }}</p>
                    <p class="text-[10px] text-stone-600" id="prev_tagline">{{ $settings->store_tagline }}</p>
                    <p class="text-[10px] text-stone-600" id="prev_address">{{ $settings->store_address }}</p>
                    <p class="text-[10px] text-stone-600" id="prev_phone">Telp/WA: {{ $settings->store_phone }}</p>
                </div>

                <div class="border-t border-dashed border-stone-300 my-2"></div>

                <!-- Metadata -->
                <div class="space-y-0.5 text-[10px] text-stone-600">
                    <div class="flex justify-between">
                        <span>No. Trans</span>
                        <span class="font-bold text-stone-900">#MRM-D08</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tipe</span>
                        <span>DINE-IN (Meja 08)</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Waktu</span>
                        <span>{{ now()->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Kasir</span>
                        <span>Najwa (Staf Kasir)</span>
                    </div>
                </div>

                <div class="border-t border-dashed border-stone-300 my-2"></div>

                <!-- Sample Items -->
                <div class="space-y-1.5 text-[10px]">
                    <div>
                        <p class="font-bold">Mie Kuah Mala Pedas Nampol</p>
                        <div class="flex justify-between text-stone-600">
                            <span>2 x Rp15.000</span>
                            <span class="font-bold text-stone-900">Rp30.000</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-bold">Udang Keju</p>
                        <div class="flex justify-between text-stone-600">
                            <span>1 x Rp10.000</span>
                            <span class="font-bold text-stone-900">Rp10.000</span>
                        </div>
                    </div>
                    <div>
                        <p class="font-bold">Es Jeruk Peras</p>
                        <div class="flex justify-between text-stone-600">
                            <span>2 x Rp4.000</span>
                            <span class="font-bold text-stone-900">Rp8.000</span>
                        </div>
                    </div>
                </div>

                <div class="border-t border-dashed border-stone-300 my-2"></div>

                <!-- Financial Calculation -->
                <div class="space-y-1 text-[10px]">
                    <div class="flex justify-between text-stone-600">
                        <span>Subtotal</span>
                        <span>Rp48.000</span>
                    </div>
                    <div class="flex justify-between text-emerald-700 font-bold">
                        <span>Diskon MAKANHEMAT</span>
                        <span>-Rp3.000</span>
                    </div>
                    <div class="flex justify-between text-stone-600">
                        <span>PB1 Resto (10%)</span>
                        <span>Termasuk</span>
                    </div>
                </div>

                <div class="border-t-2 border-stone-900 my-2"></div>

                <!-- Total -->
                <div class="flex justify-between items-center text-xs font-black">
                    <span>TOTAL BAYAR</span>
                    <span class="text-sm">Rp45.000</span>
                </div>
                <div class="flex justify-between text-[10px] text-stone-600 pt-0.5">
                    <span>Metode: TUNAI</span>
                    <span>Kembalian: Rp5.000</span>
                </div>

                <div class="border-t border-dashed border-stone-300 my-2"></div>

                <!-- Footer -->
                <div class="text-center text-[10px] text-stone-600 whitespace-pre-line leading-snug pt-1" id="prev_footer">
{{ $settings->receipt_footer }}
                </div>
            </div>
            <p class="text-[10px] text-stone-400 text-center">Preview ini mencerminkan hasil cetak fisik pada printer thermal kasir 58mm.</p>
        </div>
    </div>
</div>

<script>
    // Live update preview thermal receipt when user types
    const inpName = document.getElementById('inp_store_name');
    const inpTagline = document.getElementById('inp_store_tagline');
    const inpAddress = document.getElementById('inp_store_address');
    const inpPhone = document.getElementById('inp_store_phone');
    const inpFooter = document.getElementById('inp_receipt_footer');

    const prevName = document.getElementById('prev_name');
    const prevTagline = document.getElementById('prev_tagline');
    const prevAddress = document.getElementById('prev_address');
    const prevPhone = document.getElementById('prev_phone');
    const prevFooter = document.getElementById('prev_footer');

    inpName.addEventListener('input', () => prevName.textContent = inpName.value || 'MARMOYO SMART-DINE');
    inpTagline.addEventListener('input', () => prevTagline.textContent = inpTagline.value);
    inpAddress.addEventListener('input', () => prevAddress.textContent = inpAddress.value);
    inpPhone.addEventListener('input', () => prevPhone.textContent = 'Telp/WA: ' + inpPhone.value);
    inpFooter.addEventListener('input', () => prevFooter.textContent = inpFooter.value);
</script>
@endsection
