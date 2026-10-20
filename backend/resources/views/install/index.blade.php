<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>O'rnatish Ustasi - Marketplace</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased font-sans">
    <div class="max-w-2xl mx-auto py-12 px-4 sm:px-6">
        <div class="text-center mb-8">
            <span class="inline-block px-3 py-1 bg-lime-100 text-lime-800 text-xs font-semibold rounded-full uppercase tracking-wider mb-2">Bozor Tizimi</span>
            <h1 class="text-3xl font-extrabold text-slate-900">Hosting O'rnatish Ustasi</h1>
            <p class="text-slate-600 mt-2">Dastlabki sozlash, ma'lumotlar bazasi va administrator yaratish</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-6">
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Tizim Talablari Tekshiruvi</h2>
                <div class="space-y-3">
                    @foreach($systemChecks as $key => $check)
                        <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
                            <span class="text-sm font-medium text-slate-700">{{ $check['name'] }}</span>
                            @if($check['passed'])
                                <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    Mos (OK)
                                </span>
                            @else
                                <span class="inline-flex items-center text-xs font-bold px-2.5 py-1 bg-rose-50 text-rose-700 rounded-full">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Mos emas
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="p-6 bg-slate-50/50">
                @if(!$allPassed)
                    <div class="p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl text-sm mb-4">
                        Diqqat: Yuqoridagi talablarning barchasi bajarilmaguncha o'rnatishni boshlab bo'lmaydi. Hosting PHP sozlamalarini tekshiring.
                    </div>
                @else
                    <form id="installForm" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">O'rnatish Tokeni (INSTALL_TOKEN)</label>
                            <input type="password" name="install_token" required placeholder=".env faylidagi xavfsizlik tokeni" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-lime-500 focus:outline-none">
                            <p class="text-xs text-slate-500 mt-1">Ushbu token begona shaxslar o'rnatishni ishga tushirishining oldini oladi.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Admin Ismi</label>
                                <input type="text" name="admin_name" value="Bosh Administrator" required class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-lime-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Admin Emaili</label>
                                <input type="email" name="admin_email" required placeholder="admin@market.uz" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-lime-500 focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Admin Paroli</label>
                            <input type="password" name="admin_password" required placeholder="Kamida 8 belgi" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-lime-500 focus:outline-none">
                        </div>

                        <div class="flex items-center space-x-2 pt-2">
                            <input type="checkbox" id="seed_demo" name="seed_demo" value="1" checked class="w-4 h-4 text-lime-600 rounded border-slate-300 focus:ring-lime-500">
                            <label for="seed_demo" class="text-sm font-medium text-slate-700">Namuna tovarlar va demo ma'lumotlarni yuklash</label>
                        </div>

                        <div id="statusAlert" class="hidden p-4 rounded-xl text-sm font-medium"></div>

                        <button type="submit" id="submitBtn" class="w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow transition duration-150">
                            Tizimni O'rnatish va Qulflash
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <script>
        const form = document.getElementById('installForm');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('submitBtn');
                const alert = document.getElementById('statusAlert');
                btn.disabled = true;
                btn.textContent = "O'rnatilmoqda, kuting...";
                alert.classList.add('hidden');

                const data = {
                    install_token: form.install_token.value,
                    admin_name: form.admin_name.value,
                    admin_email: form.admin_email.value,
                    admin_password: form.admin_password.value,
                    seed_demo: form.seed_demo.checked ? 1 : 0
                };

                try {
                    const res = await fetch('/install', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(data)
                    });
                    const json = await res.json();
                    alert.classList.remove('hidden');
                    if (res.ok && json.success) {
                        alert.className = 'p-4 rounded-xl text-sm font-medium bg-emerald-50 text-emerald-800 border border-emerald-200';
                        alert.textContent = json.message + ' 3 soniyada kirish sahifasiga yo\'naltirilmoqda...';
                        setTimeout(() => window.location.href = json.redirect || '/login', 2500);
                    } else {
                        alert.className = 'p-4 rounded-xl text-sm font-medium bg-rose-50 text-rose-800 border border-rose-200';
                        alert.textContent = json.message || 'Xatolik yuz berdi.';
                        btn.disabled = false;
                        btn.textContent = "Tizimni O'rnatish va Qulflash";
                    }
                } catch (err) {
                    alert.classList.remove('hidden');
                    alert.className = 'p-4 rounded-xl text-sm font-medium bg-rose-50 text-rose-800 border border-rose-200';
                    alert.textContent = 'Tarmoq xatosi: ' + err.message;
                    btn.disabled = false;
                    btn.textContent = "Tizimni O'rnatish va Qulflash";
                }
            });
        }
    </script>
</body>
</html>
