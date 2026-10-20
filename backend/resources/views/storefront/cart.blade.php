<x-layouts.app title="Savatcha">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16">
        
        <!-- Breadcrumbs -->
        <nav class="flex flex-wrap items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">Savatcha</span>
        </nav>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-black text-[#172326] tracking-tight">Savatcha</h1>
                <p class="text-xs text-[#64748B] mt-1">
                    @if($quote['is_empty'])
                        Savatchangiz bo'sh
                    @else
                        Jami {{ $quote['items_count'] }} xil tovar, {{ count($quote['sellers']) }} ta sotuvchidan
                    @endif
                </p>
            </div>
            <a href="{{ route('catalog') }}" class="text-xs font-bold text-[#123C2F] hover:underline">
                Katalogga qaytish →
            </a>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($quote['is_empty'])
            <!-- Empty Cart State -->
            <div class="bg-white rounded-3xl border border-[#E8ECE4] p-12 text-center max-w-lg mx-auto">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
                <h3 class="font-bold text-lg text-[#172326] mb-1">Savatchangiz hozircha bo'sh</h3>
                <p class="text-xs text-[#64748B] mb-6">
                    Katalogimizdan kerakli chakana yoki optom mahsulotlarni tanlab, savatchaga qo'shishingiz mumkin.
                </p>
                <a href="{{ route('catalog') }}" class="inline-flex px-6 py-3 rounded-2xl bg-[#123C2F] text-white font-bold text-xs hover:bg-[#0d2a21] transition-colors">
                    Mahsulotlar katalogini ko'rish
                </a>
            </div>
        @else
            <!-- Multi-Vendor Cart Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Items grouped by Seller (8 cols) -->
                <div class="lg:col-span-8 space-y-6">
                    @foreach($quote['sellers'] as $seller)
                        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 shadow-xs">
                            
                            <!-- Seller Header -->
                            <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#E8ECE4]">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-[#123C2F] text-[#C9F46B] font-bold flex items-center justify-center text-sm">
                                        {{ mb_substr($seller['seller_name'], 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-sm text-[#172326]">{{ $seller['seller_name'] }}</h3>
                                        <span class="text-[11px] text-[#64748B]">Rasmiy sotuvchi</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold text-[#123C2F]">
                                        @if($seller['shipping_cost'] == '0')
                                            Yetkazish: Bepul
                                        @else
                                            Yetkazish: {{ number_format((float)$seller['shipping_cost'], 0, '.', ' ') }} UZS
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <!-- Items in this suborder -->
                            <div class="divide-y divide-[#E8ECE4]">
                                @foreach($seller['items'] as $it)
                                    <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                        
                                        <!-- Product info -->
                                        <div class="flex items-start gap-4">
                                            <div class="w-16 h-16 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] flex items-center justify-center flex-shrink-0">
                                                <svg class="w-8 h-8 text-[#123C2F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-sm text-[#172326]">{{ $it['product_name'] }}</h4>
                                                <p class="text-[11px] text-[#64748B] mt-0.5">SKU: <span class="font-mono">{{ $it['sku'] }}</span></p>
                                                
                                                <div class="flex items-center gap-2 mt-2">
                                                    <span class="text-xs font-bold text-[#123C2F]">
                                                        {{ number_format($it['unit_price'], 0, '.', ' ') }} UZS / {{ $it['unit'] }}
                                                    </span>
                                                    @if($it['tier_id'])
                                                        <span class="px-2 py-0.5 rounded-md bg-[#C9F46B] text-[#123C2F] text-[10px] font-bold">
                                                            Optom chegirma
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Quantity and Subtotal -->
                                        <div class="flex items-center justify-between sm:justify-end gap-6 pt-2 sm:pt-0">
                                            <div class="text-xs font-medium text-[#172326] bg-[#FAFBF7] px-3 py-1.5 rounded-xl border border-[#E8ECE4]">
                                                {{ $it['quantity'] }} {{ $it['unit'] }}
                                            </div>

                                            <div class="text-right">
                                                <div class="font-black text-base text-[#172326]">
                                                    {{ number_format($it['subtotal'], 0, '.', ' ') }} UZS
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                @endforeach
                            </div>

                            <!-- Seller subtotal footer -->
                            <div class="mt-4 pt-3 border-t border-[#E8ECE4] flex items-center justify-between text-xs">
                                <span class="text-[#64748B]">Do'kon bo'yicha jami:</span>
                                <span class="font-bold text-[#172326] text-sm">
                                    {{ number_format((float)$seller['total_amount'], 0, '.', ' ') }} UZS
                                </span>
                            </div>

                        </div>
                    @endforeach

                    <!-- Tiered Wholesale Hint Banner -->
                    <div class="p-5 rounded-3xl bg-[#FAFBF7] border border-[#C9F46B] flex items-start gap-4">
                        <div class="w-10 h-10 rounded-2xl bg-[#C9F46B] text-[#123C2F] font-black text-lg flex items-center justify-center flex-shrink-0">
                            💡
                        </div>
                        <div class="text-xs text-[#172326] leading-relaxed">
                            <span class="font-bold">All-Units Optom Narx Qoidasi:</span>
                            Buyurtma hajm chegarasiga yetganda (masalan, 299 dan 300 donaga o'tganda), butun partiya uchun barcha donalar arzonroq optom narxda qayta hisoblanadi. Bu katta hajmda sezilarli mablag' tejash imkonini beradi.
                        </div>
                    </div>
                </div>

                <!-- Order Summary (4 cols) -->
                <div class="lg:col-span-4 bg-white rounded-3xl border border-[#E8ECE4] p-6 shadow-xs sticky top-24 space-y-6">
                    <h3 class="font-black text-lg text-[#172326]">Buyurtma hisobi</h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between text-[#64748B]">
                            <span>Mahsulotlar summasi:</span>
                            <span class="font-semibold text-[#172326]">{{ number_format($quote['items_total'], 0, '.', ' ') }} UZS</span>
                        </div>
                        <div class="flex items-center justify-between text-[#64748B]">
                            <span>Yetkazib berish (barcha sotuvchilar):</span>
                            <span class="font-semibold text-[#172326]">{{ number_format($quote['shipping_total'], 0, '.', ' ') }} UZS</span>
                        </div>
                        <div class="pt-3 border-t border-[#E8ECE4] flex items-center justify-between text-sm">
                            <span class="font-bold text-[#172326]">Jami to'lov:</span>
                            <span class="font-black text-xl text-[#123C2F]">{{ number_format($quote['grand_total'], 0, '.', ' ') }} UZS</span>
                        </div>
                    </div>

                    <a href="{{ route('checkout') }}" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#C9F46B] text-[#123C2F] font-black text-sm hover:brightness-95 transition-all shadow-sm">
                        <span>Buyurtmani rasmiylashtirish</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>

                    <div class="text-[11px] text-[#64748B] text-center space-y-1">
                        <p>🔒 100% xavfsiz to'lov va zaxirani band qilish</p>
                        <p>Narxlar server tomonidan to'g'ridan-to'g'ri tekshiriladi</p>
                    </div>
                </div>

            </div>
        @endif

    </div>
</x-layouts.app>
