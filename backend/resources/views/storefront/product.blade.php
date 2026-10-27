<x-layouts.app :title="$product->name">
@php
    $defaultVariant = $product->variants->first();
    $unitLabel = $product->unit === 'piece' ? 'dona' : ($product->unit === 'box' ? 'quti' : ($product->unit === 'ton' ? 'tonna' : 'kg'));
    $minQty = (float)($product->min_order_quantity ?? 1);
    $step = (float)($product->unit_step ?? 1);
@endphp

<script>
function productDetails() {
    return {
        selectedVariantId: {{ $defaultVariant ? $defaultVariant->id : 'null' }},
        quantity: {{ $minQty }},
        minQuantity: {{ $minQty }},
        step: {{ $step }},
        unit: '{{ $unitLabel }}',
        variants: @json($product->variants),
        currentQuote: {
            unit_price: '0',
            base_unit_price: '0',
            total_price: '0',
            tier_id: null
        },
        activeTiers: [],
        addingToCart: false,
        addedSuccess: false,

        async addToCart() {
            this.addingToCart = true;
            try {
                const tokenEl = document.querySelector('meta[name="csrf-token"]');
                const token = tokenEl ? tokenEl.getAttribute('content') : '';
                const res = await fetch('/cart/items', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        variant_id: this.selectedVariantId,
                        quantity: this.quantity
                    })
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    this.addedSuccess = true;
                    setTimeout(() => { window.location.href = '/cart'; }, 350);
                } else {
                    alert(data.message || 'Xatolik yuz berdi');
                }
            } catch (e) {
                alert('Serverga ulanishda xatolik yuz berdi');
            } finally {
                this.addingToCart = false;
            }
        },

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
    };
}

function imageMagnifier(imgUrl) {
    return {
        imgUrl: imgUrl || '',
        isZooming: false,
        lensSize: 190,
        zoom: 2.6,
        lensX: 0,
        lensY: 0,
        bgX: 0,
        bgY: 0,
        bgW: 0,
        bgH: 0,
        isTouch: false,

        handleMove(e) {
            if (!this.imgUrl || !this.$refs.container) return;
            const rect = this.$refs.container.getBoundingClientRect();
            
            let clientX, clientY;
            if (e.touches && e.touches.length > 0) {
                this.isTouch = true;
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            } else {
                this.isTouch = false;
                clientX = e.clientX;
                clientY = e.clientY;
            }

            let x = clientX - rect.left;
            let y = clientY - rect.top;

            if (x < -20 || x > rect.width + 20 || y < -20 || y > rect.height + 20) {
                this.isZooming = false;
                return;
            }

            x = Math.max(0, Math.min(rect.width, x));
            y = Math.max(0, Math.min(rect.height, y));

            this.isZooming = true;
            const r = this.lensSize / 2;
            const touchOffset = this.isTouch ? 70 : 0;

            this.lensX = Math.round(x - r);
            this.lensY = Math.round(y - r - touchOffset);

            this.bgW = Math.round(rect.width * this.zoom);
            this.bgH = Math.round(rect.height * this.zoom);
            this.bgX = Math.round(r - (x * this.zoom));
            this.bgY = Math.round(r - (y * this.zoom));
        },

        handleLeave() {
            this.isZooming = false;
        }
    };
}
</script>

<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-3 sm:pt-6 pb-12 sm:pb-16" x-data="productDetails()">

    <!-- Breadcrumb -->
    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-[#64748B] mb-3 sm:mb-6" aria-label="Breadcrumb">
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
            <div class="aspect-square bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden flex items-center justify-center relative shadow-xs cursor-crosshair select-none touch-none"
                 x-data="imageMagnifier('{{ $product->image_url }}')"
                 x-ref="container"
                 @mouseenter="handleMove($event)"
                 @mousemove="handleMove($event)"
                 @mouseleave="handleLeave()"
                 @touchstart.passive="handleMove($event)"
                 @touchmove.prevent="handleMove($event)"
                 @touchend="handleLeave()"
                 @touchcancel="handleLeave()">

                <!-- Badges -->
                <div class="absolute top-4 left-4 flex flex-col gap-1.5 z-10 pointer-events-none">
                    @if($product->trade_mode === 'wholesale_only')
                        <span class="px-3 py-1 rounded-xl bg-[#123C2F] text-[#C9F46B] text-xs font-black uppercase tracking-wider shadow-sm">
                            Ulgurji Savdo (Optom)
                        </span>
                    @elseif($product->trade_mode === 'both')
                        <span class="px-3 py-1 rounded-xl bg-[#C9F46B] text-[#123C2F] text-xs font-black uppercase tracking-wider shadow-sm">
                            Chakana + Optom
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-xl bg-white/90 backdrop-blur-sm border border-[#E8ECE4] text-[#172326] text-xs font-bold shadow-sm">
                            Chakana
                        </span>
                    @endif
                </div>

                @if($product->image_url)
                    <!-- Zoom Hint Badge -->
                    <div class="absolute bottom-3 right-3 z-10 pointer-events-none transition-opacity duration-200"
                         :class="isZooming ? 'opacity-0' : 'opacity-100'">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/95 backdrop-blur-md border border-[#E8ECE4] text-[#172326] text-[11px] font-bold shadow-sm">
                            <svg class="w-3.5 h-3.5 text-[#123C2F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                            </svg>
                            <span>🔍 Yaqinlashtirish (Lupa)</span>
                        </span>
                    </div>

                    <!-- Base Product Image -->
                    <img src="{{ $product->image_url }}" 
                         alt="{{ $product->name }}" 
                         draggable="false"
                         class="w-full h-full object-cover select-none pointer-events-none">

                    <!-- Interactive Magnifying Glass Loupe (Radiusli Lupa) -->
                    <div x-show="isZooming"
                         x-cloak
                         x-transition.opacity.duration.150ms
                         class="pointer-events-none absolute rounded-full overflow-hidden border-[3px] border-[#123C2F] shadow-[0_16px_40px_rgba(0,0,0,0.35),0_0_0_2px_#C9F46B] z-30"
                         :style="`width: ${lensSize}px; height: ${lensSize}px; left: ${lensX}px; top: ${lensY}px; background-image: url('${imgUrl}'); background-size: ${bgW}px ${bgH}px; background-position: ${bgX}px ${bgY}px; background-repeat: no-repeat;`">
                        <!-- Glass shine / reflection highlight -->
                        <div class="absolute inset-0 rounded-full pointer-events-none ring-1 ring-inset ring-white/40"
                             style="background: radial-gradient(circle at 35% 30%, rgba(255,255,255,0.45) 0%, rgba(255,255,255,0) 65%);">
                        </div>
                        <!-- Optical Center Crosshair / Focal dot -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-3.5 h-3.5 rounded-full border-2 border-white/90 bg-[#123C2F]/50 shadow-sm backdrop-blur-xs flex items-center justify-center">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#C9F46B]"></span>
                            </div>
                        </div>
                        <!-- Zoom factor pill inside lens -->
                        <div class="absolute bottom-2.5 left-1/2 -translate-x-1/2 px-2.5 py-0.5 rounded-full bg-[#123C2F]/90 text-[#C9F46B] text-[10px] font-black tracking-wider uppercase backdrop-blur-sm shadow-md border border-white/20 whitespace-nowrap">
                            2.6x lupa
                        </div>
                    </div>
                @else
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
                @endif
            </div>

            <!-- Seller Card & Quick Contact -->
            <div class="bg-white rounded-3xl border border-[#E8ECE4] p-5 space-y-4 shadow-xs" x-data="{ showAskModal: false }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-black text-xl flex items-center justify-center shrink-0">
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
                    <a href="{{ route('seller.show', $product->seller->slug ?? '') }}" class="px-3 py-1.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] hover:bg-[#123C2F] hover:text-white text-xs font-bold transition-all">
                        Do'konga o'tish
                    </a>
                </div>

                <!-- Seller Direct Contact Actions (Qanday bog'lanadi?) -->
                <div class="pt-3 border-t border-[#E8ECE4] grid grid-cols-3 gap-2 text-center text-xs">
                    <a href="tel:{{ $product->seller->phone ?? '+998712000000' }}" class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl bg-emerald-50 text-emerald-900 border border-emerald-200 font-bold hover:bg-emerald-100 transition" title="Qo'ng'iroq qilish">
                        <span>📞</span>
                        <span class="truncate">Qo'ng'iroq</span>
                    </a>
                    <a href="https://t.me/market_seller_support?text={{ urlencode('Salom, men ' . $product->name . ' mahsuloti haqida ma\'lumot olmoqchiman.') }}" target="_blank" class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl bg-sky-50 text-sky-900 border border-sky-200 font-bold hover:bg-sky-100 transition" title="Telegram orqali yozish">
                        <span>💬</span>
                        <span class="truncate">Telegram</span>
                    </a>
                    <button type="button" @click="showAskModal = true" class="flex items-center justify-center gap-1.5 py-2 px-2 rounded-xl bg-[#FAFBF7] text-[#123C2F] border border-[#E8ECE4] font-bold hover:bg-[#123C2F] hover:text-white transition" title="Savol berish">
                        <span>❓</span>
                        <span class="truncate">Savol berish</span>
                    </button>
                </div>

                <!-- Modal: Savol berish -->
                <div x-show="showAskModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                    <div @click.away="showAskModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-[#E8ECE4]">
                        <div class="flex items-center justify-between pb-3 border-b border-[#E8ECE4]">
                            <div>
                                <h3 class="font-extrabold text-base text-[#172326]">Sotuvchiga savol yo'llash</h3>
                                <p class="text-xs text-[#64748B]">{{ $product->seller->name ?? 'Do\'kon' }} ma'murlariga</p>
                            </div>
                            <button @click="showAskModal = false" class="text-[#64748B] hover:text-[#172326] text-xl font-bold">✕</button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-[#172326] mb-1">Ismingiz</label>
                                <input type="text" placeholder="Ismingizni kiriting" class="w-full px-3.5 py-2 rounded-xl border border-[#E8ECE4] text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#172326] mb-1">Telefon raqamingiz</label>
                                <input type="text" value="+998 " class="w-full px-3.5 py-2 rounded-xl border border-[#E8ECE4] text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#172326] mb-1">Savolingiz</label>
                                <textarea rows="3" placeholder="{{ $product->name }} haqida savolingiz..." class="w-full px-3.5 py-2 rounded-xl border border-[#E8ECE4] text-xs"></textarea>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" @click="showAskModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#64748B] hover:bg-slate-100">Bekor qilish</button>
                            <button type="button" @click="showAskModal = false; alert('Savolingiz sotuvchiga yuborildi! Tez orada siz bilan telefon orqali bog\'lanishadi.')" class="px-4 py-2 rounded-xl text-xs font-bold bg-[#123C2F] text-[#C9F46B]">Yuborish ✓</button>
                        </div>
                    </div>
                </div>
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

                <!-- Rating and Reviews Count Badge -->
                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <a href="#reviews" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-500 hover:text-amber-600 transition">
                        <span class="text-sm">★</span>
                        <span class="text-[#172326] font-black text-sm">{{ number_format($product->average_rating, 1) }}</span>
                        <span class="text-[#64748B] font-medium underline">({{ $product->reviews_count }} ta sharh)</span>
                    </a>
                    <span class="text-slate-300">•</span>
                    <span class="text-xs text-emerald-700 font-bold bg-emerald-50 px-2.5 py-0.5 rounded-md">
                        ✓ 98% tavsiya qiladi
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="text-xs text-[#64748B]">
                        {{ $product->seller->name ?? 'Rasmiy do\'kon' }}
                    </span>
                </div>

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
                            @click="addToCart()" 
                            :disabled="addingToCart"
                            class="w-full h-14 rounded-2xl bg-[#123C2F] text-white hover:bg-[#0d2a21] font-bold text-base transition-all shadow-md flex items-center justify-center gap-3">
                        <svg class="w-6 h-6 text-[#C9F46B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span x-text="addingToCart ? 'Savatchaga qo\'shilmoqda...' : (addedSuccess ? '✓ Savatchaga qo\'shildi!' : `Savatchaga qo'shish (${quantity} ${unit})`)"></span>
                    </button>
                    <p class="text-center text-[11px] text-[#64748B] mt-2">
                        🔒 Xavfsiz ko'p sotuvchili buyurtma va avtomatik zaxira band qilish
                    </p>

                    <!-- Installment / Muddatli to'lov (Nasiya) Kalkulyatori -->
                    <div class="mt-4 p-4 rounded-2xl bg-amber-50/70 border border-amber-200/80">
                        <div class="flex items-center justify-between mb-2.5">
                            <div class="flex items-center gap-1.5">
                                <span class="text-sm">⚡</span>
                                <span class="text-xs font-black text-amber-950 uppercase tracking-wider">Muddatli To'lov (Nasiya)</span>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-200/70 text-amber-900">0% Boshlang'ich To'lov</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2 rounded-xl bg-white border border-amber-200/80">
                                <span class="block text-[10px] font-bold text-[#64748B]">3 oyga</span>
                                <span class="text-xs font-black text-[#123C2F]" x-text="formatNumber(Math.ceil(currentQuote.total_price / 3)) + ' UZS'"></span>
                                <span class="block text-[9px] text-[#64748B]">oyiga</span>
                            </div>
                            <div class="p-2 rounded-xl bg-white border-2 border-amber-400 shadow-xs relative">
                                <span class="absolute -top-2 left-1/2 -translate-x-1/2 px-1.5 py-0.2 rounded bg-amber-500 text-white text-[8px] font-black">XIT</span>
                                <span class="block text-[10px] font-bold text-[#64748B]">6 oyga</span>
                                <span class="text-xs font-black text-[#123C2F]" x-text="formatNumber(Math.ceil((currentQuote.total_price * 1.08) / 6)) + ' UZS'"></span>
                                <span class="block text-[9px] text-[#64748B]">oyiga</span>
                            </div>
                            <div class="p-2 rounded-xl bg-white border border-amber-200/80">
                                <span class="block text-[10px] font-bold text-[#64748B]">12 oyga</span>
                                <span class="text-xs font-black text-[#123C2F]" x-text="formatNumber(Math.ceil((currentQuote.total_price * 1.18) / 12)) + ' UZS'"></span>
                                <span class="block text-[9px] text-[#64748B]">oyiga</span>
                            </div>
                        </div>
                    </div>
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

    <!-- Customer Reviews & Star Ratings Section -->
    <section id="reviews" class="border-t border-[#E8ECE4] pt-12 pb-6 space-y-8"
             x-data="{
                 hoverRating: 5,
                 selectedRating: 5,
                 ratingLabels: {
                     1: 'Yomon (1 yulduz)',
                     2: 'Qoniqarli (2 yulduz)',
                     3: 'O\'rtacha (3 yulduz)',
                     4: 'Yaxshi (4 yulduz)',
                     5: 'A\'lo darajada! (5 yulduz)'
                 }
             }">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold uppercase tracking-wider">
                        Xaridorlar Fikri
                    </span>
                    <span class="text-xs text-[#64748B]">{{ $product->reviews_count }} ta sharh</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-[#172326] tracking-tight">
                    Mijozlar Sharhlari va Baholari
                </h2>
            </div>

            <a href="#leave-review" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#123C2F] text-[#C9F46B] text-xs font-bold hover:brightness-110 shadow-sm transition">
                <span>✍️</span>
                <span>Fikr bildirish</span>
            </a>
        </div>

        @if(session('success_review'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-3 shadow-xs">
                <span class="text-xl">✅</span>
                <span>{{ session('success_review') }}</span>
            </div>
        @endif

        <!-- Summary & Rating Breakdown Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left: Overall Rating Card (4 cols) -->
            <div class="lg:col-span-4 bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs space-y-6">
                <div class="text-center pb-6 border-b border-[#E8ECE4]">
                    <div class="text-5xl font-black text-[#172326] tracking-tight">
                        {{ number_format($product->average_rating, 1) }}
                    </div>
                    <div class="flex items-center justify-center gap-1 my-2 text-amber-400 text-xl">
                        @for($i = 1; $i <= 5; $i++)
                            <span>{{ $i <= round($product->average_rating) ? '★' : '☆' }}</span>
                        @endfor
                    </div>
                    <p class="text-xs text-[#64748B]">
                        {{ $product->reviews_count }} ta baholash asosida
                    </p>
                    <div class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold">
                        <span>✓</span>
                        <span>98% xaridorlar tavsiya qiladi</span>
                    </div>
                </div>

                <!-- Rating Bars (5 to 1 stars) -->
                <div class="space-y-2 text-xs">
                    @foreach($product->rating_breakdown as $stars => $data)
                        <div class="flex items-center gap-2">
                            <span class="w-12 font-bold text-[#172326] flex items-center gap-1">
                                <span>{{ $stars }}</span>
                                <span class="text-amber-400 text-sm">★</span>
                            </span>
                            <div class="flex-1 h-2.5 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full rounded-full bg-amber-400 transition-all duration-500" style="width: {{ $data['percentage'] }}%"></div>
                            </div>
                            <span class="w-8 text-right font-medium text-[#64748B]">{{ $data['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right: Review List & Form (8 cols) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Review List Feed -->
                <div class="space-y-4">
                    @forelse($product->reviews as $review)
                        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-5 sm:p-6 shadow-xs space-y-3">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-[#123C2F]/10 text-[#123C2F] font-bold text-sm flex items-center justify-center">
                                        {{ mb_substr($review->author_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-bold text-sm text-[#172326]">{{ $review->author_name }}</h4>
                                            @if($review->is_verified_purchase)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                                    <span>✓</span>
                                                    <span>Tasdiqlangan xarid</span>
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-[#64748B]">{{ $review->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>

                                <!-- Stars -->
                                <div class="flex items-center gap-0.5 text-amber-400 text-sm">
                                    @for($s = 1; $s <= 5; $s++)
                                        <span>{{ $s <= $review->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-[#172326] leading-relaxed">
                                {{ $review->comment }}
                            </p>

                            <div class="flex items-center justify-between pt-2 border-t border-[#E8ECE4]/70 text-[11px] text-[#64748B]">
                                <span class="text-emerald-700 font-semibold">Tavsiya etadi</span>
                                <button type="button" @click="$el.innerText = '👍 Foydali (' + ({{ $review->likes_count }} + 1) + ')'" class="hover:text-[#123C2F] font-semibold transition">
                                    👍 Foydali ({{ $review->likes_count }})
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-8 text-center text-[#64748B] text-sm">
                            Hali sharhlar qoldirilmagan. Birinchi bo'lib fikr bildiring!
                        </div>
                    @endforelse
                </div>

                <!-- Review Submission Form -->
                <div id="leave-review" class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-[#E8ECE4]">
                        <span class="text-xl">✍️</span>
                        <div>
                            <h3 class="font-black text-base text-[#172326]">Fikr va Baho Qoldirish</h3>
                            <p class="text-xs text-[#64748B]">Boshqa xaridorlarga to'g'ri tanlov qilishda yordam bering</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('product.reviews.store', ['slug' => $product->slug]) }}" class="space-y-4">
                        @csrf
                        
                        <!-- Interactive Star Rating Picker -->
                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1.5">Mahsulotni baholang:</label>
                            <input type="hidden" name="rating" :value="selectedRating">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-1 cursor-pointer">
                                    <template x-for="star in 5" :key="star">
                                        <button type="button" 
                                                @mouseenter="hoverRating = star"
                                                @mouseleave="hoverRating = selectedRating"
                                                @click="selectedRating = star; hoverRating = star"
                                                class="text-2xl transition-transform hover:scale-125 focus:outline-none">
                                            <span :class="star <= hoverRating ? 'text-amber-400' : 'text-slate-200'">★</span>
                                        </button>
                                    </template>
                                </div>
                                <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-lg" x-text="ratingLabels[hoverRating]"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1.5">Ismingiz</label>
                            <input type="text" name="author_name" required
                                   value="{{ old('author_name', auth()->user()?->name) }}"
                                   placeholder="Masalan: Azizbek T."
                                   class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white transition-colors">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1.5">Fikringiz va tajribangiz</label>
                            <textarea name="comment" rows="3" required
                                      placeholder="Mahsulot sifati, yetkazib berish tezligi yoki ishlatish jarayoni haqida fikringizni yozing..."
                                      class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white transition-colors"></textarea>
                        </div>

                        <button type="submit" class="px-6 py-3.5 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-black text-xs hover:brightness-110 shadow-sm transition">
                            Sharhni e'lon qilish →
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </section>

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
