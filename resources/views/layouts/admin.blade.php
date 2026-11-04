<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin / Owner Dashboard - Kedai Mie Marmoyo')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-stone-50 text-stone-900 min-h-screen antialiased flex">
    <!-- Sidebar Navigation -->
    <aside class="w-64 bg-white border-r border-stone-200 min-h-screen flex flex-col justify-between hidden md:flex shrink-0">
        <div>
            <!-- Branding -->
            <div class="p-5 border-b border-stone-100 flex items-center space-x-3">
                <span class="w-9 h-9 rounded-xl bg-red-600 text-white font-black flex items-center justify-center text-base shadow-sm">M</span>
                <div>
                    <h1 class="text-sm font-extrabold text-stone-900">Mie Marmoyo</h1>
                    <p class="text-[10px] text-stone-500 font-semibold tracking-wider uppercase">Owner Executive Panel</p>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="p-3 space-y-1 text-xs font-semibold">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.dashboard*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard & Omzet</span>
                </a>

                <a href="{{ route('admin.menus.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.menus*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>Katalog Menu & Stok</span>
                </a>

                <a href="{{ route('admin.tables.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.tables*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span>Meja & QR Code Generator</span>
                </a>

                <a href="{{ route('admin.reconciliation.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.reconciliation*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Laporan Kas & Rekonsiliasi</span>
                </a>

                <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.users*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Kelola Staf / Pengguna</span>
                </a>

                <a href="{{ route('admin.settings') }}" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-lg transition {{ request()->routeIs('admin.settings*') ? 'bg-red-50 text-red-700 font-bold' : 'text-stone-600 hover:bg-stone-100' }}">
                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Pengaturan Kedai &amp; Struk</span>
                </a>
            </nav>
        </div>

        <!-- Footer Profile -->
        <div class="p-4 border-t border-stone-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-stone-900">{{ auth()->user()->name ?? 'Wasis (Owner)' }}</p>
                    <p class="text-[10px] text-stone-500">Administrator</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-stone-400 hover:text-red-600 text-xs font-semibold p-1">Keluar</button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-h-screen overflow-x-hidden">
        <!-- Topbar Mobile & Desktop Header -->
        <header class="bg-white border-b border-stone-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-base font-extrabold text-stone-900">@yield('page-title', 'Overview')</h2>

            <div class="flex items-center space-x-3 text-xs">
                <span class="text-stone-500">{{ now()->translatedFormat('l, d F Y') }}</span>
                <a href="{{ route('cashier.live-orders') }}" target="_blank" class="bg-stone-100 hover:bg-stone-200 text-stone-800 font-semibold px-3 py-1.5 rounded-lg border border-stone-200 transition">
                    Buka Terminal POS Kasir &rarr;
                </a>
            </div>
        </header>

        <main class="flex-1 p-6">
            @if(session('success'))
                <div class="mb-5 bg-emerald-50 border border-emerald-300 text-emerald-800 px-4 py-3 rounded-xl text-xs font-medium">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-5 bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-xl text-xs font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>

