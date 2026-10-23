<x-layouts.app title="Mahsulotlar Katalogi">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16">

        <!-- Breadcrumbs -->
        <nav class="flex flex-wrap items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">Katalog</span>
            @if($selectedCategory)
                <span>/</span>
                <span class="text-[#123C2F] font-bold">{{ $selectedCategory->name }}</span>
            @endif
        </nav>

        <!-- Page Header & Mobile Filter Toggle -->
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-[#172326] tracking-tight">
                    {{ $selectedCategory ? $selectedCategory->name : 'Barcha Mahsulotlar' }}
                </h1>
                <p class="text-xs text-[#64748B] mt-1">
                    Jami {{ $products->total() }} ta mahsulot topildi
                    @if(request('q'))
                        • "{{ request('q') }}" so'rovi bo'yicha
                    @endif
                </p>
            </div>

            <!-- Sorting & Filter Controls -->
            <div class="flex items-center gap-3">
                <form method="GET" action="{{ route('catalog') }}" class="flex items-center gap-2">
                    @foreach(request()->except('sort', 'page') as $key => $val)
                        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                    @endforeach

                    <label for="sort-select" class="text-xs font-semibold text-[#64748B] hidden sm:inline">Saralash:</label>
                    <select id="sort-select" name="sort" onchange="this.form.submit()" class="h-10 px-3 pr-8 rounded-xl bg-white border border-[#E8ECE4] text-xs font-semibold text-[#172326] focus:border-[#123C2F] outline-none">
                        <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Eng yangilari</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Narx: arzondan qimmatga</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Narx: qimmatdan arzonga</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nomi bo'yicha (A-Z)</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Filter Sidebar -->
            <aside class="space-y-6">
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

            <!-- Product Grid -->
            <div class="lg:col-span-3">
                @if($products->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <!-- Pagination Links (with Query Preservation) -->
                    <div class="mt-10">
                        {{ $products->links() }}
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="bg-white rounded-3xl border border-[#E8ECE4] p-12 text-center max-w-lg mx-auto">
                        <div class="w-16 h-16 rounded-2xl bg-[#FAFBF7] text-[#64748B] flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="font-bold text-lg text-[#172326] mb-1">Hech narsa topilmadi</h3>
                        <p class="text-xs text-[#64748B] mb-6">
                            So'rov bo'yicha mahsulot topilmadi. Qidiruv so'zini o'zgartiring yoki filtrlarni tozalang.
                        </p>
                        <a href="{{ route('catalog') }}" class="inline-flex px-5 py-2.5 rounded-xl bg-[#123C2F] text-white font-bold text-xs hover:bg-[#0d2a21]">
                            Filtrlarni tozalash
                        </a>
                    </div>
                @endif
            </div>

        </div>

    </div>
</x-layouts.app>
