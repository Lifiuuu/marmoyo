@extends('layouts.customer')

@section('title', 'QR Code Meja Diperlukan - Marmoyo Smart-Dine')

@section('content')
<div class="py-10 text-center">
    <div class="w-20 h-20 rounded-3xl bg-red-50 border border-red-100 text-red-600 flex items-center justify-center mx-auto mb-5 shadow-inner">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
        </svg>
    </div>

    <span class="inline-block px-3 py-1 bg-red-100 text-red-800 text-[11px] font-bold rounded-full mb-2">
        Akses Pemesanan Meja
    </span>
    <h2 class="text-lg font-extrabold text-stone-900 mb-2">QR Code Meja Diperlukan</h2>
    <p class="text-xs text-stone-600 mb-6 px-4 leading-relaxed">
        {{ $message ?? 'Silakan scan QR Code yang tertera di meja makan Anda terlebih dahulu untuk melihat katalog menu dan melakukan pemesanan.' }}
    </p>

    <div class="p-4 bg-stone-50 border border-stone-200/80 rounded-2xl text-left text-xs space-y-2.5 mb-6 shadow-sm">
        <p class="font-bold text-stone-800 flex items-center space-x-1.5">
            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Cara Memulai Pemesanan:</span>
        </p>
        <ol class="list-decimal list-inside text-stone-600 space-y-1.5 pl-1">
            <li>Buka aplikasi <span class="font-semibold text-stone-800">Kamera</span> atau pemindai QR di HP Anda.</li>
            <li>Arahkan kamera ke stiker <span class="font-semibold text-stone-800">QR Code</span> yang tertempel di meja makan Anda.</li>
            <li>Ketuk link/notifikasi browser yang muncul untuk langsung membuka menu meja.</li>
            <li>Jika ada kendala pemindaian, Anda dapat langsung memesan ke staf kasir kami.</li>
        </ol>
    </div>

    <div class="space-y-2">
        <button type="button" onclick="window.location.reload()" class="w-full bg-stone-900 hover:bg-stone-800 text-white text-xs font-bold py-3 px-4 rounded-xl shadow transition active:scale-95 flex items-center justify-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>Muat Ulang Halaman</span>
        </button>
    </div>
</div>
@endsection
