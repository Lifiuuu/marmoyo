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
    <header class="bg-stone-950 border-b border-stone-800 px-6 py-3 flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
                <span class="w-8 h-8 rounded-lg bg-red-600 text-white font-black flex items-center justify-center text-sm shadow">M</span>
                <div>
                    <h1 class="text-sm font-extrabold text-white">Marmoyo Smart-Dine POS</h1>
                    <p class="text-[10px] text-stone-400">Terminal Operasional</p>
                </div>
            </div>

            <nav class="hidden md:flex items-center space-x-1 pl-6 border-l border-stone-800 text-xs">
                <a href="{{ route('cashier.live-orders') }}" class="px-3 py-1.5 rounded-md font-semibold transition {{ request()->routeIs('cashier.live-orders*') ? 'bg-red-600 text-white' : 'text-stone-300 hover:bg-stone-800' }}">
                    Live Orders
                </a>
                <a href="{{ route('cashier.menus.index') }}" class="px-3 py-1.5 rounded-md font-semibold transition {{ request()->routeIs('cashier.menus*') ? 'bg-red-600 text-white' : 'text-stone-300 hover:bg-stone-800' }}">
                    Kontrol Stok Menu
                </a>
                <a href="{{ route('cashier.settlement') }}" class="px-3 py-1.5 rounded-md font-semibold transition {{ request()->routeIs('cashier.settlement*') ? 'bg-red-600 text-white' : 'text-stone-300 hover:bg-stone-800' }}">
                    Rekonsiliasi Kas / Shift
                </a>
            </nav>
        </div>

        <div class="flex items-center space-x-3">
            <!-- Satu-satunya Tombol Input Pesanan Take Away -->
            <a href="{{ route('cashier.take-away.create') }}" class="bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition active:scale-95 shadow">
                + Pesanan Take Away
            </a>

            <!-- Scan / Validasi Tunai Fast Button -->
            <button onclick="openCashValidationModal()" class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg flex items-center space-x-1.5 transition active:scale-95 shadow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Validasi Tunai</span>
            </button>

            <!-- Cashier User Badge & Logout -->
            <div class="flex items-center space-x-2 pl-3 border-l border-stone-800 text-xs">
                <div class="text-right">
                    <p class="font-bold text-stone-200">{{ auth()->user()->name ?? 'Kasir Najwa' }}</p>
                    <span class="inline-block px-1.5 py-0.5 rounded text-[10px] bg-red-900/60 text-red-300 font-medium">Staf Kasir</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="p-1.5 text-stone-400 hover:text-white rounded-lg hover:bg-stone-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 p-4 md:p-6 bg-stone-950/70">
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

    @include('cashier.partials.cash-modal')

    @stack('scripts')
</body>
</html>

