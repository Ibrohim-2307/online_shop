<x-layouts.app title="Bosh Sahifa">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-3 sm:pt-6 pb-12 sm:pb-16 space-y-8 sm:space-y-12">

        <!-- Hero Section: Lime Banner inspired by preview.mp4 (Super Compact on Mobile) -->
        <section aria-labelledby="hero-heading" class="relative rounded-2xl sm:rounded-[32px] bg-[#C9F46B] text-[#123C2F] p-3.5 sm:p-10 lg:p-14 overflow-hidden shadow-xs">
            <!-- Decorative Background Patterns -->
            <div class="absolute -right-16 -top-16 w-80 h-80 rounded-full bg-white/20 blur-2xl pointer-events-none"></div>
            <div class="absolute -right-8 -bottom-8 w-64 h-64 rounded-full bg-[#123C2F]/10 blur-xl pointer-events-none"></div>

            <div class="relative z-10 max-w-2xl">
                <!-- Tagline Badge (Desktop/Tablet) -->
                <div class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/80 backdrop-blur-sm text-[#123C2F] font-extrabold text-xs tracking-wider uppercase mb-4 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-[#123C2F]"></span>
                    Yangi Avlod Savdo Platformasi
                </div>

                <h1 id="hero-heading" class="text-base sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight mb-2 sm:mb-5 text-[#123C2F]">
                    Chakana va Optom Savdo — Birinchi Qo'ldan.
                </h1>

                <p class="hidden sm:block text-sm sm:text-lg text-[#123C2F]/85 font-medium leading-relaxed mb-6">
                    Qurilish materiallari, sanoat uskunalari va xalq iste'moli mollari. Hajm oshgan sari narx butun hajm uchun avtomatik arzonlashadi.
                </p>

                <!-- CTA Action Buttons -->
                <div class="flex items-center gap-2 sm:gap-3.5 mt-2 sm:mt-0">
                    <a href="{{ route('catalog', ['trade_mode' => 'wholesale_only']) }}" class="px-3.5 py-2 sm:px-7 sm:py-3.5 rounded-xl sm:rounded-2xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs sm:text-sm hover:bg-[#0d2a21] transition-all shadow-sm flex items-center gap-1">
                        <span>Optom Katalog</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                    <a href="{{ route('catalog') }}" class="px-3.5 py-2 sm:px-7 sm:py-3.5 rounded-xl sm:rounded-2xl bg-white text-[#123C2F] font-bold text-xs sm:text-sm hover:bg-white/90 transition-all border border-[#123C2F]/10 shadow-2xs">
                        Barcha Tovarlar
                    </a>
                </div>

                <!-- Feature Highlights (Desktop/Tablet) -->
                <div class="hidden sm:grid mt-8 pt-6 border-t border-[#123C2F]/15 grid-cols-3 gap-4 text-xs font-bold text-[#123C2F]/80">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#123C2F]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        All-Units Optom Narx
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#123C2F]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Ombor Nazorati
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#123C2F]" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Jonli Tracking
                    </div>
                </div>
            </div>
        </section>

        <!-- Category Grid Section -->
        <section aria-labelledby="categories-heading">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <div>
                    <h2 id="categories-heading" class="text-lg sm:text-2xl font-black text-[#172326] tracking-tight">Ommabop Toifalar</h2>
                    <p class="text-[11px] sm:text-xs text-[#64748B] mt-0.5">Ixtisoslashgan yo'nalishlar bo'yicha saralangan katalog</p>
                </div>
                <a href="{{ route('catalog') }}" class="text-xs font-bold text-[#123C2F] hover:underline flex items-center gap-1">
                    Barchasi →
                </a>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7 gap-2 sm:gap-4">
                @forelse($categories as $category)
                    <a href="{{ route('catalog', ['category' => $category->slug]) }}" 
                       class="group bg-white p-2.5 sm:p-5 rounded-2xl sm:rounded-3xl border border-[#E8ECE4] hover:border-[#123C2F] hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col items-center text-center relative overflow-hidden shadow-2xs">
                        
                        <!-- Icon / Emoji Container with Soft Tint & Hover Bounce -->
                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl {{ $category->bg_color }} border border-black/5 group-hover:border-[#123C2F]/20 flex items-center justify-center mb-1.5 sm:mb-3 transition-all duration-300 group-hover:scale-110 shadow-xs">
                            <span class="text-xl sm:text-3xl select-none filter drop-shadow-sm transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                                {{ $category->display_icon }}
                            </span>
                        </div>

                        <!-- Category Name -->
                        <span class="font-bold text-xs sm:text-sm text-[#172326] group-hover:text-[#123C2F] transition-colors leading-tight line-clamp-2">
                            {{ $category->name }}
                        </span>

                        <!-- Product Count Badge if available -->
                        @if(isset($category->products_count) && $category->products_count > 0)
                            <span class="text-[9px] sm:text-[11px] font-medium text-[#64748B] mt-0.5 sm:mt-1.5 group-hover:text-[#123C2F] transition-colors">
                                {{ $category->products_count }} ta
                            </span>
                        @endif
                    </a>
                @empty
                    <div class="col-span-full py-8 text-center text-[#64748B] text-sm bg-white rounded-3xl border border-[#E8ECE4]">
                        Toifalar mavjud emas
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Featured Products (Tavsiya etilgan mahsulotlar) -->
        <section aria-labelledby="featured-heading">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full bg-[#FAFBF7] border border-[#E8ECE4] text-[10px] sm:text-[11px] font-bold text-[#123C2F] mb-1">
                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-[#C9F46B]"></span>
                        Tanlangan mahsulotlar
                    </div>
                    <h2 id="featured-heading" class="text-lg sm:text-2xl font-black text-[#172326] tracking-tight">Ommabop Mahsulotlar</h2>
                </div>

                <!-- Quick Filter Tabs -->
                <div class="flex items-center gap-1 bg-white p-1 rounded-xl border border-[#E8ECE4] text-[11px] sm:text-xs font-semibold">
                    <a href="{{ route('catalog') }}" class="px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg sm:rounded-xl bg-[#123C2F] text-white">Barchasi</a>
                    <a href="{{ route('catalog', ['trade_mode' => 'wholesale_only']) }}" class="px-2.5 py-1 sm:px-3 sm:py-1.5 rounded-lg sm:rounded-xl hover:bg-[#FAFBF7] text-[#64748B]">Optom</a>
                </div>
            </div>

            <!-- Modern 2-Column Grid on Mobile for Maximum Compactness -->
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-5">
                @forelse($featuredProducts as $product)
                    <x-product-card :product="$product" />
                @empty
                    <div class="col-span-full py-12 text-center text-[#64748B] bg-white rounded-3xl border border-[#E8ECE4]">
                        Hozircha mahsulotlar kiritilmagan
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Wholesale All-Units Pricing Callout Banner -->
        <section class="rounded-2xl sm:rounded-3xl bg-[#123C2F] text-white p-5 sm:p-12 relative overflow-hidden shadow-lg">
            <div class="relative z-10 max-w-3xl">
                <span class="px-2.5 py-1 rounded-lg sm:rounded-xl bg-[#C9F46B] text-[#123C2F] text-[10px] sm:text-xs font-black uppercase tracking-wider">
                    Ulgurji Savdo Qoidasi
                </span>
                <h3 class="text-lg sm:text-3xl font-black tracking-tight mt-3 mb-2 sm:mb-3">
                    All-Units Narxlash: Hajm oshganda barcha dona arzonlashadi
                </h3>
                <p class="text-white/80 text-xs sm:text-base leading-relaxed mb-4 sm:mb-6">
                    Masalan, Sement M-500: 299 dona xarid qilsangiz, jami summa 89 700 000 UZS bo'ladi. Ammo 300 dona olsangiz, ulgurji pog'onaga o'tib donasi 270 000 UZS ga tushadi va <strong>jami summa 81 000 000 UZS</strong> bo'ladi (8.7 mln UZS tejaladi!).
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('catalog', ['trade_mode' => 'wholesale_only']) }}" class="px-5 py-2.5 sm:px-6 sm:py-3 rounded-xl sm:rounded-2xl bg-[#C9F46B] text-[#123C2F] font-bold text-xs sm:text-sm hover:bg-[#b8e855] transition-colors">
                        Optom Takliflarni Ko'rish
                    </a>
                    <span class="text-[11px] sm:text-xs text-white/60">Serverda aniq BCMath hisobi (float xatosi yo'q)</span>
                </div>
            </div>
        </section>

        <!-- Verified Sellers Section -->
        <section aria-labelledby="sellers-heading">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <div>
                    <h2 id="sellers-heading" class="text-lg sm:text-2xl font-black text-[#172326] tracking-tight">Kafolatlangan Sotuvchilar</h2>
                    <p class="text-[11px] sm:text-xs text-[#64748B] mt-0.5">Tekshirilgan INN va litsenziyaga ega rasmiy do'konlar</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
                @forelse($sellers as $seller)
                    <div class="bg-white p-3.5 sm:p-6 rounded-2xl sm:rounded-3xl border border-[#E8ECE4] hover:shadow-lg transition-all duration-200 flex flex-col justify-between shadow-2xs">
                        <div>
                            <div class="flex items-start justify-between mb-2 sm:mb-4">
                                <div class="w-9 h-9 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-[#123C2F] text-[#C9F46B] font-black text-sm sm:text-xl flex items-center justify-center">
                                    {{ mb_substr($seller->name, 0, 1) }}
                                </div>
                                <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-lg sm:rounded-xl bg-emerald-50 text-emerald-800 text-[9px] sm:text-[10px] font-bold border border-emerald-200">
                                    ✓ Tasdiqlangan
                                </span>
                            </div>

                            <h3 class="font-bold text-xs sm:text-base text-[#172326] mb-0.5 sm:mb-1 truncate">
                                <a href="{{ route('seller.show', $seller->slug) }}" class="hover:text-[#123C2F]">
                                    {{ $seller->name }}
                                </a>
                            </h3>

                            <p class="text-[10px] sm:text-xs text-[#64748B] line-clamp-1 sm:line-clamp-2 mb-2 sm:mb-4">
                                {{ $seller->legal_name ?? $seller->name }}
                            </p>
                        </div>

                        <div class="pt-2 sm:pt-4 border-t border-[#E8ECE4] flex items-center justify-between text-[10px] sm:text-xs">
                            <span class="text-[#64748B] font-semibold">
                                {{ $seller->products_count }} ta tovar
                            </span>
                            <span class="flex items-center gap-1 font-bold text-[#172326]">
                                ★ {{ number_format($seller->rating, 1) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-[#64748B] text-sm bg-white rounded-3xl border border-[#E8ECE4]">
                        Sotuvchilar ro'yxati shakllanmoqda
                    </div>
                @endforelse
            </div>
        </section>

    </div>
</x-layouts.app>
