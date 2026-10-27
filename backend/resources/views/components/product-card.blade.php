@props(['product'])

@php
    $firstVariant = $product->variants->first();
    $tiers = $firstVariant ? $firstVariant->priceTiers->sortBy('min_quantity') : collect();
    $baseTier = $tiers->first();
    $wholesaleTier = $tiers->skip(1)->first();
    $unit = $product->unit === 'piece' ? 'dona' : ($product->unit === 'box' ? 'quti' : ($product->unit === 'ton' ? 'tonna' : 'kg'));
@endphp

<div class="group bg-white rounded-2xl sm:rounded-3xl border border-[#E8ECE4] hover:border-[#123C2F]/30 hover:shadow-xl transition-all duration-300 flex flex-col justify-between overflow-hidden relative shadow-2xs"
     x-data="{ isHovered: false }"
     @mouseenter="isHovered = true"
     @mouseleave="isHovered = false">

    <!-- Wishlist Button -->
    <button type="button" 
            @click.stop="$store.wishlist.toggle({{ $product->id }})" 
            class="absolute top-2 right-2 sm:top-3 sm:right-3 z-10 w-7 h-7 sm:w-9 sm:h-9 rounded-full bg-white/90 backdrop-blur-sm shadow-sm flex items-center justify-center text-[#64748B] hover:text-red-500 hover:scale-110 transition-all"
            :class="$store.wishlist.has({{ $product->id }}) ? 'text-red-500 fill-red-500' : ''"
            title="Sevimlilarga qo'shish">
        <svg class="w-4 h-4 sm:w-5 sm:h-5" :fill="$store.wishlist.has({{ $product->id }}) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg>
    </button>

    <!-- Top Card Image Container -->
    <a href="{{ route('product.show', $product->slug) }}" class="block relative aspect-square sm:aspect-4/3 bg-[#FAFBF7] overflow-hidden flex items-center justify-center">
        <!-- Trade Mode Badge -->
        <div class="absolute top-2 left-2 sm:top-3 sm:left-3 flex flex-wrap gap-1 z-10">
            @if($product->trade_mode === 'wholesale_only')
                <span class="px-1.5 py-0.5 sm:px-2.5 sm:py-1 rounded-lg sm:rounded-xl bg-[#123C2F] text-[#C9F46B] text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider shadow-xs">
                    Faqat Optom
                </span>
            @elseif($product->trade_mode === 'retail_only')
                <span class="px-1.5 py-0.5 sm:px-2.5 sm:py-1 rounded-lg sm:rounded-xl bg-white/90 backdrop-blur-sm border border-[#E8ECE4] text-[#172326] text-[9px] sm:text-[10px] font-bold shadow-xs">
                    Chakana
                </span>
            @else
                <span class="px-1.5 py-0.5 sm:px-2.5 sm:py-1 rounded-lg sm:rounded-xl bg-[#C9F46B] text-[#123C2F] text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider shadow-xs">
                    Chakana + Optom
                </span>
            @endif
        </div>

        @if($product->image_url)
            <img src="{{ $product->image_url }}" 
                 alt="{{ $product->name }}" 
                 loading="lazy" 
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <!-- SVG Icon Graphic based on Category/Product -->
            <div class="w-12 h-12 sm:w-20 sm:h-20 rounded-xl sm:rounded-2xl bg-white border border-[#E8ECE4] flex items-center justify-center text-[#123C2F] shadow-sm group-hover:scale-105 transition-transform duration-300">
                @if(str_contains(strtolower($product->name), 'sement'))
                    <svg class="w-6 h-6 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                @elseif(str_contains(strtolower($product->name), 'generator') || str_contains(strtolower($product->name), 'uskuna'))
                    <svg class="w-6 h-6 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                @elseif(str_contains(strtolower($product->name), 'suv'))
                    <svg class="w-6 h-6 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                @elseif(str_contains(strtolower($product->name), 'armatura'))
                    <svg class="w-6 h-6 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                @else
                    <svg class="w-6 h-6 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                @endif
            </div>
        @endif
    </a>

    <!-- Card Content -->
    <div class="p-2.5 sm:p-5 flex flex-col flex-1 justify-between">
        <div>
            <!-- Seller & Rating -->
            <div class="flex items-center justify-between text-[10px] sm:text-xs text-[#64748B] mb-1 sm:mb-2">
                <a href="{{ route('seller.show', $product->seller->slug ?? '') }}" class="hover:text-[#123C2F] font-medium truncate max-w-[70px] sm:max-w-[140px]" title="{{ $product->seller->name ?? 'Sotuvchi' }}">
                    {{ $product->seller->name ?? 'Rasmiy do\'kon' }}
                </a>
                <span class="flex items-center gap-1 font-semibold text-[#172326]">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-amber-500 fill-amber-500" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                    </svg>
                    {{ number_format($product->seller->rating ?? 5.0, 1) }}
                </span>
            </div>

            <!-- Product Title -->
            <h3 class="font-bold text-xs sm:text-base text-[#172326] leading-tight sm:leading-snug line-clamp-2 min-h-[28px] sm:min-h-[44px] mb-1 sm:mb-3 group-hover:text-[#123C2F] transition-colors">
                <a href="{{ route('product.show', $product->slug) }}">
                    {{ $product->name }}
                </a>
            </h3>
        </div>

        <div>
            <!-- Pricing Block -->
            <div class="pt-1.5 sm:pt-3 border-t border-[#E8ECE4]">
                @if($baseTier)
                    <div class="flex items-baseline justify-between gap-1 mb-0.5 sm:mb-1">
                        <div>
                            <span class="text-[10px] sm:text-xs text-[#64748B]">Boshlang'ich:</span>
                            <div class="text-sm sm:text-lg font-black text-[#123C2F] tracking-tight">
                                {{ number_format($baseTier->unit_price, 0, ' ', ' ') }} <span class="text-[10px] sm:text-xs font-bold">UZS</span>
                            </div>
                        </div>
                        <span class="text-[10px] sm:text-xs text-[#64748B]">/ {{ $unit }}</span>
                    </div>

                    <!-- Wholesale Tier Preview -->
                    @if($wholesaleTier)
                        <div class="mt-1 sm:mt-1.5 p-1 sm:p-2 rounded-lg sm:rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] flex items-center justify-between text-[10px] sm:text-xs">
                            <span class="font-bold text-[#123C2F] truncate">
                                Optom: {{ number_format($wholesaleTier->unit_price, 0, ' ', ' ') }}
                            </span>
                            <span class="text-[9px] sm:text-[11px] font-semibold text-[#64748B] shrink-0 ml-1">
                                {{ (int)$wholesaleTier->min_quantity }}+ {{ $unit }}
                            </span>
                        </div>
                    @endif
                @else
                    <div class="text-xs sm:text-sm font-bold text-amber-600">Narx so'rov bo'yicha</div>
                @endif
            </div>

            <!-- Action Button -->
            <div class="mt-2 sm:mt-4">
                <a href="{{ route('product.show', $product->slug) }}" class="w-full h-8 sm:h-11 rounded-xl sm:rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] group-hover:bg-[#123C2F] group-hover:text-white group-hover:border-[#123C2F] font-bold text-[11px] sm:text-xs flex items-center justify-center gap-1 transition-all">
                    <span class="hidden sm:inline">Tafsilotlar & Optom hisob</span>
                    <span class="sm:hidden">Ko'rish & Hisob →</span>
                    <svg class="w-3.5 h-3.5 hidden sm:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>

</div>
