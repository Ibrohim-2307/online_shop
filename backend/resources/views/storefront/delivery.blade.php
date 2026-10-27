<x-layouts.app title="Yetkazib berish shartlari">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-20">
        
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">Yetkazib berish</span>
        </nav>

        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-10 shadow-xs space-y-8">
            <div>
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider">
                    Xaridorlar uchun ma'lumot
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-[#172326] tracking-tight mt-3">
                    Yetkazib berish xizmati va muddatlari
                </h1>
                <p class="text-xs text-[#64748B] mt-1.5 leading-relaxed">
                    MARKET savdo platformasida buyurtmalarni butun O'zbekiston bo'ylab xavfsiz va o'z vaqtida yetkazib berish tartibi.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-2">
                    <span class="text-2xl">⚡</span>
                    <h3 class="font-bold text-sm text-[#172326]">Tezkor Kuryer</h3>
                    <p class="text-xs text-[#64748B] leading-relaxed">Toshkent shahri bo'ylab buyurtmalar 24 soat ichida manzilingizgacha yetkaziladi.</p>
                </div>
                <div class="p-5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-2">
                    <span class="text-2xl">🚚</span>
                    <h3 class="font-bold text-sm text-[#172326]">Viloyatlarga Yetkazish</h3>
                    <p class="text-xs text-[#64748B] leading-relaxed">Barcha viloyat markazlari va tumanlarga 2-4 ish kuni ichida ishonchli yetkazib beriladi.</p>
                </div>
                <div class="p-5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-2">
                    <span class="text-2xl">📍</span>
                    <h3 class="font-bold text-sm text-[#172326]">Jonli GPS Kuzatuv</h3>
                    <p class="text-xs text-[#64748B] leading-relaxed">Buyurtmangiz yo'lga chiqqan paytdan boshlab kuryerning jonli harakatini xaritada ko'rib borasiz.</p>
                </div>
            </div>

            <div class="space-y-6 text-xs text-[#172326] leading-relaxed pt-4 border-t border-[#E8ECE4]">
                <div>
                    <h2 class="text-base font-bold text-[#172326] mb-2">1. Yetkazib berish narxlari</h2>
                    <p class="text-[#64748B] mb-2">Standart shahar ichida yetkazib berish tarifi:</p>
                    <ul class="list-disc pl-5 space-y-1 text-[#64748B]">
                        <li>Standart posilka (5 kg gacha): <strong>20 000 UZS</strong></li>
                        <li>500 000 UZS dan ortiq buyurtmalar uchun: <strong>Bepul yetkazib berish</strong> aksiyasi amal qiladi</li>
                        <li>Katta hajmli yuklar va qurilish materiallari (tonna/m³): masofa va og'irlikka ko'ra buyurtma vaqtida avtomatik hisoblanadi</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-base font-bold text-[#172326] mb-2">2. Buyurtmani qabul qilib olish</h2>
                    <p class="text-[#64748B]">
                        Kuryer manzilga yetib kelishidan oldin siz bilan telefon orqali bog'lanadi. Mahsulotni qabul qilish paytida uning butunligini, tashqi ko'rinishini va to'liq jamlanganligini tekshirish tavsiya etiladi.
                    </p>
                </div>

                <div>
                    <h2 class="text-base font-bold text-[#172326] mb-2">3. Yetkazib berish holatini tekshirish</h2>
                    <p class="text-[#64748B]">
                        Buyurtma raqami yoki kuzatuv kodi orqali saytimizning <a href="{{ route('tracking') }}" class="text-[#123C2F] font-bold underline">Kuzatuv (Tracking)</a> sahifasida real vaqtda buyurtma holatini ko'rishingiz mumkin.
                    </p>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between border-t border-[#E8ECE4]">
                <a href="{{ route('catalog') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-[#123C2F] text-white text-xs font-bold hover:bg-[#0d2a21]">
                    Xarid qilishni boshlash →
                </a>
                <a href="{{ route('contact') }}" class="text-xs text-[#64748B] hover:text-[#123C2F] font-medium">
                    Savollaringiz bormi? Bog'lanish
                </a>
            </div>
        </div>

    </div>
</x-layouts.app>
