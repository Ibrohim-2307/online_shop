<x-layouts.app title="Profil — Boshqaruv va Foydalanuvchi Markazi">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-24"
         x-data="{
             activeTab: '{{ $activeTab ?? 'orders' }}',
             showAddressModal: false,
             showAddProductModal: false,
         }">

        <!-- Breadcrumbs Navigation -->
        <nav class="flex items-center gap-2 text-xs text-[#64748B] mb-5" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="flex items-center gap-1 hover:text-[#123C2F] font-bold">
                <span>🏠</span>
                <span>Bosh sahifa</span>
            </a>
            <span>/</span>
            <span class="text-[#172326] font-extrabold flex items-center gap-1">
                <span>👤</span>
                <span>
                    @if($user->isSeller())
                        Sotuvchi Kabineti & Profil
                    @elseif($user->isCourier())
                        Kuryer Boshqaruvi & Profil
                    @elseif($user->isSuperAdmin() || $user->isAdmin())
                        Administrator Profili
                    @else
                        Xaridor Profili
                    @endif
                </span>
            </span>
        </nav>

        <!-- Demo Mode Notice -->
        @if($isDemo)
            <div class="mb-6 p-4 rounded-3xl bg-amber-500/10 border border-amber-500/20 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-amber-900">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">ℹ️</span>
                    <div>
                        <strong>Demo Xaridor Profili:</strong> Siz ayni damda ko'rish rejimidasiz.
                        Haqiqiy buyurtmalar berish, sotuvchi yoki kuryer sifatida ishlash uchun profilingizga kiring.
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

        <!-- Profile Hero Header -->
        <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#E8ECE4] p-4 sm:p-8 shadow-xs mb-6 sm:mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-6">
                
                <!-- Avatar & Identity Info -->
                <div class="flex items-center gap-3.5 sm:gap-5">
                    <div class="relative shrink-0">
                        <div class="w-14 h-14 sm:w-24 sm:h-24 rounded-2xl sm:rounded-3xl bg-gradient-to-tr from-[#123C2F] to-[#1f5c49] text-[#C9F46B] flex items-center justify-center font-black text-xl sm:text-3xl shadow-sm border-2 sm:border-4 border-white ring-2 ring-[#E8ECE4]">
                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strstr($user->name, ' ') ?: $user->name, 1, 1)) }}
                        </div>
                        <span class="absolute -bottom-1 -right-1 w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-emerald-500 border-2 border-white flex items-center justify-center text-white text-[10px] sm:text-[11px] font-bold" title="Faol hisob">
                            ✓
                        </span>
                    </div>

                    <div class="space-y-0.5 sm:space-y-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2.5">
                            <h1 class="text-lg sm:text-3xl font-black text-[#172326] tracking-tight truncate">
                                {{ $user->name }}
                            </h1>

                            <!-- Distinct Role Badges -->
                            @if($user->isSuperAdmin())
                                <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full bg-rose-100 text-rose-900 border border-rose-300 text-[10px] sm:text-[11px] font-extrabold flex items-center gap-1 whitespace-nowrap">
                                    <span>🛡️</span>
                                    <span>Superadmin</span>
                                </span>
                            @elseif($user->isAdmin())
                                <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full bg-rose-100 text-rose-900 border border-rose-300 text-[10px] sm:text-[11px] font-extrabold flex items-center gap-1 whitespace-nowrap">
                                    <span>🛡️</span>
                                    <span>Administrator</span>
                                </span>
                            @elseif($user->isSeller())
                                <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full bg-emerald-100 text-emerald-900 border border-emerald-300 text-[10px] sm:text-[11px] font-extrabold flex items-center gap-1 whitespace-nowrap">
                                    <span>💼</span>
                                    <span>Sotuvchi</span>
                                </span>
                            @elseif($user->isCourier())
                                <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 rounded-full bg-amber-100 text-amber-900 border border-amber-300 text-[10px] sm:text-[11px] font-extrabold flex items-center gap-1 whitespace-nowrap">
                                    <span>🛵</span>
                                    <span>Kuryer</span>
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] sm:text-[11px] font-bold">
                                    ✓ Xaridor
                                </span>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-[11px] sm:text-xs text-[#64748B]">
                            <span class="flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $user->phone ?? '+998 90 123 45 67' }}
                            </span>
                            <span>•</span>
                            <span class="flex items-center gap-1 truncate max-w-[180px] sm:max-w-none">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="truncate">{{ $user->email }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Shortcuts -->
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-3">
                    <a href="{{ route('home') }}" class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl sm:rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] hover:bg-white text-[#172326] font-bold text-xs flex items-center gap-1.5 transition" title="Bosh sahifa">
                        <span>🏠 Bosh sahifa</span>
                    </a>

                    @if($user->isSuperAdmin() || $user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl sm:rounded-2xl bg-rose-50 text-rose-700 border border-rose-200 hover:bg-rose-100 font-bold text-xs flex items-center gap-1.5 transition">
                            <span>🛡️ Superadmin</span>
                        </a>
                    @endif

                    @if($user->isSeller())
                        <button type="button" @click="activeTab = 'seller_products'; showAddProductModal = true" class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl sm:rounded-2xl bg-[#C9F46B] text-[#123C2F] font-bold text-xs flex items-center gap-1.5 shadow-xs hover:bg-[#b8e855] transition">
                            <span>➕ Yangi Mahsulot</span>
                        </button>
                    @endif

                    @if($user->isCourier())
                        <button type="button" @click="activeTab = 'courier_shipments'" class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl sm:rounded-2xl bg-amber-500 text-white font-bold text-xs flex items-center gap-1.5 shadow-xs hover:bg-amber-600 transition">
                            <span>🛵 Vazifalar</span>
                        </button>
                    @endif

                    <a href="{{ route('tracking') }}" class="px-3 py-2 sm:px-4 sm:py-2.5 rounded-xl sm:rounded-2xl bg-[#123C2F] text-[#C9F46B] hover:bg-[#0d2a21] font-bold text-xs flex items-center gap-1.5 shadow-sm transition">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#C9F46B] animate-ping"></span>
                        <span>📍 Kuzatuv</span>
                    </a>

                    @auth
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-2 sm:p-2.5 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-red-50 text-[#64748B] hover:text-red-600 border border-[#E8ECE4] text-xs font-semibold transition" title="Tizimdan chiqish">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    @endauth
                </div>

            </div>

            <!-- Role-Specific KPI Stats Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-4 mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-[#E8ECE4]">
                @if($user->isSeller())
                    <!-- Seller KPI Cards -->
                    <button @click="activeTab = 'seller_products'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Do'kon Mahsulotlari</span>
                        <div class="text-base sm:text-2xl font-black text-[#172326] group-hover:text-[#123C2F]">{{ $sellerProducts->count() }} <span class="text-[10px] sm:text-xs font-medium text-[#64748B]">ta</span></div>
                        <span class="text-[9px] sm:text-[10px] text-emerald-700 font-semibold mt-0.5 block truncate">+ Tovar qo'shish →</span>
                    </button>

                    <button @click="activeTab = 'seller_orders'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Do'konga Buyurtmalar</span>
                        <div class="text-base sm:text-2xl font-black text-emerald-700">{{ $sellerSuborders->count() }} <span class="text-[10px] sm:text-xs font-medium text-[#64748B]">ta</span></div>
                        <span class="text-[9px] sm:text-[10px] text-[#64748B] mt-0.5 block truncate">Buyurtmalar ro'yxati →</span>
                    </button>

                    <button @click="activeTab = 'seller_finance'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Do'kon Sof Balansi</span>
                        <div class="text-sm sm:text-xl font-black text-[#123C2F] truncate">{{ number_format($sellerBalance, 0, '.', ' ') }} <span class="text-[9px] sm:text-xs text-[#64748B] font-bold">UZS</span></div>
                        <span class="text-[9px] sm:text-[10px] text-emerald-600 font-semibold mt-0.5 block truncate">Daromad & hisoblar →</span>
                    </button>

                    <button @click="activeTab = 'orders'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Mening Xaridlarim</span>
                        <div class="text-base sm:text-2xl font-black text-[#172326] group-hover:text-[#123C2F]">{{ $orders->count() }} <span class="text-[10px] sm:text-xs font-medium text-[#64748B]">ta</span></div>
                        <span class="text-[9px] sm:text-[10px] text-[#64748B] mt-0.5 block truncate">Shaxsiy xaridlar →</span>
                    </button>
                @elseif($user->isCourier())
                    <!-- Courier KPI Cards -->
                    <button @click="activeTab = 'courier_shipments'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Faol Yetkazishlar</span>
                        <div class="text-base sm:text-2xl font-black text-amber-700">{{ $courierActiveCount }} <span class="text-[10px] sm:text-xs font-medium text-[#64748B]">ta</span></div>
                        <span class="text-[9px] sm:text-[10px] text-amber-600 font-semibold mt-0.5 block truncate">Yo'ldagi tovarlar →</span>
                    </button>

                    <button @click="activeTab = 'courier_shipments'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Yetkazib Berildi</span>
                        <div class="text-base sm:text-2xl font-black text-emerald-700">{{ $courierDeliveredCount }} <span class="text-[10px] sm:text-xs font-medium text-[#64748B]">ta</span></div>
                        <span class="text-[9px] sm:text-[10px] text-emerald-600 font-semibold mt-0.5 block truncate">Muvaffaqiyatli →</span>
                    </button>

                    <button @click="activeTab = 'courier_shipments'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Yig'iladigan Naqd</span>
                        <div class="text-sm sm:text-xl font-black text-amber-900 truncate">{{ number_format($courierTotalCashToCollect, 0, '.', ' ') }} <span class="text-[9px] sm:text-xs text-[#64748B] font-bold">UZS</span></div>
                        <span class="text-[9px] sm:text-[10px] text-[#64748B] mt-0.5 block truncate">Xaridordan olinadi →</span>
                    </button>

                    <button @click="activeTab = 'orders'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Mening Xaridlarim</span>
                        <div class="text-base sm:text-2xl font-black text-[#172326] group-hover:text-[#123C2F]">{{ $orders->count() }} <span class="text-[10px] sm:text-xs font-medium text-[#64748B]">ta</span></div>
                        <span class="text-[9px] sm:text-[10px] text-[#64748B] mt-0.5 block truncate">Shaxsiy xaridlar →</span>
                    </button>
                @else
                    <!-- Customer Standard KPI Cards -->
                    <button @click="activeTab = 'orders'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Buyurtmalarim</span>
                        <div class="text-base sm:text-2xl font-black text-[#172326] group-hover:text-[#123C2F]">{{ $orders->count() }} <span class="text-[10px] sm:text-xs font-medium text-[#64748B]">ta</span></div>
                        <span class="text-[9px] sm:text-[10px] text-[#64748B] mt-0.5 block truncate">Xaridlar tarixi →</span>
                    </button>

                    <button @click="activeTab = 'orders'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Yo'ldagi Buyurtmalar</span>
                        <div class="text-base sm:text-2xl font-black text-emerald-700">{{ $activeOrdersCount }} <span class="text-[10px] sm:text-xs font-medium text-[#64748B]">ta</span></div>
                        <span class="text-[9px] sm:text-[10px] text-emerald-600 font-semibold mt-0.5 block truncate">Kuryer harakatda →</span>
                    </button>

                    <button @click="activeTab = 'wishlist'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Sevimlilar</span>
                        <div class="text-base sm:text-2xl font-black text-[#172326] group-hover:text-[#123C2F]">{{ $savedProducts->count() }} <span class="text-[10px] sm:text-xs font-medium text-[#64748B]">ta</span></div>
                        <span class="text-[9px] sm:text-[10px] text-[#64748B] mt-0.5 block truncate">Saqlangan tovarlar →</span>
                    </button>

                    <button @click="activeTab = 'addresses'" class="p-2.5 sm:p-4 rounded-xl sm:rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] text-left transition hover:shadow-xs group">
                        <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#64748B] block mb-0.5 sm:mb-1 truncate">Manzillar</span>
                        <div class="text-base sm:text-2xl font-black text-[#172326] group-hover:text-[#123C2F]">{{ $addresses->count() }} <span class="text-[10px] sm:text-xs font-medium text-[#64748B]">ta</span></div>
                        <span class="text-[9px] sm:text-[10px] text-[#64748B] mt-0.5 block truncate">Yetkazish nuqtalari →</span>
                    </button>
                @endif
            </div>
        </div>

        <!-- Mobile Horizontal Tab Switcher Ribbon (Direct 1-tap tab switching on phone) -->
        <div class="lg:hidden mb-5 overflow-x-auto no-scrollbar flex items-center gap-1.5 text-xs">
            @if($user->isSeller())
                <button @click="activeTab = 'seller_products'" 
                        :class="activeTab === 'seller_products' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]'" 
                        class="px-3 py-2 rounded-xl font-bold whitespace-nowrap border shadow-2xs flex items-center gap-1.5">
                    <span>📦</span>
                    <span>Mahsulotlar ({{ $sellerProducts->count() }})</span>
                </button>
                <button @click="activeTab = 'seller_orders'" 
                        :class="activeTab === 'seller_orders' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]'" 
                        class="px-3 py-2 rounded-xl font-bold whitespace-nowrap border shadow-2xs flex items-center gap-1.5">
                    <span>🛒</span>
                    <span>Buyurtmalar ({{ $sellerSuborders->count() }})</span>
                </button>
                <button @click="activeTab = 'seller_shipping'" 
                        :class="activeTab === 'seller_shipping' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]'" 
                        class="px-3 py-2 rounded-xl font-bold whitespace-nowrap border shadow-2xs flex items-center gap-1.5">
                    <span>🚚</span>
                    <span>Kuryerga</span>
                </button>
                <button @click="activeTab = 'seller_finance'" 
                        :class="activeTab === 'seller_finance' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]'" 
                        class="px-3 py-2 rounded-xl font-bold whitespace-nowrap border shadow-2xs flex items-center gap-1.5">
                    <span>💰</span>
                    <span>Moliya</span>
                </button>
            @endif

            @if($user->isCourier())
                <button @click="activeTab = 'courier_shipments'" 
                        :class="activeTab === 'courier_shipments' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]'" 
                        class="px-3 py-2 rounded-xl font-bold whitespace-nowrap border shadow-2xs flex items-center gap-1.5">
                    <span>🛵</span>
                    <span>Jo'natmalar ({{ $courierActiveCount }})</span>
                </button>
            @endif

            <button @click="activeTab = 'orders'" 
                    :class="activeTab === 'orders' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]'" 
                    class="px-3 py-2 rounded-xl font-bold whitespace-nowrap border shadow-2xs flex items-center gap-1.5">
                <span>📦</span>
                <span>Xaridlarim ({{ $orders->count() }})</span>
            </button>
            <button @click="activeTab = 'wishlist'" 
                    :class="activeTab === 'wishlist' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]'" 
                    class="px-3 py-2 rounded-xl font-bold whitespace-nowrap border shadow-2xs flex items-center gap-1.5">
                <span>❤️</span>
                <span>Sevimlilar ({{ $savedProducts->count() }})</span>
            </button>
            <button @click="activeTab = 'addresses'" 
                    :class="activeTab === 'addresses' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]'" 
                    class="px-3 py-2 rounded-xl font-bold whitespace-nowrap border shadow-2xs flex items-center gap-1.5">
                <span>📍</span>
                <span>Manzillar ({{ $addresses->count() }})</span>
            </button>
            <button @click="activeTab = 'settings'" 
                    :class="activeTab === 'settings' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]'" 
                    class="px-3 py-2 rounded-xl font-bold whitespace-nowrap border shadow-2xs flex items-center gap-1.5">
                <span>⚙️</span>
                <span>Sozlamalar</span>
            </button>
        </div>

        <!-- Main Workspace: Tabs and Content Panels -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
            
            <!-- Left Sidebar Navigation Tabs (3 cols on Desktop, hidden on mobile in favor of horizontal pills) -->
            <div class="hidden lg:block lg:col-span-3 space-y-4">
                
                {{-- 1. SOTUVCHI BO'LIMI (If user is seller) --}}
                @if($user->isSeller())
                    <div class="bg-white rounded-3xl border-2 border-emerald-500/20 p-3 shadow-xs space-y-1">
                        <div class="px-3 py-2 text-[10px] font-black uppercase tracking-wider text-emerald-800 bg-emerald-50 rounded-xl mb-1 flex items-center justify-between">
                            <span>💼 SOTUVCHI BO'LIMI</span>
                            <span>Do'kon</span>
                        </div>

                        <button @click="activeTab = 'seller_products'" 
                                :class="activeTab === 'seller_products' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition">
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm">📦</span>
                                <span>Mahsulotlarim</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'seller_products' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#FAFBF7] text-[#64748B]'">
                                {{ $sellerProducts->count() }}
                            </span>
                        </button>

                        <button @click="activeTab = 'seller_orders'" 
                                :class="activeTab === 'seller_orders' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition">
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm">🛒</span>
                                <span>Kelgan Buyurtmalar</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'seller_orders' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#FAFBF7] text-[#64748B]'">
                                {{ $sellerSuborders->count() }}
                            </span>
                        </button>

                        <button @click="activeTab = 'seller_shipping'" 
                                :class="activeTab === 'seller_shipping' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition">
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm">🚚</span>
                                <span>Kuryerga Topshirish</span>
                            </div>
                            <span class="text-[10px] text-slate-400">Logistika</span>
                        </button>

                        <button @click="activeTab = 'seller_finance'" 
                                :class="activeTab === 'seller_finance' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition">
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm">💰</span>
                                <span>Moliya & Balans</span>
                            </div>
                            <span class="text-[10px] font-bold text-emerald-600">{{ number_format($sellerBalance, 0, '.', ' ') }}</span>
                        </button>
                    </div>
                @endif

                {{-- 2. KURYER BO'LIMI (If user is courier) --}}
                @if($user->isCourier())
                    <div class="bg-white rounded-3xl border-2 border-amber-500/20 p-3 shadow-xs space-y-1">
                        <div class="px-3 py-2 text-[10px] font-black uppercase tracking-wider text-amber-900 bg-amber-50 rounded-xl mb-1 flex items-center justify-between">
                            <span>🛵 KURYER BO'LIMI</span>
                            <span>Yetkazish</span>
                        </div>

                        <button @click="activeTab = 'courier_shipments'" 
                                :class="activeTab === 'courier_shipments' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                                class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition">
                            <div class="flex items-center gap-2.5">
                                <span class="text-sm">🛵</span>
                                <span>Biriktirilgan Jo'natmalar</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'courier_shipments' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#FAFBF7] text-[#64748B]'">
                                {{ $courierActiveCount }}
                            </span>
                        </button>
                    </div>
                @endif

                {{-- 3. SUPERADMIN LINK (If user is superadmin or admin) --}}
                @if($user->isSuperAdmin() || $user->isAdmin())
                    <div class="bg-rose-50 rounded-3xl border border-rose-200 p-4 shadow-xs space-y-2">
                        <div class="text-[10px] font-black uppercase tracking-wider text-rose-800 flex items-center gap-1.5">
                            <span>🛡️</span>
                            <span>SUPERADMIN HUQUQI</span>
                        </div>
                        <p class="text-[11px] text-rose-700 leading-snug">
                            Barcha foydalanuvchilar rollari, do'konlar, kuryerlar va tizim sozlamalarini boshqarish.
                        </p>
                        <a href="{{ route('admin.dashboard') }}" class="w-full py-2 px-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 transition shadow-xs">
                            <span>Boshqaruv Paneliga O'tish →</span>
                        </a>
                    </div>
                @endif

                <!-- 4. SHAXSIY / XARIDOR BO'LIMI (For all users) -->
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-3 shadow-xs space-y-1">
                    <div class="px-3 py-1.5 text-[10px] font-black uppercase tracking-wider text-[#64748B] mb-1">
                        👤 SHAXSIY BO'LIM
                    </div>
                    
                    <button @click="activeTab = 'orders'" 
                            :class="activeTab === 'orders' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition">
                        <div class="flex items-center gap-2.5">
                            <span class="text-sm">📦</span>
                            <span>Mening Buyurtmalarim</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'orders' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#FAFBF7] text-[#64748B]'">
                            {{ $orders->count() }}
                        </span>
                    </button>

                    <button @click="activeTab = 'wishlist'" 
                            :class="activeTab === 'wishlist' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition">
                        <div class="flex items-center gap-2.5">
                            <span class="text-sm">❤️</span>
                            <span>Sevimlilar & Saqlangan</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'wishlist' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#FAFBF7] text-[#64748B]'">
                            {{ $savedProducts->count() }}
                        </span>
                    </button>

                    <button @click="activeTab = 'addresses'" 
                            :class="activeTab === 'addresses' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition">
                        <div class="flex items-center gap-2.5">
                            <span class="text-sm">📍</span>
                            <span>Yetkazish Manzillari</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'addresses' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#FAFBF7] text-[#64748B]'">
                            {{ $addresses->count() }}
                        </span>
                    </button>

                    <button @click="activeTab = 'settings'" 
                            :class="activeTab === 'settings' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-[#64748B] hover:text-[#172326] hover:bg-[#FAFBF7]'"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs font-bold transition">
                        <div class="flex items-center gap-2.5">
                            <span class="text-sm">⚙️</span>
                            <span>Hisob Sozlamalari</span>
                        </div>
                        <span class="text-[10px] font-semibold">Parol & Profil</span>
                    </button>
                </div>

                <!-- Customer Support Widget -->
                <div class="bg-gradient-to-br from-[#123C2F] to-[#1f5c49] text-white rounded-3xl p-5 shadow-xs space-y-3">
                    <span class="text-xs font-bold text-[#C9F46B] uppercase tracking-wider block">Yordam & Operator</span>
                    <p class="text-xs text-white/80 leading-relaxed">
                        Savollaringiz bormi? Do'kon, kuryer yoki buyurtma bo'yicha yordam berishga tayyormiz.
                    </p>
                    <a href="tel:+998712000000" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-[#123C2F] font-bold text-xs hover:bg-[#C9F46B] transition shadow-xs">
                        <span>📞 +998 (71) 200-00-00</span>
                    </a>
                </div>
            </div>

            <!-- Right Content Panels (9 cols) -->
            <div class="lg:col-span-9 space-y-6 min-w-0 max-w-full overflow-hidden">
                
                {{-- =========================================================================
                     SELLER PANELS (Visible to Sellers)
                     ========================================================================= --}}
                @if($user->isSeller())
                    <!-- TAB: SELLER PRODUCTS -->
                    <div x-show="activeTab === 'seller_products'" class="space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-black text-[#172326]">Mening Do'konim Mahsulotlari</h2>
                                <p class="text-xs text-[#64748B]">Do'koningizdagi tovarlar ro'yxati, narxlar va qoldiqlarni to'g'ridan-to'g'ri boshqaring</p>
                            </div>
                            <button @click="showAddProductModal = true" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-[#123C2F] text-[#C9F46B] hover:bg-[#0d2a21] font-bold text-xs transition shadow-sm">
                                <span>➕ Yangi Mahsulot Qo'shish</span>
                            </button>
                        </div>

                        <!-- Products Table -->
                        <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs">
                            <div class="overflow-x-auto w-full">
                                <table class="w-full text-left text-xs text-slate-600">
                                    <thead class="bg-[#FAFBF7] text-[#172326] font-bold uppercase tracking-wider text-[11px] border-b border-[#E8ECE4]">
                                        <tr>
                                            <th class="p-3.5">Mahsulot</th>
                                            <th class="p-3.5">Toifa & Birlik</th>
                                            <th class="p-3.5">Narx</th>
                                            <th class="p-3.5">Qoldiq</th>
                                            <th class="p-3.5">Holati</th>
                                            <th class="p-3.5 text-right">Amal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#E8ECE4]">
                                        @forelse($sellerProducts as $prod)
                                            @php
                                                $firstTier = $prod->variants->first()?->priceTiers->first();
                                                $totalStock = $prod->variants->flatMap->inventoryBalances->sum('quantity');
                                            @endphp
                                            <tr class="hover:bg-[#FAFBF7]/60">
                                                <td class="p-3.5">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-12 h-12 rounded-xl bg-slate-100 overflow-hidden border border-[#E8ECE4] flex-shrink-0 flex items-center justify-center">
                                                            @if($prod->image_url)
                                                                <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" class="w-full h-full object-cover">
                                                            @else
                                                                <span class="text-xl">📦</span>
                                                            @endif
                                                        </div>
                                                        <div>
                                                            <a href="{{ route('product.show', $prod->slug) }}" target="_blank" class="font-bold text-[#172326] hover:text-[#123C2F] hover:underline">
                                                                {{ $prod->name }}
                                                            </a>
                                                            <span class="block text-[10px] text-[#64748B] font-mono">SKU: {{ $prod->variants->first()?->sku ?? '—' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="p-3.5">
                                                    <span class="font-semibold text-[#172326]">{{ $prod->category->name ?? '—' }}</span>
                                                    <span class="block text-[10px] text-[#64748B]">Birlik: {{ $prod->unit }}</span>
                                                </td>
                                                <td class="p-3.5 font-black text-[#123C2F]">
                                                    {{ $firstTier ? number_format($firstTier->unit_price, 0, '.', ' ') . ' UZS' : '—' }}
                                                </td>
                                                <td class="p-3.5">
                                                    <span class="font-bold text-[#172326]">{{ number_format($totalStock) }} {{ $prod->unit }}</span>
                                                    <span class="block text-[10px] text-[#64748B]">MOQ: {{ $prod->min_order_quantity }}</span>
                                                </td>
                                                <td class="p-3.5">
                                                    @if($prod->is_active)
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                            ● Faol (Sotuvda)
                                                        </span>
                                                    @else
                                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">
                                                            ○ Yashirilgan
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="p-3.5 text-right space-x-1">
                                                    <form action="{{ route('profile.seller.products.status', $prod->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <input type="hidden" name="toggle_active" value="1">
                                                        <button type="submit" class="px-2.5 py-1 rounded-lg border border-[#E8ECE4] hover:bg-[#FAFBF7] text-[#172326] font-bold text-[11px] transition">
                                                            {{ $prod->is_active ? 'Yashirish' : 'Faollashtirish' }}
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="p-8 text-center text-[#64748B]">
                                                    Do'koningizda hozircha mahsulotlar yo'q. Birinchi mahsulotingizni qo'shing!
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TAB: SELLER ORDERS -->
                    <div x-show="activeTab === 'seller_orders'" class="space-y-6">
                        <div>
                            <h2 class="text-xl font-black text-[#172326]">Do'konga Tushgan Buyurtmalar</h2>
                            <p class="text-xs text-[#64748B]">Xaridorlar tomonidan do'koningizdan sotib olingan tovarlar va ularning yetkazilish bosqichlari</p>
                        </div>

                        @forelse($sellerSuborders as $sub)
                            <div class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs hover:border-[#123C2F]/30 transition-all p-5 space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#E8ECE4]">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-black text-sm text-[#172326]">#{{ $sub->suborder_number }}</span>
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
                                                {{ $sub->status }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-[#64748B] mt-0.5">Sana: {{ $sub->created_at->format('Y-m-d H:i') }}</p>
                                    </div>

                                    <div class="flex items-center gap-4">
                                        <div class="text-right">
                                            <span class="text-[10px] text-[#64748B] uppercase font-bold block">Buyurtma Summasi:</span>
                                            <span class="text-base font-black text-[#123C2F]">{{ number_format($sub->total_amount, 0, '.', ' ') }} UZS</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Buyer contact & Dropoff info -->
                                <div class="p-3.5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                    <div>
                                        <span class="text-[10px] font-bold uppercase text-[#64748B] block">Xaridor Ma'lumotlari:</span>
                                        <div class="font-bold text-[#172326] flex items-center gap-2 mt-0.5">
                                            <span>{{ $sub->order->user->name ?? 'Xaridor' }}</span>
                                            <span>•</span>
                                            <a href="tel:{{ $sub->order->user->phone ?? '' }}" class="text-[#123C2F] hover:underline flex items-center gap-1">
                                                <span>📞</span> {{ $sub->order->user->phone ?? 'Telefon raqam yo\'q' }}
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Status Update Actions for Seller -->
                                    <form action="{{ route('profile.seller.orders.status', $sub->id) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        <select name="status" class="h-9 px-3 rounded-xl bg-white border border-[#E8ECE4] text-xs font-bold text-[#172326] outline-none">
                                            <option value="confirmed" {{ $sub->status === 'confirmed' ? 'selected' : '' }}>1. Tasdiqlash (Tayyorlanmoqda)</option>
                                            <option value="packed" {{ $sub->status === 'packed' ? 'selected' : '' }}>2. Qadoqlash (Qadoqlandi)</option>
                                            <option value="ready_for_shipping" {{ $sub->status === 'ready_for_shipping' ? 'selected' : '' }}>3. Kuryerga berishga tayyor</option>
                                        </select>

                                        <select name="courier_id" class="h-9 px-3 rounded-xl bg-white border border-[#E8ECE4] text-xs font-medium text-[#172326] outline-none">
                                            <option value="">-- Kuryer tanlang --</option>
                                            @foreach($couriers as $c)
                                                <option value="{{ $c->id }}">🛵 {{ $c->name }} ({{ $c->phone ?? $c->email }})</option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="h-9 px-4 rounded-xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs hover:bg-[#0d2a21] transition">
                                            Saqlash
                                        </button>
                                    </form>
                                </div>

                                <!-- Ordered Items -->
                                <div class="space-y-2">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#64748B] block">Buyurtma tarkibi:</span>
                                    @foreach($sub->items as $it)
                                        <div class="flex items-center justify-between text-xs py-1 border-b border-[#E8ECE4]/40">
                                            <div class="flex items-center gap-2">
                                                <span>📦</span>
                                                <span class="font-bold text-[#172326]">{{ $it->product_name }}</span>
                                                <span class="text-[#64748B]">({{ $it->quantity }} {{ $it->unit }} × {{ number_format($it->unit_price, 0, '.', ' ') }} UZS)</span>
                                            </div>
                                            <span class="font-black text-[#172326]">{{ number_format($it->subtotal, 0, '.', ' ') }} UZS</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="bg-white rounded-3xl border border-[#E8ECE4] p-12 text-center shadow-xs">
                                <div class="text-3xl mb-2">🛒</div>
                                <h3 class="font-extrabold text-base text-[#172326]">Hozircha buyurtmalar tushmagan</h3>
                                <p class="text-xs text-[#64748B]">Do'koningizga yangi buyurtmalar tushganda shu yerda paydo bo'ladi.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- TAB: SELLER SHIPPING -->
                    <div x-show="activeTab === 'seller_shipping'" class="space-y-6">
                        <div>
                            <h2 class="text-xl font-black text-[#172326]">Kuryerga Topshirish va Logistika</h2>
                            <p class="text-xs text-[#64748B]">Biriktirilgan kuryerlar bilan aloqa, jo'natmalar holati va topshirish jarayoni</p>
                        </div>

                        <div class="space-y-4">
                            @php
                                $shippingSuborders = $sellerSuborders->filter(fn($s) => $s->shipments->isNotEmpty());
                            @endphp

                            @forelse($shippingSuborders as $sub)
                                @php
                                    $shipment = $sub->shipments->first();
                                    $courier = $shipment?->courier;
                                @endphp
                                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-5 shadow-xs space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#E8ECE4]">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-bold text-xs text-[#172326]">Tracking: {{ $shipment->tracking_number }}</span>
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-blue-50 text-blue-800 border border-blue-200">
                                                    {{ $shipment->status }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-[#64748B] mt-0.5">Buyurtma #{{ $sub->suborder_number }}</p>
                                        </div>

                                        <a href="{{ route('tracking', ['trackingNumber' => $shipment->tracking_number]) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100 font-bold text-xs flex items-center gap-1.5 transition">
                                            <span>📍</span>
                                            <span>Jonli Xaritada Kuzatish</span>
                                        </a>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                        <div class="p-3.5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-1">
                                            <span class="text-[10px] font-bold uppercase text-[#64748B] block">Biriktirilgan Kuryer:</span>
                                            @if($courier)
                                                <p class="font-bold text-[#172326] text-sm">{{ $courier->name }}</p>
                                                <a href="tel:{{ $courier->phone ?? '' }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 transition mt-1">
                                                    <span>📞 Kuryerga qo'ng'iroq:</span>
                                                    <span>{{ $courier->phone ?? '+998 90 000 00 00' }}</span>
                                                </a>
                                            @else
                                                <p class="text-amber-800 font-bold">Kuryer hali biriktirilmagan</p>
                                                <span class="text-[11px] text-[#64748B]">Admin yoki do'kon tomonidan biriktirilishi kutilmoqda.</span>
                                            @endif
                                        </div>

                                        <div class="p-3.5 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] space-y-1">
                                            <span class="text-[10px] font-bold uppercase text-[#64748B] block">Yetkazish Manzili:</span>
                                            <p class="font-bold text-[#172326]">{{ $sub->order->user->name ?? 'Xaridor' }}</p>
                                            <p class="text-[#64748B]">{{ is_array($shipment->delivery_address) ? ($shipment->delivery_address['address_line'] ?? 'Toshkent sh.') : 'Toshkent sh.' }}</p>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-10 text-center">
                                    <p class="text-xs text-[#64748B]">Hozircha kuryerga yuborilgan jo'natmalar yo'q.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB: SELLER FINANCE -->
                    <div x-show="activeTab === 'seller_finance'" class="space-y-6">
                        <div>
                            <h2 class="text-xl font-black text-[#172326]">Do'kon Moliyasi & Hisob-kitoblar</h2>
                            <p class="text-xs text-[#64748B]">Savdolardan tushgan mablag'lar, platforma komissiyasi va to'lovlar tarixi</p>
                        </div>

                        <!-- Finance summary widgets -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-5 rounded-3xl bg-white border border-[#E8ECE4] shadow-xs">
                                <span class="text-[11px] font-bold uppercase text-[#64748B] block mb-1">Do'kon Sof Balansi</span>
                                <div class="text-2xl font-black text-[#123C2F]">{{ number_format($sellerBalance, 0, '.', ' ') }} <span class="text-xs text-[#64748B] font-bold">UZS</span></div>
                                <span class="text-[10px] text-emerald-600 block mt-1">Yechib olish uchun tayyor</span>
                            </div>

                            <div class="p-5 rounded-3xl bg-white border border-[#E8ECE4] shadow-xs">
                                <span class="text-[11px] font-bold uppercase text-[#64748B] block mb-1">Komissiya Stavkasi</span>
                                <div class="text-2xl font-black text-slate-800">{{ $seller->commission_rate ?? 5.0 }}%</div>
                                <span class="text-[10px] text-[#64748B] block mt-1">Platforma xizmat haqi</span>
                            </div>

                            <div class="p-5 rounded-3xl bg-white border border-[#E8ECE4] shadow-xs">
                                <span class="text-[11px] font-bold uppercase text-[#64748B] block mb-1">Muvaffaqiyatli Savdolar</span>
                                <div class="text-2xl font-black text-slate-800">{{ $sellerSuborders->count() }} <span class="text-xs text-[#64748B] font-bold">ta</span></div>
                                <span class="text-[10px] text-emerald-600 block mt-1">Umumiy buyurtmalar</span>
                            </div>
                        </div>

                        <!-- Ledger Transactions Table -->
                        <div class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs">
                            <div class="p-4 bg-[#FAFBF7] border-b border-[#E8ECE4]">
                                <h3 class="font-bold text-xs text-[#172326]">Oxirgi Moliya Operatsiyalari (Ledger)</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-slate-600">
                                    <thead class="bg-white text-[#172326] font-bold uppercase tracking-wider text-[10px] border-b border-[#E8ECE4]">
                                        <tr>
                                            <th class="p-3">Sana</th>
                                            <th class="p-3">Turi</th>
                                            <th class="p-3">Summa</th>
                                            <th class="p-3">Izoh</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#E8ECE4]">
                                        @forelse($sellerLedger as $item)
                                            <tr class="hover:bg-[#FAFBF7]/60">
                                                <td class="p-3 text-slate-400 font-mono">{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                                <td class="p-3 font-bold uppercase {{ $item->type === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}">
                                                    {{ $item->type === 'credit' ? '+ Kirim (Kredit)' : '- Chiqim (Debet)' }}
                                                </td>
                                                <td class="p-3 font-black text-slate-900">{{ number_format($item->amount, 0, '.', ' ') }} UZS</td>
                                                <td class="p-3 text-slate-600">{{ $item->description ?? 'Savdo tushumi' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="p-6 text-center text-slate-400">Hozircha moliya yozuvlari mavjud emas.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- =========================================================================
                     COURIER PANELS (Visible to Couriers)
                     ========================================================================= --}}
                @if($user->isCourier())
                    <!-- TAB: COURIER SHIPMENTS -->
                    <div x-show="activeTab === 'courier_shipments'" class="space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-black text-[#172326]">Menga Biriktirilgan Yetkazishlar</h2>
                                <p class="text-xs text-[#64748B]">Do'kondan tovarlarni qabul qilish, manzilga yetkazish va xaridor bilan hisob-kitob qilish</p>
                            </div>
                            <a href="{{ route('tracking') }}" target="_blank" class="px-4 py-2.5 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs flex items-center gap-1.5 shadow-xs hover:bg-[#0d2a21] transition">
                                <span>📍 Jonli Marshrut Xaritasi</span>
                            </a>
                        </div>

                        <div class="space-y-5">
                            @forelse($courierShipments as $sh)
                                @php
                                    $isCOD = $sh->suborder?->order?->latest_payment?->payment_method === 'cash_on_delivery';
                                    $codAmount = $sh->suborder?->total_amount ?? 0;
                                @endphp

                                <div class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs hover:border-[#123C2F]/30 transition-all p-5 space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#E8ECE4]">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-black text-sm text-[#172326]">{{ $sh->tracking_number }}</span>
                                                @php
                                                    $courierBadgeColors = [
                                                        'assigned' => 'bg-blue-100 text-blue-900',
                                                        'picked_up' => 'bg-indigo-100 text-indigo-900',
                                                        'in_transit' => 'bg-amber-100 text-amber-900',
                                                        'delivered' => 'bg-emerald-100 text-emerald-900',
                                                        'failed' => 'bg-rose-100 text-rose-900',
                                                    ];
                                                @endphp
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $courierBadgeColors[$sh->status] ?? 'bg-slate-100 text-slate-800' }}">
                                                    {{ $sh->status }}
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-[#64748B] mt-0.5">Buyurtma #{{ $sh->suborder?->suborder_number ?? '—' }}</p>
                                        </div>

                                        <!-- COD or Online Paid Badge -->
                                        @if($isCOD)
                                            <div class="px-4 py-2 rounded-2xl bg-amber-50 border-2 border-amber-300 text-amber-950 font-black text-xs flex items-center gap-2">
                                                <span class="text-base">💵</span>
                                                <div>
                                                    <span class="block text-[10px] uppercase font-bold text-amber-800">Naqd Pul (COD) Undiriladi:</span>
                                                    <span>{{ number_format($codAmount, 0, '.', ' ') }} UZS</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="px-4 py-2 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 font-bold text-xs flex items-center gap-2">
                                                <span class="text-base">💳</span>
                                                <div>
                                                    <span class="block text-[10px] uppercase font-bold text-emerald-700">Online To'langan</span>
                                                    <span>Xaridordan pul olinmaydi</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- 2 Destination Cards (Pickup Store vs Delivery Customer) -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                                        <!-- Step 1: Pickup Location -->
                                        <div class="p-4 rounded-2xl bg-emerald-50/40 border border-emerald-200/60 space-y-2">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-900 flex items-center gap-1">
                                                <span>🏬</span> 1. SOTUVCHIDAN OLISH (PICKUP)
                                            </span>
                                            <div>
                                                <div class="font-extrabold text-[#172326] text-sm">{{ $sh->suborder?->seller?->name ?? 'Do\'kon' }}</div>
                                                <div class="text-[#64748B] text-[11px]">{{ $sh->pickup_address['address'] ?? ($sh->suborder?->seller?->address ?? 'Toshkent shahri') }}</div>
                                            </div>
                                            <div class="pt-1">
                                                <a href="tel:{{ $sh->pickup_address['phone'] ?? ($sh->suborder?->seller?->phone ?? '') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-[#123C2F] border border-emerald-300 font-bold text-xs hover:bg-[#C9F46B] transition shadow-2xs">
                                                    <span>📞 Do'konga qo'ng'iroq:</span>
                                                    <span>{{ $sh->pickup_address['phone'] ?? ($sh->suborder?->seller?->phone ?? 'Mavjud emas') }}</span>
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Step 2: Dropoff Location -->
                                        <div class="p-4 rounded-2xl bg-blue-50/40 border border-blue-200/60 space-y-2">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-blue-900 flex items-center gap-1">
                                                <span>🏠</span> 2. XARIDORGA TOPSHIRISH (DROPOFF)
                                            </span>
                                            <div>
                                                <div class="font-extrabold text-[#172326] text-sm">{{ $sh->suborder?->order?->user?->name ?? 'Xaridor' }}</div>
                                                <div class="text-[#64748B] text-[11px]">{{ is_array($sh->delivery_address) ? ($sh->delivery_address['address_line'] ?? 'Toshkent sh.') : 'Toshkent sh.' }}</div>
                                            </div>
                                            <div class="pt-1">
                                                <a href="tel:{{ $sh->suborder?->order?->user?->phone ?? '' }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white text-blue-900 border border-blue-300 font-bold text-xs hover:bg-blue-100 transition shadow-2xs">
                                                    <span>📞 Xaridorga qo'ng'iroq:</span>
                                                    <span>{{ $sh->suborder?->order?->user?->phone ?? 'Mavjud emas' }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Courier Action Buttons -->
                                    <div class="pt-3 border-t border-[#E8ECE4] flex flex-wrap items-center justify-between gap-3">
                                        <div class="text-[11px] text-[#64748B]">
                                            Amaldagi holat: <strong class="text-[#172326] font-bold">{{ $sh->status }}</strong>
                                        </div>

                                        <form action="{{ route('profile.courier.shipments.status', $sh->id) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            @if($sh->status === 'assigned')
                                                <button type="submit" name="status" value="picked_up" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5">
                                                    <span>📦</span>
                                                    <span>Do'kondan qabul qildim</span>
                                                </button>
                                            @elseif($sh->status === 'picked_up')
                                                <button type="submit" name="status" value="in_transit" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5">
                                                    <span>🚚</span>
                                                    <span>Yo'lga chiqdim (Yetkazmoqdaman)</span>
                                                </button>
                                            @elseif($sh->status === 'in_transit' || $sh->status === 'out_for_delivery')
                                                <button type="submit" name="status" value="delivered" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shadow-xs flex items-center gap-1.5">
                                                    <span>✓</span>
                                                    <span>Xaridorga yetkazildi (Topshirildi)</span>
                                                </button>
                                                <button type="submit" name="status" value="failed" class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 font-bold text-xs transition">
                                                    Yetkazib bo'lmadi
                                                </button>
                                            @elseif($sh->status === 'delivered')
                                                <span class="px-3 py-1.5 rounded-xl bg-emerald-100 text-emerald-900 font-bold text-xs">
                                                    ✓ Muvaffaqiyatli topshirilgan
                                                </span>
                                            @endif

                                            <a href="{{ route('tracking', ['trackingNumber' => $sh->tracking_number]) }}" target="_blank" class="px-3 py-2 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] hover:bg-[#E8ECE4] text-[#172326] font-bold text-xs transition">
                                                Xarita →
                                            </a>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-12 text-center shadow-xs">
                                    <div class="text-3xl mb-2">🛵</div>
                                    <h3 class="font-extrabold text-base text-[#172326]">Hozircha biriktirilgan yetkazishlar yo'q</h3>
                                    <p class="text-xs text-[#64748B]">Administrator yoki do'konlar sizga yangi buyurtmalarni biriktirganda shu yerda ko'rinadi.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endif

                {{-- =========================================================================
                     CUSTOMER STANDARD PANELS (Always available)
                     ========================================================================= --}}

                <!-- TAB 1: BUYURTMALARIM (Customer Orders) -->
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
                </div>

                <!-- TAB 4: SOZLAMALAR (Settings) -->
                <div x-show="activeTab === 'settings'" class="space-y-6">
                    <div>
                        <h2 class="text-xl font-black text-[#172326]">Hisob Sozlamalari & Xavfsizlik</h2>
                        <p class="text-xs text-[#64748B]">Shaxsiy ma'lumotlarni tahrirlash, parolni yangilash va bildirishnomalar</p>
                    </div>

                    <!-- Personal Information Form -->
                    <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 shadow-xs space-y-5">
                        <h3 class="font-extrabold text-sm text-[#172326] pb-3 border-b border-[#E8ECE4]">
                            Shaxsiy Ma'lumotlar
                        </h3>

                        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-[#172326] mb-1">To'liq ism (F.I.SH) *</label>
                                    <input type="text" name="name" value="{{ $user->name }}" required class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[#172326] mb-1">Telefon raqam *</label>
                                    <input type="text" name="phone" value="{{ $user->phone ?? '+998' }}" required class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
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
                </div>

            </div>

        </div>

    </div>

    <!-- Add Product Modal for Seller -->
    @if($user->isSeller())
        <div x-show="showAddProductModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
            <div @click.away="showAddProductModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-[#E8ECE4] space-y-5 animate-scale-up max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-[#E8ECE4]">
                    <div>
                        <h3 class="font-black text-lg text-[#172326]">Yangi Mahsulot Qo'shish</h3>
                        <p class="text-xs text-[#64748B]">Do'koningizga yangi tovar kiriting va darhol sotuvga chiqaring</p>
                    </div>
                    <button @click="showAddProductModal = false" class="text-[#64748B] hover:text-[#172326] font-bold text-xl">✕</button>
                </div>

                <form action="{{ route('profile.seller.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-[#172326] mb-1">Mahsulot Nomi *</label>
                        <input type="text" name="name" required placeholder="Masalan: M-500 Sement 50kg yoki Alyumin profil" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1">Kategoriya *</label>
                            <select name="category_id" required class="w-full h-11 px-3 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none font-medium">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1">Savdo Turi *</label>
                            <select name="trade_mode" required class="w-full h-11 px-3 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none font-medium">
                                <option value="both">Chakana va Ulgurji (Har ikkisi)</option>
                                <option value="retail_only">Faqat Chakana (Retail)</option>
                                <option value="wholesale_only">Faqat Ulgurji (Optom)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1">Birlik *</label>
                            <select name="unit" required class="w-full h-11 px-3 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none font-medium">
                                <option value="piece">Dona (piece)</option>
                                <option value="box">Quti (box)</option>
                                <option value="kg">Kilogramm (kg)</option>
                                <option value="ton">Tonna (ton)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1">Birlik Narxi (UZS) *</label>
                            <input type="number" name="price" required min="100" step="100" placeholder="50000" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1">Ombordagi Soni *</label>
                            <input type="number" name="stock_quantity" required min="0" step="1" placeholder="100" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1">Minimal Buyurtma Soni</label>
                            <input type="number" name="min_order_quantity" min="1" step="1" value="1" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[#172326] mb-1">Mahsulot Rasmini Yuklash</label>
                            <input type="file" name="image_file" accept="image/*" class="w-full h-11 px-2.5 py-2 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-[#123C2F] file:text-white file:font-bold">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#172326] mb-1">Mahsulot Haqida Tavsif</label>
                        <textarea name="description" rows="3" placeholder="Texnik xususiyatlari, markasi va tavsifi..." class="w-full p-3 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none"></textarea>
                    </div>

                    <div class="pt-3 border-t border-[#E8ECE4] flex items-center justify-end gap-3">
                        <button type="button" @click="showAddProductModal = false" class="px-5 py-2.5 rounded-xl bg-[#FAFBF7] text-[#64748B] hover:bg-[#E8ECE4] text-xs font-bold">
                            Bekor qilish
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#123C2F] text-[#C9F46B] hover:bg-[#0d2a21] text-xs font-bold transition shadow-xs">
                            Mahsulotni Saqlash & Sotuvga Chiqarish
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Add Address Modal for Customer -->
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
</x-layouts.app>
