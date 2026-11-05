<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem - Marmoyo Smart-Dine</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-900 min-h-screen flex items-center justify-center p-3.5 sm:p-4">
    <div class="max-w-md w-full bg-white rounded-2xl sm:rounded-3xl p-5 sm:p-8 shadow-2xl space-y-5 sm:space-y-6">
        <!-- Logo -->
        <div class="text-center space-y-2">
            <span class="w-12 h-12 rounded-2xl bg-red-600 text-white font-black text-xl inline-flex items-center justify-center shadow-lg">M</span>
            <h1 class="text-lg font-black text-stone-900">Marmoyo Smart-Dine</h1>
            <p class="text-xs text-stone-500">Portal Operasional Kasir &amp; Dashboard Owner</p>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 text-xs p-3 rounded-xl font-medium">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-rose-50 border border-rose-300 text-rose-800 text-xs p-3 rounded-xl font-medium">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-rose-50 border border-rose-300 text-rose-800 text-xs p-3 rounded-xl font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-bold text-stone-700 mb-1">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Email" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 outline-none focus:border-red-500 text-stone-900 font-medium">
            </div>

            <div>
                <label class="block font-bold text-stone-700 mb-1">Kata Sandi</label>
                <input type="password" name="password" required placeholder="Kata Sandi" class="w-full bg-stone-50 border border-stone-200 rounded-xl px-3.5 py-2.5 outline-none focus:border-red-500 text-stone-900 font-medium">
            </div>

            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-extrabold py-3 rounded-xl shadow-md transition active:scale-95 text-xs">
                Masuk ke Sistem &rarr;
            </button>
        </form>
    </div>
</body>
</html>

