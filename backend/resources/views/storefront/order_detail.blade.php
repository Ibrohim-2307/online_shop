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
            $isNasiya = $latestPayment && $latestPayment->payment_method === 'nasiya';
            $nasiyaInfo = $isNasiya ? ($latestPayment->payload_snapshot['nasiya'] ?? []) : [];
            $nasiyaMonths = $nasiyaInfo['months'] ?? 6;
            $nasiyaMonthly = $nasiyaInfo['monthly_amount'] ?? 0;
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
                @if($order->status === 'cancelled')
                    <span class="px-3.5 py-1.5 rounded-xl bg-rose-100 text-rose-800 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span>✕</span>
                        <span>Bekor qilingan</span>
                    </span>
                @elseif($isPaid)
                    <span class="px-3.5 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span>✓</span>
                        <span>To'langan{{ $latestPayment?->payment_method === 'demo' ? ' (test)' : '' }}</span>
                    </span>
                @elseif($isCod)
                    <span class="px-3.5 py-1.5 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span>💵</span>
                        <span>Kuryerga to'lanadi</span>
                    </span>
                @elseif($isNasiya)
                    <span class="px-3.5 py-1.5 rounded-xl bg-amber-100 text-amber-900 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span>⚡</span>
                        <span>Muddatli To'lov (Nasiya - {{ $nasiyaMonths }} oy)</span>
                    </span>
                @else
                    <span class="px-3.5 py-1.5 rounded-xl bg-amber-100 text-amber-900 text-xs font-black uppercase tracking-wider inline-flex items-center gap-1.5">
                        <span>⏳</span>
                        <span>To'lov kutilmoqda</span>
                    </span>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold">{{ session('error') }}</div>
        @endif

        @if($isNasiya)
            <div class="mb-8 p-6 rounded-3xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">⚡</span>
                    <div>
                        <h3 class="font-bold text-sm text-amber-950">Muddatli to'lov (Nasiya) shartnomasi faollashtirildi</h3>
                        <p class="text-xs text-amber-800 mt-0.5">{{ $nasiyaMonths }} oyga bo'lingan. Oylik to'lov: <strong>{{ number_format($nasiyaMonthly, 0, '.', ' ') }} UZS/oy</strong>. 0% boshlang'ich to'lov.</p>
                    </div>
                </div>
                <span class="px-3.5 py-1.5 rounded-xl bg-amber-200 text-amber-950 text-xs font-black whitespace-nowrap">
                    ✓ 0% Boshlang'ich To'lov
                </span>
            </div>
        @endif

        @if($canCancel ?? false)
            <form method="POST" action="{{ route('orders.cancel', $order->order_number) }}"
                  onsubmit="return confirm('Buyurtmani bekor qilmoqchimisiz?')"
                  class="mb-8 p-5 rounded-3xl bg-white border border-[#E8ECE4] flex flex-col sm:flex-row sm:items-center gap-3">
                @csrf
                <div class="flex-1">
                    <h3 class="font-bold text-sm text-[#172326]">Fikringiz o'zgardimi?</h3>
                    <p class="text-xs text-[#64748B] mt-0.5">Buyurtma kuryerga topshirilgunga qadar uni bepul bekor qilishingiz mumkin.</p>
                </div>
                <select name="reason" class="px-3 py-2.5 rounded-xl border border-[#E8ECE4] text-xs">
                    <option value="">Sababni tanlang (ixtiyoriy)</option>
                    <option>Fikrimni o'zgartirdim</option>
                    <option>Boshqa joydan arzonroq topdim</option>
                    <option>Yetkazish muddati to'g'ri kelmaydi</option>
                    <option>Xato buyurtma berdim</option>
                </select>
                <button type="submit" class="px-5 py-2.5 rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-50 font-bold text-xs transition">
                    Buyurtmani bekor qilish
                </button>
            </form>
        @endif

        <!-- Pending Payment Banner -->
        @if(!$isPaid && !$isCod && !$isNasiya && $order->status !== 'cancelled')
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

                        <!-- Progress timeline for this seller's part -->
                        @php
                            $steps = ['Qabul qilindi', 'Tasdiqlandi', 'Qadoqlanmoqda', "Yo'lda", 'Yetkazildi'];
                            $isCancelled = $order->status === 'cancelled' || $suborder->status === 'cancelled';
                            $shipStatus = $suborder->shipments->first()?->status;
                            $stepIndex = match (true) {
                                $suborder->status === 'delivered' || $shipStatus === 'delivered' => 4,
                                $suborder->status === 'shipped' || in_array($shipStatus, ['picked_up', 'in_transit'], true) => 3,
                                $suborder->status === 'preparing' => 2,
                                $suborder->status === 'confirmed' => 1,
                                default => 0,
                            };
                            $subRefund = $order->refunds->where('suborder_id', $suborder->id)->sortByDesc('id')->first();
                            $refundLabels = [
                                'requested' => ["Qaytarish so'rovi ko'rib chiqilmoqda", 'bg-amber-50 text-amber-900 border-amber-200'],
                                'approved' => ["Qaytarish tasdiqlandi — tovarni topshiring", 'bg-blue-50 text-blue-800 border-blue-200'],
                                'goods_returned' => ['Tovar qabul qilindi, pul qaytarilmoqda', 'bg-blue-50 text-blue-800 border-blue-200'],
                                'completed' => ['Pul qaytarildi', 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                                'rejected' => ["Qaytarish rad etildi", 'bg-rose-50 text-rose-800 border-rose-200'],
                            ];
                        @endphp
                        @if($isCancelled)
                            <div class="px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-800">✕ Bu qism bekor qilingan</div>
                        @else
                            <ol class="grid grid-cols-5 gap-1" aria-label="Buyurtma holati">
                                @foreach($steps as $i => $label)
                                    <li class="flex flex-col items-center text-center gap-1.5">
                                        <div class="w-full h-1.5 rounded-full {{ $i <= $stepIndex ? 'bg-[#123C2F]' : 'bg-[#E8ECE4]' }}"></div>
                                        <span class="hidden sm:block text-[11px] leading-tight {{ $i === $stepIndex ? 'font-black text-[#123C2F]' : ($i < $stepIndex ? 'font-semibold text-[#172326]' : 'text-[#94A3B8]') }}"
                                              @if($i === $stepIndex) aria-current="step" @endif>
                                            {{ $label }}
                                        </span>
                                    </li>
                                @endforeach
                            </ol>
                            <p class="sm:hidden -mt-1 text-xs text-[#64748B]">
                                Holat: <span class="font-black text-[#123C2F]">{{ $steps[$stepIndex] }}</span>
                                <span class="text-[#94A3B8]">({{ $stepIndex + 1 }}/{{ count($steps) }})</span>
                            </p>
                        @endif

                        @if($subRefund)
                            <div class="px-4 py-3 rounded-xl border text-xs font-bold {{ $refundLabels[$subRefund->status][1] ?? '' }}">
                                ↩ {{ $refundLabels[$subRefund->status][0] ?? $subRefund->status }}
                                <span class="font-mono font-medium opacity-70">#{{ $subRefund->refund_number }}</span>
                                @if($subRefund->moderator_notes)
                                    <p class="font-medium mt-1">{{ $subRefund->moderator_notes }}</p>
                                @endif
                            </div>
                        @endif

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

                        @if(in_array($suborder->id, $returnableSuborderIds ?? [], true))
                            <details class="pt-3 border-t border-[#E8ECE4] group">
                                <summary class="cursor-pointer text-xs font-bold text-[#123C2F] hover:underline list-none">
                                    ↩ Tovarni qaytarish (yetkazilgandan keyin {{ \App\Http\Controllers\Web\OrderHubController::RETURN_WINDOW_DAYS }} kun ichida)
                                </summary>
                                <form method="POST" action="{{ route('orders.returns.store', $order->order_number) }}" class="mt-3 space-y-3">
                                    @csrf
                                    <input type="hidden" name="suborder_id" value="{{ $suborder->id }}">
                                    <select name="reason" required class="w-full px-3 py-2.5 rounded-xl border border-[#E8ECE4] bg-white text-xs">
                                        <option value="">Sababni tanlang</option>
                                        <option>Tovar nuqsonli yoki singan</option>
                                        <option>Boshqa tovar keldi</option>
                                        <option>Tavsifga mos emas</option>
                                        <option>O'lchami / rangi to'g'ri kelmadi</option>
                                        <option>Fikrimni o'zgartirdim</option>
                                    </select>
                                    <textarea name="details" rows="2" maxlength="400" placeholder="Qo'shimcha izoh (ixtiyoriy)"
                                              class="w-full px-3 py-2.5 rounded-xl border border-[#E8ECE4] bg-white text-xs"></textarea>
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <span class="text-[11px] text-[#64748B]">Qaytariladigan summa: <b>{{ number_format($suborder->refundableGoodsAmount(), 0, '.', ' ') }} UZS</b>@if($suborder->discountShare() > 0) <span class="text-[10px]">(promokod chegirmasi ayirilgan)</span>@endif. <a href="{{ route('returns') }}" class="underline">Qaytarish shartlari</a></span>
                                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs">So'rov yuborish</button>
                                    </div>
                                </form>
                            </details>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Grand Total -->
            <div class="pt-4 border-t border-[#E8ECE4] flex items-center justify-between text-base">
                <span class="font-bold text-[#172326]">Jami to'lov:</span>
                <span class="font-black text-2xl text-[#123C2F]">{{ number_format($order->grand_total, 0, '.', ' ') }} UZS</span>
            </div>
        </div>

        @if($isOwner ?? false)
            <form method="POST" action="{{ route('orders.reorder', $order->order_number) }}" class="mb-6 flex justify-center">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl border-2 border-[#123C2F] text-[#123C2F] font-black text-xs hover:bg-[#123C2F] hover:text-[#C9F46B] transition">
                    🔁 Yana shu tovarlarni buyurtma qilish
                </button>
            </form>
        @endif

        <!-- Back to Orders Hub -->
        <div class="text-center">
            <a href="{{ route('orders.hub') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#123C2F] hover:underline">
                ← Barcha buyurtmalar ro'yxatiga qaytish
            </a>
        </div>

    </div>
</x-layouts.app>
