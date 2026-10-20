<x-layouts.app title="Buyurtmalar va Xaridlar Markazi">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-24">
        
        <!-- Header Banner -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-3 py-1 rounded-full bg-[#123C2F] text-[#C9F46B] text-xs font-bold uppercase tracking-wider">
                        Logistika & Savdo Markazi
                    </span>
                    <span class="text-xs text-[#64748B]">Foydalanuvchi: <strong class="text-[#172326]">{{ $user->name }}</strong></span>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-[#172326] tracking-tight">
                    Buyurtmalar va Yetkazib Berishlar
                </h1>
                <p class="text-sm text-[#64748B] mt-1">
                    Siz sotib olgan tovarlar, yo'ldagi yetkazib berishlar hamda do'koningizga tushgan mijozlar buyurtmalari.
                </p>
            </div>

            <!-- Quick Map Shortcut -->
            <a href="{{ route('tracking') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-[#C9F46B] text-[#123C2F] hover:bg-[#b8e855] font-extrabold text-xs transition-all shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-[#123C2F] animate-ping"></span>
                <span>📍 Jonli GPS Xaritani Ochish</span>
            </a>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            
            <!-- Inbound Purchases Metric -->
            <div class="bg-white rounded-3xl border border-[#E8ECE4] p-5 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Mening Xaridlarim</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-sm">🛒</span>
                </div>
                <div class="text-2xl font-black text-[#172326]">{{ number_format($totalPurchasedSum, 0, '.', ' ') }} <span class="text-xs font-bold text-[#64748B]">UZS</span></div>
                <div class="text-[11px] text-[#64748B] mt-1">{{ $purchases->count() }} ta buyurtma rasmiylashtirilgan</div>
            </div>

            <!-- Inbound In-Transit Metric -->
            <div class="bg-white rounded-3xl border border-[#E8ECE4] p-5 shadow-xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Yo'ldagi Xaridlar</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-sm animate-pulse">🚚</span>
                </div>
                <div class="text-2xl font-black text-emerald-700">{{ $activeInboundDeliveries }} <span class="text-xs font-bold text-[#64748B]">ta faol jo'natma</span></div>
                <div class="text-[11px] text-emerald-600 font-semibold mt-1">Real vaqtda xaritada ko'rinadi</div>
            </div>

            @if($isSeller)
                <!-- Customer Sales Orders Metric -->
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-5 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Do'kon Sotuvlari</span>
                        <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-sm">💼</span>
                    </div>
                    <div class="text-2xl font-black text-[#172326]">{{ number_format($totalSalesSum, 0, '.', ' ') }} <span class="text-xs font-bold text-[#64748B]">UZS</span></div>
                    <div class="text-[11px] text-[#64748B] mt-1">Mijozlardan {{ $sales->count() }} ta buyurtma</div>
                </div>

                <!-- Outbound In-Transit Metric -->
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-5 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#64748B]">Mijozga Yetkazilayotgan</span>
                        <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-sm">📦</span>
                    </div>
                    <div class="text-2xl font-black text-amber-700">{{ $activeOutboundDeliveries }} <span class="text-xs font-bold text-[#64748B]">ta jo'natma</span></div>
                    <div class="text-[11px] text-[#64748B] mt-1">Kuryer topshiruvida</div>
                </div>
            @endif

        </div>

        <!-- Master Tabs & Switcher -->
        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-2 sm:p-3 shadow-xs mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                
                <!-- Primary Switcher: Purchases vs Sales -->
                <div class="flex items-center p-1 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs font-bold">
                    <a href="{{ route('orders.hub', ['tab' => 'purchases', 'status' => request('status', 'all')]) }}" 
                       class="flex items-center gap-2 px-4 sm:px-6 py-2.5 rounded-xl transition-all {{ $activeTab === 'purchases' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326]' }}">
                        <span>🛒 Mening Xaridlarim (Sotib olganlarim)</span>
                        <span class="px-2 py-0.5 rounded-full {{ $activeTab === 'purchases' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#E8ECE4] text-[#64748B]' }} text-[11px]">
                            {{ $purchases->count() }}
                        </span>
                    </a>

                    @if($isSeller)
                        <a href="{{ route('orders.hub', ['tab' => 'sales', 'status' => request('status', 'all')]) }}" 
                           class="flex items-center gap-2 px-4 sm:px-6 py-2.5 rounded-xl transition-all {{ $activeTab === 'sales' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326]' }}">
                            <span>📤 Mijozlar Buyurtmalari (Sotuvlarim)</span>
                            <span class="px-2 py-0.5 rounded-full {{ $activeTab === 'sales' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#E8ECE4] text-[#64748B]' }} text-[11px]">
                                {{ $sales->count() }}
                            </span>
                        </a>
                    @endif
                </div>

                <!-- Secondary Filter: Status -->
                <div class="flex flex-wrap items-center gap-1.5 text-xs font-semibold px-2">
                    <a href="{{ route('orders.hub', ['tab' => $activeTab, 'status' => 'all']) }}" 
                       class="px-3 py-1.5 rounded-xl transition-all {{ $statusFilter === 'all' ? 'bg-[#123C2F] text-white' : 'bg-[#FAFBF7] text-[#64748B] hover:bg-[#E8ECE4]' }}">
                        Barchasi
                    </a>
                    <a href="{{ route('orders.hub', ['tab' => $activeTab, 'status' => 'in_transit']) }}" 
                       class="px-3 py-1.5 rounded-xl transition-all flex items-center gap-1.5 {{ $statusFilter === 'in_transit' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                        Yo'lda (Yetkazilmoqda)
                    </a>
                    <a href="{{ route('orders.hub', ['tab' => $activeTab, 'status' => 'processing']) }}" 
                       class="px-3 py-1.5 rounded-xl transition-all {{ $statusFilter === 'processing' ? 'bg-amber-600 text-white' : 'bg-[#FAFBF7] text-[#64748B] hover:bg-[#E8ECE4]' }}">
                        Tayyorlanmoqda
                    </a>
                    <a href="{{ route('orders.hub', ['tab' => $activeTab, 'status' => 'completed']) }}" 
                       class="px-3 py-1.5 rounded-xl transition-all {{ $statusFilter === 'completed' ? 'bg-slate-700 text-white' : 'bg-[#FAFBF7] text-[#64748B] hover:bg-[#E8ECE4]' }}">
                        Yetkazilgan
                    </a>
                </div>

            </div>
        </div>

        <!-- CONTENT TAB 1: PURCHASES (Sotib olgan narsalari & Kiruvchi Yetkazib Berishlar) -->
        @if($activeTab === 'purchases')
            <div class="space-y-6">
                
                @forelse($purchases as $order)
                    @php
                        $firstShipment = $order->suborders->flatMap->shipments->first();
                        $isInTransit = $order->suborders->flatMap->shipments->contains(fn($s) => $s->status === 'in_transit');
                        $isDelivered = $order->status === 'completed' || $order->suborders->flatMap->shipments->contains(fn($s) => $s->status === 'delivered');
                    @endphp

                    <div class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs hover:border-[#123C2F]/30 transition-all">
                        
                        <!-- Order Header Bar -->
                        <div class="p-5 sm:p-6 bg-[#FAFBF7] border-b border-[#E8ECE4] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-[#123C2F] text-white flex items-center justify-center font-bold text-sm">
                                    🛒
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-mono font-black text-base text-[#172326]">{{ $order->order_number }}</h3>
                                        @if($isInTransit)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                                                Kuryer Yo'lda (Yetkazilmoqda)
                                            </span>
                                        @elseif($isDelivered)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 text-[11px] font-bold">
                                                ✓ Yetkazib berilgan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-900 text-[11px] font-bold uppercase">
                                                {{ $order->status }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-[#64748B] mt-0.5">Rasmiylashtirilgan sana: {{ $order->created_at->format('Y-m-d H:i') }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <span class="text-[11px] text-[#64748B] block">Jami Xarid Summasi:</span>
                                    <span class="text-xl font-black text-[#123C2F]">{{ number_format($order->grand_total, 0, '.', ' ') }} UZS</span>
                                </div>

                                @if($firstShipment)
                                    <a href="{{ route('tracking', ['trackingNumber' => $firstShipment->tracking_number]) }}" class="px-4 py-2.5 rounded-xl bg-[#123C2F] text-[#C9F46B] hover:bg-[#0d2a21] text-xs font-bold flex items-center gap-1.5 shadow-sm transition">
                                        <span>📍</span>
                                        <span>Xaritada Jonli Kuzatish</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Order Body: Suborders & Items -->
                        <div class="p-6 space-y-6">
                            
                            @foreach($order->suborders as $suborder)
                                <div class="rounded-2xl border border-[#E8ECE4] p-5 bg-white space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-[#E8ECE4] gap-2">
                                        <div>
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#64748B]">Sotuvchi / Ta'minotchi:</span>
                                            <h4 class="font-bold text-sm text-[#172326] flex items-center gap-2">
                                                <span>🏢 {{ $suborder->seller->name }}</span>
                                                <span class="text-xs text-[#64748B] font-normal">• Suborder: <span class="font-mono">{{ $suborder->suborder_number }}</span></span>
                                            </h4>
                                        </div>

                                        @foreach($suborder->shipments as $sh)
                                            <div class="flex items-center gap-2">
                                                <span class="px-3 py-1 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] font-mono font-bold text-xs text-[#123C2F]">
                                                    Trek: {{ $sh->tracking_number }}
                                                </span>
                                                <a href="{{ route('tracking', ['trackingNumber' => $sh->tracking_number]) }}" class="px-3 py-1 rounded-xl bg-emerald-50 text-emerald-800 hover:bg-emerald-100 font-bold text-xs transition flex items-center gap-1">
                                                    <span>📍</span> Xarita
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Items Table -->
                                    <div class="space-y-2">
                                        <div class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider">Xarid qilingan tovarlar:</div>
                                        <div class="divide-y divide-[#E8ECE4]/60">
                                            @foreach($suborder->items as $item)
                                                <div class="py-2.5 flex items-center justify-between text-xs">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-8 h-8 rounded-lg bg-[#FAFBF7] border border-[#E8ECE4] flex items-center justify-center text-xs font-bold text-[#123C2F]">
                                                            📦
                                                        </div>
                                                        <div>
                                                            <div class="font-bold text-[#172326]">{{ $item->product_name }}</div>
                                                            <div class="text-[11px] text-[#64748B]">SKU: {{ $item->variant_sku ?? '—' }} • {{ $item->quantity }} {{ $item->unit }} × {{ number_format($item->unit_price, 0, '.', ' ') }} UZS</div>
                                                        </div>
                                                    </div>
                                                    <div class="font-black text-[#172326] text-sm">
                                                        {{ number_format($item->subtotal, 0, '.', ' ') }} UZS
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Logistics & Delivery Info Bar -->
                                    <div class="pt-3 border-t border-[#E8ECE4] grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs bg-[#FAFBF7] p-3 rounded-xl">
                                        <div>
                                            <span class="text-[#64748B] block text-[10px] uppercase font-bold">Yetkazish manzili:</span>
                                            <span class="font-semibold text-[#172326]">{{ $order->shipping_address['address_line'] ?? 'Sergeli ombori' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-[#64748B] block text-[10px] uppercase font-bold">Qabul qiluvchi:</span>
                                            <span class="font-semibold text-[#172326]">{{ $order->shipping_address['recipient_name'] ?? $user->name }} ({{ $order->shipping_address['phone'] ?? '+998 90 123 45 67' }})</span>
                                        </div>
                                        <div>
                                            <span class="text-[#64748B] block text-[10px] uppercase font-bold">Kuryerga aytiladigan kod (OTP):</span>
                                            <span class="font-mono font-black text-[#123C2F] text-sm bg-white px-2.5 py-0.5 rounded border border-[#E8ECE4]">8492</span>
                                        </div>
                                    </div>

                                </div>
                            @endforeach

                        </div>

                    </div>
                @empty
                    <div class="bg-white rounded-3xl border border-[#E8ECE4] p-12 text-center shadow-xs">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">🛒</div>
                        <h3 class="font-extrabold text-base text-[#172326] mb-1">Xaridlar topilmadi</h3>
                        <p class="text-xs text-[#64748B] mb-6">Ushbu parametr bo'yicha sizda hali rasmiylashtirilgan xaridlar mavjud emas.</p>
                        <a href="{{ route('catalog') }}" class="px-6 py-3 rounded-2xl bg-[#123C2F] text-white text-xs font-bold hover:bg-[#0d2a21] transition">
                            Katalogdan tovar tanlash
                        </a>
                    </div>
                @endforelse

            </div>
        @endif

        <!-- CONTENT TAB 2: SALES (Mijozlar bergan buyurtmalar / Do'kon sotuvlari) -->
        @if($activeTab === 'sales' && $isSeller)
            <div class="space-y-6">
                
                @forelse($sales as $suborder)
                    @php
                        $sh = $suborder->shipments->first();
                        $order = $suborder->order;
                        $isInTransit = $sh && $sh->status === 'in_transit';
                    @endphp

                    <div class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs hover:border-[#123C2F]/30 transition-all">
                        
                        <!-- Header Bar -->
                        <div class="p-5 sm:p-6 bg-[#FAFBF7] border-b border-[#E8ECE4] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="w-10 h-10 rounded-2xl bg-purple-700 text-white flex items-center justify-center font-bold text-sm">
                                    💼
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-mono font-black text-base text-[#172326]">{{ $suborder->suborder_number }}</h3>
                                        <span class="text-xs text-[#64748B]">Ota buyurtma: <span class="font-mono font-bold">{{ $order->order_number ?? '—' }}</span></span>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-800">
                                            {{ $suborder->status }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-[#64748B] mt-0.5">
                                        Xaridor: <strong class="text-[#172326]">{{ $order->shipping_address['recipient_name'] ?? $order->user?->name ?? 'Mijoz' }}</strong> ({{ $order->shipping_address['phone'] ?? '—' }})
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <span class="text-[11px] text-[#64748B] block">Do'konga tushum:</span>
                                    <span class="text-xl font-black text-emerald-700">{{ number_format($suborder->total_amount - $suborder->commission_amount, 0, '.', ' ') }} UZS</span>
                                    <span class="text-[10px] text-[#64748B] block">Komissiya: {{ number_format($suborder->commission_amount, 0, '.', ' ') }} UZS</span>
                                </div>

                                @if($sh)
                                    <a href="{{ route('tracking', ['trackingNumber' => $sh->tracking_number]) }}" class="px-4 py-2.5 rounded-xl bg-[#123C2F] text-[#C9F46B] hover:bg-[#0d2a21] text-xs font-bold flex items-center gap-1.5 shadow-sm transition">
                                        <span>📍</span>
                                        <span>Xaritada Kuzatish</span>
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Body: Products list -->
                        <div class="p-6 space-y-4">
                            <div class="text-[11px] font-bold text-[#64748B] uppercase tracking-wider">Mijoz buyurtma qilgan tovarlar:</div>
                            <div class="divide-y divide-[#E8ECE4]">
                                @foreach($suborder->items as $item)
                                    <div class="py-2.5 flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-3">
                                            <span class="w-6 h-6 rounded-lg bg-[#FAFBF7] flex items-center justify-center font-bold text-xs">✓</span>
                                            <div>
                                                <span class="font-bold text-[#172326]">{{ $item->product_name }}</span>
                                                <span class="text-[#64748B] text-[11px] block">{{ $item->quantity }} {{ $item->unit }} × {{ number_format($item->unit_price, 0, '.', ' ') }} UZS</span>
                                            </div>
                                        </div>
                                        <div class="font-black text-[#172326]">
                                            {{ number_format($item->subtotal, 0, '.', ' ') }} UZS
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="pt-3 border-t border-[#E8ECE4] flex flex-col sm:flex-row sm:items-center justify-between text-xs text-[#64748B] gap-2">
                                <div>
                                    <strong class="text-[#172326]">Yetkazish manzili:</strong> {{ $order->shipping_address['address_line'] ?? 'Manzil ko\'rsatilmagan' }}
                                </div>
                                @if($sh)
                                    <div>
                                        <strong class="text-[#172326]">Trek:</strong> <span class="font-mono text-[#123C2F] font-bold">{{ $sh->tracking_number }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="bg-white rounded-3xl border border-[#E8ECE4] p-12 text-center shadow-xs">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">💼</div>
                        <h3 class="font-extrabold text-base text-[#172326] mb-1">Mijoz buyurtmalari mavjud emas</h3>
                        <p class="text-xs text-[#64748B]">Do'koningizga hali buyurtmalar kelib tushmagan.</p>
                    </div>
                @endforelse

            </div>
        @endif

    </div>
</x-layouts.app>
