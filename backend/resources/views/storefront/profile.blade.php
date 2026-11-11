<x-layouts.app title="Profil — Boshqaruv va Foydalanuvchi Markazi">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-24"
         x-data="{
             activeTab: '{{ $activeTab ?? 'orders' }}',
             showAddressModal: false,
             showAddProductModal: false,
             editProductId: null,
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
        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-xs font-bold text-rose-800 flex items-center gap-2">
                <span>!</span>
                <span>{{ session('error') }}</span>
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
                            {{ mb_strtoupper(mb_substr($user->name ?: 'U', 0, 1)) }}{{ mb_strtoupper(mb_substr(trim(strstr($user->name ?: '', ' ') ?: ($user->name ?: '')), 0, 1)) }}
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
                    @if($sellerNeedsActionCount > 0)
                        <span class="px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[10px] font-black">{{ $sellerNeedsActionCount }} yangi</span>
                    @endif
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
                            @if($sellerNeedsActionCount > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500 text-white">{{ $sellerNeedsActionCount }} yangi</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" :class="activeTab === 'seller_orders' ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#FAFBF7] text-[#64748B]'">
                                    {{ $sellerSuborders->count() }}
                                </span>
                            @endif
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
                        @if($sellerNeedsActionCount > 0)
                            <button type="button" @click="activeTab = 'seller_orders'" class="w-full text-left p-4 rounded-2xl bg-rose-50 border-2 border-rose-300 text-rose-900 font-bold text-sm flex items-center justify-between gap-3">
                                <span>🔔 {{ $sellerNeedsActionCount }} ta yangi buyurtma tayyorlashni kutmoqda</span>
                                <span class="text-xs underline whitespace-nowrap">Ko'rish →</span>
                            </button>
                        @endif

                        @if(!empty($sellerSalesStats))
                            <div class="bg-white rounded-3xl border border-[#E8ECE4] p-5 space-y-4">
                                <h3 class="font-black text-sm text-[#172326]">So'nggi 30 kun</h3>
                                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                                    <div class="p-3 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4]">
                                        <span class="text-[10px] font-bold uppercase text-[#64748B] block">Savdo</span>
                                        <span class="text-base font-black text-[#123C2F]">{{ number_format($sellerSalesStats['revenue'], 0, '.', ' ') }} UZS</span>
                                    </div>
                                    <div class="p-3 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4]">
                                        <span class="text-[10px] font-bold uppercase text-[#64748B] block">Buyurtmalar</span>
                                        <span class="text-base font-black text-[#172326]">{{ $sellerSalesStats['orders'] }}</span>
                                        <span class="text-[10px] text-[#64748B]">({{ $sellerSalesStats['delivered'] }} yetkazildi)</span>
                                    </div>
                                    <div class="p-3 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4]">
                                        <span class="text-[10px] font-bold uppercase text-[#64748B] block">O'rtacha chek</span>
                                        <span class="text-base font-black text-[#172326]">{{ number_format($sellerSalesStats['average'], 0, '.', ' ') }} UZS</span>
                                    </div>
                                    <div class="p-3 rounded-2xl border {{ $sellerSalesStats['out_of_stock'] > 0 ? 'bg-rose-50 border-rose-200' : 'bg-[#FAFBF7] border-[#E8ECE4]' }}">
                                        <span class="text-[10px] font-bold uppercase text-[#64748B] block">Tugagan mahsulotlar</span>
                                        <span class="text-base font-black {{ $sellerSalesStats['out_of_stock'] > 0 ? 'text-rose-700' : 'text-[#172326]' }}">{{ $sellerSalesStats['out_of_stock'] }}</span>
                                    </div>
                                </div>
                                @if($sellerSalesStats['top_products']->isNotEmpty())
                                    @php $topMax = max(1, (int) $sellerSalesStats['top_products']->max('revenue')); @endphp
                                    <div class="space-y-1.5">
                                        <span class="text-[10px] font-bold uppercase text-[#64748B]">Eng ko'p sotilganlar</span>
                                        @foreach($sellerSalesStats['top_products'] as $tp)
                                            <div class="text-xs">
                                                <div class="flex justify-between gap-2">
                                                    <span class="font-semibold text-[#172326] truncate">{{ $tp->product_name }} <span class="text-[#64748B] font-normal">×{{ (float) $tp->qty }}</span></span>
                                                    <span class="font-bold text-[#123C2F] whitespace-nowrap">{{ number_format($tp->revenue, 0, '.', ' ') }} UZS</span>
                                                </div>
                                                <div class="h-1.5 rounded-full bg-[#E8ECE4] mt-1">
                                                    <div class="h-1.5 rounded-full bg-[#123C2F]" style="width: {{ round($tp->revenue / $topMax * 100) }}%"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-black text-[#172326]">Mening Do'konim Mahsulotlari</h2>
                                <p class="text-xs text-[#64748B]">Do'koningizdagi tovarlar ro'yxati, narxlar va qoldiqlarni to'g'ridan-to'g'ri boshqaring</p>
                            </div>
                            <button @click="showAddProductModal = true" type="button" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-[#123C2F] text-[#C9F46B] hover:bg-[#0d2a21] font-bold text-xs transition shadow-sm">
                                <span>➕ Yangi Mahsulot Qo'shish</span>
                            </button>
                        </div>

                        <!-- Products: compact cards (phone-friendly, no sideways scrolling) -->
                        @php
                            $productState = function ($prod) {
                                $balances = $prod->variants->flatMap->inventoryBalances;
                                $available = max(0, (float) $balances->sum('quantity') - (float) $balances->sum('reserved_quantity'));
                                return match (true) {
                                    $prod->status === 'pending' => 'pending',
                                    $prod->status === 'rejected' => 'rejected',
                                    !$prod->is_active => 'frozen',
                                    $available <= 0 => 'soldout',
                                    default => 'active',
                                };
                            };
                            $stateCounts = $sellerProducts->countBy(fn($p) => in_array($productState($p), ['frozen', 'soldout'], true) ? 'frozen' : ($productState($p) === 'active' ? 'active' : 'other'));
                            $activeCount = $stateCounts['active'] ?? 0;
                            $frozenCount = $stateCounts['frozen'] ?? 0;
                            $lowStockCount = $sellerProducts->filter(fn($p) => $productState($p) === 'active' && max(0, (float)$p->variants->flatMap->inventoryBalances->sum('quantity') - (float)$p->variants->flatMap->inventoryBalances->sum('reserved_quantity')) < 5)->count();
                            $pendingCount = $sellerProducts->where('status', 'pending')->count();
                        @endphp
                        <div x-data="{
                            prodSearch: '',
                            selectedCategory: 'all',
                            prodFilter: 'all',
                            matchesProd(name, sku, categoryId, state, availableStock, categoryName) {
                                if (this.prodSearch.trim() !== '') {
                                    const q = this.prodSearch.toLowerCase().trim();
                                    const text = (name + ' ' + (sku || '') + ' ' + (categoryName || '')).toLowerCase();
                                    if (!text.includes(q)) return false;
                                }
                                if (this.selectedCategory !== 'all' && String(categoryId) !== String(this.selectedCategory)) {
                                    return false;
                                }
                                if (this.prodFilter === 'active' && state !== 'active') return false;
                                if (this.prodFilter === 'frozen' && state !== 'frozen' && state !== 'soldout') return false;
                                if (this.prodFilter === 'low_stock' && (availableStock >= 5 || state !== 'active')) return false;
                                if (this.prodFilter === 'pending' && state !== 'pending') return false;
                                return true;
                            }
                        }" class="space-y-4">
                            @if($sellerProducts->isNotEmpty())
                                <!-- Filter & Search Toolbar -->
                                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-3 sm:p-4 space-y-3 shadow-xs">
                                    <div class="flex flex-col sm:flex-row gap-2.5 items-stretch sm:items-center">
                                        <!-- Search input -->
                                        <div class="relative flex-1">
                                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">🔍</span>
                                            <input type="text" x-model="prodSearch" placeholder="Mahsulot nomi, SKU (artikul) yoki kategoriya bo'yicha qidirish..." 
                                                   class="w-full h-11 pl-10 pr-9 rounded-2xl border border-[#E8ECE4] bg-[#FAFBF7] focus:bg-white text-xs placeholder:text-slate-400 focus:border-[#123C2F] outline-none">
                                            <button type="button" x-show="prodSearch.length > 0" @click="prodSearch = ''" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 text-xs font-bold">✕</button>
                                        </div>

                                        <!-- Category selector -->
                                        <div class="w-full sm:w-60">
                                            <select x-model="selectedCategory" class="w-full h-11 px-3.5 rounded-2xl border border-[#E8ECE4] bg-[#FAFBF7] focus:bg-white text-xs font-semibold text-[#172326] focus:border-[#123C2F] outline-none">
                                                <option value="all">Barcha Kategoriyalar ({{ $sellerProducts->count() }})</option>
                                                @foreach($categories as $cat)
                                                    @php $cCount = $sellerProducts->where('category_id', $cat->id)->count(); @endphp
                                                    @if($cCount > 0)
                                                        <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cCount }})</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Quick status pills -->
                                    <div class="flex gap-2 overflow-x-auto no-scrollbar text-xs pt-2 border-t border-[#E8ECE4]">
                                        <button type="button" @click="prodFilter = 'all'" :class="prodFilter === 'all' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                            Hammasi ({{ $sellerProducts->count() }})
                                        </button>
                                        <button type="button" @click="prodFilter = 'active'" :class="prodFilter === 'active' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                            ● Sotuvda ({{ $activeCount }})
                                        </button>
                                        @if($lowStockCount > 0)
                                            <button type="button" @click="prodFilter = 'low_stock'" :class="prodFilter === 'low_stock' ? 'bg-amber-600 text-white border-amber-600' : 'bg-amber-50 text-amber-900 border-amber-200'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                                ⚠️ Kam qolgan (< 5 ta) ({{ $lowStockCount }})
                                            </button>
                                        @endif
                                        <button type="button" @click="prodFilter = 'frozen'" :class="prodFilter === 'frozen' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                            ❄️ Muzlatilgan / Tugagan ({{ $frozenCount }})
                                        </button>
                                        @if($pendingCount > 0)
                                            <button type="button" @click="prodFilter = 'pending'" :class="prodFilter === 'pending' ? 'bg-amber-600 text-white border-amber-600' : 'bg-amber-50 text-amber-900 border-amber-200'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                                ⏳ Moderatsiyada ({{ $pendingCount }})
                                            </button>
                                        @endif

                                        <button type="button" x-show="prodSearch !== '' || selectedCategory !== 'all' || prodFilter !== 'all'" 
                                                @click="prodSearch = ''; selectedCategory = 'all'; prodFilter = 'all'" 
                                                class="ml-auto text-[11px] font-bold text-rose-600 hover:text-rose-800 whitespace-nowrap self-center underline">
                                            Tozalash ✕
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @forelse($sellerProducts as $prod)
                                @php
                                    $variant = $prod->variants->first();
                                    $firstTier = $variant?->priceTiers->sortBy('min_quantity')->first();
                                    $tier300 = $variant?->priceTiers->first(fn($t) => (float)$t->min_quantity >= 300 && (float)$t->min_quantity < 1000);
                                    $tier1000 = $variant?->priceTiers->first(fn($t) => (float)$t->min_quantity >= 1000);
                                    $totalStock = (float) $prod->variants->flatMap->inventoryBalances->sum('quantity');
                                    $reservedStock = (float) $prod->variants->flatMap->inventoryBalances->sum('reserved_quantity');
                                    $availableStock = max(0, $totalStock - $reservedStock);
                                    $state = $productState($prod);
                                    $group = in_array($state, ['frozen', 'soldout'], true) ? 'frozen' : ($state === 'active' ? 'active' : 'other');
                                    $badge = [
                                        'pending' => ['⏳ Moderatsiyada', 'bg-amber-100 text-amber-900'],
                                        'rejected' => ['✕ Rad etilgan', 'bg-rose-100 text-rose-800'],
                                        'frozen' => ['❄️ Muzlatilgan', 'bg-sky-100 text-sky-900'],
                                        'soldout' => ['❄️ Qoldiq tugadi', 'bg-sky-100 text-sky-900'],
                                        'active' => ['● Sotuvda', 'bg-emerald-100 text-emerald-800'],
                                    ][$state];
                                    $fmtQty = fn($q) => rtrim(rtrim(number_format($q, 3, '.', ' '), '0'), '.');
                                @endphp
                                <article x-show="matchesProd('{{ addslashes($prod->name) }}', '{{ addslashes($variant?->sku ?? '') }}', '{{ $prod->category_id }}', '{{ $state }}', {{ $availableStock }}, '{{ addslashes($prod->category->name ?? '') }}')"
                                         class="bg-white rounded-2xl border {{ in_array($state, ['frozen', 'soldout'], true) ? 'border-sky-200 bg-sky-50/30' : 'border-[#E8ECE4]' }} p-3 sm:p-4 shadow-2xs">
                                    <div class="flex gap-3">
                                        <a href="{{ route('product.show', $prod->slug) }}" target="_blank" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl bg-slate-100 overflow-hidden border border-[#E8ECE4] flex-shrink-0 flex items-center justify-center {{ in_array($state, ['frozen', 'soldout'], true) ? 'grayscale opacity-70' : '' }}">
                                            @if($prod->image_url)
                                                <img src="{{ $prod->image_url }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                            @else
                                                <span class="text-2xl">📦</span>
                                            @endif
                                        </a>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-2">
                                                <a href="{{ route('product.show', $prod->slug) }}" target="_blank" class="font-bold text-sm text-[#172326] leading-snug line-clamp-2 hover:underline">{{ $prod->name }}</a>
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold whitespace-nowrap {{ $badge[1] }}">{{ $badge[0] }}</span>
                                            </div>
                                            <div class="text-[11px] text-[#64748B] mt-0.5 truncate">{{ $prod->category->name ?? '—' }} · {{ $variant?->sku ?? '—' }}</div>
                                            <div class="mt-2 flex flex-wrap items-baseline gap-x-4 gap-y-1">
                                                <span class="font-black text-base text-[#123C2F] whitespace-nowrap">{{ $firstTier ? number_format($firstTier->unit_price, 0, '.', ' ') : '—' }} <span class="text-[10px] font-bold">UZS</span></span>
                                                <span class="text-xs whitespace-nowrap {{ $availableStock <= 0 ? 'text-rose-700 font-bold' : ($availableStock < 5 ? 'text-amber-700 font-bold' : 'text-[#172326] font-semibold') }}">
                                                    {{ $fmtQty($availableStock) }} {{ $prod->unit }} sotuvda
                                                </span>
                                                @if($reservedStock > 0)
                                                    <span class="text-[11px] text-[#64748B] whitespace-nowrap">+{{ $fmtQty($reservedStock) }} band</span>
                                                @endif
                                            </div>
                                            @if($state === 'soldout')
                                                <p class="text-[11px] text-sky-800 mt-1">Avtomatik muzlatildi. Qoldiqni to'ldirsangiz, o'zi sotuvga qaytadi.</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
                                        <button type="button" @click="editProductId = editProductId === {{ $prod->id }} ? null : {{ $prod->id }}"
                                                :class="editProductId === {{ $prod->id }} ? 'bg-[#C9F46B] text-[#123C2F]' : 'bg-[#123C2F] text-[#C9F46B]'"
                                                class="h-10 rounded-xl font-bold">
                                            ✏️ Tahrirlash
                                        </button>
                                        <form action="{{ route('profile.seller.products.status', $prod->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="toggle_active" value="1">
                                            <button type="submit" class="w-full h-10 rounded-xl border font-bold {{ $prod->is_active ? 'border-sky-300 text-sky-800 bg-white hover:bg-sky-50' : 'border-emerald-300 text-emerald-800 bg-white hover:bg-emerald-50' }}">
                                                {{ $prod->is_active ? '❄️ Muzlatish' : '▶ Sotuvga' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('profile.seller.products.status', $prod->id) }}" method="POST"
                                              onsubmit="return confirm('&quot;{{ addslashes($prod->name) }}&quot; butunlay o\'chirilsinmi? Bu amalni qaytarib bo\'lmaydi.')">
                                            @csrf
                                            <input type="hidden" name="delete" value="1">
                                            <button type="submit" class="w-full h-10 rounded-xl border border-rose-300 text-rose-700 bg-white hover:bg-rose-50 font-bold">
                                                🗑 O'chirish
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Inline edit form -->
                                    <form x-show="editProductId === {{ $prod->id }}" x-cloak
                                          action="{{ route('profile.seller.products.update', $prod->id) }}" method="POST" enctype="multipart/form-data"
                                          class="mt-3 pt-3 border-t border-[#E8ECE4] space-y-4 text-xs">
                                        @csrf
                                        
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                            <!-- Mahsulot Nomi -->
                                            <div class="sm:col-span-2 space-y-1">
                                                <label class="block font-bold text-[#172326]">Mahsulot Nomi *</label>
                                                <input type="text" name="name" value="{{ $prod->name }}" required maxlength="255" class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                            </div>

                                            <!-- Kategoriya -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">Kategoriya *</label>
                                                <select name="category_id" required class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                                    @foreach($categories as $cat)
                                                        <option value="{{ $cat->id }}" @selected($prod->category_id == $cat->id)>{{ $cat->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Savdo turi -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">Savdo turi *</label>
                                                <select name="trade_mode" required class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                                    <option value="both" @selected($prod->trade_mode === 'both')>Chakana va Ulgurji</option>
                                                    <option value="retail_only" @selected($prod->trade_mode === 'retail_only')>Faqat Chakana (Retail)</option>
                                                    <option value="wholesale_only" @selected($prod->trade_mode === 'wholesale_only')>Faqat Ulgurji (Optom)</option>
                                                </select>
                                            </div>

                                            <!-- O'lchov birligi -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">O'lchov birligi *</label>
                                                <select name="unit" required class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                                    <option value="piece" @selected($prod->unit === 'piece')>Dona (piece)</option>
                                                    <option value="box" @selected($prod->unit === 'box')>Quti (box)</option>
                                                    <option value="kg" @selected($prod->unit === 'kg')>Kilogramm (kg)</option>
                                                    <option value="ton" @selected($prod->unit === 'ton')>Tonna (ton)</option>
                                                </select>
                                            </div>

                                            <!-- Chakana narx -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">Chakana narx, UZS <span class="font-normal text-[#64748B]">(1 {{ $prod->unit }})</span> *</label>
                                                <input type="number" name="price" value="{{ (int) ($firstTier?->unit_price ?? 0) }}" min="100" step="100" required inputmode="numeric" class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                            </div>

                                            <!-- Ombordagi qoldiq -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">Ombordagi qoldiq *</label>
                                                <input type="number" name="stock_quantity" value="{{ $totalStock }}" min="{{ $reservedStock }}" step="any" required inputmode="decimal" class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                            </div>

                                            <!-- Min. buyurtma -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">Min. buyurtma</label>
                                                <input type="number" name="min_order_quantity" value="{{ (float) $prod->min_order_quantity }}" min="0.001" step="any" inputmode="decimal" class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                            </div>

                                            <!-- Qadam (step) -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">Sotish qadami (Step)</label>
                                                <input type="number" name="unit_step" value="{{ (float) ($prod->unit_step ?? 1.0) }}" min="0.001" step="any" inputmode="decimal" placeholder="1" class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                            </div>

                                            <!-- Qutidagi donalar -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">Qutidagi donalar (Qadoq)</label>
                                                <input type="number" name="items_per_box" value="{{ $prod->items_per_box }}" min="1" step="1" placeholder="Masalan: 12" class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                            </div>

                                            <!-- Optom narx (300+) -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">300+ Ulgurji narx, UZS</label>
                                                <input type="number" name="tier_300_price" value="{{ (int) ($tier300?->unit_price ?? '') }}" min="100" step="100" placeholder="Chegirmali narx" class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                            </div>

                                            <!-- Optom narx (1000+) -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">1000+ Katta ulgurji narx, UZS</label>
                                                <input type="number" name="tier_1000_price" value="{{ (int) ($tier1000?->unit_price ?? '') }}" min="100" step="100" placeholder="Eng arzon ulgurji narx" class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                            </div>

                                            <!-- Artikul / SKU -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">Artikul (SKU)</label>
                                                <input type="text" name="sku" value="{{ $variant?->sku }}" maxlength="100" placeholder="SKU-..." class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                            </div>

                                            <!-- Rasm URL -->
                                            <div class="space-y-1">
                                                <label class="block font-bold text-[#172326]">Rasm URL havolasi</label>
                                                <input type="text" name="image_url" value="{{ $prod->image_url }}" placeholder="https://... yoki /images/..." class="w-full h-10 px-3 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">
                                            </div>

                                            <!-- Yangi rasm yuklash -->
                                            <div class="sm:col-span-2 space-y-1">
                                                <label class="block font-bold text-[#172326]">Yangi rasm fayli yuklash</label>
                                                <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="w-full h-10 px-2.5 py-1.5 rounded-xl border border-[#E8ECE4] bg-white text-[11px] file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-[#123C2F] file:text-white file:font-bold">
                                            </div>

                                            <!-- Tavsif -->
                                            <div class="sm:col-span-2 lg:col-span-4 space-y-1">
                                                <label class="block font-bold text-[#172326]">Mahsulot tavsifi</label>
                                                <textarea name="description" rows="3" maxlength="2000" placeholder="Texnik xususiyatlari, markasi, sifati..." class="w-full px-3 py-2 rounded-xl border border-[#E8ECE4] bg-white text-xs focus:border-[#123C2F] outline-none">{{ $prod->description }}</textarea>
                                            </div>
                                        </div>

                                        @if($reservedStock > 0)
                                            <p class="text-[11px] text-[#64748B]">Qoldiq {{ $fmtQty($reservedStock) }} dan kam bo'lolmaydi — shuncha tovar xaridorlar buyurtmalarida band qilingan.</p>
                                        @endif

                                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-[#E8ECE4]">
                                            <button type="button" @click="editProductId = null" class="h-11 rounded-xl border border-[#E8ECE4] bg-white font-bold hover:bg-slate-50 transition">Bekor qilish</button>
                                            <button type="submit" class="h-11 rounded-xl bg-[#123C2F] text-[#C9F46B] font-bold hover:bg-[#0d2a21] transition shadow-xs">O'zgarishlarni Saqlash</button>
                                        </div>
                                    </form>
                                </article>
                            @empty
                                <div class="bg-white rounded-2xl border border-[#E8ECE4] p-8 text-center text-xs text-[#64748B]">
                                    Do'koningizda hozircha mahsulotlar yo'q. Birinchi mahsulotingizni qo'shing!
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB: SELLER ORDERS -->
                    <div x-show="activeTab === 'seller_orders'" class="space-y-6">
                        @php
                            $inTransitSubordersCount = $sellerSuborders->filter(fn($s) => in_array($s->status, ['shipped', 'in_transit']) || $s->shipments->contains(fn($sh) => in_array($sh->status, ['in_transit', 'out_for_delivery'])))->count();
                            $deliveredSubordersCount = $sellerSuborders->filter(fn($s) => $s->status === 'delivered')->count();
                            $cancelledSubordersCount = $sellerSuborders->filter(fn($s) => $s->status === 'cancelled' || $s->order?->status === 'cancelled')->count();
                        @endphp
                        <div x-data="{
                            orderSearch: '',
                            orderStatusTab: 'all',
                            matchesOrder(number, buyer, phone, itemsText, status, isAction) {
                                if (this.orderSearch.trim() !== '') {
                                    const q = this.orderSearch.toLowerCase().trim();
                                    const full = (number + ' ' + (buyer || '') + ' ' + (phone || '') + ' ' + (itemsText || '')).toLowerCase();
                                    if (!full.includes(q)) return false;
                                }
                                if (this.orderStatusTab === 'needs_action' && !isAction) return false;
                                if (this.orderStatusTab === 'in_transit' && status !== 'shipped' && status !== 'in_transit') return false;
                                if (this.orderStatusTab === 'delivered' && status !== 'delivered') return false;
                                if (this.orderStatusTab === 'cancelled' && status !== 'cancelled') return false;
                                return true;
                            }
                        }" class="space-y-4">
                            <div>
                                <h2 class="text-xl font-black text-[#172326]">Do'konga Tushgan Buyurtmalar</h2>
                                <p class="text-xs text-[#64748B]">Xaridorlar tomonidan do'koningizdan sotib olingan tovarlar va ularning yetkazilish bosqichlari</p>
                            </div>

                            @if($sellerSuborders->isNotEmpty())
                                <!-- Filter & Search Toolbar -->
                                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-3 sm:p-4 space-y-3 shadow-xs">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">🔍</span>
                                        <input type="text" x-model="orderSearch" placeholder="Buyurtma #SO-..., xaridor ismi, telefon raqami yoki mahsulot nomi bo'yicha qidirish..." 
                                               class="w-full h-11 pl-10 pr-9 rounded-2xl border border-[#E8ECE4] bg-[#FAFBF7] focus:bg-white text-xs placeholder:text-slate-400 focus:border-[#123C2F] outline-none">
                                        <button type="button" x-show="orderSearch.length > 0" @click="orderSearch = ''" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 text-xs font-bold">✕</button>
                                    </div>

                                    <div class="flex gap-2 overflow-x-auto no-scrollbar text-xs pt-2 border-t border-[#E8ECE4]">
                                        <button type="button" @click="orderStatusTab = 'all'" :class="orderStatusTab === 'all' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                            Hammasi ({{ $sellerSuborders->count() }})
                                        </button>
                                        <button type="button" @click="orderStatusTab = 'needs_action'" :class="orderStatusTab === 'needs_action' ? 'bg-rose-600 text-white border-rose-600' : 'bg-rose-50 text-rose-900 border-rose-200'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition flex items-center gap-1.5">
                                            <span>⚡ Yangi / Qadoqlash kerak</span>
                                            @if($sellerNeedsActionCount > 0)
                                                <span class="px-1.5 py-0.2 rounded-full bg-white text-rose-700 text-[10px] font-black">{{ $sellerNeedsActionCount }}</span>
                                            @endif
                                        </button>
                                        <button type="button" @click="orderStatusTab = 'in_transit'" :class="orderStatusTab === 'in_transit' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                            🚚 Kuryerda / Yo'lda ({{ $inTransitSubordersCount }})
                                        </button>
                                        <button type="button" @click="orderStatusTab = 'delivered'" :class="orderStatusTab === 'delivered' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                            ✅ Yetkazildi ({{ $deliveredSubordersCount }})
                                        </button>
                                        @if($cancelledSubordersCount > 0)
                                            <button type="button" @click="orderStatusTab = 'cancelled'" :class="orderStatusTab === 'cancelled' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                                ❌ Bekor / Qaytarilgan ({{ $cancelledSubordersCount }})
                                            </button>
                                        @endif

                                        <button type="button" x-show="orderSearch !== '' || orderStatusTab !== 'all'" 
                                                @click="orderSearch = ''; orderStatusTab = 'all'" 
                                                class="ml-auto text-[11px] font-bold text-rose-600 hover:text-rose-800 whitespace-nowrap self-center underline">
                                            Tozalash ✕
                                        </button>
                                    </div>
                                </div>
                            @endif

                            @forelse($sellerSuborders as $sub)
                                @php
                                    $subShipment = $sub->shipments->first();
                                    $isSubNeedsAction = in_array($sub->status, ['pending', 'confirmed'], true) && $sub->shipments->isEmpty();
                                @endphp
                                <div x-show="matchesOrder('{{ addslashes($sub->suborder_number) }}', '{{ addslashes($sub->order->user->name ?? '') }}', '{{ addslashes($sub->order->user->phone ?? '') }}', '{{ addslashes($sub->items->pluck('product_name')->implode(' ')) }}', '{{ $sub->status }}', {{ $isSubNeedsAction ? 'true' : 'false' }})"
                                     class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs hover:border-[#123C2F]/30 transition-all p-5 space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#E8ECE4]">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-black text-sm text-[#172326]">#{{ $sub->suborder_number }}</span>
                                            @php
                                                $isUnpaid = $sub->order?->status === 'pending_payment';
                                                $isLocked = in_array($sub->status, ['cancelled', 'shipped', 'delivered'], true) || $sub->order?->status === 'cancelled';
                                                $statusLabel = match (true) {
                                                    $sub->status === 'cancelled' || $sub->order?->status === 'cancelled' => ['Bekor qilingan', 'bg-rose-50 text-rose-800 border-rose-200'],
                                                    $isUnpaid => ["To'lov kutilmoqda", 'bg-amber-50 text-amber-900 border-amber-200'],
                                                    $sub->status === 'delivered' => ['Yetkazildi', 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                                                    $sub->status === 'shipped' => ["Yo'lda", 'bg-blue-50 text-blue-800 border-blue-200'],
                                                    $sub->status === 'preparing' && $subShipment?->courier_id => ['Kuryerga tayyor', 'bg-indigo-50 text-indigo-800 border-indigo-200'],
                                                    $sub->status === 'preparing' => ['Qadoqlanmoqda', 'bg-indigo-50 text-indigo-800 border-indigo-200'],
                                                    $sub->status === 'confirmed' => ['Tasdiqlandi', 'bg-emerald-50 text-emerald-800 border-emerald-200'],
                                                    default => ['Yangi', 'bg-slate-50 text-slate-700 border-slate-200'],
                                                };
                                            @endphp
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ $statusLabel[1] }}">
                                                {{ $statusLabel[0] }}
                                            </span>
                                            @if($sub->order?->latest_payment?->payment_method === 'demo')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-900 border border-amber-300" title="Haqiqiy pul tushmagan">🧪 Test to'lov</span>
                                            @endif
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
                                    @if($isLocked)
                                        <span class="text-[11px] text-[#64748B]">Bu buyurtma bo'yicha amal talab qilinmaydi.</span>
                                    @elseif($isUnpaid)
                                        <span class="text-[11px] text-amber-800 font-semibold">Mijoz to'lovni tugatgach tayyorlashni boshlang.</span>
                                    @else
                                    <form action="{{ route('profile.seller.orders.status', $sub->id) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        <select name="status" class="h-9 px-3 rounded-xl bg-white border border-[#E8ECE4] text-xs font-bold text-[#172326] outline-none">
                                            <option value="confirmed" {{ in_array($sub->status, ['pending', 'confirmed']) ? 'selected' : '' }}>1. Tasdiqlash</option>
                                            <option value="preparing" {{ $sub->status === 'preparing' && !$subShipment?->courier_id ? 'selected' : '' }}>2. Qadoqlanmoqda</option>
                                            <option value="ready_for_shipping" {{ $sub->status === 'preparing' && $subShipment?->courier_id ? 'selected' : '' }}>3. Kuryerga berishga tayyor</option>
                                        </select>

                                        <select name="courier_id" class="h-9 px-3 rounded-xl bg-white border border-[#E8ECE4] text-xs font-medium text-[#172326] outline-none">
                                            <option value="">-- Kuryer tanlang --</option>
                                            @foreach($couriers as $c)
                                                <option value="{{ $c->id }}" {{ $subShipment?->courier_id === $c->id ? 'selected' : '' }}>🛵 {{ $c->name }} ({{ $c->phone ?? $c->email }})</option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="h-9 px-4 rounded-xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs hover:bg-[#0d2a21] transition">
                                            Saqlash
                                        </button>
                                    </form>
                                    @endif
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
                            <h2 class="text-xl font-black text-[#172326]">Do'kon moliyasi</h2>
                            <p class="text-xs text-[#64748B]">Qancha sotdingiz, sayt qancha oldi, qo'lingizga qancha tegadi va pulni qanday olasiz</p>
                        </div>

                        <!-- Finance summary widgets -->
                        @php
                            $money = fn($sum) => number_format((int) $sum, 0, '.', ' ');
                            $fin = $sellerFinance + ['balance' => 0, 'on_hold' => 0, 'pending_payouts' => 0, 'available' => 0, 'total_sales' => 0, 'total_commission' => 0, 'total_refunds' => 0, 'total_paid_out' => 0, 'has_dispute' => false];
                            $holdDays = \App\Services\Ledger\SellerLedgerService::HOLD_DAYS;
                            $minPayout = \App\Services\Ledger\SellerLedgerService::MIN_PAYOUT;
                        @endphp
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="col-span-2 lg:col-span-1 p-4 sm:p-5 rounded-3xl bg-[#123C2F] text-white">
                                <span class="text-[11px] font-bold uppercase text-[#C9F46B] block mb-1">Yechish mumkin</span>
                                <div class="text-2xl font-black">{{ $money($fin['available']) }} <span class="text-xs font-bold text-white/70">UZS</span></div>
                                <span class="text-[10px] text-white/70 block mt-1">Hozir so'rash mumkin bo'lgan pul</span>
                            </div>

                            <div class="p-4 sm:p-5 rounded-3xl bg-white border border-[#E8ECE4] shadow-xs">
                                <span class="text-[11px] font-bold uppercase text-[#64748B] block mb-1">Kutish muddatida</span>
                                <div class="text-lg sm:text-xl font-black text-slate-800">{{ $money($fin['on_hold']) }} <span class="text-[10px] text-[#64748B] font-bold">UZS</span></div>
                                <span class="text-[10px] text-[#64748B] block mt-1">Oxirgi {{ $holdDays }} kunlik savdo</span>
                            </div>

                            <div class="p-4 sm:p-5 rounded-3xl bg-white border border-[#E8ECE4] shadow-xs">
                                <span class="text-[11px] font-bold uppercase text-[#64748B] block mb-1">So'ralgan</span>
                                <div class="text-lg sm:text-xl font-black text-slate-800">{{ $money($fin['pending_payouts']) }} <span class="text-[10px] text-[#64748B] font-bold">UZS</span></div>
                                <span class="text-[10px] text-[#64748B] block mt-1">Admin ko'rib chiqmoqda</span>
                            </div>

                            <div class="col-span-2 lg:col-span-1 p-4 sm:p-5 rounded-3xl bg-white border border-[#E8ECE4] shadow-xs">
                                <span class="text-[11px] font-bold uppercase text-[#64748B] block mb-1">Jami balans</span>
                                <div class="text-lg sm:text-xl font-black text-[#123C2F]">{{ $money($fin['balance']) }} <span class="text-[10px] text-[#64748B] font-bold">UZS</span></div>
                                <span class="text-[10px] text-[#64748B] block mt-1">Saytda turgan barcha pulingiz</span>
                            </div>
                        </div>

                        <!-- How the balance was made -->
                        <div class="bg-white rounded-3xl border border-[#E8ECE4] shadow-xs p-4 sm:p-5">
                            <h3 class="font-bold text-xs text-[#172326] mb-3">Balans qanday hisoblangan</h3>
                            <dl class="text-xs space-y-2">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-[#64748B]">Jami savdo</dt>
                                    <dd class="font-bold text-emerald-700 whitespace-nowrap">+ {{ $money($fin['total_sales']) }} UZS</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-[#64748B]">Sayt komissiyasi ({{ (float) ($seller->commission_rate ?? 5) }}%)</dt>
                                    <dd class="font-bold text-rose-700 whitespace-nowrap">− {{ $money($fin['total_commission']) }} UZS</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-[#64748B]">Qaytarilgan tovarlar</dt>
                                    <dd class="font-bold text-rose-700 whitespace-nowrap">− {{ $money($fin['total_refunds']) }} UZS</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-[#64748B]">Sizga to'lab berilgan</dt>
                                    <dd class="font-bold text-rose-700 whitespace-nowrap">− {{ $money($fin['total_paid_out']) }} UZS</dd>
                                </div>
                                <div class="flex justify-between gap-3 pt-2 border-t border-[#E8ECE4]">
                                    <dt class="font-bold text-[#172326]">Jami balans</dt>
                                    <dd class="font-black text-[#123C2F] whitespace-nowrap">{{ $money($fin['balance']) }} UZS</dd>
                                </div>
                            </dl>
                            <p class="text-[11px] text-[#64748B] mt-3">
                                Savdo puli buyurtma to'langach (naqd to'lovda — yetkazilgach) balansga tushadi va {{ $holdDays }} kun kutadi: shu vaqt ichida xaridor tovarni qaytarishi mumkin.
                            </p>
                        </div>

                        <!-- Payout request -->
                        <div class="bg-white rounded-3xl border border-[#E8ECE4] shadow-xs p-4 sm:p-5">
                            <h3 class="font-bold text-sm text-[#172326]">Pulni yechib olish</h3>
                            @if(!$canRequestPayout)
                                <p class="text-xs text-[#64748B] mt-2">Pul yechishni faqat do'kon egasi so'rashi mumkin.</p>
                            @elseif($fin['has_dispute'])
                                <p class="text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-2xl p-3 mt-2">
                                    Hal qilinmagan qaytarish so'rovi bor. U yopilgach pul yechish mumkin bo'ladi.
                                </p>
                            @elseif($fin['available'] < $minPayout)
                                <p class="text-xs text-[#64748B] mt-2">
                                    Hozircha yechish uchun pul yetarli emas. Eng kam summa: {{ $money($minPayout) }} UZS.
                                    @if($fin['on_hold'] > 0) Kutish muddatidagi {{ $money($fin['on_hold']) }} UZS {{ $holdDays }} kun ichida ochiladi. @endif
                                </p>
                            @else
                                <p class="text-xs text-[#64748B] mt-1">So'rovni administrator ko'rib chiqadi va pulni kartangiz yoki hisob raqamingizga o'tkazadi.</p>
                                <form method="POST" action="{{ route('profile.seller.payouts.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2"
                                      x-data="{ method: '{{ old('method', 'card') === 'account' ? 'account' : 'card' }}' }">
                                    @csrf
                                    <div class="sm:col-span-2">
                                        <label for="payout_amount" class="text-[11px] font-bold text-[#172326] block mb-1">Summa (UZS)</label>
                                        <input id="payout_amount" type="text" inputmode="numeric" name="amount" required
                                               value="{{ $money(old('amount', $fin['available'])) }}"
                                               class="w-full px-3 py-2.5 rounded-xl border border-[#E8ECE4] text-sm font-bold focus:outline-none focus:border-[#123C2F]">
                                        <span class="text-[10px] text-[#64748B] block mt-1">{{ $money($minPayout) }} dan {{ $money($fin['available']) }} UZS gacha</span>
                                    </div>
                                    <div class="sm:col-span-2 flex gap-2">
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="method" value="card" x-model="method" class="sr-only">
                                            <span :class="method === 'card' ? 'bg-[#123C2F] text-white border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]'"
                                                  class="block text-center px-3 py-2.5 rounded-xl border text-xs font-bold">Plastik karta</span>
                                        </label>
                                        <label class="flex-1 cursor-pointer">
                                            <input type="radio" name="method" value="account" x-model="method" class="sr-only">
                                            <span :class="method === 'account' ? 'bg-[#123C2F] text-white border-[#123C2F]' : 'bg-white text-[#172326] border-[#E8ECE4]'"
                                                  class="block text-center px-3 py-2.5 rounded-xl border text-xs font-bold">Bank hisob raqami</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label for="payout_number" class="text-[11px] font-bold text-[#172326] block mb-1" x-text="method === 'card' ? 'Karta raqami (16 ta raqam)' : 'Hisob raqami (20 ta raqam)'">Karta raqami (16 ta raqam)</label>
                                        <input id="payout_number" type="text" inputmode="numeric" name="account_number" required autocomplete="off"
                                               :maxlength="method === 'card' ? 19 : 24"
                                               :placeholder="method === 'card' ? '8600 0000 0000 0000' : '2020 8000 0000 0000 0000'"
                                               class="w-full px-3 py-2.5 rounded-xl border border-[#E8ECE4] text-sm font-mono focus:outline-none focus:border-[#123C2F]">
                                    </div>
                                    <div>
                                        <label for="payout_holder" class="text-[11px] font-bold text-[#172326] block mb-1">Karta yoki hisob egasi</label>
                                        <input id="payout_holder" type="text" name="holder_name" required maxlength="100"
                                               value="{{ old('holder_name', $seller->legal_name) }}"
                                               class="w-full px-3 py-2.5 rounded-xl border border-[#E8ECE4] text-sm focus:outline-none focus:border-[#123C2F]">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <button type="submit" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-[#123C2F] hover:bg-[#0E2F25] text-white text-sm font-bold">
                                            Pul yechishni so'rash
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>

                        <!-- Payout history -->
                        @if($sellerPayouts->isNotEmpty())
                            @php
                                $payoutStatus = [
                                    'requested' => ["Ko'rib chiqilmoqda", 'bg-amber-100 text-amber-900'],
                                    'held' => ['Ushlab turilgan', 'bg-amber-100 text-amber-900'],
                                    'processing' => ['Jarayonda', 'bg-blue-100 text-blue-800'],
                                    'completed' => ["To'landi", 'bg-emerald-100 text-emerald-800'],
                                    'rejected' => ['Rad etildi', 'bg-rose-100 text-rose-800'],
                                ];
                            @endphp
                            <div class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs">
                                <div class="p-4 bg-[#FAFBF7] border-b border-[#E8ECE4]">
                                    <h3 class="font-bold text-xs text-[#172326]">Pul yechish so'rovlarim</h3>
                                </div>
                                <div class="divide-y divide-[#E8ECE4]">
                                    @foreach($sellerPayouts as $payout)
                                        <div class="p-4 text-xs">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="font-black text-sm text-[#172326]">{{ $money($payout->amount) }} UZS</div>
                                                    <div class="text-[#64748B] mt-0.5">{{ $payout->created_at->format('d.m.Y H:i') }} • {{ $payout->maskedAccount() }}</div>
                                                </div>
                                                <span class="shrink-0 px-2 py-1 rounded-full text-[10px] font-bold {{ $payoutStatus[$payout->status][1] ?? 'bg-slate-100' }}">
                                                    {{ $payoutStatus[$payout->status][0] ?? $payout->status }}
                                                </span>
                                            </div>
                                            @if($payout->admin_notes)
                                                <div class="mt-2 text-slate-700"><span class="text-[#64748B]">Administrator izohi:</span> {{ $payout->admin_notes }}</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Ledger Transactions Table -->
                        <div class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs">
                            <div class="p-4 bg-[#FAFBF7] border-b border-[#E8ECE4]">
                                <h3 class="font-bold text-xs text-[#172326]">Oxirgi pul harakatlari</h3>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs text-slate-600">
                                    <thead class="bg-white text-[#172326] font-bold uppercase tracking-wider text-[10px] border-b border-[#E8ECE4]">
                                        <tr>
                                            <th class="p-3">Sana</th>
                                            <th class="p-3">Turi</th>
                                            <th class="p-3">Summa</th>
                                            <th class="p-3">Buyurtma</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-[#E8ECE4]">
                                        @php
                                            $entryLabels = [
                                                'order_sale' => 'Savdo tushumi',
                                                'commission_fee' => 'Sayt komissiyasi',
                                                'payout' => "Sizga to'landi",
                                                'refund_deduction' => 'Tovar qaytarildi',
                                                'dispute_hold' => 'Nizo uchun ushlandi',
                                                'dispute_release' => 'Nizodan qaytdi',
                                            ];
                                        @endphp
                                        @forelse($sellerLedger as $item)
                                            <tr class="hover:bg-[#FAFBF7]/60">
                                                <td class="p-3 text-slate-400 font-mono whitespace-nowrap">{{ $item->created_at->format('d.m.Y H:i') }}</td>
                                                <td class="p-3 font-bold text-slate-800 whitespace-nowrap">{{ $entryLabels[$item->entry_type] ?? $item->entry_type }}</td>
                                                <td class="p-3 font-black whitespace-nowrap {{ $item->type === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}">
                                                    {{ $item->type === 'credit' ? '+' : '−' }} {{ number_format($item->amount, 0, '.', ' ') }} UZS
                                                </td>
                                                <td class="p-3 text-slate-600">{{ $item->reference ?? '—' }}</td>
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
                        <div>
                            <h2 class="text-xl font-black text-[#172326]">Menga Biriktirilgan Yetkazishlar</h2>
                            <p class="text-xs text-[#64748B]">Do'kondan tovarlarni qabul qilish, manzilga yetkazish va xaridor bilan hisob-kitob qilish</p>
                        </div>

                        <!-- Today's shift summary -->
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="p-4 rounded-2xl bg-white border border-[#E8ECE4]">
                                <span class="text-[10px] font-bold uppercase text-[#64748B] block">Faol jo'natmalar</span>
                                <span class="text-xl font-black text-[#172326]">{{ $courierStats['active'] ?? 0 }}</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-white border border-[#E8ECE4]">
                                <span class="text-[10px] font-bold uppercase text-[#64748B] block">Bugun yetkazildi</span>
                                <span class="text-xl font-black text-emerald-700">{{ $courierStats['delivered_today'] ?? 0 }}</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200">
                                <span class="text-[10px] font-bold uppercase text-amber-800 block">Undirilishi kerak (naqd)</span>
                                <span class="text-base sm:text-lg font-black text-amber-950">{{ number_format($courierStats['cash_to_collect'] ?? 0, 0, '.', ' ') }} UZS</span>
                            </div>
                            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200">
                                <span class="text-[10px] font-bold uppercase text-emerald-800 block">Bugun olingan naqd</span>
                                <span class="text-base sm:text-lg font-black text-emerald-900">{{ number_format($courierStats['cash_collected_today'] ?? 0, 0, '.', ' ') }} UZS</span>
                                <span class="text-[10px] text-emerald-700 block">Kun oxirida do'konga/kassaga topshiring</span>
                            </div>
                        </div>

                        @php
                            $shipLabels = [
                                'pending' => ['Kutilmoqda', 'bg-slate-100 text-slate-800'],
                                'assigned' => ["Do'kondan olish kerak", 'bg-blue-100 text-blue-900'],
                                'picked_up' => ['Qo\'lingizda', 'bg-indigo-100 text-indigo-900'],
                                'in_transit' => ["Yo'lda", 'bg-amber-100 text-amber-900'],
                                'out_for_delivery' => ["Yo'lda", 'bg-amber-100 text-amber-900'],
                                'delivered' => ['Topshirildi', 'bg-emerald-100 text-emerald-900'],
                                'failed' => ["Yetkazib bo'lmadi", 'bg-rose-100 text-rose-900'],
                            ];
                            $inTransitShipmentsCount = $courierActiveShipments->filter(fn($s) => in_array($s->status, ['in_transit', 'out_for_delivery']))->count();
                            $pickupShipmentsCount = $courierActiveShipments->filter(fn($s) => $s->status === 'assigned')->count();
                            $inHandShipmentsCount = $courierActiveShipments->filter(fn($s) => $s->status === 'picked_up')->count();
                            $codShipmentsCount = $courierActiveShipments->filter(fn($s) => $s->suborder?->order?->latest_payment?->payment_method === 'cash_on_delivery')->count();
                            $failedShipmentsCount = $courierActiveShipments->filter(fn($s) => $s->status === 'failed')->count();
                        @endphp

                        <div x-data="{
                            courierSearch: '',
                            courierFilter: 'all',
                            matchesCourier(tracking, suborder, sellerName, dropName, dropPhone, dropAddress, status, isCOD) {
                                if (this.courierSearch.trim() !== '') {
                                    const q = this.courierSearch.toLowerCase().trim();
                                    const text = (tracking + ' ' + suborder + ' ' + (sellerName || '') + ' ' + (dropName || '') + ' ' + (dropPhone || '') + ' ' + (dropAddress || '')).toLowerCase();
                                    if (!text.includes(q)) return false;
                                }
                                if (this.courierFilter === 'in_transit' && status !== 'in_transit' && status !== 'out_for_delivery') return false;
                                if (this.courierFilter === 'pickup' && status !== 'assigned') return false;
                                if (this.courierFilter === 'in_hand' && status !== 'picked_up') return false;
                                if (this.courierFilter === 'cod' && !isCOD) return false;
                                if (this.courierFilter === 'failed' && status !== 'failed') return false;
                                return true;
                            }
                        }" class="space-y-4">

                            @if($courierActiveShipments->isNotEmpty())
                                <!-- Courier Search & Filter Toolbar -->
                                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-3 sm:p-4 space-y-3 shadow-xs">
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">🔍</span>
                                        <input type="text" x-model="courierSearch" placeholder="Tracking raqami, xaridor ismi, manzil, telefon yoki do'kon nomi bo'yicha qidirish..." 
                                               class="w-full h-11 pl-10 pr-9 rounded-2xl border border-[#E8ECE4] bg-[#FAFBF7] focus:bg-white text-xs placeholder:text-slate-400 focus:border-[#123C2F] outline-none">
                                        <button type="button" x-show="courierSearch.length > 0" @click="courierSearch = ''" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 text-xs font-bold">✕</button>
                                    </div>

                                    <div class="flex gap-2 overflow-x-auto no-scrollbar text-xs pt-2 border-t border-[#E8ECE4]">
                                        <button type="button" @click="courierFilter = 'all'" :class="courierFilter === 'all' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                            Barcha Faol ({{ $courierActiveShipments->count() }})
                                        </button>
                                        <button type="button" @click="courierFilter = 'in_transit'" :class="courierFilter === 'in_transit' ? 'bg-amber-600 text-white border-amber-600' : 'bg-amber-50 text-amber-900 border-amber-200'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition flex items-center gap-1.5">
                                            <span>🔥 Bugun topshirish kerak / Yo'lda</span>
                                            <span class="px-1.5 py-0.2 rounded-full bg-white text-amber-800 text-[10px] font-black">{{ $inTransitShipmentsCount }}</span>
                                        </button>
                                        <button type="button" @click="courierFilter = 'pickup'" :class="courierFilter === 'pickup' ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-indigo-50 text-indigo-900 border-indigo-200'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition flex items-center gap-1.5">
                                            <span>🏬 Do'kondan olish kerak</span>
                                            <span class="px-1.5 py-0.2 rounded-full bg-white text-indigo-800 text-[10px] font-black">{{ $pickupShipmentsCount }}</span>
                                        </button>
                                        <button type="button" @click="courierFilter = 'in_hand'" :class="courierFilter === 'in_hand' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                            📦 Qo'limda ({{ $inHandShipmentsCount }})
                                        </button>
                                        @if($codShipmentsCount > 0)
                                            <button type="button" @click="courierFilter = 'cod'" :class="courierFilter === 'cod' ? 'bg-amber-600 text-white border-amber-600' : 'bg-amber-50 text-amber-950 border-amber-300'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                                💵 Naqd pul (COD) ({{ $codShipmentsCount }})
                                            </button>
                                        @endif
                                        @if($failedShipmentsCount > 0)
                                            <button type="button" @click="courierFilter = 'failed'" :class="courierFilter === 'failed' ? 'bg-rose-600 text-white border-rose-600' : 'bg-rose-50 text-rose-900 border-rose-200'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                                ⚠️ Qayta urinish ({{ $failedShipmentsCount }})
                                            </button>
                                        @endif

                                        <button type="button" x-show="courierSearch !== '' || courierFilter !== 'all'" 
                                                @click="courierSearch = ''; courierFilter = 'all'" 
                                                class="ml-auto text-[11px] font-bold text-rose-600 hover:text-rose-800 whitespace-nowrap self-center underline">
                                            Tozalash ✕
                                        </button>
                                    </div>
                                </div>
                            @endif

                            <div class="space-y-5">
                                @forelse($courierActiveShipments as $sh)
                                @php
                                    $isCOD = $sh->suborder?->order?->latest_payment?->payment_method === 'cash_on_delivery';
                                    $codAmount = $sh->suborder?->total_amount ?? 0;
                                    $drop = is_array($sh->delivery_address) ? $sh->delivery_address : [];
                                    $dropName = $drop['recipient_name'] ?? $drop['recipient'] ?? ($sh->suborder?->order?->user?->name ?? 'Xaridor');
                                    $dropPhone = $drop['phone'] ?? ($sh->suborder?->order?->user?->phone ?? null);
                                    $dropAddress = collect([$drop['region'] ?? null, $drop['city'] ?? null, $drop['address_line'] ?? ($drop['address'] ?? null)])->filter()->implode(', ');
                                    $mapQuery = !empty($drop['latitude']) && !empty($drop['longitude'])
                                        ? $drop['latitude'] . ',' . $drop['longitude']
                                        : $dropAddress;
                                    $pickupAddress = $sh->pickup_address['address'] ?? ($sh->suborder?->seller?->address ?? null);
                                    $pickupPhone = $sh->pickup_address['phone'] ?? ($sh->suborder?->seller?->phone ?? null);
                                    $orderNotes = $sh->suborder?->order?->notes;
                                @endphp

                                <div x-show="matchesCourier('{{ addslashes($sh->tracking_number) }}', '{{ addslashes($sh->suborder?->suborder_number ?? '') }}', '{{ addslashes($sh->suborder?->seller?->name ?? '') }}', '{{ addslashes($dropName) }}', '{{ addslashes($dropPhone ?? '') }}', '{{ addslashes($dropAddress) }}', '{{ $sh->status }}', {{ $isCOD ? 'true' : 'false' }})"
                                     class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs hover:border-[#123C2F]/30 transition-all p-5 space-y-4">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#E8ECE4]">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="font-mono font-black text-sm text-[#172326]">{{ $sh->tracking_number }}</span>
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $shipLabels[$sh->status][1] ?? 'bg-slate-100 text-slate-800' }}">
                                                    {{ $shipLabels[$sh->status][0] ?? $sh->status }}
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
                                                <div class="text-[#64748B] text-[11px]">{{ $pickupAddress ?? "Manzil ko'rsatilmagan — do'konga qo'ng'iroq qiling" }}</div>
                                            </div>
                                            <div class="pt-1 grid grid-cols-2 gap-2">
                                                @if($pickupPhone)
                                                    <a href="tel:{{ $pickupPhone }}" class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white text-[#123C2F] border border-emerald-300 font-bold text-xs hover:bg-[#C9F46B] transition">
                                                        📞 Qo'ng'iroq
                                                    </a>
                                                @endif
                                                @if($pickupAddress)
                                                    <a href="https://yandex.uz/maps/?text={{ urlencode($pickupAddress) }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white text-[#123C2F] border border-emerald-300 font-bold text-xs hover:bg-[#C9F46B] transition">
                                                        🗺️ Xaritada
                                                    </a>
                                                @endif
                                            </div>
                                            @if($sh->suborder?->items?->isNotEmpty())
                                                <div class="pt-1 text-[11px] text-[#172326]">
                                                    <span class="text-[#64748B]">Olinadigan tovarlar:</span>
                                                    @foreach($sh->suborder->items as $it)
                                                        <b>{{ $it->product_name }}</b> ×{{ (float) $it->quantity }}@if(!$loop->last), @endif
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Step 2: Dropoff Location -->
                                        <div class="p-4 rounded-2xl bg-blue-50/40 border border-blue-200/60 space-y-2">
                                            <span class="text-[10px] font-black uppercase tracking-wider text-blue-900 flex items-center gap-1">
                                                <span>🏠</span> 2. XARIDORGA TOPSHIRISH (DROPOFF)
                                            </span>
                                            <div>
                                                <div class="font-extrabold text-[#172326] text-sm">{{ $dropName }}</div>
                                                <div class="text-[#64748B] text-[11px]">{{ $dropAddress ?: "Manzil ko'rsatilmagan" }}</div>
                                            </div>
                                            <div class="pt-1 grid grid-cols-2 gap-2">
                                                @if($dropPhone)
                                                    <a href="tel:{{ $dropPhone }}" class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white text-blue-900 border border-blue-300 font-bold text-xs hover:bg-blue-100 transition">
                                                        📞 {{ $dropPhone }}
                                                    </a>
                                                @endif
                                                @if($mapQuery)
                                                    <a href="https://yandex.uz/maps/?text={{ urlencode($mapQuery) }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white text-blue-900 border border-blue-300 font-bold text-xs hover:bg-blue-100 transition">
                                                        🗺️ Xaritada
                                                    </a>
                                                @endif
                                            </div>
                                            @if($orderNotes)
                                                <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-950">
                                                    <b>Mijoz izohi:</b> {{ $orderNotes }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Courier Action Buttons -->
                                    <div class="pt-3 border-t border-[#E8ECE4] space-y-2">
                                        @if($sh->status === 'failed' && $sh->delay_reason)
                                            <div class="text-[11px] text-rose-700">Oldingi urinish: {{ $sh->delay_reason }}</div>
                                        @endif

                                        <form action="{{ route('profile.courier.shipments.status', $sh->id) }}" method="POST" class="grid gap-2 sm:flex sm:flex-wrap sm:items-center">
                                            @csrf
                                            @if($sh->status === 'assigned')
                                                <button type="submit" name="status" value="picked_up" class="w-full sm:w-auto px-5 py-3.5 sm:py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm sm:text-xs transition shadow-xs">
                                                    📦 Do'kondan oldim
                                                </button>
                                            @elseif($sh->status === 'picked_up' || $sh->status === 'failed')
                                                <button type="submit" name="status" value="in_transit" class="w-full sm:w-auto px-5 py-3.5 sm:py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm sm:text-xs transition shadow-xs">
                                                    🚚 {{ $sh->status === 'failed' ? "Qayta yo'lga chiqdim" : "Yo'lga chiqdim" }}
                                                </button>
                                            @elseif($sh->status === 'in_transit' || $sh->status === 'out_for_delivery')
                                                <button type="submit" name="status" value="delivered"
                                                        @if($isCOD) onclick="return confirm('Xaridordan {{ number_format($codAmount, 0, '.', ' ') }} UZS naqd oldingizmi?')" @endif
                                                        class="w-full sm:w-auto px-5 py-3.5 sm:py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm sm:text-xs transition shadow-xs">
                                                    ✓ Topshirdim{{ $isCOD ? ' va ' . number_format($codAmount, 0, '.', ' ') . ' UZS oldim' : '' }}
                                                </button>
                                            @endif
                                        </form>

                                        @if(in_array($sh->status, ['picked_up', 'in_transit', 'out_for_delivery'], true))
                                            <details class="text-xs">
                                                <summary class="cursor-pointer text-rose-700 font-bold py-1">Yetkazib bo'lmadimi?</summary>
                                                <form action="{{ route('profile.courier.shipments.status', $sh->id) }}" method="POST" class="mt-2 grid gap-2 sm:flex sm:items-center">
                                                    @csrf
                                                    <input type="hidden" name="status" value="failed">
                                                    <select name="delay_reason" required class="px-3 py-2.5 rounded-xl border border-rose-300 bg-white text-xs">
                                                        <option value="">Sababni tanlang</option>
                                                        <option>Mijoz telefonga javob bermadi</option>
                                                        <option>Mijoz manzilda yo'q</option>
                                                        <option>Manzil noto'g'ri yoki topilmadi</option>
                                                        <option>Mijoz qabul qilishdan bosh tortdi</option>
                                                        <option>Mijoz boshqa vaqtni so'radi</option>
                                                    </select>
                                                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 font-bold text-xs">Belgilash</button>
                                                </form>
                                            </details>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-12 text-center shadow-xs">
                                    <div class="text-3xl mb-2">🛵</div>
                                    <h3 class="font-extrabold text-base text-[#172326]">Hozircha faol yetkazishlar yo'q</h3>
                                    <p class="text-xs text-[#64748B]">Administrator yoki do'konlar sizga yangi buyurtmalarni biriktirganda shu yerda ko'rinadi.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                        @if($courierFinishedShipments->isNotEmpty())
                            <details class="bg-white rounded-3xl border border-[#E8ECE4] p-5">
                                <summary class="cursor-pointer font-extrabold text-sm text-[#172326]">
                                    Yakunlangan va bekor qilingan ({{ $courierFinishedShipments->count() }})
                                </summary>
                                <div class="mt-3 divide-y divide-[#E8ECE4] text-xs">
                                    @foreach($courierFinishedShipments as $sh)
                                        @php $cancelled = $sh->suborder?->order?->status === 'cancelled' || $sh->suborder?->status === 'cancelled'; @endphp
                                        <div class="py-2.5 flex flex-wrap items-center justify-between gap-2">
                                            <span class="font-mono font-bold text-[#172326]">{{ $sh->tracking_number }}</span>
                                            <span class="text-[#64748B]">{{ $sh->suborder?->seller?->name }}</span>
                                            @if($cancelled)
                                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px]">Bekor qilingan — tovarni olmang</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-900 font-bold text-[10px]">Topshirildi {{ $sh->delivered_at?->format('d.m H:i') }}</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </div>
                @endif

                {{-- =========================================================================
                     CUSTOMER STANDARD PANELS (Always available)
                     ========================================================================= --}}

                <!-- TAB 1: BUYURTMALARIM (Customer Orders) -->
                <div x-show="activeTab === 'orders'" class="space-y-6">
                    @php
                        $inTransitOrdersCount = $orders->filter(fn($o) => 
                            in_array($o->status, ['pending_payment', 'processing', 'awaiting_payment']) || 
                            $o->suborders->flatMap->shipments->contains(fn($s) => in_array($s->status, ['in_transit', 'out_for_delivery']))
                        )->count();
                        $deliveredOrdersCount = $orders->filter(fn($o) => 
                            in_array($o->status, ['completed', 'delivered']) || 
                            $o->suborders->flatMap->shipments->contains(fn($s) => $s->status === 'delivered')
                        )->count();
                        $cancelledOrdersCount = $orders->filter(fn($o) => $o->status === 'cancelled')->count();
                    @endphp

                    <div x-data="{
                        custSearch: '',
                        custTab: 'all',
                        matchesCust(orderNumber, itemsText, status, isInTransit, isDelivered) {
                            if (this.custSearch.trim() !== '') {
                                const q = this.custSearch.toLowerCase().trim();
                                const full = (orderNumber + ' ' + (itemsText || '')).toLowerCase();
                                if (!full.includes(q)) return false;
                            }
                            if (this.custTab === 'in_transit' && !isInTransit && !['processing', 'pending_payment'].includes(status)) return false;
                            if (this.custTab === 'delivered' && !isDelivered) return false;
                            if (this.custTab === 'cancelled' && status !== 'cancelled') return false;
                            return true;
                        }
                    }" class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-black text-[#172326]">Mening Buyurtmalarim</h2>
                                <p class="text-xs text-[#64748B]">Barcha xaridlaringiz, tovarlar tarkibi va ularning yetkazilish holati</p>
                            </div>
                            <a href="{{ route('orders.hub') }}" class="text-xs font-bold text-[#123C2F] hover:underline flex items-center gap-1">
                                <span>To'liq Ta'minot Markazi</span>
                                <span>→</span>
                            </a>
                        </div>

                        @if($orders->isNotEmpty())
                            <!-- Customer Orders Search & Filter Toolbar -->
                            <div class="bg-white rounded-3xl border border-[#E8ECE4] p-3 sm:p-4 space-y-3 shadow-xs">
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">🔍</span>
                                    <input type="text" x-model="custSearch" placeholder="Buyurtma raqami yoki tovar nomi bo'yicha qidirish..." 
                                           class="w-full h-11 pl-10 pr-9 rounded-2xl border border-[#E8ECE4] bg-[#FAFBF7] focus:bg-white text-xs placeholder:text-slate-400 focus:border-[#123C2F] outline-none">
                                    <button type="button" x-show="custSearch.length > 0" @click="custSearch = ''" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-700 text-xs font-bold">✕</button>
                                </div>

                                <div class="flex gap-2 overflow-x-auto no-scrollbar text-xs pt-2 border-t border-[#E8ECE4]">
                                    <button type="button" @click="custTab = 'all'" :class="custTab === 'all' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                        Barchasi ({{ $orders->count() }})
                                    </button>
                                    <button type="button" @click="custTab = 'in_transit'" :class="custTab === 'in_transit' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition flex items-center gap-1.5">
                                        <span>🚚 Yo'lda / Kutilmoqda</span>
                                        <span class="px-1.5 py-0.2 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black">{{ $inTransitOrdersCount }}</span>
                                    </button>
                                    <button type="button" @click="custTab = 'delivered'" :class="custTab === 'delivered' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                        ✅ Qabul qilingan ({{ $deliveredOrdersCount }})
                                    </button>
                                    @if($cancelledOrdersCount > 0)
                                        <button type="button" @click="custTab = 'cancelled'" :class="custTab === 'cancelled' ? 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]' : 'bg-[#FAFBF7] text-[#172326] border-[#E8ECE4]'" class="px-3.5 py-1.5 rounded-xl border font-bold whitespace-nowrap transition">
                                            ❌ Bekor qilingan ({{ $cancelledOrdersCount }})
                                        </button>
                                    @endif

                                    <button type="button" x-show="custSearch !== '' || custTab !== 'all'" 
                                            @click="custSearch = ''; custTab = 'all'" 
                                            class="ml-auto text-[11px] font-bold text-rose-600 hover:text-rose-800 whitespace-nowrap self-center underline">
                                        Tozalash ✕
                                    </button>
                                </div>
                            </div>
                        @endif

                        @forelse($orders as $order)
                            @php
                                $shipment = $order->suborders->flatMap->shipments->first();
                                $isInTransit = $shipment && in_array($shipment->status, ['in_transit', 'out_for_delivery']);
                                $isDelivered = $order->status === 'completed' || ($shipment && $shipment->status === 'delivered');
                                $orderItemsText = $order->suborders->flatMap->items->pluck('product_name')->implode(' ');
                            @endphp

                            <div x-show="matchesCust('{{ addslashes($order->order_number) }}', '{{ addslashes($orderItemsText) }}', '{{ $order->status }}', {{ $isInTransit ? 'true' : 'false' }}, {{ $isDelivered ? 'true' : 'false' }})"
                                 class="bg-white rounded-3xl border border-[#E8ECE4] overflow-hidden shadow-xs hover:border-[#123C2F]/30 transition-all">
                            
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
                                            <span class="font-bold text-[#172326]">🏢 Sotuvchi: {{ $sub->seller?->name ?? 'Do\'kon' }}</span>
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
                                        <p class="font-semibold text-[#172326]">{{ is_array($order->shipping_address) ? ($order->shipping_address['address_line'] ?? $order->shipping_address['full_address'] ?? 'Mustaqillik shoh ko\'chasi, 105-uy') : ($order->shipping_address ?: 'Mustaqillik shoh ko\'chasi, 105-uy') }}</p>
                                    </div>

                                    @if($shipment)
                                        <div class="flex items-center gap-3">
                                            <div class="text-right">
                                                <span class="text-[10px] font-bold uppercase text-[#64748B] block">Qabul qilish kodi (OTP):</span>
                                                <span class="font-mono font-black text-sm text-[#123C2F] bg-white px-2.5 py-0.5 rounded-lg border border-[#E8ECE4] tracking-wider">{{ $shipment->delivery_otp ?: $shipment->ensureDeliveryOtp() }}</span>
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

                    <form action="{{ route('profile.seller.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label class="block font-bold text-[#172326] mb-1">Mahsulot Nomi *</label>
                            <input type="text" name="name" required placeholder="Masalan: M-500 Sement 50kg yoki Alyumin profil" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">Kategoriya *</label>
                                <select name="category_id" required class="w-full h-11 px-3 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none font-medium">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">Savdo Turi *</label>
                                <select name="trade_mode" required class="w-full h-11 px-3 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none font-medium">
                                    <option value="both">Chakana va Ulgurji (Har ikkisi)</option>
                                    <option value="retail_only">Faqat Chakana (Retail)</option>
                                    <option value="wholesale_only">Faqat Ulgurji (Optom)</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">Birlik *</label>
                                <select name="unit" required class="w-full h-11 px-3 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none font-medium">
                                    <option value="piece">Dona (piece)</option>
                                    <option value="box">Quti (box)</option>
                                    <option value="kg">Kilogramm (kg)</option>
                                    <option value="ton">Tonna (ton)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">Birlik Narxi (UZS) *</label>
                                <input type="number" name="price" required min="100" step="100" placeholder="50000" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">Ombordagi Soni *</label>
                                <input type="number" name="stock_quantity" required min="0" step="1" placeholder="100" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">Minimal Buyurtma</label>
                                <input type="number" name="min_order_quantity" min="1" step="1" value="1" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">Sotish Qadami (Step)</label>
                                <input type="number" name="unit_step" min="0.001" step="any" value="1" placeholder="1" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">Qutidagi donalar soni</label>
                                <input type="number" name="items_per_box" min="1" step="1" placeholder="Masalan: 12" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">300+ Ulgurji Narx (UZS)</label>
                                <input type="number" name="tier_300_price" min="100" step="100" placeholder="Ixtiyoriy" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">1000+ Katta Ulgurji (UZS)</label>
                                <input type="number" name="tier_1000_price" min="100" step="100" placeholder="Ixtiyoriy" class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                            </div>
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">Artikul / SKU</label>
                                <input type="text" name="sku" maxlength="100" placeholder="Avtomatik yoki SKU-..." class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">Rasm Yuklash</label>
                                <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="w-full h-11 px-2.5 py-1.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:bg-[#123C2F] file:text-white file:font-bold">
                            </div>
                            <div>
                                <label class="block font-bold text-[#172326] mb-1">Yoki Rasm URL havolasi</label>
                                <input type="text" name="image_url" placeholder="https://... yoki /images/products/..." class="w-full h-11 px-3.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs focus:bg-white focus:border-[#123C2F] outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-[#172326] mb-1">Mahsulot Haqida Tavsif</label>
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
</div>
</x-layouts.app>
