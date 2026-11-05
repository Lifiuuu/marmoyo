<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin / Owner Dashboard - Marmoyo Smart-Dine')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen antialiased flex flex-col md:flex-row">
    <!-- Mobile Off-Canvas Drawer Backdrop -->
    <div id="admin-mobile-backdrop" onclick="toggleAdminDrawer()" class="fixed inset-0 bg-stone-950/60 backdrop-blur-xs z-40 hidden transition-opacity md:hidden"></div>

    <!-- Mobile Off-Canvas Sidebar Drawer -->
    <aside id="admin-mobile-drawer" class="fixed inset-y-0 left-0 w-72 max-w-[85vw] bg-white border-r border-stone-200 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden flex flex-col justify-between shadow-2xl">
        <div class="overflow-y-auto">
            <!-- Mobile Drawer Header -->
            <div class="p-4 border-b border-stone-100 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <span class="w-9 h-9 rounded-xl bg-red-600 text-white font-black flex items-center justify-center text-base shadow-sm">M</span>
                    <div>
                        <h1 class="text-sm font-extrabold text-stone-900">Marmoyo Smart-Dine</h1>
                        <p class="text-[10px] text-stone-500 font-semibold tracking-wider uppercase">Owner Executive Panel</p>
                    </div>
                </div>
                <button type="button" onclick="toggleAdminDrawer()" class="w-8 h-8 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-stone-100 flex items-center justify-center text-lg font-bold" aria-label="Tutup Menu">
                    &times;
                </button>
            </div>

            <!-- Mobile Nav Links -->
            <nav class="p-3 space-y-1 text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard &amp; Omzet</span>
                </a>

                <a href="{{ route('admin.menus.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.menus*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>Katalog Menu &amp; Stok</span>
                </a>

                <a href="{{ route('admin.tables.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.tables*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span>Meja &amp; QR Code Generator</span>
                </a>

                <a href="{{ route('admin.reconciliation.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.reconciliation*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Laporan Kas &amp; Rekonsiliasi</span>
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.users*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Kelola Staf / Pengguna</span>
                </a>

                <a href="{{ route('admin.settings') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.settings*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Pengaturan Kedai &amp; Struk</span>
                </a>
            </nav>
        </div>

        <!-- Mobile Drawer Footer Profile -->
        <div class="p-4 border-t border-stone-200 bg-stone-50">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-stone-900">{{ auth()->user()->name ?? 'Wasis (Owner)' }}</p>
                    <p class="text-[10px] text-stone-500">Administrator</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-stone-500 hover:text-red-600 text-xs font-bold px-2 py-1 rounded hover:bg-stone-200 transition">Keluar</button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Desktop & Tablet Permanent Sidebar Navigation -->
    <aside class="w-60 lg:w-64 bg-white border-r border-stone-200 min-h-screen flex-col justify-between hidden md:flex shrink-0 sticky top-0 h-screen">
        <div class="overflow-y-auto flex-1">
            <!-- Branding -->
            <div class="p-4 lg:p-5 border-b border-stone-100 flex items-center space-x-3">
                <span class="w-9 h-9 rounded-xl bg-red-600 text-white font-black flex items-center justify-center text-base shadow-sm shrink-0">M</span>
                <div class="min-w-0">
                    <h1 class="text-sm font-extrabold text-stone-900 truncate">Marmoyo Smart-Dine</h1>
                    <p class="text-[10px] text-stone-500 font-semibold tracking-wider uppercase truncate">Owner Executive Panel</p>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="p-3 space-y-1 text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard &amp; Omzet</span>
                </a>

                <a href="{{ route('admin.menus.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.menus*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>Katalog Menu &amp; Stok</span>
                </a>

                <a href="{{ route('admin.tables.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.tables*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span>Meja &amp; QR Code Generator</span>
                </a>

                <a href="{{ route('admin.reconciliation.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.reconciliation*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Laporan Kas &amp; Rekonsiliasi</span>
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.users*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Kelola Staf / Pengguna</span>
                </a>

                <a href="{{ route('admin.settings') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl transition {{ request()->routeIs('admin.settings*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Pengaturan Kedai &amp; Struk</span>
                </a>
            </nav>
        </div>

        <!-- Desktop Footer Profile -->
        <div class="p-4 border-t border-stone-200">
            <div class="flex items-center justify-between">
                <div class="min-w-0 pr-2">
                    <p class="text-xs font-bold text-stone-900 truncate">{{ auth()->user()->name ?? 'Wasis (Owner)' }}</p>
                    <p class="text-[10px] text-stone-500">Administrator</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-stone-400 hover:text-red-600 text-xs font-semibold p-1 transition">Keluar</button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden min-w-0">
        <!-- Topbar Mobile & Desktop Header -->
        <header class="bg-white border-b border-stone-200 px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between sticky top-0 z-30 shadow-2xs">
            <div class="flex items-center space-x-2.5 min-w-0">
                <!-- Hamburger Button for Mobile & Small Tablets -->
                <button type="button" onclick="toggleAdminDrawer()" class="md:hidden p-2 -ml-1 text-stone-600 hover:text-stone-900 hover:bg-stone-100 rounded-xl transition" aria-label="Buka Menu Navigasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h2 class="text-sm sm:text-base font-extrabold text-stone-900 truncate">@yield('page-title', 'Overview')</h2>
            </div>

            <div class="flex items-center space-x-2 sm:space-x-3 text-xs shrink-0">
                <span class="text-stone-500 hidden lg:inline">{{ now()->translatedFormat('l, d F Y') }}</span>
                <a href="{{ route('cashier.live-orders') }}" target="_blank" class="bg-stone-100 hover:bg-stone-200 text-stone-800 font-semibold px-2.5 sm:px-3 py-1.5 rounded-xl border border-stone-200 transition text-[11px] sm:text-xs flex items-center">
                    <span class="hidden sm:inline">Buka Terminal POS Kasir</span>
                    <span class="sm:hidden">Terminal POS</span>
                </a>
            </div>
        </header>

        <main class="flex-1 p-3.5 sm:p-5 md:p-6">
            @if(session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-2xl text-xs font-medium flex items-center justify-between shadow-xs">
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-5 bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-2xl text-xs font-medium flex items-center justify-between shadow-xs">
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleAdminDrawer() {
            const drawer = document.getElementById('admin-mobile-drawer');
            const backdrop = document.getElementById('admin-mobile-backdrop');
            if (!drawer || !backdrop) return;
            const isOpen = !drawer.classList.contains('-translate-x-full');
            if (isOpen) {
                drawer.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            } else {
                drawer.classList.remove('-translate-x-full');
                backdrop.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }
    </script>
    @stack('scripts')
</body>
</html>