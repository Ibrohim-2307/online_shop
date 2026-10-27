<x-layouts.app title="Onlayn to'lov">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-24"
         x-data="{
             activeTab: '{{ in_array($payment->payment_method, ['payme', 'uzum']) ? $payment->payment_method : 'click' }}',
         }">

        <!-- Breadcrumbs -->
        <nav class="flex flex-wrap items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <a href="{{ route('checkout') }}" class="hover:text-[#123C2F]">Buyurtma</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">Onlayn to'lov oynasi</span>
        </nav>

        <!-- Top Header & Amount Summary -->
        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black uppercase tracking-wider flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>Onlayn Savdo To'lovi</span>
                    </span>
                    <span class="text-xs text-[#64748B]">Buyurtma: <strong class="font-mono text-[#172326]">{{ $order->order_number }}</strong></span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-[#172326] tracking-tight">
                    Onlayn To'lov Oynasi
                </h1>
                <p class="text-xs text-[#64748B] mt-1">
                    Click yoki Payme to'lov tizimlari orqali xavfsiz to'lovni tasdiqlang
                </p>
            </div>

            <div class="p-4 sm:p-5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] text-right sm:min-w-[220px]">
                <span class="block text-xs text-[#64748B] font-medium">Jami to'lanadi:</span>
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
                <div class="grid grid-cols-3 gap-2 p-1.5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4]">
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
                        <span>💳</span>
                        <span>Uzcard / Humo</span>
                    </button>
                </div>

                <!-- 1. CLICK UP TAB -->
                <div x-show="activeTab === 'click'" class="space-y-6">
                    <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/60 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-[#00A551] text-white flex items-center justify-center font-black text-2xl shadow-sm">
                                🟢
                            </div>
                            <div>
                                <h3 class="font-black text-base text-[#172326]">Click Up / Click Evolution</h3>
                                <p class="text-xs text-[#64748B]">Click ilovasi yoki to'lov oynasi orqali rasmiy xavfsiz to'lov</p>
                            </div>
                        </div>
                        @if($isClickConfigured)
                            <span class="px-3 py-1 rounded-xl bg-emerald-200/80 text-emerald-900 text-xs font-bold">Faol</span>
                        @else
                            <span class="px-3 py-1 rounded-xl bg-amber-100 text-amber-800 text-xs font-bold">Sozlanmoqda</span>
                        @endif
                    </div>

                    @if($isClickConfigured && $clickUrl)
                        <div class="space-y-2">
                            <a href="{{ $clickUrl }}" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#00A551] text-white font-black text-sm hover:brightness-105 transition-all shadow-md">
                                <span>Click ilovasida to'lash →</span>
                            </a>
                            <p class="text-center text-xs text-[#64748B]">Click rasmiy himoyalangan to'lov oynasiga yo'naltirilasiz</p>
                        </div>
                    @elseif($isClickConfigured)
                        <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="click">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#00A551] text-white font-black text-sm hover:brightness-105 transition-all shadow-md">
                                <span>Click orqali to'lash sahifasiga o'tish →</span>
                            </button>
                        </form>
                    @else
                        <div class="p-5 rounded-2xl bg-amber-50/70 border border-amber-200 text-xs text-amber-800 space-y-2">
                            <p class="font-bold text-amber-900 flex items-center gap-2">
                                <span>⚙️</span>
                                <span>Click integratsiyasi sozlanmoqda</span>
                            </p>
                            <p>
                                Click tizimi orqali to'lovlarni qabul qilish uchun server sozlamalarida <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-amber-300">CLICK_SERVICE_ID</code>, <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-amber-300">CLICK_MERCHANT_ID</code> va <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-amber-300">CLICK_SECRET_KEY</code> kiritilishi kerak.
                            </p>
                            <p class="text-slate-600">
                                Iltimos, boshqa to'lov usulidan foydalaning (masalan, Kuryer orqali qabul qilishda to'lov).
                            </p>
                        </div>
                    @endif
                </div>

                <!-- 2. PAYME TAB -->
                <div x-show="activeTab === 'payme'" style="display: none;" class="space-y-6">
                    <div class="p-6 rounded-2xl bg-gradient-to-r from-teal-50 to-cyan-50 border border-teal-200/60 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-[#14B8A6] text-white flex items-center justify-center font-black text-2xl shadow-sm">
                                🔵
                            </div>
                            <div>
                                <h3 class="font-black text-base text-[#172326]">Payme (Paycom)</h3>
                                <p class="text-xs text-[#64748B]">Uzcard va Humo kartalari orqali rasmiy xavfsiz onlayn to'lov</p>
                            </div>
                        </div>
                        @if($isPaymeConfigured)
                            <span class="px-3 py-1 rounded-xl bg-teal-200/80 text-teal-900 text-xs font-bold">Faol</span>
                        @else
                            <span class="px-3 py-1 rounded-xl bg-amber-100 text-amber-800 text-xs font-bold">Sozlanmoqda</span>
                        @endif
                    </div>

                    @if($isPaymeConfigured && $paymeUrl)
                        <div class="space-y-2">
                            <a href="{{ $paymeUrl }}" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#14B8A6] text-white font-black text-sm hover:brightness-105 transition-all shadow-md">
                                <span>Payme ilovasida to'lash →</span>
                            </a>
                            <p class="text-center text-xs text-[#64748B]">Payme rasmiy to'lov oynasiga yo'naltirilasiz</p>
                        </div>
                    @elseif($isPaymeConfigured)
                        <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="payme">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#14B8A6] text-white font-black text-sm hover:brightness-105 transition-all shadow-md">
                                <span>Payme to'lov oynasiga o'tish →</span>
                            </button>
                        </form>
                    @else
                        <div class="p-5 rounded-2xl bg-amber-50/70 border border-amber-200 text-xs text-amber-800 space-y-2">
                            <p class="font-bold text-amber-900 flex items-center gap-2">
                                <span>⚙️</span>
                                <span>Payme integratsiyasi sozlanmoqda</span>
                            </p>
                            <p>
                                Payme orqali to'lovlarni qabul qilish uchun server sozlamalarida <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-amber-300">PAYME_MERCHANT_ID</code> va <code class="font-mono bg-white px-1.5 py-0.5 rounded border border-amber-300">PAYME_SECRET_KEY</code> kiritilishi kerak.
                            </p>
                            <p class="text-slate-600">
                                Iltimos, boshqa to'lov usulidan foydalaning (masalan, Kuryer orqali qabul qilishda to'lov).
                            </p>
                        </div>
                    @endif
                </div>

                <!-- 3. UZCARD / HUMO BANK KARTALARI MA'LUMOTI -->
                <div x-show="activeTab === 'uzum'" style="display: none;" class="space-y-6">
                    <div class="p-6 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">💳</span>
                            <div>
                                <h3 class="font-black text-base text-[#172326]">Bank kartalari (Uzcard / Humo / Visa)</h3>
                                <p class="text-xs text-[#64748B]">Xavfsizlik va PCI-DSS xalqaro to'lov standartlari</p>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl bg-white border border-[#E8ECE4] text-xs text-[#64748B] space-y-2.5">
                            <p class="text-[#172326] font-medium leading-relaxed">
                                Xaridorlarning shaxsiy bank karta ma'lumotlarini (PAN raqami va amal qilish muddati) firibgarlikdan himoya qilish uchun to'g'ridan-to'g'ri karta ma'lumotlari saytda saqlanmaydi.
                            </p>
                            <p class="text-[#172326] font-medium leading-relaxed">
                                Uzcard va Humo kartalaringiz orqali komissiyasiz va xavfsiz to'lash uchun rasmiy <strong>Click Up</strong> yoki <strong>Payme</strong> tugmasini bosing:
                            </p>
                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <button type="button" @click="activeTab = 'click'" class="py-2.5 px-4 rounded-xl bg-[#00A551] text-white font-bold text-xs text-center hover:brightness-105 transition">
                                    Click orqali to'lash →
                                </button>
                                <button type="button" @click="activeTab = 'payme'" class="py-2.5 px-4 rounded-xl bg-[#14B8A6] text-white font-bold text-xs text-center hover:brightness-105 transition">
                                    Payme orqali to'lash →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. DEVELOPER SANDBOX SIMULATION (Only shown in local/test mode if explicitly enabled) -->
                @if($isDemoAllowed)
                    <div class="p-5 rounded-2xl bg-amber-50 border-2 border-dashed border-amber-300 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-amber-800 font-black text-xs uppercase tracking-wider">⚠️ Dasturchi / Test Rejimi (Sandbox)</span>
                            </div>
                            <span class="text-[10px] font-mono bg-amber-200/80 text-amber-900 px-2 py-0.5 rounded font-bold">DEV ONLY</span>
                        </div>
                        <p class="text-xs text-amber-800 leading-relaxed">
                            Ushbu blok faqat mahalliy test va dasturlash bosqichida ko'rinadi. Production (haqiqiy server)da butunlay o'chirilgan.
                        </p>
                        <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="demo">
                            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition shadow-sm">
                                Test to'lovini simulyatsiya qilish (Sandbox Settlement)
                            </button>
                        </form>
                    </div>
                @endif

            </div>

            <!-- Order Details Sidebar (4 cols) -->
            <div class="lg:col-span-4 bg-white rounded-3xl border border-[#E8ECE4] p-6 shadow-xs space-y-6">
                <h3 class="font-black text-base text-[#172326] pb-3 border-b border-[#E8ECE4]">
                    Buyurtma Ma'lumotlari
                </h3>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between text-[#64748B]">
                        <span>Xaridor:</span>
                        <strong class="text-[#172326]">{{ $order->shipping_address['recipient_name'] ?? 'Mijoz' }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-[#64748B]">
                        <span>Telefon:</span>
                        <strong class="text-[#172326]">{{ $order->shipping_address['phone'] ?? '—' }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-[#64748B]">
                        <span>Yetkazish manzili:</span>
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
                    <div class="flex items-center gap-2">
                        <span>⚡</span>
                        <span>Elektron fiskal chek darhol taqdim etiladi</span>
                    </div>
                </div>

                <a href="{{ route('order.success', ['orderNumber' => $order->order_number]) }}" class="block text-center text-xs font-bold text-[#64748B] hover:text-[#123C2F] pt-2">
                    ← Keyinroq to'lash (Buyurtmaga qaytish)
                </a>
            </div>

        </div>

    </div>
</x-layouts.app>
