<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ro'yxatdan o'tish — Marketplace</title>
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
            <h1 class="text-xl font-extrabold text-slate-900">Ro'yxatdan o'tish</h1>
            <p class="text-xs text-slate-500 mt-1">Xarid qilish yoki o'z do'koningizni ochish uchun hisob yarating</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-xl">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-4 mb-6">
            @csrf

            <div>
                <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Hisob turi</span>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(['customer' => '🛍️ Xaridor', 'wholesale_customer' => '📦 Ulgurji', 'seller' => "🏪 Sotuvchi"] as $type => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="account_type" value="{{ $type }}" class="peer sr-only"
                                   {{ old('account_type', 'customer') === $type ? 'checked' : '' }}
                                   onchange="toggleSellerFields()">
                            <span class="block text-center px-2 py-2.5 rounded-xl border border-slate-300 text-xs font-bold peer-checked:border-[#123C2F] peer-checked:bg-[#123C2F] peer-checked:text-white transition">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Ism familiya</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
            </div>

            <div>
                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Telefon raqami</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', '+998') }}" required autocomplete="tel"
                       placeholder="+998901234567"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Email <span class="normal-case font-medium text-slate-400">(ixtiyoriy)</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
            </div>

            <div id="sellerFields" class="space-y-4 p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200 hidden">
                <div>
                    <label for="store_name" class="block text-xs font-bold uppercase tracking-wider text-emerald-900 mb-1">Do'kon nomi</label>
                    <input type="text" id="store_name" name="store_name" value="{{ old('store_name') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-emerald-300 text-sm bg-white focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
                </div>
                <div>
                    <label for="tax_id" class="block text-xs font-bold uppercase tracking-wider text-emerald-900 mb-1">STIR (INN) <span class="normal-case font-medium text-emerald-600">(ixtiyoriy)</span></label>
                    <input type="text" id="tax_id" name="tax_id" value="{{ old('tax_id') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-emerald-300 text-sm bg-white focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
                </div>
                <p class="text-[11px] text-emerald-800">Do'kon administrator tomonidan tekshirilgach faollashtiriladi.</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Parol</label>
                    <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Takrorlang</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-[#123C2F] focus:outline-none">
                </div>
            </div>

            <label class="flex items-start gap-2 text-xs text-slate-600">
                <input type="checkbox" name="terms" value="1" required {{ old('terms') ? 'checked' : '' }} class="mt-0.5 rounded border-slate-300">
                <span><a href="{{ route('privacy') }}" class="font-bold text-[#123C2F] underline">Maxfiylik siyosati</a> va <a href="{{ route('returns') }}" class="font-bold text-[#123C2F] underline">qaytarish shartlari</a> bilan tanishdim va roziman</span>
            </label>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-[#123C2F] hover:bg-[#0d2a21] text-white font-bold text-sm shadow transition duration-150">
                Hisob yaratish
            </button>
        </form>

        <div class="text-center text-xs text-slate-600">
            Hisobingiz bormi?
            <a href="{{ route('login') }}" class="font-bold text-[#123C2F] hover:underline">Tizimga kiring</a>
        </div>

        <div class="text-center mt-4">
            <a href="/" class="text-xs font-bold text-slate-500 hover:text-[#123C2F] transition">
                ← Bosh sahifaga qaytish
            </a>
        </div>
    </div>

    <script>
        function toggleSellerFields() {
            const isSeller = document.querySelector('input[name="account_type"]:checked')?.value === 'seller';
            document.getElementById('sellerFields').classList.toggle('hidden', !isSeller);
            document.getElementById('store_name').required = isSeller;
        }
        toggleSellerFields();
    </script>
</body>
</html>
