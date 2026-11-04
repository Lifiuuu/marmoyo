@extends('layouts.customer')

@section('title', 'Meja Tidak Valid - Marmoyo Smart-Dine')

@section('content')
<div class="py-12 text-center">
    <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    </div>
    <h2 class="text-base font-extrabold text-stone-900 mb-2">QR Code Tidak Terdeteksi</h2>
    <p class="text-xs text-stone-600 mb-6 px-4">{{ $message ?? 'Silakan pindai kembali QR Code resmi yang tertempel pada meja Anda.' }}</p>

    <div class="p-4 bg-stone-50 border border-stone-200 rounded-xl text-left text-xs space-y-2 mb-6">
        <p class="font-bold text-stone-800">Petunjuk Pemesanan:</p>
        <ol class="list-decimal list-inside text-stone-600 space-y-1">
            <li>Pastikan kamera HP Anda mengarah tepat ke stiker QR meja.</li>
            <li>Hubungkan HP Anda ke Wi-Fi kedai jika koneksi internet lambat.</li>
            <li>Jika masih terkendala, silakan menuju kasir untuk pemesanan manual.</li>
        </ol>
    </div>

    <a href="{{ route('customer.menu') }}" class="inline-block bg-red-600 text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-md hover:bg-red-700 transition">
        Coba Buka Menu Kedai
    </a>
</div>
@endsection

