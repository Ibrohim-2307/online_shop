<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tizimga kirish — Marketplace</title>
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
            <h1 class="text-xl font-extrabold text-slate-900">Tizimga kirish</h1>
            <p class="text-xs text-slate-500 mt-1">Admin, sotuvchi yoki xaridor hisobingizga kiring</p>
        </div>

        @if(session('info'))
            <div class="mb-4 p-3.5 bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold rounded-xl flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium rounded-xl">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-xl">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4 mb-6">
            @csrf
            <div>
                <label for="login" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email yoki Telefon</label>
                <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                       placeholder="admin@market.uz yoki +998901234567"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Parol</label>
                <input type="password" id="password" name="password" required
                       placeholder="••••••••"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
                <div class="text-right mt-1.5">
                    <a href="{{ route('password.request') }}" class="text-xs font-bold text-[#123C2F] hover:underline">Parolni unutdingizmi?</a>
                </div>
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#123C2F] hover:bg-[#0d2a21] text-white font-bold text-sm shadow transition duration-150">
                Kirish
            </button>
        </form>

        <div class="text-center text-xs text-slate-600 mb-6">
            Hisobingiz yo'qmi?
            <a href="{{ route('register') }}" class="font-bold text-[#123C2F] hover:underline">Ro'yxatdan o'ting</a>
        </div>

        <!-- Quick Demo Accounts One-Click Fill -->
        <div class="border-t border-slate-100 pt-5">
            <span class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2.5 text-center">
                Tezkor Hisoblar (1-bosishda to'ldirish):
            </span>
            <div class="grid grid-cols-2 gap-2 text-left">
                <button type="button" onclick="fillLogin('admin@market.uz', 'admin123')" 
                        class="p-2.5 rounded-xl border border-rose-200 bg-rose-50/50 hover:bg-rose-100/60 text-left transition">
                    <span class="text-xs font-bold text-rose-900 block">👑 Administrator</span>
                    <span class="text-[10px] text-rose-600 font-mono block">admin@market.uz</span>
                    <span class="text-[9px] text-rose-500 font-mono block">parol: admin123</span>
                </button>

                <button type="button" onclick="fillLogin('seller@market.uz', 'admin123')" 
                        class="p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/50 hover:bg-emerald-100/60 text-left transition">
                    <span class="text-xs font-bold text-emerald-900 block">🏪 Sotuvchi (Do'kon)</span>
                    <span class="text-[10px] text-emerald-600 font-mono block">seller@market.uz</span>
                    <span class="text-[9px] text-emerald-500 font-mono block">parol: admin123</span>
                </button>

                <button type="button" onclick="fillLogin('customer@market.uz', 'admin123')" 
                        class="p-2.5 rounded-xl border border-blue-200 bg-blue-50/50 hover:bg-blue-100/60 text-left transition">
                    <span class="text-xs font-bold text-blue-900 block">🛍️ Xaridor</span>
                    <span class="text-[10px] text-blue-600 font-mono block">customer@market.uz</span>
                    <span class="text-[9px] text-blue-500 font-mono block">parol: admin123</span>
                </button>

                <button type="button" onclick="fillLogin('courier@market.uz', 'admin123')" 
                        class="p-2.5 rounded-xl border border-amber-200 bg-amber-50/50 hover:bg-amber-100/60 text-left transition">
                    <span class="text-xs font-bold text-amber-900 block">🚚 Kuryer</span>
                    <span class="text-[10px] text-amber-600 font-mono block">courier@market.uz</span>
                    <span class="text-[9px] text-amber-500 font-mono block">parol: admin123</span>
                </button>
            </div>
        </div>

        <div class="text-center mt-6">
            <a href="/" class="text-xs font-bold text-slate-500 hover:text-[#123C2F] transition">
                ← Bosh sahifaga qaytish
            </a>
        </div>

    </div>

    <script>
        function fillLogin(email, pass) {
            document.getElementById('login').value = email;
            document.getElementById('password').value = pass;
        }
    </script>
</body>
</html>
