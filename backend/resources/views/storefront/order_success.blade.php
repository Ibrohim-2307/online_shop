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

            @if($isPaid)
                <!-- Rasmiy Elektron Fiskal Chek (Online Chek) -->
                <div class="mt-8 bg-white rounded-3xl border-2 border-slate-300 p-6 sm:p-8 max-w-lg mx-auto text-left shadow-lg space-y-4 relative overflow-hidden">
                    <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600"></div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-[#123C2F]">ONLINE DO'KON FISKAL CHEKI</div>
                            <h4 class="font-black text-sm text-[#172326]">MARKETPLACE UZBEKISTAN</h4>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase border border-emerald-300">
                            ✓ TO'LANDI
                        </span>
                    </div>

                    <div class="space-y-1.5 text-xs font-mono bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                        <div class="flex justify-between text-[#64748B]">
                            <span>Chek raqami:</span>
                            <span class="font-bold text-[#172326]">#CHK-{{ strtoupper(substr($order->order_number, -8)) }}</span>
                        </div>
                        <div class="flex justify-between text-[#64748B]">
                            <span>Tranzaksiya:</span>
                            <span class="font-bold text-[#172326]">{{ $latestPayment->transaction_id ?? 'TXN-ONLINE' }}</span>
                        </div>
                        <div class="flex justify-between text-[#64748B]">
                            <span>To'lov tizimi:</span>
                            <span class="font-bold text-[#123C2F] uppercase">{{ $latestPayment->payment_method ?? 'ONLINE' }}</span>
                        </div>
                        <div class="flex justify-between text-[#64748B]">
                            <span>Vaqti:</span>
                            <span>{{ $latestPayment->paid_at ? \Carbon\Carbon::parse($latestPayment->paid_at)->format('d.m.Y H:i:s') : now()->format('d.m.Y H:i:s') }}</span>
                        </div>
                        <div class="flex justify-between text-[#64748B]">
                            <span>Fiskal belgi:</span>
                            <span>FSK-{{ rand(10000000, 99999999) }}</span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-dashed border-[#E8ECE4] space-y-1.5 text-xs">
                        @foreach($order->suborders->flatMap->items as $it)
                            <div class="flex justify-between font-mono">
                                <span class="truncate max-w-[200px]">{{ $it->product_name }}</span>
                                <span>{{ number_format($it->subtotal, 0, '.', ' ') }} UZS</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-[#E8ECE4] flex justify-between items-baseline font-mono">
                        <span class="font-black text-sm text-[#172326]">JAMI TO'LANDI:</span>
                        <span class="font-black text-lg text-[#123C2F]">{{ number_format($order->grand_total, 0, '.', ' ') }} UZS</span>
                    </div>

                    <div class="pt-4 border-t border-[#E8ECE4] flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="w-12 h-12 bg-white border border-[#E8ECE4] rounded-lg p-1 flex items-center justify-center">
                                <svg class="w-10 h-10 text-slate-800" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm-2 10h8v8H2v-8zm2 2v4h4v-4H4zm10-14h8v8h-8V2zm2 2v4h4V4h-4zm-1 10h2v2h-2v-2zm2 2h2v2h-2v-2zm-2 2h2v2h-2v-2zm4-4h2v2h-2v-2zm0 4h2v2h-2v-2zm-4-10h2v2h-2V6zm2 2h2v2h-2V8zm2-2h2v2h-2V6z"/>
                                </svg>
                            </div>
                            <div class="text-[10px] text-[#64748B]">
                                <p class="font-bold text-[#172326]">Soliq.uz QR-kodi</p>
                                <p>Elektron fiskal kvitansiya</p>
                            </div>
                        </div>

                        <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl bg-white border border-[#E8ECE4] hover:bg-[#FAFBF7] text-xs font-bold text-[#172326] flex items-center gap-1.5 shadow-xs transition">
                            <span>🖨️</span>
                            <span>Chop etish</span>
                        </button>
                    </div>
                </div>
            @endif

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
                                    <span>Telegram</span>
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
