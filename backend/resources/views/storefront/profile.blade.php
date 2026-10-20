<x-layouts.app title="Mening Profilim — Shaxsiy Kabinet">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-24"
         x-data="{
             activeTab: '{{ $activeTab ?? 'orders' }}',
             showAddressModal: false,
         }">

        <!-- Demo Mode Notice -->
        @if($isDemo)
            <div class="mb-6 p-4 rounded-3xl bg-amber-500/10 border border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-900">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">ℹ️</span>
                    <div>
                        <strong>Demo Xaridor Profili:</strong> Siz ayni damda <strong>Dilshod Xaridor</strong> profili ko'rinishidasiz.
                        Haqiqiy buyurtmalar berish va profilingizni saqlash uchun tizimga kiring.
                    </div>
                </div>
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-[#123C2F] text-[#C9F46B] font-bold whitespace-nowrap hover:bg-[#0d2a21] transition text-center">
                    Tizimga kirish →
                </a>
            </div>
        @endif

        <!-- Success and Error Flash Messages -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs font-bold text-emerald-800 flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
                @foreach($errors->all() as $err)
                    <div class="font-bold flex items-center gap-1.5">
                        <span>⚠️</span>
                        <span>{{ $err }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Customer Hero Profile Header (Amazon / AliExpress style) -->
        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                
                <!-- Avatar & Identity Info -->
                <div class="flex items-center gap-5">
                    <div class="relative">
                        <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-3xl bg-gradient-to-tr from-[#123C2F] to-[#1f5c49] text-[#C9F46B] flex items-center justify-center font-black text-2xl sm:text-3xl shadow-sm border-4 border-white ring-2 ring-[#E8ECE4]">
                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strstr($user->name, ' ') ?: $user->name, 1, 1)) }}
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-white text-[11px] font-bold" title="Faol hisob">
                            ✓
                        </span>
                    </div>

                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h1 class="text-2xl sm:text-3xl font-black text-[#172326] tracking-tight">
                                {{ $user->name }}
                            </h1>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold">
                                ✓ Tasdiqlangan Xaridor
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-[#64748B]">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $user->phone ?? '+998 90 987 65 43' }}
                            </span>
                            <span>•</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                {{ $user->email }}
                            </span>
                        </div>
                        <p class="text-[11px] text-[#64748B]">Platforma a'zosi: {{ $user->created_at ? $user->created_at->format('Y-yil') : '2026-yil' }}</p>
                    </div>
                </div>

                <!-- Action Shortcuts -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    @if($user->hasRole(['seller_owner', 'seller_manager', 'seller_operator']))
                        <a href="{{ route('seller.dashboard') }}" class="px-4 py-2.5 rounded-2xl bg-[#C9F46B]/20 text-[#123C2F] border border-[#C9F46B]/50 hover:bg-[#C9F46B]/30 font-bold text-xs flex items-center gap-1.5 transition">
                            <span>💼 Do'kon Kabineti</span>
                        </a>
                    @endif

                    @if($user->hasRole(['admin', 'moderator']))
                        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-2xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 font-bold text-xs flex items-center gap-1.5 transition">
                            <span>🛡️ Admin Panel</span>
                        </a>
                    @endif

                    <a href="{{ route('tracking') }}" class="px-4 py-2.5 rounded-2xl bg-[#123C2F] text-[#C9F46B] hover:bg-[#0d2a21] font-bold text-xs flex items-center gap-1.5 shadow-sm transition">
                        <span class="w-2 h-2 rounded-full bg-[#C9F46B] animate-ping"></span>
                        <span>📍 Jonli Xarita</span>
                    </a>

                    @auth
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-2.5 rounded-2xl bg-[#FAFBF7] hover:bg-red-50 text-[#64748B] hover:text-red-600 border border-[#E8ECE4] text-xs font-semibold transition" title="Tizimdan chiqish">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    @endauth
                </div>

            </div>

            <!-- 4 Quick KPI Stats Cards (Amazon / AliExpress format) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mt-6 pt-6 border-t border-[#E8ECE4]">
                
                <button @click="activeTab = 'orders'" class="p-4 rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-1">Buyurtmalarim</span>
                    <div class="text-xl sm:text-2xl font-black text-[#172326] group-hover:text-[#123C2F]">{{ $orders->count() }} <span class="text-xs font-medium text-[#64748B]">ta</span></div>
                    <span class="text-[10px] text-[#64748B] mt-0.5 block">Xaridlar tarixi →</span>
                </button>

                <button @click="activeTab = 'orders'" class="p-4 rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-1">Yo'ldagi Buyurtmalar</span>
                    <div class="text-xl sm:text-2xl font-black text-emerald-700">{{ $activeOrdersCount }} <span class="text-xs font-medium text-[#64748B]">ta faol</span></div>
                    <span class="text-[10px] text-emerald-600 font-semibold mt-0.5 block">Kuryer harakatda →</span>
                </button>

                <button @click="activeTab = 'wishlist'" class="p-4 rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-1">Sevimlilar</span>
                    <div class="text-xl sm:text-2xl font-black text-[#172326] group-hover:text-[#123C2F]">{{ $savedProducts->count() }} <span class="text-xs font-medium text-[#64748B]">ta</span></div>
                    <span class="text-[10px] text-[#64748B] mt-0.5 block">Saqlangan tovarlar →</span>
                </button>

                <button @click="activeTab = 'addresses'" class="p-4 rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-1">Manzillar</span>
                    <div class="text-xl sm:text-2xl font-black text-[#172326] group-hover:text-[#123C2F]">{{ $addresses->count() }} <span class="text-xs font-medium text-[#64748B]">ta</span></div>
                    <span class="text-[10px] text-[#64748B] mt-0.5 block">Yetkazish nuqtalari →</span>
                </button>

            </div>
        </div>

        <!-- Main Workspace: Tabs and Content Panels -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Sidebar Navigation Tabs (Amazon Style) (3 cols) -->
            <div class="lg:col-span-3 space-y-2">
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-3 shadow-xs space-y-1">
                    
                    <button @click="activeTab = 'orders'" 
                            :class="activeTab === 'orders' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-bold transition">
                        <div class="flex items-center gap-3">
                            <span class="text-base">📦</span>
                            <span>Mening Buyurtmalarim</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'orders' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#FAFBF7] text-[#64748B]'">
                            {{ $orders->count() }}
                        </span>
                    </button>

                    <button @click="activeTab = 'wishlist'" 
                            :class="activeTab === 'wishlist' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-bold transition">
                        <div class="flex items-center gap-3">
                            <span class="text-base">❤️</span>
                            <span>Sevimlilar & Saqlangan</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'wishlist' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#FAFBF7] text-[#64748B]'">
                            {{ $savedProducts->count() }}
                        </span>
                    </button>

                    <button @click="activeTab = 'addresses'" 
                            :class="activeTab === 'addresses' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-bold transition">
                        <div class="flex items-center gap-3">
                            <span class="text-base">📍</span>
                            <span>Yetkazish Manzillari</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'addresses' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#FAFBF7] text-[#64748B]'">
                            {{ $addresses->count() }}
                        </span>
                    </button>

                    <button @click="activeTab = 'settings'" 
                            :class="activeTab === 'settings' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl text-xs font-bold transition">
                        <div class="flex items-center gap-3">
                            <span class="text-base">⚙️</span>
                            <span>Hisob Sozlamalari</span>
                        </div>
                        <span class="text-[10px] font-semibold">Parol & Profil</span>
                    </button>

                </div>

                <!-- Customer Support Widget -->
                <div class="bg-gradient-to-br from-[#123C2F] to-[#1f5c49] text-white rounded-3xl p-5 shadow-xs space-y-3">
                    <span class="text-xs font-bold text-[#C9F46B] uppercase tracking-wider block">Yordam Markazi</span>
                    <p class="text-xs text-white/80 leading-relaxed">
                        Buyurtma yoki yetkazib berish bo'yicha savollaringiz bormi? Mutaxassislarimiz yordam berishga tayyor.
                    </p>
                    <a href="tel:+998712000000" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-[#123C2F] font-bold text-xs hover:bg-[#C9F46B] transition shadow-xs">
                        <span>📞 +998 (71) 200-00-00</span>
                    </a>
                </div>
            </div>

            <!-- Right Content Panels (9 cols) -->
            <div class="lg:col-span-9 space-y-6">
                
                <!-- TAB 1: BUYURTMALARIM (Orders & Deliveries) -->
                <div x-show="activeTab === 'orders'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black text-[#172326]">Mening Buyurtmalarim</h2>
                            <p class="text-xs text-[#64748B]">Barcha xaridlaringiz, tovarlar tarkibi va ularning yetkazilish holati</p>
                        </div>
                        <a href="{{ route('orders.hub') }}" class="text-xs font-bold text-[#123C2F] hover:underline flex items-center gap-1">
                            <span>To'liq Ta'minot Markazi</span>
                            <span>→</span>
                        </a>
                    </div>

                    @forelse($orders as $order)
                        @php
                            $shipment = $order->suborders->flatMap->shipments->first();
                            $isInTransit = $shipment && in_array($shipment->status, ['in_transit', 'out_for_delivery']);
                            $isDelivered = $order->status === 'completed' || ($shipment && $shipment->status === 'delivered');
                        @endphp

                        <div class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs hover:border-[#123C2F]/30 transition-all">
                            
                            <!-- Order Header -->
                            <div class="p-5 bg-[#FAFBF7] border-b border-[#E8ECE4] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-black text-sm text-[#172326]">{{ $order->order_number }}</span>
                                        @if($isInTransit)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                                Kuryer Yo'lda (Yetkazilmoqda)
                                            </span>
                                        @elseif($isDelivered)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 text-[10px] font-bold">
                                                ✓ Yetkazib berilgan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[10px] font-bold uppercase">
                                                {{ $order->status }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-[#64748B] mt-0.5">Sana: {{ $order->created_at->format('Y-m-d H:i') }}</p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <span class="text-[10px] text-[#64748B] uppercase font-bold block">Jami To'lov:</span>
                                        <span class="text-base font-black text-[#123C2F]">{{ number_format($order->grand_total, 0, '.', ' ') }} UZS</span>
                                    </div>

                                    @if($shipment)
                                        <a href="{{ route('tracking', ['trackingNumber' => $shipment->tracking_number]) }}" class="px-4 py-2 rounded-xl bg-[#123C2F] text-[#C9F46B] hover:bg-[#0d2a21] text-xs font-bold flex items-center gap-1.5 shadow-sm transition">
                                            <span>📍</span>
                                            <span>Xaritada Kuzatish</span>
                                        </a>
                                    @endif
                                </div>
                            </div>

                            <!-- Order Suborders & Items -->
                            <div class="p-5 space-y-4">
                                @foreach($order->suborders as $sub)
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between text-xs pb-2 border-b border-[#E8ECE4]">
                                            <span class="font-bold text-[#172326]">🏢 Sotuvchi: {{ $sub->seller->name }}</span>
                                            <span class="font-mono text-[#64748B] text-[11px]">Suborder: {{ $sub->suborder_number }}</span>
                                        </div>

                                        <div class="space-y-2">
                                            @foreach($sub->items as $item)
                                                <div class="flex items-center justify-between text-xs py-1">
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="w-7 h-7 rounded-lg bg-[#FAFBF7] border border-[#E8ECE4] flex items-center justify-center text-xs font-bold text-[#123C2F]">📦</span>
                                                        <div>
                                                            <span class="font-bold text-[#172326] block">{{ $item->product_name }}</span>
                                                            <span class="text-[11px] text-[#64748B]">{{ $item->quantity }} {{ $item->unit }} × {{ number_format($item->unit_price, 0, '.', ' ') }} UZS</span>
                                                        </div>
                                                    </div>
                                                    <span class="font-black text-[#172326]">{{ number_format($item->subtotal, 0, '.', ' ') }} UZS</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach

                                <!-- Delivery Details Bar with OTP Code -->
                                <div class="pt-3 border-t border-[#E8ECE4] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs bg-[#FAFBF7] p-3 rounded-2xl">
                                    <div class="space-y-0.5">
                                        <span class="text-[10px] font-bold uppercase text-[#64748B]">Yetkazish manzili:</span>
                                        <p class="font-semibold text-[#172326]">{{ $order->shipping_address['address_line'] ?? 'Mustaqillik shoh ko\'chasi, 105-uy' }}</p>
                                    </div>

                                    @if($shipment)
                                        <div class="flex items-center gap-3">
                                            <div class="text-right">
                                                <span class="text-[10px] font-bold uppercase text-[#64748B] block">Qabul qilish kodi (OTP):</span>
                                                <span class="font-mono font-black text-sm text-[#123C2F] bg-white px-2.5 py-0.5 rounded-lg border border-[#E8ECE4]">8492</span>
                                            </div>
                                            <a href="{{ route('tracking', ['trackingNumber' => $shipment->tracking_number]) }}" class="px-3 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-xs hover:bg-emerald-200 transition">
                                                Jonli Marshrut
                                            </a>
                                        </div>
                                    @endif
                                </div>

                            </div>

                        </div>
                    @empty
                        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-12 text-center shadow-xs">
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4 text-2xl">🛒</div>
                            <h3 class="font-extrabold text-base text-[#172326] mb-1">Buyurtmalar hali mavjud emas</h3>
                            <p class="text-xs text-[#64748B] mb-6">Siz hali birorta ham mahsulot xarid qilmadingiz. Katalogni ochib xaridni boshlang.</p>
                            <a href="{{ route('catalog') }}" class="px-6 py-3 rounded-2xl bg-[#123C2F] text-white font-bold text-xs hover:bg-[#0d2a21] transition">
                                Katalogga o'tish
                            </a>
                        </div>
                    @endforelse
                </div>

                <!-- TAB 2: SEVIMLILAR & SAQLANGANLAR (Wishlist) -->
                <div x-show="activeTab === 'wishlist'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black text-[#172326]">Saqlangan Mahsulotlar (Sevimlilar)</h2>
                            <p class="text-xs text-[#64748B]">Siz yoqtirgan va keyinchalik sotib olish uchun belgilangan tovarlar</p>
                        </div>
                        <a href="{{ route('catalog') }}" class="text-xs font-bold text-[#123C2F] hover:underline">
                            Katalogni to'liq ko'rish →
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($savedProducts as $product)
                            @php
                                $price = $product->variants->first()?->priceTiers->first()?->price ?? 50000;
                            @endphp
                            <div class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs hover:border-[#123C2F]/30 hover:shadow-md transition-all flex flex-col justify-between p-4 space-y-3">
                                
                                <div>
                                    <div class="aspect-video w-full rounded-2xl bg-[#FAFBF7] overflow-hidden mb-3 relative flex items-center justify-center">
                                        @if($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="text-3xl">📦</div>
                                        @endif
                                        <span class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/90 text-red-500 flex items-center justify-center shadow-xs text-xs font-bold">
                                            ❤️
                                        </span>
                                    </div>

                                    <div class="flex items-center justify-between text-[11px] text-[#64748B] mb-1">
                                        <span>{{ $product->category->name ?? 'Qurilish' }}</span>
                                        <span class="text-amber-500 font-bold">★ {{ $product->seller->rating ?? '4.9' }}</span>
                                    </div>

                                    <h4 class="font-bold text-sm text-[#172326] line-clamp-2">{{ $product->name }}</h4>
                                    <p class="text-xs text-[#64748B] mt-0.5">{{ $product->seller->name ?? 'Do\'kon' }}</p>
                                </div>

                                <div class="pt-3 border-t border-[#E8ECE4] flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] text-[#64748B] block">Narxi:</span>
                                        <span class="font-black text-sm text-[#123C2F]">{{ number_format($price, 0, '.', ' ') }} UZS</span>
                                    </div>

                                    <a href="{{ route('product.show', $product->slug) }}" class="px-3 py-2 rounded-xl bg-[#123C2F] text-white hover:bg-[#0d2a21] text-xs font-bold transition">
                                        Sotib olish
                                    </a>
                                </div>

                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- TAB 3: YETKAZIB BERISH MANZILLARI (Addresses) -->
                <div x-show="activeTab === 'addresses'" class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h2 class="text-xl font-black text-[#172326]">Yetkazib Berish Manzillari</h2>
                            <p class="text-xs text-[#64748B]">Tovarlar yetib boradigan manzillar daftarchasi (uy, ishxona, ombor)</p>
                        </div>
                        <button @click="showAddressModal = true" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-[#123C2F] text-white hover:bg-[#0d2a21] font-bold text-xs transition shadow-sm">
                            <span>+ Yangi Manzil Qo'shish</span>
                        </button>
                    </div>

                    <!-- Address Cards Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @forelse($addresses as $addr)
                            <div class="bg-white rounded-3xl border {{ $addr->is_default ? 'border-[#123C2F] ring-2 ring-[#123C2F]/10' : 'border-[#E8ECE4]' }} p-5 shadow-xs relative flex flex-col justify-between space-y-4">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-7 h-7 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] flex items-center justify-center text-xs font-bold">📍</span>
                                            <h4 class="font-extrabold text-sm text-[#172326]">{{ $addr->title }}</h4>
                                        </div>
                                        @if($addr->is_default)
                                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold">
                                                ✓ Asosiy Manzil
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-xs font-semibold text-[#172326] leading-relaxed">{{ $addr->full_address }}</p>
                                    @if($addr->landmark)
                                        <p class="text-[11px] text-[#64748B]">Mo'ljal: {{ $addr->landmark }}</p>
                                    @endif
                                    <div class="text-xs text-[#64748B] pt-1 border-t border-[#E8ECE4]/50">
                                        <strong>Qabul qiluvchi:</strong> {{ $addr->recipient_name }} • {{ $addr->phone }}
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-[#E8ECE4] flex items-center justify-between gap-2 text-xs">
                                    @if(!$addr->is_default)
                                        <form action="{{ route('profile.addresses.default', $addr->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="font-bold text-[#123C2F] hover:underline text-[11px]">
                                                Asosiy qilish
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] text-emerald-700 font-bold">Standart tanlangan</span>
                                    @endif

                                    <form action="{{ route('profile.addresses.delete', $addr->id) }}" method="POST" onsubmit="return confirm('Ushbu manzilni o\'chirishga ishonchingiz komilmi?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-bold text-red-600 hover:text-red-800 text-[11px]">
                                            O'chirish
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="sm:col-span-2 bg-white rounded-3xl border border-[#E8ECE4] p-8 text-center">
                                <p class="text-xs text-[#64748B] mb-4">Hozircha saqlangan manzillar mavjud emas.</p>
                                <button @click="showAddressModal = true" class="px-5 py-2.5 rounded-xl bg-[#123C2F] text-white font-bold text-xs">
                                    + Yangi manzil kiritish
                                </button>
                            </div>
                        @endforelse
                    </div>

                    <!-- Add Address Modal / Form -->
                    <div x-show="showAddressModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
                        <div @click.away="showAddressModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-[#E8ECE4] space-y-4 animate-scale-up">
                            <div class="flex items-center justify-between pb-3 border-b border-[#E8ECE4]">
                                <h3 class="font-black text-base text-[#172326]">Yangi Yetkazib Berish Manzili</h3>
                                <button @click="showAddressModal = false" class="text-[#64748B] hover:text-[#172326] font-bold text-lg">✕</button>
                            </div>

                            <form action="{{ route('profile.addresses.store') }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-[#172326] mb-1">Manzil Nomi (Masalan: Uyim, Qurilish ob'ekti) *</label>
                                    <input type="text" name="title" required placeholder="Masalan: Shahardagi xonadonim" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-[#172326] mb-1">Qabul qiluvchi ismi *</label>
                                        <input type="text" name="recipient_name" value="{{ $user->name }}" required class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-[#172326] mb-1">Telefon raqam *</label>
                                        <input type="text" name="phone" value="{{ $user->phone ?? '+998' }}" required class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-[#172326] mb-1">Aniq manzil (Shahar, tuman, ko'cha, uy, xonadon) *</label>
                                    <textarea name="full_address" required rows="2" placeholder="Masalan: Toshkent sh., Mirzo Ulug'bek tumani, Mustaqillik shohi, 105-uy, 3-xonadon" class="w-full p-3 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none"></textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-[#172326] mb-1">Mo'ljal (Kuryer topishi oson bo'lishi uchun)</label>
                                    <input type="text" name="landmark" placeholder="Masalan: Maktab yoki metro bekati yaqinida" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="is_default" id="is_default" value="1" class="rounded text-[#123C2F] focus:ring-[#123C2F]">
                                    <label for="is_default" class="text-xs font-medium text-[#172326]">Ushbu manzilni asosiy (birlamchi) deb belgilash</label>
                                </div>

                                <div class="pt-3 border-t border-[#E8ECE4] flex items-center justify-end gap-2">
                                    <button type="button" @click="showAddressModal = false" class="px-4 py-2.5 rounded-xl bg-[#FAFBF7] text-[#64748B] hover:bg-[#E8ECE4] text-xs font-bold">Bekor qilish</button>
                                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#123C2F] text-white hover:bg-[#0d2a21] text-xs font-bold">Saqlash</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: HISOB SOZLAMALARI & XAVFSIZLIK (Settings) -->
                <div x-show="activeTab === 'settings'" class="space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-[#172326]">Hisob Sozlamalari va Xavfsizlik</h2>
                        <p class="text-xs text-[#64748B]">Shaxsiy ma'lumotlarni tahrirlash, parolni yangilash va bildirishnomalar</p>
                    </div>

                    <!-- Personal Info Form -->
                    <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 shadow-xs space-y-5">
                        <h3 class="font-extrabold text-sm text-[#172326] pb-3 border-b border-[#E8ECE4]">
                            Shaxsiy ma'lumotlar
                        </h3>

                        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#172326] mb-1">To'liq ism-familiya *</label>
                                    <input type="text" name="name" value="{{ $user->name }}" required class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#172326] mb-1">Telefon raqam *</label>
                                    <input type="text" name="phone" value="{{ $user->phone ?? '+998909876543' }}" required class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#172326] mb-1">Elektron pochta (Email) *</label>
                                <input type="email" name="email" value="{{ $user->email }}" required class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#123C2F] text-white hover:bg-[#0d2a21] text-xs font-bold transition shadow-xs">
                                    Ma'lumotlarni Saqlash
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Password Security Form -->
                    <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 shadow-xs space-y-5">
                        <h3 class="font-extrabold text-sm text-[#172326] pb-3 border-b border-[#E8ECE4]">
                            Parolni o'zgartirish (Xavfsizlik)
                        </h3>

                        <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-[#172326] mb-1">Hozirgi parol *</label>
                                <input type="password" name="current_password" required placeholder="••••••••" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#172326] mb-1">Yangi parol *</label>
                                    <input type="password" name="new_password" required placeholder="Kamida 6 ta belgi" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#172326] mb-1">Yangi parolni takrorlang *</label>
                                    <input type="password" name="new_password_confirmation" required placeholder="••••••••" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                                </div>
                            </div>

                            <div class="pt-2 flex justify-end">
                                <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 text-white hover:bg-slate-900 text-xs font-bold transition shadow-xs">
                                    Parolni Yangilash
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Notification Preferences -->
                    <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 shadow-xs space-y-4">
                        <h3 class="font-extrabold text-sm text-[#172326] pb-3 border-b border-[#E8ECE4]">
                            Bildirishnomalar (SMS & Tizim xabarlari)
                        </h3>

                        <div class="space-y-3 text-xs">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" checked class="rounded text-[#123C2F] focus:ring-[#123C2F]">
                                <span class="font-medium text-[#172326]">Buyurtma holati o'zgarganda (kuryer yo'lga chiqqanda) SMS xabar yuborish</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" checked class="rounded text-[#123C2F] focus:ring-[#123C2F]">
                                <span class="font-medium text-[#172326]">Yetkazib berish kodi (OTP) ni SMS orqali qabul qilish</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" class="rounded text-[#123C2F] focus:ring-[#123C2F]">
                                <span class="font-medium text-[#172326]">Optom narxlar va chegirmalar haqida haftalik bildirishnomalar</span>
                            </label>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>
</x-layouts.app>
