<x-layouts.app :title="$product->name">
@php
    $defaultVariant = $product->variants->first();
    $unitLabel = $product->unit === 'piece' ? 'dona' : ($product->unit === 'box' ? 'quti' : ($product->unit === 'ton' ? 'tonna' : 'kg'));
    $minQty = (float)($product->min_order_quantity ?? 1);
    $step = (float)($product->unit_step ?? 1);
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16"
     x-data="{
        selectedVariantId: {{ $defaultVariant ? $defaultVariant->id : 'null' }},
        quantity: {{ $minQty }},
        minQuantity: {{ $minQty }},
        step: {{ $step }},
        unit: '{{ $unitLabel }}',
        variants: {{ $product->variants->toJson() }},
        currentQuote: {
            unit_price: '0',
            base_unit_price: '0',
            total_price: '0',
            tier_id: null
        },
        activeTiers: [],

        init() {
            this.updateActiveTiers();
            this.recalculate();
        },

        selectVariant(id) {
            this.selectedVariantId = id;
            this.updateActiveTiers();
            this.recalculate();
        },

        updateActiveTiers() {
            const v = this.variants.find(item => item.id === this.selectedVariantId);
            this.activeTiers = v && v.price_tiers ? v.price_tiers : [];
        },

        increment() {
            this.quantity = Math.round((this.quantity + this.step) * 1000) / 1000;
            this.recalculate();
        },

        decrement() {
            if (this.quantity - this.step >= this.minQuantity) {
                this.quantity = Math.round((this.quantity - this.step) * 1000) / 1000;
                this.recalculate();
            }
        },

        setQuantity(val) {
            const n = parseFloat(val);
            if (!isNaN(n) && n >= this.minQuantity) {
                this.quantity = n;
                this.recalculate();
            }
        },

        recalculate() {
            if (!this.activeTiers || this.activeTiers.length === 0) return;

            // Find matching tier (all-units model)
            let matched = null;
            for (let t of this.activeTiers) {
                const min = parseFloat(t.min_quantity);
                const max = t.max_quantity ? parseFloat(t.max_quantity) : Infinity;
                if (this.quantity >= min && this.quantity <= max) {
                    matched = t;
                    break;
                }
            }

            if (!matched && this.activeTiers.length > 0) {
                matched = this.activeTiers[0];
            }

            if (matched) {
                const baseTier = this.activeTiers[0];
                const unitPrice = BigInt(matched.unit_price);
                const qtyInt = BigInt(Math.floor(this.quantity));
                const total = (qtyInt * unitPrice).toString();

                this.currentQuote = {
                    unit_price: matched.unit_price.toString(),
                    base_unit_price: baseTier ? baseTier.unit_price.toString() : matched.unit_price.toString(),
                    total_price: total,
                    tier_id: matched.id
                };
            }
        },

        formatNumber(numStr) {
            if (!numStr) return '0';
            return numStr.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        }
     }">

    <!-- Breadcrumb -->
    <nav class="flex flex-wrap items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
        <span>/</span>
        <a href="{{ route('catalog') }}" class="hover:text-[#123C2F]">Katalog</a>
        @if($product->category)
            <span>/</span>
            <a href="{{ route('catalog', ['category' => $product->category->slug]) }}" class="hover:text-[#123C2F]">{{ $product->category->name }}</a>
        @endif
        <span>/</span>
        <span class="text-[#172326] font-semibold truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-16">
        
        <!-- Left: Product Image / Visual Showcase (5 cols) -->
        <div class="lg:col-span-5 space-y-4">
            <div class="aspect-square bg-white rounded-3xl border border-[#E8ECE4] p-8 flex items-center justify-center relative overflow-hidden shadow-xs">
                <!-- Badges -->
                <div class="absolute top-4 left-4 flex flex-col gap-1.5 z-10">
                    @if($product->trade_mode === 'wholesale_only')
                        <span class="px-3 py-1 rounded-xl bg-[#123C2F] text-[#C9F46B] text-xs font-black uppercase tracking-wider">
                            Ulgurji Savdo (Optom)
                        </span>
                    @elseif($product->trade_mode === 'both')
                        <span class="px-3 py-1 rounded-xl bg-[#C9F46B] text-[#123C2F] text-xs font-black uppercase tracking-wider">
                            Chakana + Optom
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-[#172326] text-xs font-bold">
                            Chakana
                        </span>
                    @endif
                </div>

                <!-- Product SVG Representation -->
                <div class="w-40 h-40 rounded-3xl bg-[#FAFBF7] border border-[#E8ECE4] flex items-center justify-center text-[#123C2F] shadow-sm">
                    @if(str_contains(strtolower($product->name), 'sement'))
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    @elseif(str_contains(strtolower($product->name), 'generator') || str_contains(strtolower($product->name), 'uskuna'))
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    @elseif(str_contains(strtolower($product->name), 'suv'))
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    @elseif(str_contains(strtolower($product->name), 'armatura'))
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    @else
                        <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    @endif
                </div>
            </div>

            <!-- Seller Card Preview -->
            <div class="bg-white rounded-3xl border border-[#E8ECE4] p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-black text-xl flex items-center justify-center">
                        {{ mb_substr($product->seller->name ?? 'S', 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <h4 class="font-bold text-sm text-[#172326]">{{ $product->seller->name ?? 'Sotuvchi' }}</h4>
                            <span class="text-xs text-emerald-600 font-bold" title="Litsenziyalangan">✓</span>
                        </div>
                        <p class="text-xs text-[#64748B]">★ {{ number_format($product->seller->rating ?? 5.0, 1) }} • Rasmiy do'kon</p>
                    </div>
                </div>
                <a href="{{ route('seller.show', $product->seller->slug ?? '') }}" class="px-4 py-2 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] hover:bg-[#123C2F] hover:text-white text-xs font-bold transition-all">
                    Do'konni ko'rish
                </a>
            </div>
        </div>

        <!-- Right: Product Information & Live Quote Calculator (7 cols) -->
        <div class="lg:col-span-7 space-y-6">
            
            <!-- Title & Basic Info -->
            <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#64748B]">
                        {{ $product->category->name ?? 'Katalog' }}
                    </span>
                    <button type="button" @click="$store.wishlist.toggle({{ $product->id }})" class="text-xs font-semibold flex items-center gap-1 text-[#64748B] hover:text-red-500">
                        <svg class="w-4 h-4" :fill="$store.wishlist.has({{ $product->id }}) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span x-text="$store.wishlist.has({{ $product->id }}) ? 'Saqlangan' : 'Sevimlilarga'">Sevimlilarga</span>
                    </button>
                </div>

                <h1 class="text-2xl sm:text-3xl font-black text-[#172326] tracking-tight leading-snug">
                    {{ $product->name }}
                </h1>

                <p class="text-sm text-[#64748B] leading-relaxed">
                    {{ $product->description ?? 'Ushbu mahsulot uchun batafsil tavsif tayyorlanmoqda.' }}
                </p>

                <!-- SKU & Attributes -->
                @if($defaultVariant)
                    <div class="pt-4 border-t border-[#E8ECE4] flex flex-wrap items-center gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-[#64748B]">
                            SKU: <strong class="text-[#172326]">{{ $defaultVariant->sku }}</strong>
                        </span>
                        @if($defaultVariant->barcode)
                            <span class="px-2.5 py-1 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-[#64748B]">
                                Shtrixkod: <strong class="text-[#172326]">{{ $defaultVariant->barcode }}</strong>
                            </span>
                        @endif
                        @if($defaultVariant->attributes)
                            @foreach($defaultVariant->attributes as $attrKey => $attrVal)
                                <span class="px-2.5 py-1 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-[#64748B]">
                                    {{ ucfirst($attrKey) }}: <strong class="text-[#172326]">{{ $attrVal }}</strong>
                                </span>
                            @endforeach
                        @endif
                    </div>
                @endif
            </div>

            <!-- Interactive All-Units Pricing Tier Table & Live Calculator -->
            <div class="bg-white rounded-3xl border-2 border-[#123C2F] p-6 sm:p-8 space-y-6 shadow-sm">
                
                <div class="flex items-center justify-between">
                    <div>
                        <span class="px-3 py-1 rounded-full bg-[#C9F46B] text-[#123C2F] text-[11px] font-black uppercase tracking-wider">
                            All-Units Pog'onali Narx
                        </span>
                        <h2 class="text-xl font-black text-[#172326] mt-1.5">Optom Narx Jadvali</h2>
                    </div>
                    <span class="text-xs text-[#64748B]">Birlik: <strong>{{ $unitLabel }}</strong></span>
                </div>

                <!-- Tier Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="border-b border-[#E8ECE4] text-[#64748B]">
                                <th class="py-2.5 font-bold uppercase tracking-wider">Xarid Hajmi</th>
                                <th class="py-2.5 font-bold uppercase tracking-wider">Birlik Narxi</th>
                                <th class="py-2.5 font-bold uppercase tracking-wider text-right">Model</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E8ECE4]">
                            <template x-for="tier in activeTiers" :key="tier.id">
                                <tr class="transition-colors"
                                    :class="currentQuote.tier_id === tier.id ? 'bg-[#C9F46B]/20 font-bold' : ''">
                                    <td class="py-3">
                                        <span x-text="tier.max_quantity ? (parseInt(tier.min_quantity) + ' – ' + parseInt(tier.max_quantity) + ' ' + unit) : (parseInt(tier.min_quantity) + '+ ' + unit)"></span>
                                        <span x-show="currentQuote.tier_id === tier.id" class="ml-2 px-2 py-0.5 rounded-full bg-[#123C2F] text-white text-[9px] font-bold uppercase">Aktiv</span>
                                    </td>
                                    <td class="py-3 text-[#123C2F] font-black text-sm">
                                        <span x-text="formatNumber(tier.unit_price)"></span> UZS
                                    </td>
                                    <td class="py-3 text-right text-[#64748B]">
                                        All-units
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Quantity Controller & Live Calculation -->
                <div class="p-5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#172326] mb-1">
                                Miqdorni kiriting (Min: {{ $minQty }}, Qadam: {{ $step }})
                            </label>
                            <span class="text-[11px] text-[#64748B]">
                                Sinov: <button type="button" @click="setQuantity(299)" class="underline hover:text-[#123C2F] font-bold">299</button>, 
                                <button type="button" @click="setQuantity(300)" class="underline hover:text-[#123C2F] font-bold text-[#123C2F]">300</button>, 
                                <button type="button" @click="setQuantity(999)" class="underline hover:text-[#123C2F] font-bold">999</button>, 
                                <button type="button" @click="setQuantity(1000)" class="underline hover:text-[#123C2F] font-bold text-[#123C2F]">1000</button>
                            </span>
                        </div>

                        <!-- Quantity Selector Counter -->
                        <div class="flex items-center gap-1 bg-white p-1 rounded-2xl border border-[#E8ECE4]">
                            <button type="button" @click="decrement()" class="w-10 h-10 rounded-xl bg-[#FAFBF7] hover:bg-[#E8ECE4] text-[#172326] font-bold text-lg flex items-center justify-center transition-colors">
                                -
                            </button>
                            <input type="number" 
                                   x-model.number="quantity" 
                                   @input="recalculate()"
                                   min="{{ $minQty }}" 
                                   step="{{ $step }}" 
                                   class="w-20 text-center font-black text-base text-[#172326] outline-none">
                            <button type="button" @click="increment()" class="w-10 h-10 rounded-xl bg-[#FAFBF7] hover:bg-[#E8ECE4] text-[#172326] font-bold text-lg flex items-center justify-center transition-colors">
                                +
                            </button>
                        </div>
                    </div>

                    <!-- Live Calculated Quote Summary -->
                    <div class="pt-4 border-t border-[#E8ECE4] grid grid-cols-1 sm:grid-cols-3 gap-3 text-center sm:text-left">
                        <div class="p-3 rounded-xl bg-white border border-[#E8ECE4]">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Boshlang'ich Narx</span>
                            <div class="text-sm font-bold text-[#64748B] mt-0.5">
                                <span x-text="formatNumber(currentQuote.base_unit_price)"></span> UZS
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-white border border-[#E8ECE4]">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#123C2F]">Qo'llangan Narx</span>
                            <div class="text-sm font-black text-[#123C2F] mt-0.5">
                                <span x-text="formatNumber(currentQuote.unit_price)"></span> UZS
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-[#123C2F] text-white">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-white/70">Jami Hisob</span>
                            <div class="text-base font-black text-[#C9F46B] mt-0.5">
                                <span x-text="formatNumber(currentQuote.total_price)"></span> UZS
                            </div>
                        </div>
                    </div>

                    <!-- 299 vs 300 savings banner alert -->
                    <div x-show="quantity === 300" x-cloak class="p-3 rounded-xl bg-emerald-50 text-emerald-900 border border-emerald-200 text-xs font-semibold flex items-center gap-2">
                        <span>💡</span>
                        <span>
                            <strong>All-Units afzalligi:</strong> 300 dona xarid qilganda butun hajm 270 000 UZS dan hisoblanadi (jami 81 000 000 UZS). Bu 299 donadan (89 700 000 UZS) <strong>8 700 000 UZS ga arzonroq!</strong>
                        </span>
                    </div>

                    <div x-show="quantity >= 1000" x-cloak class="p-3 rounded-xl bg-[#C9F46B]/30 text-[#123C2F] border border-[#123C2F]/20 text-xs font-semibold flex items-center gap-2">
                        <span>🚀</span>
                        <span>
                            <strong>Eng yuqori optom chegirma:</strong> 1 000+ dona uchun donasiga 250 000 UZS! (1 000 dona = 250 000 000 UZS).
                        </span>
                    </div>
                </div>

                <!-- Add to Cart CTA -->
                <div class="pt-2">
                    <button type="button" 
                            @click="$store.cart.notify()" 
                            class="w-full h-14 rounded-2xl bg-[#123C2F] text-white hover:bg-[#0d2a21] font-bold text-base transition-all shadow-md flex items-center justify-center gap-3">
                        <svg class="w-6 h-6 text-[#C9F46B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Savatchaga qo'shish (<span x-text="quantity"></span> <span x-text="unit"></span>)</span>
                    </button>
                    <p class="text-center text-[11px] text-[#64748B] mt-2">
                        * Savatcha va ko'p sotuvchili checkout 05-bosqichda to'liq ulanadi.
                    </p>
                </div>

            </div>

            <!-- Warehouse Stock Balances -->
            @if($defaultVariant && $defaultVariant->inventoryBalances->count() > 0)
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 space-y-3">
                    <h3 class="font-black text-sm uppercase tracking-wider text-[#172326]">Ombordagi Zaxira Holati</h3>
                    <div class="space-y-2">
                        @foreach($defaultVariant->inventoryBalances as $balance)
                            <div class="flex items-center justify-between p-3 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs">
                                <div>
                                    <span class="font-bold text-[#172326]">{{ $balance->warehouse->name ?? 'Ombor' }}</span>
                                    <p class="text-[11px] text-[#64748B]">{{ $balance->warehouse->address ?? 'Manzil ko\'rsatilmagan' }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="font-black text-sm text-[#123C2F]">{{ number_format($balance->quantity, 0, ' ', ' ') }} {{ $unitLabel }}</span>
                                    <span class="block text-[10px] text-emerald-600 font-bold">Mavjud</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <section class="border-t border-[#E8ECE4] pt-12">
            <h2 class="text-2xl font-black text-[#172326] tracking-tight mb-6">O'xshash Mahsulotlar</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($relatedProducts as $relProduct)
                    <x-product-card :product="$relProduct" />
                @endforeach
            </div>
        </section>
    @endif

</div>
</x-layouts.app>
