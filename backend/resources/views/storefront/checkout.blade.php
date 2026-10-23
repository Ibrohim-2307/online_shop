<x-layouts.app title="Buyurtmani rasmiylashtirish">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16"
         x-data="{
             minutes: 14,
             seconds: 59,
             timer: null,
             init() {
                 this.timer = setInterval(() => {
                     if (this.seconds > 0) {
                         this.seconds--;
                     } else if (this.minutes > 0) {
                         this.minutes--;
                         this.seconds = 59;
                     }
                 }, 1000);
             }
         }">

        <!-- Breadcrumbs -->
        <nav class="flex flex-wrap items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <a href="{{ route('cart') }}" class="hover:text-[#123C2F]">Savatcha</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">Buyurtma rasmiylashtirish</span>
        </nav>

        <!-- Top Header & Reservation Expiry Alert -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black text-[#172326] tracking-tight">Buyurtmani rasmiylashtirish</h1>
                <p class="text-xs text-[#64748B] mt-1">Yetkazib berish manzili va qabul qiluvchi ma'lumotlarini kiriting</p>
            </div>

            <!-- Reservation TTL Banner -->
            <div class="flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-semibold">
                <svg class="w-4 h-4 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Zaxira va narx bandligi:</span>
                <span class="font-mono font-bold text-amber-900" x-text="`${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`">15:00</span>
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

        <form method="POST" action="{{ route('checkout.post') }}" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            @csrf
            <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">

            <!-- Left: Shipping Address & Details (7 cols) -->
            <div class="lg:col-span-7 space-y-6">
                
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs space-y-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-[#E8ECE4]">
                        <div class="w-9 h-9 rounded-xl bg-[#C9F46B] text-[#123C2F] flex items-center justify-center font-black">
                            1
                        </div>
                        <h2 class="text-lg font-bold text-[#172326]">Qabul qiluvchi va Manzil</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1.5">F.I.SH. (Ism va Familiya)</label>
                            <input type="text" name="recipient_name" required
                                   value="{{ old('recipient_name', $user?->name) }}"
                                   placeholder="Masalan: Sardor Aliyev"
                                   class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white transition-colors">
                            @error('recipient_name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1.5">Telefon raqami</label>
                            <input type="text" name="phone" required
                                   value="{{ old('phone', $user?->phone ?? '+998') }}"
                                   placeholder="+998 90 123 45 67"
                                   class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white transition-colors">
                            @error('phone')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1.5">Viloyat / Hudud</label>
                            <select name="region" required class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white transition-colors">
                                <option value="Toshkent shahri" selected>Toshkent shahri</option>
                                <option value="Toshkent viloyati">Toshkent viloyati</option>
                                <option value="Samarqand">Samarqand viloyati</option>
                                <option value="Farg'ona">Farg'ona viloyati</option>
                                <option value="Andijon">Andijon viloyati</option>
                                <option value="Namangan">Namangan viloyati</option>
                                <option value="Buxoro">Buxoro viloyati</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1.5">Shahar / Tuman</label>
                            <input type="text" name="city" required
                                   value="{{ old('city', 'Yunusobod tumani') }}"
                                   placeholder="Masalan: Mirzo Ulug'bek"
                                   class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white transition-colors">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#172326] mb-1.5">Aniq manzil (Ko'cha, uy, xonadon)</label>
                        <input type="text" name="address_line" required
                               value="{{ old('address_line', 'Amir Temur shox ko\'chasi, 42-uy') }}"
                               placeholder="Masalan: Mustaqillik shoh ko'chasi 105, 3-xonadon"
                               class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white transition-colors">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#172326] mb-1.5">Kuryer uchun izoh (Ixtiyoriy)</label>
                        <textarea name="notes" rows="2"
                                  placeholder="Masalan: Domofon kodi: 45K, 2-yo'lak"
                                  class="w-full px-4 py-3 rounded-xl border border-[#E8ECE4] bg-[#FAFBF7] text-sm text-[#172326] focus:outline-none focus:border-[#123C2F] focus:bg-white transition-colors">{{ old('notes') }}</textarea>
                    </div>

                </div>

                <!-- Onlayn To'lov Usullari (100% Online Savdo) -->
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs space-y-4" x-data="{ selectedMethod: 'click' }">
                    <div class="flex items-center justify-between pb-4 border-b border-[#E8ECE4]">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-[#C9F46B] text-[#123C2F] flex items-center justify-center font-black">
                                2
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-[#172326]">Onlayn to'lov usuli</h2>
                                <p class="text-xs text-[#64748B]">Xarid summasi to'g'ridan-to'g'ri onlayn qabul qilinadi</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-[#123C2F] bg-[#C9F46B] px-3.5 py-1 rounded-full shadow-xs">
                            ⚡ 100% Onlayn Savdo
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-2">
                        
                        <!-- 1. Click Up (Default, Recommended) -->
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                               :class="selectedMethod === 'click' ? 'border-[#123C2F] bg-[#FAFBF7] shadow-sm ring-2 ring-[#123C2F]/10' : 'border-[#E8ECE4] hover:border-slate-300 bg-white'">
                            <input type="radio" name="payment_method" value="click" x-model="selectedMethod" class="sr-only">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-2xl">🟢</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider"
                                      :class="selectedMethod === 'click' ? 'bg-[#123C2F] text-[#C9F46B]' : 'bg-slate-100 text-slate-600'">
                                    Asosiy
                                </span>
                            </div>
                            <span class="font-bold text-sm text-[#172326] leading-tight">Click Up / Evolution</span>
                            <span class="text-xs text-[#64748B] mt-1">Click ilovasi yoki QR-kod orqali avtomatik onlayn to'lov</span>
                        </label>

                        <!-- 2. Payme -->
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                               :class="selectedMethod === 'payme' ? 'border-[#123C2F] bg-[#FAFBF7] shadow-sm ring-2 ring-[#123C2F]/10' : 'border-[#E8ECE4] hover:border-slate-300 bg-white'">
                            <input type="radio" name="payment_method" value="payme" x-model="selectedMethod" class="sr-only">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-2xl">🔵</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider"
                                      :class="selectedMethod === 'payme' ? 'bg-[#123C2F] text-[#C9F46B]' : 'bg-slate-100 text-slate-600'">
                                    Payme.uz
                                </span>
                            </div>
                            <span class="font-bold text-sm text-[#172326] leading-tight">Payme</span>
                            <span class="text-xs text-[#64748B] mt-1">Payme ilovasi orqali Uzcard va Humo bilan to'lov</span>
                        </label>

                        <!-- 3. Uzcard & Humo (Online Karta) -->
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200"
                               :class="selectedMethod === 'uzum' ? 'border-[#123C2F] bg-[#FAFBF7] shadow-sm ring-2 ring-[#123C2F]/10' : 'border-[#E8ECE4] hover:border-slate-300 bg-white'">
                            <input type="radio" name="payment_method" value="uzum" x-model="selectedMethod" class="sr-only">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-2xl">💳</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider"
                                      :class="selectedMethod === 'uzum' ? 'bg-[#123C2F] text-[#C9F46B]' : 'bg-slate-100 text-slate-600'">
                                    Bank Kartasi
                                </span>
                            </div>
                            <span class="font-bold text-sm text-[#172326] leading-tight">Uzcard / Humo (SMS bilan)</span>
                            <span class="text-xs text-[#64748B] mt-1">Saytda to'g'ridan-to'g'ri karta va SMS orqali to'lash</span>
                        </label>

                    </div>

                    <!-- Security & alternative toggle -->
                    <div class="pt-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-[#64748B] border-t border-[#E8ECE4]">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#123C2F]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            256-bit xavfsiz shifrlangan onlayn to'lov tizimlari
                        </span>
                        <label class="cursor-pointer hover:text-[#172326] flex items-center gap-1.5">
                            <input type="radio" name="payment_method" value="cash_on_delivery" x-model="selectedMethod" class="rounded text-[#123C2F]">
                            <span>Yetkazib berganda to'lash</span>
                        </label>
                    </div>
                </div>

            </div>

            <!-- Right: Order Summary & Multi-Vendor breakdown (5 cols) -->
            <div class="lg:col-span-5 bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-8 shadow-xs sticky top-24 space-y-6">
                <h3 class="font-black text-lg text-[#172326]">Buyurtma tarkibi</h3>

                <!-- Sellers suborders preview -->
                <div class="space-y-4 max-h-80 overflow-y-auto pr-1">
                    @foreach($quote['sellers'] as $seller)
                        <div class="p-4 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-2">
                            <div class="flex items-center justify-between text-xs font-bold text-[#172326]">
                                <span>{{ $seller['seller_name'] }}</span>
                                <span class="text-[#123C2F]">
                                    @if($seller['shipping_cost'] == '0')
                                        Bepul yetkazish
                                    @else
                                        +{{ number_format((float)$seller['shipping_cost'], 0, '.', ' ') }} UZS
                                    @endif
                                </span>
                            </div>

                            <div class="space-y-1 text-xs text-[#64748B]">
                                @foreach($seller['items'] as $it)
                                    <div class="flex items-center justify-between">
                                        <span class="truncate max-w-[200px]">{{ $it['product_name'] }} (×{{ $it['quantity'] }})</span>
                                        <span class="font-semibold text-[#172326]">{{ number_format($it['subtotal'], 0, '.', ' ') }} UZS</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Totals -->
                <div class="pt-4 border-t border-[#E8ECE4] space-y-2.5 text-xs">
                    <div class="flex items-center justify-between text-[#64748B]">
                        <span>Mahsulotlar jami:</span>
                        <span class="font-bold text-[#172326]">{{ number_format($quote['items_total'], 0, '.', ' ') }} UZS</span>
                    </div>
                    <div class="flex items-center justify-between text-[#64748B]">
                        <span>Yetkazib berish (barcha sotuvchilar):</span>
                        <span class="font-bold text-[#172326]">{{ number_format($quote['shipping_total'], 0, '.', ' ') }} UZS</span>
                    </div>
                    <div class="pt-3 border-t border-[#E8ECE4] flex items-center justify-between text-sm">
                        <span class="font-black text-[#172326]">Jami to'lov:</span>
                        <span class="font-black text-2xl text-[#123C2F]">{{ number_format($quote['grand_total'], 0, '.', ' ') }} UZS</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-4 px-6 rounded-2xl bg-[#C9F46B] text-[#123C2F] font-black text-sm hover:brightness-95 transition-all shadow-sm">
                    <span x-text="selectedMethod === 'cash_on_delivery' ? 'Buyurtmani tasdiqlash' : 'Onlayn to\'lovga o\'tish (Click / Payme) →'">
                        Onlayn to'lovga o'tish →
                    </span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>

                <div class="text-[11px] text-[#64748B] text-center space-y-1">
                    <p>🔒 Qayta buyurtma yaratish himoyasi (Idempotent)</p>
                    <p>Ombor balansi real vaqtda atomar tarzda band qilinadi</p>
                </div>
            </div>

        </form>

    </div>
</x-layouts.app>
