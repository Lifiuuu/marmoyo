<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kedai Mie Marmoyo - Self Ordering')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-stone-100 text-stone-900 min-h-screen antialiased flex flex-col justify-between">
    <!-- Customer Mobile-First Shell -->
    <div class="w-full max-w-md mx-auto bg-white min-h-screen shadow-2xl flex flex-col relative pb-28">
        <!-- Sticky Header -->
        <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-stone-200 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <a href="{{ url('/') }}" class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-full bg-red-600 text-white font-extrabold flex items-center justify-center text-sm shadow-md">M</span>
                    <div>
                        <h1 class="text-sm font-extrabold text-stone-900 leading-tight">Kedai Mie Marmoyo</h1>
                        <p class="text-[10px] text-stone-500">Smart-Dine Self-Ordering</p>
                    </div>
                </a>
            </div>

            @if(session('table_number'))
                <div class="flex items-center space-x-1.5 bg-amber-50 border border-amber-300 text-amber-900 px-2.5 py-1 rounded-full text-xs font-bold shadow-sm">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h10a2 2 0 002-2v-2a2 2 0 00-2-2H5z"/>
                    </svg>
                    <span>Meja {{ session('table_number') }}</span>
                </div>
            @endif
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 px-4 py-3">
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-3 rounded text-xs text-emerald-800 font-medium">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-rose-50 border-l-4 border-rose-500 p-3 rounded text-xs text-rose-800 font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

        <!-- Floating Table Help & Call Waiter Floating Button -->
        <div id="waiter-modal-btn" class="fixed bottom-20 max-w-md w-full px-4 pointer-events-none z-20 flex justify-end">
            <button onclick="toggleCallWaiterModal()" class="pointer-events-auto bg-stone-900/90 text-white text-xs px-3 py-1.5 rounded-full shadow-lg flex items-center space-x-1.5 hover:bg-stone-800 transition active:scale-95">
                <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span>Panggil Pelayan</span>
            </button>
        </div>
    </div>

    <!-- Panggil Pelayan Modal -->
    <div id="call-waiter-modal" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-xs w-full p-5 shadow-2xl text-center">
            <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <h3 class="font-bold text-stone-900 text-base mb-1">Butuh Bantuan?</h3>
            <p class="text-xs text-stone-500 mb-4">Pilih jenis bantuan untuk Meja {{ session('table_number', 'Anda') }}</p>

            <form action="{{ route('customer.call-waiter') }}" method="POST" class="space-y-2">
                @csrf
                <input type="hidden" name="table_number" value="{{ session('table_number') }}">
                <button type="submit" name="call_type" value="utensil" class="w-full py-2 px-3 text-xs font-semibold rounded-lg border border-stone-200 text-stone-700 hover:bg-stone-50 text-left flex items-center justify-between">
                    <span>Minta Sendok / Garpu / Tisu</span>
                    <span>🍴</span>
                </button>
                <button type="submit" name="call_type" value="water" class="w-full py-2 px-3 text-xs font-semibold rounded-lg border border-stone-200 text-stone-700 hover:bg-stone-50 text-left flex items-center justify-between">
                    <span>Minta Refill Air / Tambah Es</span>
                    <span>🧊</span>
                </button>
                <button type="submit" name="call_type" value="assistance" class="w-full py-2 px-3 text-xs font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 text-left flex items-center justify-between">
                    <span>Panggil Pelayan ke Meja</span>
                    <span>🙋</span>
                </button>
            </form>

            <button onclick="toggleCallWaiterModal()" class="mt-4 text-xs text-stone-400 hover:text-stone-600">Batal</button>
        </div>
    </div>

    <script>
        function toggleCallWaiterModal() {
            const modal = document.getElementById('call-waiter-modal');
            modal.classList.toggle('hidden');
        }
    </script>
    @stack('scripts')
</body>
</html>
