<x-layouts.app title="Tovarlarni qaytarish siyosati">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-20">
        
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">Qaytarish siyosati</span>
        </nav>

        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-10 shadow-xs space-y-8">
            <div>
                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-bold uppercase tracking-wider">
                    Iste'molchi huquqlari
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-[#172326] tracking-tight mt-3">
                    Tovarni qaytarish va almashtirish tartibi
                </h1>
                <p class="text-xs text-[#64748B] mt-1.5 leading-relaxed">
                    O'zbekiston Respublikasining "Iste'molchilarning huquqlarini himoya qilish to'g'risida"gi Qonuniga muvofiq mahsulotlarni qaytarish shartlari.
                </p>
            </div>

            <div class="space-y-6 text-xs text-[#172326] leading-relaxed pt-2">
                <div>
                    <h2 class="text-base font-bold text-[#172326] mb-2">1. Qaytarish muddati</h2>
                    <p class="text-[#64748B]">
                        Xaridor sifatli nooziq-ovqat tovarini qabul qilib olgan kundan e'tiboran <strong>10 (o'n) kun</strong> mobaynida uning asl ko'rinishi, qadog'i, yorliqlari va xarid cheki saqlangan holda qaytarish yoki shunga o'xshash tovarga almashtirish huquqiga ega.
                    </p>
                </div>

                <div>
                    <h2 class="text-base font-bold text-[#172326] mb-2">2. Sifatsiz tovarlar</h2>
                    <p class="text-[#64748B]">
                        Agar mahsulotda zavod nuqsoni, shikastlanish yoki buyurtma qilingan xususiyatlarga nomuvofiqlik aniqlansa, xaridor quyidagi huquqlarga ega:
                    </p>
                    <ul class="list-disc pl-5 mt-2 space-y-1 text-[#64748B]">
                        <li>Tovarni shunga o'xshash sifatli mahsulotga bepul almashtirish;</li>
                        <li>Shartnomani bekor qilib, to'langan pul summasini to'liq qaytarib olish;</li>
                        <li>Tovarning kamchiliklarini sotuvchi hisobidan bartaraf etilishini talab qilish.</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-base font-bold text-[#172326] mb-2">3. Pul mablag'larini qaytarish (Refund)</h2>
                    <p class="text-[#64748B]">
                        Onlayn to'lov (Click yoki Payme) orqali to'langan buyurtmalar uchun qaytariladigan summa to'lov amalga oshirilgan bank kartasiga rasmiy to'lov tizimi orqali 1-3 bank ish kuni ichida to'liq qaytariladi. Naqd to'langan buyurtmalar bo'yicha pul mijozning bank kartasiga yoki naqd shaklda rasmiylashtiriladi.
                    </p>
                </div>

                <div>
                    <h2 class="text-base font-bold text-[#172326] mb-2">4. Qaytarish arizasini berish</h2>
                    <p class="text-[#64748B]">
                        Qaytarish bo'yicha ariza berish uchun <a href="{{ route('orders.hub') }}" class="text-[#123C2F] font-bold underline">Mening buyurtmalarim</a> bo'limiga kiring yoki qo'llab-quvvatlash xizmati bilan bog'laning.
                    </p>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between border-t border-[#E8ECE4]">
                <a href="{{ route('orders.hub') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-[#123C2F] text-white text-xs font-bold hover:bg-[#0d2a21]">
                    Buyurtmalarimga o'tish →
                </a>
                <a href="{{ route('contact') }}" class="text-xs text-[#64748B] hover:text-[#123C2F] font-medium">
                    Qo'llab-quvvatlash bilan bog'lanish
                </a>
            </div>
        </div>

    </div>
</x-layouts.app>
