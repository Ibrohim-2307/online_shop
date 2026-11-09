<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yangi parol — Marketplace</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 antialiased font-sans text-slate-800">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-sm border border-slate-200 p-8">

        <div class="text-center mb-6">
            <a href="/" class="inline-flex items-center gap-2 mb-3">
                <span class="w-9 h-9 rounded-xl bg-[#123C2F] text-white font-black flex items-center justify-center text-lg shadow-sm">M</span>
                <span class="font-black text-2xl tracking-tight text-[#172326]">MARKET</span>
            </a>
            <h1 class="text-xl font-extrabold text-slate-900">Yangi parol</h1>
            <p class="text-xs text-slate-500 mt-1">{{ $phone }} ro'yxatdan o'tgan bo'lsa, unga 6 xonali kod yuborildi. Kod 5 daqiqa amal qiladi.</p>
        </div>

        @if(session('demo_code'))
            <div class="mb-4 p-3.5 bg-amber-50 border border-amber-200 text-amber-900 text-xs font-medium rounded-xl">
                Sinov rejimi. SMS kodi: <b>{{ session('demo_code') }}</b>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-xl">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" class="space-y-4 mb-6">
            @csrf
            <div>
                <label for="code" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">SMS kod</label>
                <input type="text" id="code" name="code" value="{{ old('code') }}" required autofocus inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="\d{6}"
                       placeholder="123456"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm tracking-widest focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Yangi parol</label>
                <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password"
                       placeholder="Kamida 8 ta belgi"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Parolni takrorlang</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password"
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#123C2F] hover:bg-[#0d2a21] text-white font-bold text-sm shadow transition duration-150">
                Parolni yangilash
            </button>
        </form>

        <div class="text-center text-xs text-slate-600">
            Kod kelmadimi?
            <a href="{{ route('password.request') }}" class="font-bold text-[#123C2F] hover:underline">Qayta yuborish</a>
        </div>

    </div>
</body>
</html>
