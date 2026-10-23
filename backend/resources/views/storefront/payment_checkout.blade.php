<x-layouts.app title="To'lovni amalga oshirish">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-24"
         x-data="{
             activeTab: '{{ $payment->payment_method === 'payme' ? 'payme' : 'click' }}',
             cardNumber: '',
             cardExpiry: '',
             formatCard(e) {
                 let v = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                 let matches = v.match(/\d{4,16}/g);
                 let match = matches && matches[0] || '';
                 let parts = [];
                 for (let i=0, len=match.length; i<len; i+=4) {
                     parts.push(match.substring(i, i+4));
                 }
                 if (parts.length) {
                     this.cardNumber = parts.join(' ');
                 } else {
                     this.cardNumber = v;
                 }
             }
         }">

        <!-- Breadcrumbs -->
        <nav class="flex flex-wrap items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <a href="{{ route('checkout') }}" class="hover:text-[#123C2F]">Buyurtma</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">To'lov sahifasi</span>
        </nav>

        <!-- Top Header & Amount Summary -->
        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-900 text-xs font-bold uppercase tracking-wider">
                        To'lov Kutilmoqda
                    </span>
                    <span class="text-xs text-[#64748B]">Buyurtma: <strong class="font-mono text-[#172326]">{{ $order->order_number }}</strong></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-[#172326] tracking-tight">
                    Xavfsiz To'lov Oynasi
                </h1>
                <p class="text-xs text-[#64748B] mt-1">
                    Click, Payme, Uzum yoki Yetkazib berilganda to'lash usulini tasdiqlang
                </p>
            </div>

            <div class="p-4 sm:p-5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] text-right sm:min-w-[220px]">
                <span class="block text-xs text-[#64748B] font-medium">To'lanishi lozim:</span>
                <span class="text-3xl font-black text-[#123C2F] tracking-tight">
                    {{ number_format($order->grand_total, 0, '.', ' ') }}
                    <span class="text-xs font-bold text-[#64748B]">UZS</span>
                </span>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-semibold flex items-center gap-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Payment Provider Tabs & Interface (8 cols) -->
            <div class="lg:col-span-8 bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs space-y-6">
                
                <!-- Tab Selector Buttons -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 p-1.5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4]">
                    <button type="button" @click="activeTab = 'click'"
                            :class="activeTab === 'click' ? 'bg-[#123C2F] text-white shadow-xs font-bold' : 'text-[#64748B] hover:text-[#172326] font-medium'"
                            class="py-3 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all">
                        <span>🟢</span>
                        <span>Click Up</span>
                    </button>
                    <button type="button" @click="activeTab = 'payme'"
                            :class="activeTab === 'payme' ? 'bg-[#123C2F] text-white shadow-xs font-bold' : 'text-[#64748B] hover:text-[#172326] font-medium'"
                            class="py-3 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all">
                        <span>🔵</span>
                        <span>Payme</span>
                    </button>
                    <button type="button" @click="activeTab = 'uzum'"
                            :class="activeTab === 'uzum' ? 'bg-[#123C2F] text-white shadow-xs font-bold' : 'text-[#64748B] hover:text-[#172326] font-medium'"
                            class="py-3 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all">
                        <span>🟣</span>
                        <span>Uzum / Karta</span>
                    </button>
                    <button type="button" @click="activeTab = 'cod'"
                            :class="activeTab === 'cod' ? 'bg-[#123C2F] text-white shadow-xs font-bold' : 'text-[#64748B] hover:text-[#172326] font-medium'"
                            class="py-3 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all">
                        <span>💵</span>
                        <span>Naqd pul</span>
                    </button>
                </div>

                <!-- 1. CLICK UP TAB -->
                <div x-show="activeTab === 'click'" class="space-y-6">
                    <div class="p-6 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-[#00A551]/10 text-[#00A551] flex items-center justify-center font-black text-2xl">
                                🟢
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base text-[#172326]">Click (Click Up / Click Evolution)</h3>
                                <p class="text-xs text-[#64748B]">Click ilovasi yoki bank kartasi orqali to'lov</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-xl bg-emerald-100 text-emerald-800 text-xs font-bold">Faol</span>
                    </div>

                    @if($clickUrl)
                        <div class="space-y-3">
                            <a href="{{ $clickUrl }}" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#00A551] text-white font-black text-sm hover:brightness-105 transition-all shadow-md">
                                <span>Click ilovasida to'lash →</span>
                            </a>
                            <p class="text-center text-xs text-[#64748B]">Click rasmiy to'lov oynasiga yo'naltirilasiz</p>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}">
                        @csrf
                        <input type="hidden" name="payment_method" value="click">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-black text-sm hover:brightness-110 transition-all shadow-md">
                            <span>Click orqali to'lashni tasdiqlash (Instant Settlement)</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <p class="text-center text-[11px] text-[#64748B] mt-2">
                            To'lov avtomatik tarzda chek bilan tasdiqlanadi va do'kon balansi yangilanadi
                        </p>
                    </form>
                </div>

                <!-- 2. PAYME TAB -->
                <div x-show="activeTab === 'payme'" style="display: none;" class="space-y-6">
                    <div class="p-6 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-[#14B8A6]/10 text-[#14B8A6] flex items-center justify-center font-black text-2xl">
                                🔵
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base text-[#172326]">Payme (Payme.uz)</h3>
                                <p class="text-xs text-[#64748B]">Uzcard va Humo kartalari orqali xavfsiz to'lov</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-xl bg-teal-100 text-teal-800 text-xs font-bold">Faol</span>
                    </div>

                    @if($paymeUrl)
                        <div class="space-y-3">
                            <a href="{{ $paymeUrl }}" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#14B8A6] text-white font-black text-sm hover:brightness-105 transition-all shadow-md">
                                <span>Payme ilovasida to'lash →</span>
                            </a>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}">
                        @csrf
                        <input type="hidden" name="payment_method" value="payme">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-black text-sm hover:brightness-110 transition-all shadow-md">
                            <span>Payme orqali to'lashni tasdiqlash (Instant Settlement)</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <p class="text-center text-[11px] text-[#64748B] mt-2">
                            Payme to'lov kvitansiyasi avtomatik generatsiya qilinadi
                        </p>
                    </form>
                </div>

                <!-- 3. UZUM / BANK KARTA TAB -->
                <div x-show="activeTab === 'uzum'" style="display: none;" class="space-y-6">
                    <div class="p-6 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-[#8B5CF6]/10 text-[#8B5CF6] flex items-center justify-center font-black text-2xl">
                                🟣
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base text-[#172326]">Uzum Bank / Bank Kartasi</h3>
                                <p class="text-xs text-[#64748B]">Uzcard, Humo, Visa va Mastercard</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-xl bg-purple-100 text-purple-800 text-xs font-bold">Faol</span>
                    </div>

                    <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="payment_method" value="uzum">

                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1.5">Karta raqami (16 xonali)</label>
                            <input type="text" x-model="cardNumber" @input="formatCard($event)" maxlength="19"
                                   placeholder="8600 0000 0000 0000"
                                   class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] font-mono text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#172326] mb-1.5">Amal qilish muddati</label>
                                <input type="text" maxlength="5" placeholder="MM/YY"
                                       class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] font-mono text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#172326] mb-1.5">Karta egasi</label>
                                <input type="text" placeholder="ISM FAMILIYA"
                                       class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white uppercase">
                            </div>
                        </div>

                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#7C3AED] text-white font-black text-sm hover:brightness-105 transition-all shadow-md">
                            <span>Karta orqali to'lash: {{ number_format($order->grand_total, 0, '.', ' ') }} UZS</span>
                        </button>
                    </form>
                </div>

                <!-- 4. CASH ON DELIVERY (NAQD PULDA KURYERGA TO'LASH) TAB -->
                <div x-show="activeTab === 'cod'" style="display: none;" class="space-y-6">
                    <div class="p-6 rounded-2xl bg-amber-50 border border-amber-200 space-y-3">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">💵</span>
                            <div>
                                <h3 class="font-extrabold text-base text-amber-900">Yetkazib berilganda to'lash (Naqd yoki Karta)</h3>
                                <p class="text-xs text-amber-700">Oldindan to'lov talab qilinmaydi</p>
                            </div>
                        </div>
                        <p class="text-xs text-amber-800 leading-relaxed">
                            Kuryer buyurtmangizni eshigingizgacha olib borganda mahsulotni ko'zdan kechirib, naqd pul yoki bank terminali orqali to'lovni amalga oshirasiz.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}">
                        @csrf
                        <input type="hidden" name="payment_method" value="cash_on_delivery">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-black text-sm hover:brightness-110 transition-all shadow-md">
                            <span>Yetkazib berilganda to'lashni tasdiqlash →</span>
                        </button>
                    </form>
                </div>

            </div>

            <!-- Order Details Sidebar (4 cols) -->
            <div class="lg:col-span-4 bg-white rounded-3xl border border-[#E8ECE4] p-6 shadow-xs space-y-6">
                <h3 class="font-black text-base text-[#172326] pb-3 border-b border-[#E8ECE4]">
                    Buyurtma Ma'lumotlari
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between text-[#64748B]">
                        <span>Qabul qiluvchi:</span>
                        <strong class="text-[#172326]">{{ $order->shipping_address['recipient_name'] ?? 'Mijoz' }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-[#64748B]">
                        <span>Telefon:</span>
                        <strong class="text-[#172326]">{{ $order->shipping_address['phone'] ?? '—' }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-[#64748B]">
                        <span>Manzil:</span>
                        <span class="text-right font-medium text-[#172326] max-w-[180px] truncate">
                            {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['address_line'] ?? '' }}
                        </span>
                    </div>
                </div>

                <div class="pt-4 border-t border-[#E8ECE4] space-y-2 text-xs">
                    <div class="flex items-center justify-between text-[#64748B]">
                        <span>Mahsulotlar:</span>
                        <strong class="text-[#172326]">{{ number_format($order->items_total, 0, '.', ' ') }} UZS</strong>
                    </div>
                    <div class="flex items-center justify-between text-[#64748B]">
                        <span>Yetkazib berish:</span>
                        <strong class="text-[#172326]">{{ number_format($order->shipping_total, 0, '.', ' ') }} UZS</strong>
                    </div>
                    <div class="pt-3 border-t border-[#E8ECE4] flex items-center justify-between text-sm">
                        <span class="font-black text-[#172326]">Jami to'lov:</span>
                        <span class="font-black text-lg text-[#123C2F]">{{ number_format($order->grand_total, 0, '.', ' ') }} UZS</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-[#E8ECE4] text-[11px] text-[#64748B] space-y-2">
                    <div class="flex items-center gap-2">
                        <span>🔒</span>
                        <span>256-bit xavfsiz SSL shifrlangan aloqa</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span>✓</span>
                        <span>O'zbekiston Markaziy Banki litsenziyalangan tizimlari</span>
                    </div>
                </div>

                <a href="{{ route('order.success', ['orderNumber' => $order->order_number]) }}" class="block text-center text-xs font-bold text-[#64748B] hover:text-[#123C2F] pt-2">
                    ← Keyinroq to'lash (Buyurtmaga qaytish)
                </a>
            </div>

        </div>

    </div>
</x-layouts.app>
