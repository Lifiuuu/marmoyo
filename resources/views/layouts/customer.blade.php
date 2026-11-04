<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<body class="bg-stone-100 text-stone-900 min-h-screen antialiased flex flex-col justify-between overflow-x-hidden">
    <!-- Customer Responsive Shell: Adaptive for small screens (320px-360px), normal phones (390px-430px), and foldables/tablets like Z Fold (768px-880px) -->
    <div class="w-full max-w-md sm:max-w-xl md:max-w-2xl mx-auto bg-white min-h-screen shadow-2xl flex flex-col relative pb-28 border-x border-stone-200/50">
        <!-- Sticky Header -->
        <header class="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-stone-200 px-3.5 sm:px-6 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <a href="{{ route('customer.menu') }}" class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-full bg-red-600 text-white font-extrabold flex items-center justify-center text-sm shadow-md shrink-0">M</span>
                    <div>
                        <h1 class="text-xs sm:text-sm font-extrabold text-stone-900 leading-tight">Kedai Mie Marmoyo</h1>
                        <p class="text-[9px] sm:text-[10px] text-stone-500">Smart-Dine Self-Ordering</p>
                    </div>
                </a>
            </div>

            @if(session('table_number'))
                <div class="flex items-center space-x-1.5 bg-amber-50 border border-amber-300 text-amber-900 px-2.5 py-1 rounded-full text-[11px] sm:text-xs font-bold shadow-sm shrink-0">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h10a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h10a2 2 0 002-2v-2a2 2 0 00-2-2H5z"/>
                    </svg>
                    <span>Meja {{ session('table_number') }}</span>
                </div>
            @endif
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 px-3.5 sm:px-6 py-3 sm:py-4">
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
    </div>

    @stack('scripts')
</body>
</html>

