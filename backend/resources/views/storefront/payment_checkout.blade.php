<x-layouts.app title="Onlayn to'lov">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-24"
         x-data="{
             activeTab: '{{ in_array($payment->payment_method, ['payme', 'uzum']) ? $payment->payment_method : 'click' }}',
             cardNumber: '8600 4512 8900 3456',
             cardExpiry: '12/28',
             cardHolder: '{{ $order->shipping_address['recipient_name'] ?? 'AZIZ ALIYEV' }}',
             cardStep: 'input',
             smsCode: '',
             smsTimer: 60,
             isSubmitting: false,
             formatCard(e) {
                 let v = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                 let matches = v.match(/\d{4,16}/g);
                 let match = matches && matches[0] || '';
                 let parts = [];
                 for (let i=0, len=match.length; i<len; i+=4) {
                     parts.push(match.substring(i, i+4));
                 }
                 this.cardNumber = parts.length ? parts.join(' ') : v;
             },
             formatExpiry(e) {
                 let v = e.target.value.replace(/[^0-9]/g, '');
                 if (v.length > 2) {
                     this.cardExpiry = v.substring(0, 2) + '/' + v.substring(2, 4);
                 } else {
                     this.cardExpiry = v;
                 }
             },
             goToSms() {
                 if (this.cardNumber.replace(/\s/g, '').length < 16) {
                     alert('Iltimos, 16 xonali karta raqamini to\'liq kiriting!');
                     return;
                 }
                 this.cardStep = 'sms';
                 this.smsTimer = 60;
                 let interval = setInterval(() => {
                     if (this.smsTimer > 0) this.smsTimer--;
                     else clearInterval(interval);
                 }, 1000);
             }
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
                    Click, Payme yoki Bank kartasi (Uzcard / Humo) orqali to'lovni tasdiqlang
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
                                <p class="text-xs text-[#64748B]">Click ilovasi yoki QR-kod orqali 1-bosqichda onlayn to'lov</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-xl bg-emerald-200/80 text-emerald-900 text-xs font-bold">Faol</span>
                    </div>

                    @if($clickUrl)
                        <div class="space-y-2">
                            <a href="{{ $clickUrl }}" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#00A551] text-white font-black text-sm hover:brightness-105 transition-all shadow-md">
                                <span>Click ilovasida to'lash →</span>
                            </a>
                            <p class="text-center text-xs text-[#64748B]">Click rasmiy to'lov oynasiga yo'naltirilasiz</p>
                        </div>
                    @endif

                    <div class="p-5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] flex flex-col sm:flex-row items-center gap-6">
                        <!-- Simulated QR Code -->
                        <div class="w-32 h-32 bg-white p-2 rounded-2xl border border-[#E8ECE4] flex items-center justify-center shadow-xs flex-shrink-0">
                            <svg class="w-28 h-28 text-slate-800" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm-2 10h8v8H2v-8zm2 2v4h4v-4H4zm10-14h8v8h-8V2zm2 2v4h4V4h-4zm-1 10h2v2h-2v-2zm2 2h2v2h-2v-2zm-2 2h2v2h-2v-2zm4-4h2v2h-2v-2zm0 4h2v2h-2v-2zm-4-10h2v2h-2V6zm2 2h2v2h-2V8zm2-2h2v2h-2V6z"/>
                            </svg>
                        </div>
                        <div class="text-xs space-y-2 text-[#64748B]">
                            <p class="font-bold text-[#172326] text-sm">Click Evolution ilovasidan skanerlang</p>
                            <p>Telefoningizdagi Click ilovasi orqali yuqoridagi QR-kodni skaner qilib to'lovni tasdiqlashingiz mumkin.</p>
                            <p class="font-semibold text-emerald-700">✓ Fiskal chek darhol soliq tizimida ro'yxatga olinadi</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}">
                        @csrf
                        <input type="hidden" name="payment_method" value="click">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-black text-sm hover:brightness-110 transition-all shadow-md">
                            <span>Click orqali to'lashni tasdiqlash (Instant Onlayn To'lov)</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </form>
                </div>

                <!-- 2. PAYME TAB -->
                <div x-show="activeTab === 'payme'" style="display: none;" class="space-y-6">
                    <div class="p-6 rounded-2xl bg-gradient-to-r from-teal-50 to-cyan-50 border border-teal-200/60 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-[#14B8A6] text-white flex items-center justify-center font-black text-2xl shadow-sm">
                                🔵
                            </div>
                            <div>
                                <h3 class="font-black text-base text-[#172326]">Payme (Payme.uz)</h3>
                                <p class="text-xs text-[#64748B]">Uzcard va Humo kartalari orqali xavfsiz onlayn to'lov</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-xl bg-teal-200/80 text-teal-900 text-xs font-bold">Faol</span>
                    </div>

                    @if($paymeUrl)
                        <div class="space-y-2">
                            <a href="{{ $paymeUrl }}" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#14B8A6] text-white font-black text-sm hover:brightness-105 transition-all shadow-md">
                                <span>Payme ilovasida to'lash →</span>
                            </a>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}">
                        @csrf
                        <input type="hidden" name="payment_method" value="payme">
                        <button type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-black text-sm hover:brightness-110 transition-all shadow-md">
                            <span>Payme orqali to'lashni tasdiqlash (Instant Onlayn To'lov)</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </form>
                </div>

                <!-- 3. UZCARD / HUMO ONLINE KARTA (SMS VERIFY) TAB -->
                <div x-show="activeTab === 'uzum'" style="display: none;" class="space-y-6">
                    
                    <!-- Virtual Card Representation -->
                    <div class="relative w-full max-w-sm mx-auto h-48 rounded-2xl p-5 bg-gradient-to-tr from-slate-900 via-[#123C2F] to-emerald-800 text-white shadow-xl flex flex-col justify-between overflow-hidden">
                        <div class="absolute -right-10 -bottom-10 w-40 h-40 rounded-full bg-white/10 blur-xl"></div>
                        <div class="flex items-center justify-between">
                            <span class="font-black text-sm tracking-widest text-[#C9F46B]">UZCARD / HUMO</span>
                            <span class="text-xs font-mono opacity-80">ONLAYN TO'LOV</span>
                        </div>
                        <div class="font-mono text-xl tracking-widest font-black text-center my-auto" x-text="cardNumber || '8600 •••• •••• ••••'">
                            8600 •••• •••• ••••
                        </div>
                        <div class="flex items-center justify-between text-xs font-mono">
                            <div>
                                <span class="block text-[9px] uppercase opacity-70">Egasi</span>
                                <span class="font-bold uppercase tracking-wider" x-text="cardHolder || 'AZIZ ALIYEV'">AZIZ ALIYEV</span>
                            </div>
                            <div class="text-right">
                                <span class="block text-[9px] uppercase opacity-70">Muddati</span>
                                <span class="font-bold" x-text="cardExpiry || '12/28'">12/28</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 1: Card Input -->
                    <div x-show="cardStep === 'input'" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1.5">Karta raqami (16 xonali Uzcard / Humo)</label>
                            <input type="text" x-model="cardNumber" @input="formatCard($event)" maxlength="19"
                                   placeholder="8600 0000 0000 0000"
                                   class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] font-mono text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#172326] mb-1.5">Amal qilish muddati</label>
                                <input type="text" x-model="cardExpiry" @input="formatExpiry($event)" maxlength="5" placeholder="MM/YY"
                                       class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] font-mono text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#172326] mb-1.5">Karta egasining ismi</label>
                                <input type="text" x-model="cardHolder" placeholder="ISM FAMILIYA"
                                       class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white uppercase">
                            </div>
                        </div>

                        <button type="button" @click="goToSms()" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-black text-sm hover:brightness-110 transition-all shadow-md">
                            <span>SMS-kod olish va Davom etish →</span>
                        </button>
                    </div>

                    <!-- Step 2: SMS OTP Verification Modal -->
                    <div x-show="cardStep === 'sms'" style="display: none;" class="p-6 rounded-2xl bg-[#FAFBF7] border border-[#C9F46B] space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="font-black text-sm text-[#172326]">SMS-kodni kiriting</h4>
                                <p class="text-xs text-[#64748B]">Kartaga ulangan telefon raqamiga 6 xonali tasdiqlash kodi yuborildi</p>
                            </div>
                            <span class="text-xs font-mono font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg">
                                <span x-text="smsTimer">60</span>s
                            </span>
                        </div>

                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between">
                            <span>📲 Test SMS kodi: <strong>777777</strong></span>
                            <span class="text-[10px] text-emerald-600">Avtomatik to'ldirish</span>
                        </div>

                        <form method="POST" action="{{ route('payment.pay', ['orderNumber' => $order->order_number]) }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="payment_method" value="uzum">
                            
                            <div>
                                <input type="text" name="sms_code" x-model="smsCode" maxlength="6"
                                       placeholder="777777"
                                       class="w-full px-4 py-3 rounded-xl border border-[#123C2F] bg-white font-mono text-center text-xl font-black tracking-widest text-[#172326] focus:outline-none focus:ring-2 focus:ring-[#C9F46B]">
                            </div>

                            <button type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#00A551] text-white font-black text-sm hover:brightness-105 transition-all shadow-md">
                                <span>To'lovni yakunlash ({{ number_format($order->grand_total, 0, '.', ' ') }} UZS)</span>
                            </button>
                        </form>

                        <button type="button" @click="cardStep = 'input'" class="text-xs text-[#64748B] hover:text-[#172326] font-medium block mx-auto underline pt-1">
                            ← Kartani o'zgartirish
                        </button>
                    </div>

                </div>

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
