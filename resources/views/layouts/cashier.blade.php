<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kasir POS - Marmoyo Smart-Dine')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    @stack('styles')
</head>
<body class="bg-stone-900 text-stone-100 min-h-screen antialiased flex flex-col">
    <!-- POS Top Navigation Bar -->
    <header class="bg-stone-950 border-b border-stone-800 px-4 sm:px-6 py-2.5 sm:py-3 flex items-center justify-between sticky top-0 z-40 shadow-md">
        <!-- Left: Logo & Branding + Desktop Nav -->
        <div class="flex items-center space-x-3 sm:space-x-4 min-w-0">
            <div class="flex items-center space-x-2.5 shrink-0">
                <span class="w-8 h-8 rounded-xl bg-red-600 text-white font-black flex items-center justify-center text-sm shadow">M</span>
                <div class="min-w-0">
                    <h1 class="text-xs sm:text-sm font-extrabold text-white truncate">Marmoyo Smart-Dine POS</h1>
                    <p class="text-[9px] sm:text-[10px] text-stone-400">Terminal Kasir</p>
                </div>
            </div>

            <!-- Desktop-only Navigation Links (Di Mobile & Tab navigasi ada di Bottom Bar) -->
            <nav class="hidden lg:flex items-center space-x-1 pl-6 border-l border-stone-800 text-xs">
                <a href="{{ route('cashier.live-orders') }}" class="px-3 py-1.5 rounded-lg font-semibold transition {{ request()->routeIs('cashier.live-orders*') ? 'bg-red-600 text-white' : 'text-stone-300 hover:bg-stone-800' }}">
                    Live Orders
                </a>
                <a href="{{ route('cashier.menus.index') }}" class="px-3 py-1.5 rounded-lg font-semibold transition {{ request()->routeIs('cashier.menus*') ? 'bg-red-600 text-white' : 'text-stone-300 hover:bg-stone-800' }}">
                    Kontrol Stok Menu
                </a>
                <a href="{{ route('cashier.settlement') }}" class="px-3 py-1.5 rounded-lg font-semibold transition {{ request()->routeIs('cashier.settlement*') ? 'bg-red-600 text-white' : 'text-stone-300 hover:bg-stone-800' }}">
                    Rekonsiliasi Kas / Shift
                </a>
            </nav>
        </div>

        <!-- Right: Action Buttons (Desktop only) & User Profile -->
        <div class="flex items-center space-x-2 sm:space-x-3 shrink-0">
            <!-- Tombol Input Pesanan Take Away (Hanya tampil di Desktop; di Mobile & Tab sudah ada di Bottom Bar) -->
            <a href="{{ route('cashier.take-away.create') }}" class="hidden lg:flex items-center space-x-1.5 bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition active:scale-95 shadow">
                <span>+ Pesanan Take Away</span>
            </a>

            <!-- Tombol Validasi Tunai (Hanya tampil di Desktop; di Mobile & Tab sudah ada di Bottom Bar) -->
            <button type="button" onclick="openCashValidationModal()" class="hidden lg:flex items-center space-x-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-3.5 py-2 rounded-xl transition active:scale-95 shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Validasi Tunai</span>
            </button>

            <!-- Cashier User Badge & Logout -->
            <div class="flex items-center space-x-2 pl-2 sm:pl-3 border-l border-stone-800 text-xs">
                <div class="text-right">
                    <p class="font-bold text-stone-200 text-xs truncate max-w-[110px] sm:max-w-[140px]">{{ auth()->user()->name ?? 'Kasir' }}</p>
                    <span class="inline-block px-1.5 py-0.2 rounded text-[9px] bg-red-900/60 text-red-300 font-medium">Staf Kasir</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Keluar / Logout" class="p-1.5 text-stone-400 hover:text-white rounded-lg hover:bg-stone-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 p-3.5 sm:p-5 md:p-6 bg-stone-950/70 pb-24 lg:pb-6">
        @if(session('success'))
            <div class="mb-4 bg-emerald-950/90 border border-emerald-800 text-emerald-200 px-4 py-3 rounded-2xl text-xs font-medium flex items-center justify-between shadow-lg">
                <span>{{ session('success') }}</span>
                @if(session('last_order_id'))
                    <div class="flex items-center shrink-0 ml-4">
                        <a href="{{ route('cashier.print.receipt', session('last_order_id')) }}" target="_blank" class="bg-emerald-700 hover:bg-emerald-600 text-white font-bold px-3 py-1.5 rounded-xl transition">
                            Cetak Struk
                        </a>
                    </div>
                @endif
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-rose-950/90 border border-rose-800 text-rose-200 px-4 py-3 rounded-2xl text-xs font-medium flex items-center justify-between shadow-lg">
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Mobile & Tablet Bottom Sticky App Bar (Modern POS Mobile Dock) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-stone-950/95 backdrop-blur border-t border-stone-800 px-2 py-1.5 flex items-center justify-around shadow-2xl">
        <a href="{{ route('cashier.live-orders') }}" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition {{ request()->routeIs('cashier.live-orders*') ? 'text-red-500 font-extrabold' : 'text-stone-400 hover:text-stone-200' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <span class="text-[10px]">Live Orders</span>
        </a>

        <a href="{{ route('cashier.take-away.create') }}" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition {{ request()->routeIs('cashier.take-away*') ? 'text-amber-400 font-extrabold' : 'text-stone-400 hover:text-stone-200' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <span class="text-[10px]">Take Away</span>
        </a>

        <button type="button" onclick="openCashValidationModal()" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl text-emerald-400 hover:text-emerald-300 transition active:scale-95">
            <div class="w-7 h-7 rounded-full bg-emerald-600/30 border border-emerald-500/50 flex items-center justify-center mb-0.5">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <span class="text-[10px] font-bold">Validasi</span>
        </button>

        <a href="{{ route('cashier.menus.index') }}" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition {{ request()->routeIs('cashier.menus*') ? 'text-red-500 font-extrabold' : 'text-stone-400 hover:text-stone-200' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="text-[10px]">Stok Menu</span>
        </a>

        <a href="{{ route('cashier.settlement') }}" class="flex flex-col items-center justify-center py-1 px-2.5 rounded-xl transition {{ request()->routeIs('cashier.settlement*') ? 'text-red-500 font-extrabold' : 'text-stone-400 hover:text-stone-200' }}">
            <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span class="text-[10px]">Tutup Shift</span>
        </a>
    </nav>

    @include('cashier.partials.cash-modal')

    @stack('scripts')
</body>
</html>
