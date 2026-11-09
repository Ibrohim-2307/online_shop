<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parolni tiklash — Marketplace</title>
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
            <h1 class="text-xl font-extrabold text-slate-900">Parolni tiklash</h1>
            <p class="text-xs text-slate-500 mt-1">Ro'yxatdan o'tgan telefon raqamingizga SMS kod yuboramiz</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-xl">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if($smsAvailable)
            <form action="{{ route('password.email') }}" method="POST" class="space-y-4 mb-6">
                @csrf
                <div>
                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Telefon raqami</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required autofocus inputmode="tel" autocomplete="tel"
                           placeholder="+998901234567"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
                </div>

                <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#123C2F] hover:bg-[#0d2a21] text-white font-bold text-sm shadow transition duration-150">
                    Kod yuborish
                </button>
            </form>
        @else
            <div class="mb-6 p-3.5 bg-amber-50 border border-amber-200 text-amber-900 text-xs font-medium rounded-xl">
                SMS orqali tiklash hozircha ishlamayapti. Parolni tiklash uchun administrator bilan bog'laning.
            </div>
        @endif

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-xs font-bold text-slate-500 hover:text-[#123C2F] transition">
                ← Kirish sahifasiga qaytish
            </a>
        </div>

    </div>
</body>
</html>
