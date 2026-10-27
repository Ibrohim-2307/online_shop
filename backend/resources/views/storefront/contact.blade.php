<x-layouts.app title="Bog'lanish va Yordam">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-20">
        
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">Bog'lanish</span>
        </nav>

        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-10 shadow-xs space-y-8">
            <div>
                <span class="px-3 py-1 rounded-full bg-[#123C2F]/10 text-[#123C2F] text-xs font-bold uppercase tracking-wider">
                    Yordam & Aloqa
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-[#172326] tracking-tight mt-3">
                    Mijozlarni Qo'llab-quvvatlash Xizmati
                </h1>
                <p class="text-xs text-[#64748B] mt-1.5 leading-relaxed">
                    Savollaringiz, takliflaringiz yoki buyurtmalar bo'yicha yordam kerak bo'lsa, biz sizga yordam berishdan mamnunmiz.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="p-6 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-2">
                    <span class="text-2xl">📞</span>
                    <h3 class="font-bold text-sm text-[#172326]">Telefon Aloqa</h3>
                    <p class="text-xs text-[#64748B]">Har kuni 09:00 dan 21:00 gacha</p>
                    <p class="font-mono font-bold text-sm text-[#123C2F] pt-1">+998 (71) 200-00-00</p>
                </div>

                <div class="p-6 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-2">
                    <span class="text-2xl">✉️</span>
                    <h3 class="font-bold text-sm text-[#172326]">Elektron Pochta</h3>
                    <p class="text-xs text-[#64748B]">Savollar va rasmiy takliflar uchun</p>
                    <p class="font-bold text-xs text-[#123C2F] pt-1">support@market.uz</p>
                </div>

                <div class="p-6 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-2">
                    <span class="text-2xl">💬</span>
                    <h3 class="font-bold text-sm text-[#172326]">Telegram Bot</h3>
                    <p class="text-xs text-[#64748B]">Tezkor savol-javob va buyurtma holati</p>
                    <p class="font-bold text-xs text-[#123C2F] pt-1">@market_support_bot</p>
                </div>
            </div>

            <!-- Seller Support Information -->
            <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 space-y-3">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">🏪</span>
                    <div>
                        <h3 class="font-bold text-sm text-[#172326]">Sotuvchi bilan to'g'ridan-to'g'ri bog'lanish</h3>
                        <p class="text-xs text-[#64748B]">Mahsulot xususiyatlari, mavjudligi yoki ulgurji narxlar bo'yicha</p>
                    </div>
                </div>
                <p class="text-xs text-[#172326] leading-relaxed">
                    Har bir tovar sahifasida rasmiy sotuvchi do'koni ko'rsatilgan. Sotuvchining sahifasiga o'tib, uning aloqa telefon raqami, manzili va reytingi bilan tanishishingiz hamda to'g'ridan-to'g'ri bog'lanishingiz mumkin.
                </p>
            </div>

            <!-- Frequently Asked Questions -->
            <div class="space-y-4 pt-2">
                <h2 class="text-base font-bold text-[#172326]">Ko'p beriladigan savollar</h2>
                
                <div class="space-y-3 text-xs">
                    <div class="p-4 rounded-xl border border-[#E8ECE4] bg-white">
                        <h4 class="font-bold text-[#172326] mb-1">Buyurtmam qachon yetib keladi?</h4>
                        <p class="text-[#64748B]">Toshkent bo'yicha 24 soat ichida, viloyatlarga 2-4 kun mobaynida yetkaziladi. <a href="{{ route('tracking') }}" class="text-[#123C2F] font-bold underline">Kuzatuv sahifasida</a> real vaqtda ko'rishingiz mumkin.</p>
                    </div>

                    <div class="p-4 rounded-xl border border-[#E8ECE4] bg-white">
                        <h4 class="font-bold text-[#172326] mb-1">Qanday to'lov usullari mavjud?</h4>
                        <p class="text-[#64748B]">Click Up, Payme to'lov tizimlari orqali onlayn yoki buyurtmani qabul qilib olganda kuryerga (naqd yoki karta orqali) to'lashingiz mumkin.</p>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between border-t border-[#E8ECE4]">
                <a href="{{ route('home') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-[#123C2F] text-white text-xs font-bold hover:bg-[#0d2a21]">
                    Bosh sahifaga qaytish
                </a>
                <a href="{{ route('delivery') }}" class="text-xs text-[#64748B] hover:text-[#123C2F] font-medium">
                    Yetkazib berish qoidalari →
                </a>
            </div>
        </div>

    </div>
</x-layouts.app>
