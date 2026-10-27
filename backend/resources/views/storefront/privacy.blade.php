<x-layouts.app title="Maxfiylik siyosati">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-20">
        
        <!-- Breadcrumbs -->
        <nav class="flex items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">Maxfiylik siyosati</span>
        </nav>

        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-10 shadow-xs space-y-8">
            <div>
                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider">
                    Xavfsizlik kafolati
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-[#172326] tracking-tight mt-3">
                    Maxfiylik va Shaxsiy Ma'lumotlarni Himoya Qilish Siyosati
                </h1>
                <p class="text-xs text-[#64748B] mt-1.5 leading-relaxed">
                    Sizning shaxsiy ma'lumotlaringiz qanday to'planishi, ishlatilishi va qat'iy himoya qilinishi haqida batafsil ma'lumot.
                </p>
            </div>

            <div class="space-y-6 text-xs text-[#172326] leading-relaxed pt-2">
                <div>
                    <h2 class="text-base font-bold text-[#172326] mb-2">1. To'planadigan ma'lumotlar</h2>
                    <p class="text-[#64748B]">
                        Platformada xaridni amalga oshirish va buyurtmani yetkazish uchun quyidagi ma'lumotlar so'raladi:
                    </p>
                    <ul class="list-disc pl-5 mt-2 space-y-1 text-[#64748B]">
                        <li>F.I.Sh. va bog'lanish uchun telefon raqami;</li>
                        <li>Yetkazib berish manzili (shahar, tuman, ko'cha, uy raqami);</li>
                        <li>Buyurtmalar tarixi va to'lov tranzaksiyalari identifikatori (ID).</li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-base font-bold text-[#172326] mb-2">2. Bank karta ma'lumotlarining xavfsizligi</h2>
                    <p class="text-[#64748B]">
                        Biz mijozlarning bank karta raqamlarini (PAN) va xavfsizlik kodlarini (CVV) <strong>saqlamaymiz</strong>. Barcha to'lovlar O'zbekiston Markaziy Banki litsenziyasiga ega rasmiy to'lov tizimlari (Click va Payme) orqali xalqaro PCI-DSS standartlariga muvofiq shifrlangan holda amalga oshiriladi.
                    </p>
                </div>

                <div>
                    <h2 class="text-base font-bold text-[#172326] mb-2">3. Ma'lumotlarni uchinchi shaxslarga bermaslik</h2>
                    <p class="text-[#64748B]">
                        Mijozlarning shaxsiy ma'lumotlari qonun hujjatlarida belgilangan hollar (sud va huquqni muhofaza qiluvchi organlar qonuniy talabi)dan tashqari hech qanday uchinchi shaxslarga taqdim etilmaydi, sotilmaydi va tarqatilmaydi. Yetkazib berish uchun zarur bo'lgan manzil va telefon raqami faqat buyurtmani bajaruvchi rasmiy kuryerga xavfsiz kanallar orqali taqdim etiladi.
                    </p>
                </div>

                <div>
                    <h2 class="text-base font-bold text-[#172326] mb-2">4. Ma'lumotlarni o'chirish yoki o'zgartirish huquqi</h2>
                    <p class="text-[#64748B]">
                        Foydalanuvchi istalgan vaqtda shaxsiy kabinetida ma'lumotlarini o'zgartirishi yoki qo'llab-quvvatlash xizmati orqali hisobini o'chirishni talab qilish huquqiga ega.
                    </p>
                </div>
            </div>

            <div class="pt-4 flex items-center justify-between border-t border-[#E8ECE4]">
                <a href="{{ route('home') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-[#123C2F] text-white text-xs font-bold hover:bg-[#0d2a21]">
                    Bosh sahifaga qaytish
                </a>
                <a href="{{ route('contact') }}" class="text-xs text-[#64748B] hover:text-[#123C2F] font-medium">
                    Maxfiylik bo'yicha savollar
                </a>
            </div>
        </div>

    </div>
</x-layouts.app>
