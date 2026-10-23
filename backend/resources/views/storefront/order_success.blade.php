<x-layouts.app title="Buyurtma qabul qilindi">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-20">
        
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-3 shadow-xs">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-sm font-semibold flex items-center gap-3 shadow-xs">
                <span class="text-xl">ℹ️</span>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @php
            $latestPayment = $order->latest_payment;
            $isPaid = $latestPayment && $latestPayment->status === 'paid';
            $isCod = $latestPayment && $latestPayment->payment_method === 'cash_on_delivery';
        @endphp

        <!-- Success Confirmation Card -->
        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-8 sm:p-12 text-center shadow-xs mb-8">
            <div class="w-20 h-20 rounded-3xl {{ $isPaid ? 'bg-emerald-500 text-white' : ($isCod ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-amber-400 text-amber-950') }} flex items-center justify-center mx-auto mb-6 text-3xl font-black shadow-sm">
                {{ $isPaid ? '✓' : ($isCod ? '📦' : '⏳') }}
            </div>

            @if($isPaid)
                <span class="px-3.5 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                    <span>✓</span>
                    <span>To'lov to'liq qabul qilindi (Paid)</span>
                </span>
            @elseif($isCod)
                <span class="px-3.5 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                    <span>💵</span>
                    <span>Yetkazib berishda to'lanadi (Kuryerga naqd yoki karta)</span>
                </span>
            @else
                <span class="px-3.5 py-1.5 rounded-xl bg-amber-100 text-amber-900 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                    <span>⏳</span>
                    <span>To'lov kutilmoqda (Click / Payme)</span>
                </span>
            @endif

            <h1 class="text-3xl sm:text-4xl font-black text-[#172326] tracking-tight mt-3 mb-2">
                {{ $isPaid ? 'Xaridingiz uchun rahmat!' : 'Buyurtmangiz qabul qilindi!' }}
            </h1>
            
            <p class="text-sm text-[#64748B] max-w-md mx-auto mb-6">
                @if($isPaid)
                    To'lov muvaffaqiyatli qabul qilindi. Buyurtmangiz omborga yuborildi va tez orada kuryerga topshiriladi.
                @elseif($isCod)
                    Rahmat! Buyurtmangiz tasdiqlandi. Kuryer buyurtmani olib borgan vaqtda naqd pul yoki karta orqali to'lov qilasiz.
                @else
                    Buyurtmangiz tizimda ro'yxatga olindi va ombordagi zaxiralar siz uchun 15 daqiqaga band qilindi. To'lovni Click yoki Payme orqali yakunlang.
                @endif
            </p>

            <div class="inline-flex flex-wrap items-center justify-center gap-3 px-5 py-3 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] text-sm">
                <span class="text-[#64748B]">Buyurtma raqami:</span>
                <span class="font-mono font-black text-[#123C2F] text-base">{{ $order->order_number }}</span>
                @if($latestPayment && $latestPayment->transaction_id)
                    <span class="text-slate-300">•</span>
                    <span class="text-[#64748B]">Tranzaksiya:</span>
                    <span class="font-mono text-xs text-slate-700">{{ $latestPayment->transaction_id }}</span>
                @endif
            </div>

            <!-- Pending Payment Actions Widget -->
            @if(!$isPaid && !$isCod)
                <div class="mt-8 p-6 rounded-2xl bg-amber-50/70 border border-amber-200 text-left max-w-lg mx-auto space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-900">To'lovni amalga oshirish:</span>
                        <span class="font-black text-amber-900 text-sm">{{ number_format($order->grand_total, 0, '.', ' ') }} UZS</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <a href="{{ route('payment.checkout', ['orderNumber' => $order->order_number]) }}" 
                           class="flex items-center justify-center gap-2 py-3 px-4 rounded-xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs hover:brightness-110 shadow-sm transition-all">
                            <span>To'lov sahifasiga o'tish →</span>
                        </a>

                        <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="click">
                            <button type="submit" class="w-full flex items-center justify-center gap-1.5 py-3 px-4 rounded-xl bg-[#00A551] text-white font-bold text-xs hover:brightness-105 shadow-sm transition-all">
                                <span>🟢 Click Test to'lash</span>
                            </button>
                        </form>
                    </div>

                    <!-- Switch to COD option -->
                    <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}" class="text-center pt-2 border-t border-amber-200/60">
                        @csrf
                        <input type="hidden" name="payment_method" value="cash_on_delivery">
                        <button type="submit" class="text-xs text-amber-800 hover:text-amber-950 font-semibold underline">
                            💵 Yoki yetkazib berilganda naqd to'lashga o'tish
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <!-- Order Suborders & Tracking breakdown -->
        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs space-y-6 mb-8">
            <h2 class="text-lg font-black text-[#172326] pb-3 border-b border-[#E8ECE4]">
                Do'konlar va Jo'natmalar (Multi-Vendor Suborders)
            </h2>

            <div class="space-y-6">
                @foreach($order->suborders as $suborder)
                    <div class="p-5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-[#E8ECE4]">
                            <div>
                                <h3 class="font-bold text-sm text-[#172326]">{{ $suborder->seller->name }}</h3>
                                <p class="text-[11px] text-[#64748B]">Suborder: <span class="font-mono">{{ $suborder->suborder_number }}</span></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-lg bg-[#FAFBF7] border border-[#E8ECE4] text-xs font-bold text-[#123C2F]">
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

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('orders.hub') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs hover:bg-[#0d2a21] transition-colors text-center">
                Mening buyurtmalarim
            </a>
            <a href="{{ route('catalog') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] text-[#172326] font-bold text-xs hover:bg-white transition-colors text-center">
                Xaridni davom ettirish
            </a>
        </div>

    </div>
</x-layouts.app>
