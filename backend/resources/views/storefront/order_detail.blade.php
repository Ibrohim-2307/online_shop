<x-layouts.app title="Buyurtma tafsilotlari">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-20">
        
        <!-- Breadcrumbs -->
        <nav class="flex flex-wrap items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <a href="{{ route('orders.hub') }}" class="hover:text-[#123C2F]">Buyurtmalar</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">{{ $order->order_number }}</span>
        </nav>

        @php
            $latestPayment = $order->latest_payment;
            $isPaid = $latestPayment && $latestPayment->status === 'paid';
            $isCod = $latestPayment && $latestPayment->payment_method === 'cash_on_delivery';
        @endphp

        <!-- Top Header Card -->
        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="font-mono text-sm font-black text-[#123C2F]">{{ $order->order_number }}</span>
                    <span class="text-xs text-[#64748B]">• {{ $order->created_at->format('d.m.Y H:i') }}</span>
                </div>
                <h1 class="text-2xl font-black text-[#172326] tracking-tight">
                    Buyurtma Tafsilotlari
                </h1>
            </div>

            <div>
                @if($isPaid)
                    <span class="px-3.5 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span>✓</span>
                        <span>To'langan</span>
                    </span>
                @elseif($isCod)
                    <span class="px-3.5 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span>💵</span>
                        <span>Kuryerga to'lanadi</span>
                    </span>
                @else
                    <span class="px-3.5 py-1.5 rounded-xl bg-amber-100 text-amber-900 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span>⏳</span>
                        <span>To'lov kutilmoqda</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Pending Payment Banner -->
        @if(!$isPaid && !$isCod)
            <div class="mb-8 p-6 rounded-3xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-sm text-amber-950">To'lov hali amalga oshirilmagan</h3>
                    <p class="text-xs text-amber-800 mt-0.5">Click yoki Payme orqali to'lang, yoki yetkazib berilganda naqd to'lash rejimiga o'ting.</p>
                </div>
                <a href="{{ route('payment.checkout', ['orderNumber' => $order->order_number]) }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs hover:brightness-110 shadow-sm transition-all whitespace-nowrap">
                    <span>Hozir to'lash (Click / Payme) →</span>
                </a>
            </div>
        @endif

        <!-- Order Suborders & Items -->
        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs space-y-6 mb-8">
            <h2 class="text-lg font-black text-[#172326] pb-3 border-b border-[#E8ECE4]">
                Do'konlar va Tovarlar
            </h2>

            <div class="space-y-6">
                @foreach($order->suborders as $suborder)
                    <div class="p-5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#E8ECE4]">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-sm text-[#172326]">{{ $suborder->seller->name }}</h3>
                                    <a href="{{ route('seller.show', $suborder->seller->slug) }}" class="text-[11px] text-[#123C2F] font-bold underline hover:text-emerald-700">Do'kon sahifasi →</a>
                                </div>
                                <p class="text-[11px] text-[#64748B]">Suborder: <span class="font-mono">{{ $suborder->suborder_number }}</span></p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if($suborder->seller->phone)
                                    <a href="tel:{{ $suborder->seller->phone }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-bold hover:bg-emerald-100 transition" title="Sotuvchiga qo'ng'iroq qilish">
                                        <span>📞</span>
                                        <span>{{ $suborder->seller->phone }}</span>
                                    </a>
                                @endif
                                <a href="https://t.me/market_seller_support?text={{ urlencode('Salom! Men buyurtma bo\'yicha murojaat qilyapman. Suborder raqami: ' . $suborder->suborder_number) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-sky-50 text-sky-800 border border-sky-200 text-xs font-bold hover:bg-sky-100 transition" title="Sotuvchiga Telegram orqali yozish">
                                    <span>💬</span>
                                    <span>Telegramda yozish</span>
                                </a>
                                <span class="px-2.5 py-1 rounded-lg bg-white border border-[#E8ECE4] text-xs font-bold text-[#123C2F]">
                                    {{ number_format($suborder->total_amount, 0, '.', ' ') }} UZS
                                </span>
                            </div>
                        </div>

                        <!-- Items in this suborder -->
                        <div class="space-y-2">
                            @foreach($suborder->items as $item)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-[#172326] font-medium">{{ $item->product_name }} (×{{ $item->quantity }} {{ $item->unit }})</span>
                                    <span class="font-bold text-[#172326]">{{ number_format($item->subtotal, 0, '.', ' ') }} UZS</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Shipment Tracking -->
                        @foreach($suborder->shipments as $shipment)
                            <div class="pt-3 border-t border-[#E8ECE4] flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <div>
                                    <span class="text-[#64748B]">Kuzatuv (Tracking):</span>
                                    <span class="font-mono font-bold text-[#123C2F]">{{ $shipment->tracking_number }}</span>
                                </div>
                                <a href="{{ route('tracking', ['trackingNumber' => $shipment->tracking_number]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs hover:bg-[#0d2a21] transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>📍 Xaritada jonli kuzatish</span>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <!-- Grand Total -->
            <div class="pt-4 border-t border-[#E8ECE4] flex items-center justify-between text-base">
                <span class="font-bold text-[#172326]">Jami to'lov:</span>
                <span class="font-black text-2xl text-[#123C2F]">{{ number_format($order->grand_total, 0, '.', ' ') }} UZS</span>
            </div>
        </div>

        <!-- Back to Orders Hub -->
        <div class="text-center">
            <a href="{{ route('orders.hub') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#123C2F] hover:underline">
                ← Barcha buyurtmalar ro'yxatiga qaytish
            </a>
        </div>

    </div>
</x-layouts.app>
