<x-layouts.app title="Mahsulotlar Katalogi">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-3 sm:pt-6 pb-12 sm:pb-16"
         x-data="{ mobileFiltersOpen: false }">

        <!-- Breadcrumbs -->
        <nav class="flex flex-wrap items-center gap-1.5 text-xs text-[#64748B] mb-3 sm:mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">Katalog</span>
            @if($selectedCategory)
                <span>/</span>
                <span class="text-[#123C2F] font-bold">{{ $selectedCategory->name }}</span>
            @endif
        </nav>

        <!-- Page Header & Sorting Controls -->
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-4 sm:mb-8">
            <div>
                <h1 class="text-xl sm:text-3xl font-black text-[#172326] tracking-tight">
                    {{ $selectedCategory ? $selectedCategory->name : 'Barcha Mahsulotlar' }}
                </h1>
                <p class="text-[11px] sm:text-xs text-[#64748B] mt-0.5 sm:mt-1">
                    Jami {{ $products->total() }} ta mahsulot topildi
                    @if(request('q'))
                        • "{{ request('q') }}" so'rovi bo'yicha
                    @endif
                </p>
            </div>

            <!-- Sorting & Filter Controls -->
            <div class="flex items-center gap-2">
                <form method="GET" action="{{ route('catalog') }}" class="flex items-center gap-2 w-full sm:w-auto">
                    @foreach(request()->except('sort', 'page') as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach

                    <label for="sort-select" class="text-xs font-semibold text-[#64748B] hidden sm:inline">Saralash:</label>
                    <select id="sort-select" name="sort" onchange="this.form.submit()" class="h-9 sm:h-10 px-2.5 sm:px-3 pr-7 rounded-xl bg-white border border-[#E8ECE4] text-xs font-semibold text-[#172326] focus:border-[#123C2F] outline-none shadow-2xs">
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Eng yangilari</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Narx: arzondan qimmatga</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Narx: qimmatdan arzonga</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nomi bo'yicha (A-Z)</option>
                    </select>
                </form>

                <!-- Mobile Filter Trigger Button -->
                <button type="button" 
                        @click="mobileFiltersOpen = true" 
                        class="lg:hidden h-9 px-3 rounded-xl bg-[#123C2F] text-[#C9F46B] text-xs font-bold flex items-center gap-1.5 shadow-2xs whitespace-nowrap">
                    <span>⚡ Filtrlar</span>
                    @if(request()->hasAny(['category', 'trade_mode', 'seller', 'q']))
                        <span class="w-2 h-2 rounded-full bg-[#C9F46B] animate-pulse"></span>
                    @endif
                </button>
            </div>
        </div>

        <!-- Mobile Quick Filter Pills (Trade Mode) -->
        <div class="lg:hidden mb-4 overflow-x-auto no-scrollbar flex items-center gap-1.5 text-xs">
            @php
                $currentTrade = request('trade_mode', 'all');
            @endphp
            <a href="{{ route('catalog', array_merge(request()->except('trade_mode', 'page'), ['trade_mode' => 'all'])) }}" 
               class="px-3 py-1.5 rounded-xl font-bold whitespace-nowrap border shadow-2xs {{ $currentTrade === 'all' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]' }}">
                Barchasi
            </a>
            <a href="{{ route('catalog', array_merge(request()->except('trade_mode', 'page'), ['trade_mode' => 'retail_only'])) }}" 
               class="px-3 py-1.5 rounded-xl font-bold whitespace-nowrap border shadow-2xs {{ $currentTrade === 'retail_only' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]' }}">
                Chakana
            </a>
            <a href="{{ route('catalog', array_merge(request()->except('trade_mode', 'page'), ['trade_mode' => 'wholesale_only'])) }}" 
               class="px-3 py-1.5 rounded-xl font-bold whitespace-nowrap border shadow-2xs {{ $currentTrade === 'wholesale_only' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]' }}">
                Faqat Optom
            </a>
        </div>

        <!-- Mobile Filter Modal / Drawer -->
        <div x-show="mobileFiltersOpen" 
             x-cloak 
             class="fixed inset-0 z-50 lg:hidden"
             role="dialog" 
             aria-modal="true">
            <!-- Backdrop -->
            <div x-show="mobileFiltersOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-200" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="transition-opacity ease-linear duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 @click="mobileFiltersOpen = false" 
                 class="fixed inset-0 bg-black/50 backdrop-blur-xs"></div>

            <!-- Drawer Container -->
            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div x-show="mobileFiltersOpen" 
                     x-transition:enter="transition ease-in-out duration-300 transform" 
                     x-transition:enter-start="translate-x-full" 
                     x-transition:enter-end="translate-x-0" 
                     x-transition:leave="transition ease-in-out duration-300 transform" 
                     x-transition:leave-start="translate-x-0" 
                     x-transition:leave-end="translate-x-full" 
                     class="w-screen max-w-md bg-white p-6 shadow-2xl flex flex-col justify-between overflow-y-auto">
                    
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-4 border-b border-[#E8ECE4]">
                            <div>
                                <h3 class="font-black text-base text-[#172326]">Katalog Filtrlari</h3>
                                <p class="text-[11px] text-[#64748B]">Toifalar va savdo rejimini tanlang</p>
                            </div>
                            <button @click="mobileFiltersOpen = false" class="p-2 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-[#172326] text-base font-bold">
                                ✕
                            </button>
                        </div>

                        <!-- Filter Form -->
                        <form method="GET" action="{{ route('catalog') }}" class="space-y-6 pt-5">
                            @if(request('q'))
                                <input type="hidden" name="q" value="{{ request('q') }}">
                            @endif
                            @if(request('sort'))
                                <input type="hidden" name="sort" value="{{ request('sort') }}">
                            @endif

                            <!-- Trade Mode Filter -->
                            <div>
                                <label class="block text-xs font-extrabold uppercase tracking-wider text-[#64748B] mb-3">
                                    Savdo Rejimi
                                </label>
                                <div class="space-y-2 text-xs">
                                    <label class="flex items-center gap-2.5 p-2 rounded-xl border border-[#E8ECE4] cursor-pointer hover:bg-[#FAFBF7]">
                                        <input type="radio" name="trade_mode" value="all" {{ request('trade_mode', 'all') === 'all' ? 'checked' : '' }} class="accent-[#123C2F]">
                                        <span class="font-medium text-[#172326]">Barchasi (Chakana + Optom)</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 p-2 rounded-xl border border-[#E8ECE4] cursor-pointer hover:bg-[#FAFBF7]">
                                        <input type="radio" name="trade_mode" value="retail_only" {{ request('trade_mode') === 'retail_only' ? 'checked' : '' }} class="accent-[#123C2F]">
                                        <span class="font-medium text-[#172326]">Faqat Chakana</span>
                                    </label>
                                    <label class="flex items-center gap-2.5 p-2 rounded-xl border border-[#E8ECE4] cursor-pointer hover:bg-[#FAFBF7]">
                                        <input type="radio" name="trade_mode" value="wholesale_only" {{ request('trade_mode') === 'wholesale_only' ? 'checked' : '' }} class="accent-[#123C2F]">
                                        <span class="font-medium text-[#172326]">Faqat Ulgurji (Optom)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Categories Filter -->
                            <div>
                                <label class="block text-xs font-extrabold uppercase tracking-wider text-[#64748B] mb-3">
                                    Toifalar
                                </label>
                                <div class="space-y-1.5 max-h-72 overflow-y-auto pr-1">
                                    <a href="{{ route('catalog', request()->except('category', 'page')) }}" 
                                       class="flex items-center justify-between px-3 py-2 rounded-xl text-xs {{ !request('category') ? 'bg-[#123C2F] text-white font-bold' : 'text-[#64748B] hover:bg-[#FAFBF7] border border-[#E8ECE4]' }}">
                                        <span class="flex items-center gap-2">
                                            <span>🌐</span>
                                            <span>Barcha toifalar</span>
                                        </span>
                                    </a>
                                    @foreach($categories as $cat)
                                        <a href="{{ route('catalog', array_merge(request()->except('page'), ['category' => $cat->slug])) }}" 
                                           class="flex items-center justify-between px-3 py-2 rounded-xl text-xs {{ request('category') === $cat->slug ? 'bg-[#123C2F] text-white font-bold' : 'text-[#172326] hover:bg-[#FAFBF7] border border-[#E8ECE4]' }}">
                                            <span class="flex items-center gap-2">
                                                <span>{{ $cat->display_icon }}</span>
                                                <span class="leading-snug">{{ $cat->name }}</span>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div class="pt-4 border-t border-[#E8ECE4] flex items-center gap-3">
                                <button type="submit" class="flex-1 py-3 rounded-xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs shadow-md">
                                    Filtrlarni Qo'llash
                                </button>
                                @if(request()->hasAny(['q', 'category', 'trade_mode', 'seller', 'sort']))
                                    <a href="{{ route('catalog') }}" class="py-3 px-4 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-red-600 font-bold text-xs text-center">
                                        Tozalash
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 lg:gap-8">
            
            <!-- Desktop Filter Sidebar (Hidden on mobile) -->
            <aside class="hidden lg:block space-y-6">
                <div class="bg-white p-6 rounded-3xl border border-[#E8ECE4] sticky top-24">
                    <div class="flex items-center justify-between pb-4 border-b border-[#E8ECE4]">
                        <h3 class="font-black text-sm uppercase tracking-wider text-[#172326]">Filtrlar</h3>
                        @if(request()->hasAny(['q', 'category', 'trade_mode', 'seller', 'sort']))
                            <a href="{{ route('catalog') }}" class="text-xs text-red-600 hover:underline font-semibold">Tozalash</a>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('catalog') }}" class="space-y-6 pt-4">
                        @if(request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif

                        <!-- Trade Mode Filter -->
                        <div>
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-[#64748B] mb-3">
                                Savdo Rejimi
                            </label>
                            <div class="space-y-2 text-sm">
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="radio" name="trade_mode" value="all" {{ request('trade_mode', 'all') === 'all' ? 'checked' : '' }} onchange="this.form.submit()" class="accent-[#123C2F]">
                                    <span class="text-xs font-medium text-[#172326]">Barchasi (Chakana + Optom)</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="radio" name="trade_mode" value="retail_only" {{ request('trade_mode') === 'retail_only' ? 'checked' : '' }} onchange="this.form.submit()" class="accent-[#123C2F]">
                                    <span class="text-xs font-medium text-[#172326]">Faqat Chakana</span>
                                </label>
                                <label class="flex items-center gap-2.5 cursor-pointer">
                                    <input type="radio" name="trade_mode" value="wholesale_only" {{ request('trade_mode') === 'wholesale_only' ? 'checked' : '' }} onchange="this.form.submit()" class="accent-[#123C2F]">
                                    <span class="text-xs font-medium text-[#172326]">Faqat Ulgurji (Optom)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Categories Filter -->
                        <div>
                            <label class="block text-xs font-extrabold uppercase tracking-wider text-[#64748B] mb-3">
                                Toifalar
                            </label>
                            <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
                                <a href="{{ route('catalog', request()->except('category', 'page')) }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs {{ !request('category') ? 'bg-[#123C2F] text-white font-bold' : 'text-[#64748B] hover:bg-[#FAFBF7]' }}">
                                    <span class="flex items-center gap-2">
                                        <span class="text-sm">🌐</span>
                                        <span>Barcha toifalar</span>
                                    </span>
                                </a>
                                @foreach($categories as $cat)
                                    <a href="{{ route('catalog', array_merge(request()->except('page'), ['category' => $cat->slug])) }}" 
                                       class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs {{ request('category') === $cat->slug ? 'bg-[#123C2F] text-white font-bold' : 'text-[#172326] hover:bg-[#FAFBF7]' }}">
                                        <span class="flex items-center gap-2.5">
                                            <span class="text-base select-none">{{ $cat->display_icon }}</span>
                                            <span class="leading-snug">{{ $cat->name }}</span>
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                    </form>
                </div>
            </aside>

            <!-- Product Grid: 2 columns on mobile, 3 on large screens -->
            <div class="lg:col-span-3">
                @if($products->count() > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-2 xl:grid-cols-3 gap-2.5 sm:gap-5">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <!-- Pagination Links (with Query Preservation) -->
                    <div class="mt-8 sm:mt-10">
                        {{ $products->links() }}
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#E8ECE4] p-6 sm:p-12 text-center max-w-lg mx-auto shadow-2xs">
                        <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-xl sm:rounded-2xl bg-[#FAFBF7] text-[#64748B] flex items-center justify-center mx-auto mb-3 sm:mb-4">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-base sm:text-lg text-[#172326] mb-1">Hech narsa topilmadi</h3>
                        <p class="text-xs text-[#64748B] mb-5 sm:mb-6">
                            So'rov bo'yicha mahsulot topilmadi. Qidiruv so'zini o'zgartiring yoki filtrlarni tozalang.
                        </p>
                        <a href="{{ route('catalog') }}" class="inline-flex px-4 py-2 sm:px-5 sm:py-2.5 rounded-xl bg-[#123C2F] text-white font-bold text-xs hover:bg-[#0d2a21]">
                            Filtrlarni tozalash
                        </a>
                    </div>
                @endif
            </div>

        </div>

    </div>
</x-layouts.app>
