<!DOCTYPE html>
<html lang="uz" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Marketplace' }} — Chakana va Ulgurji Savdo Platformasi</title>
    <meta name="description" content="O'zbekistondagi ko'p sotuvchili chakana va ulgurji marketplace. Sement, sanoat uskunalari, materiallar va iste'mol mollari to'g'ridan-to'g'ri birinchi qo'ldan.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS & JS Bundle with CDN Fallback -->
    <style>[x-cloak] { display: none !important; }</style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="/build/assets/app-D2FgvmjV.css">
    <script type="module" src="/build/assets/app-DbeYgy-K.js"></script>
</head>
<body class="min-h-full flex flex-col bg-[#FAFBF7] text-[#172326] antialiased selection:bg-[#C9F46B] selection:text-[#123C2F] overflow-x-hidden"
      x-data="{ 
          mobileMenuOpen: false, 
          isOnline: navigator.onLine,
          init() {
              window.addEventListener('online', () => this.isOnline = true);
              window.addEventListener('offline', () => this.isOnline = false);
          }
      }">

    <!-- Accessible Skip link -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:px-4 focus:py-2 focus:bg-[#123C2F] focus:text-white focus:rounded-xl">
        Asosiy mazmunga o'tish
    </a>

    <!-- Offline Notification Banner -->
    <div x-show="!isOnline" x-cloak class="bg-amber-500 text-white text-xs font-semibold py-2 px-4 text-center sticky top-0 z-50">
        ⚠️ Internet aloqasi uzildi. Offline rejimda faqat avval yuklangan sahifalar ko'rinadi.
    </div>

    <!-- Announcement Bar -->
    <div class="bg-[#123C2F] text-white text-xs py-2 px-4">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5 font-medium">
                    <span class="w-2 h-2 rounded-full bg-[#C9F46B] animate-pulse"></span>
                    O'zbekiston bo'ylab tezkor yetkazib berish
                </span>
                <span class="hidden md:inline text-white/40">|</span>
                <span class="hidden md:inline text-white/80">Chakana va ulgurji narxlar bitta platformada</span>
            </div>
            <div class="flex items-center gap-4 text-white/80">
                <a href="{{ route('tracking') }}" class="flex items-center gap-1.5 text-[#C9F46B] font-bold hover:underline transition-colors">
                    <span class="w-2 h-2 rounded-full bg-[#C9F46B] animate-ping"></span>
                    <span>📍 Jonli Xarita (Kuzatuv)</span>
                </a>
                <span class="text-white/40">|</span>
                <a href="tel:+998712000000" class="hover:text-[#C9F46B] transition-colors">Yordam: +998 (71) 200-00-00</a>
                <span class="text-white/40">|</span>
                <span class="font-medium text-[#C9F46B]">UZS</span>
            </div>
        </div>
    </div>

    <!-- Desktop & Tablet Header -->
    <header class="bg-white border-b border-[#E8ECE4] sticky top-0 z-40 transition-shadow duration-200">
        
        <!-- TOP MAIN BAR: Logo, Catalog, Search, and Action Icons -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-3 sm:gap-4">
                
                <!-- Brand Logo & Catalog Button -->
                <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                        <div class="w-11 h-11 rounded-2xl bg-[#123C2F] flex items-center justify-center text-white font-extrabold text-xl shadow-sm group-hover:bg-[#C9F46B] group-hover:text-[#123C2F] transition-all duration-200">
                            M
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl font-black tracking-tight text-[#172326] flex items-center gap-1">
                                MARKET
                                <span class="w-2 h-2 rounded-full bg-[#C9F46B]"></span>
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-[#64748B]">B2B & B2C Savdo</span>
                        </div>
                    </a>

                    <!-- Category Catalog Button (Desktop) -->
                    <a href="{{ route('catalog') }}" class="hidden lg:flex items-center gap-2 px-4 py-2.5 rounded-2xl bg-[#C9F46B] text-[#123C2F] font-extrabold text-xs hover:bg-[#b8e855] transition-all shadow-xs whitespace-nowrap" title="Katalog">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <span>Katalog</span>
                    </a>
                </div>

                <!-- Central Search Bar (Expanded Width & Clean Layout) -->
                <div class="flex-1 max-w-2xl xl:max-w-3xl hidden md:block mx-2">
                    <form action="{{ route('catalog') }}" method="GET" class="relative">
                        @if(request('trade_mode'))
                            <input type="hidden" name="trade_mode" value="{{ request('trade_mode') }}">
                        @endif
                        @if(request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        <input type="search" 
                               name="q" 
                               value="{{ request('q') }}"
                               placeholder="Mahsulot, SKU yoki toifani qidiring (masalan: sement, gipsokarton, profil)..." 
                               class="w-full h-11 pl-11 pr-24 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs font-medium text-[#172326] placeholder-[#64748B] focus:bg-white focus:border-[#123C2F] focus:ring-2 focus:ring-[#123C2F]/10 transition-all outline-none">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#64748B]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <button type="submit" class="absolute inset-y-1 right-1 px-4 rounded-xl bg-[#123C2F] text-white font-bold text-xs hover:bg-[#0d2a21] transition-colors flex items-center gap-1">
                            Qidirish
                        </button>
                    </form>
                </div>

                <!-- Right Nav Actions (Single Row, Matched 40px Height, Whitespace-Nowrap) -->
                <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                    
                    <!-- Wishlist (Sevimlilar) -->
                    <a href="{{ route('wishlist') }}" class="relative p-2.5 rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] hover:border-[#123C2F] text-[#172326] transition-all" title="Sevimlilar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                        <span x-data x-show="$store.wishlist.count() > 0" x-text="$store.wishlist.count()" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-[#123C2F] text-white font-black text-[9px] flex items-center justify-center">0</span>
                    </a>

                    <!-- Cart (Savatcha) -->
                    <a href="{{ route('cart') }}" class="relative flex items-center gap-1.5 px-3 py-2.5 rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] hover:border-[#123C2F] transition-all text-xs font-bold text-[#172326] whitespace-nowrap" title="Savatcha">
                        <svg class="w-4 h-4 text-[#123C2F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span class="hidden xl:inline">Savatcha</span>
                    </a>

                    <!-- Orders Hub Link (Buyurtmalar & Xaridlar) -->
                    <a href="{{ route('orders.hub') }}" class="relative flex items-center gap-1.5 px-3 py-2.5 rounded-2xl bg-[#FAFBF7] hover:bg-white border border-[#E8ECE4] hover:border-[#123C2F] text-xs font-bold text-[#172326] transition-all whitespace-nowrap" title="Buyurtmalar va Yetkazib Berishlar">
                        <span class="text-sm">📦</span>
                        <span class="hidden xl:inline">Buyurtmalar</span>
                    </a>

                    <!-- Account / Auth / Profil - Mening profilim -->
                    @auth
                        <div class="flex items-center gap-1.5 pl-1">
                            @if(auth()->user()->hasRole(['admin', 'moderator']))
                                <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold hover:bg-rose-100 transition whitespace-nowrap">
                                    Admin
                                </a>
                            @elseif(auth()->user()->hasRole(['seller_owner', 'seller_manager', 'seller_operator']))
                                <a href="{{ route('seller.dashboard') }}" class="px-2.5 py-1.5 rounded-xl bg-[#C9F46B]/20 text-[#123C2F] border border-[#C9F46B]/40 text-xs font-bold hover:bg-[#C9F46B]/30 transition whitespace-nowrap">
                                    Do'kon
                                </a>
                            @endif

                            <a href="{{ route('profile') }}" class="flex items-center gap-1.5 px-3.5 py-2 rounded-2xl bg-[#123C2F] text-[#C9F46B] hover:bg-[#0d2a21] transition-all text-xs font-bold shadow-xs whitespace-nowrap" title="Profil — Mening profilim">
                                <span class="w-5 h-5 rounded-full bg-white/20 text-white flex items-center justify-center text-[10px] font-black">
                                    👤
                                </span>
                                <span>Profil</span>
                            </a>

                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 rounded-2xl bg-[#FAFBF7] hover:bg-red-50 text-[#64748B] hover:text-red-600 border border-[#E8ECE4] transition" title="Tizimdan chiqish">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('profile') }}" class="px-3 py-2 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] hover:border-[#123C2F] text-xs font-bold text-[#172326] flex items-center gap-1.5 transition whitespace-nowrap" title="Profil — Mening profilim">
                                <span>👤</span>
                                <span>Profil</span>
                            </a>
                            <a href="{{ route('login') }}" class="px-3.5 py-2 rounded-2xl bg-[#123C2F] text-white font-bold text-xs hover:bg-[#0d2a21] transition whitespace-nowrap">
                                Kirish
                            </a>
                        </div>
                    @endauth

                </div>
            </div>

            <!-- Mobile Search Bar (under top bar) -->
            <div class="py-2.5 md:hidden">
                <form action="{{ route('catalog') }}" method="GET" class="relative">
                    <input type="search" 
                           name="q" 
                           value="{{ request('q') }}"
                           placeholder="Mahsulot yoki toifani qidiring..." 
                           class="w-full h-10 pl-10 pr-20 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs text-[#172326] placeholder-[#64748B] focus:bg-white focus:border-[#123C2F] outline-none">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-[#64748B]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <button type="submit" class="absolute inset-y-1 right-1 px-3 rounded-lg bg-[#123C2F] text-white font-bold text-xs">
                        Topish
                    </button>
                </form>
            </div>

        </div>

        <!-- TIER 2: DEDICATED CATEGORIES & NAVIGATION RIBBON -->
        <div class="border-t border-[#E8ECE4] bg-white hidden md:block">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-12 text-xs">
                    
                    <!-- Left: Bosh sahifa + Main Category Pills -->
                    <nav class="flex items-center gap-1 overflow-x-auto no-scrollbar py-1">
                        
                        <!-- Bosh sahifa Tab -->
                        <a href="{{ route('home') }}" 
                           class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl transition font-extrabold whitespace-nowrap {{ request()->routeIs('home') ? 'bg-[#123C2F] text-white shadow-xs' : 'bg-[#FAFBF7] text-[#172326] hover:bg-[#E8ECE4] border border-[#E8ECE4]' }}">
                            <span>🏠</span>
                            <span>Bosh sahifa</span>
                        </a>

                        <div class="h-4 w-px bg-[#E8ECE4] mx-1"></div>

                        <!-- Real Catalog Categories -->
                        <a href="{{ route('catalog', ['category' => 'qurilish-materiallari']) }}" 
                           class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('category') === 'qurilish-materiallari' ? 'bg-[#123C2F] text-white' : 'text-[#475569] hover:text-[#123C2F] hover:bg-[#FAFBF7]' }}">
                            🧱 Qurilish mollari
                        </a>

                        <a href="{{ route('catalog', ['category' => 'sanoat-uskunalari']) }}" 
                           class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('category') === 'sanoat-uskunalari' ? 'bg-[#123C2F] text-white' : 'text-[#475569] hover:text-[#123C2F] hover:bg-[#FAFBF7]' }}">
                            ⚙️ Sanoat uskunalari
                        </a>

                        <a href="{{ route('catalog', ['category' => 'elektr-jihozlari']) }}" 
                           class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('category') === 'elektr-jihozlari' ? 'bg-[#123C2F] text-white' : 'text-[#475569] hover:text-[#123C2F] hover:bg-[#FAFBF7]' }}">
                            ⚡ Elektr & Yoritish
                        </a>

                        <a href="{{ route('catalog', ['category' => 'santexnika-va-quvurlar']) }}" 
                           class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('category') === 'santexnika-va-quvurlar' ? 'bg-[#123C2F] text-white' : 'text-[#475569] hover:text-[#123C2F] hover:bg-[#FAFBF7]' }}">
                            🚰 Santexnika
                        </a>

                        <a href="{{ route('catalog', ['category' => 'boyoqlar-va-kimyo']) }}" 
                           class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('category') === 'boyoqlar-va-kimyo' ? 'bg-[#123C2F] text-white' : 'text-[#475569] hover:text-[#123C2F] hover:bg-[#FAFBF7]' }}">
                            🎨 Bo'yoqlar
                        </a>

                        <a href="{{ route('catalog', ['category' => 'asbob-uskunalar']) }}" 
                           class="px-3 py-1.5 rounded-xl font-bold transition whitespace-nowrap {{ request('category') === 'asbob-uskunalar' ? 'bg-[#123C2F] text-white' : 'text-[#475569] hover:text-[#123C2F] hover:bg-[#FAFBF7]' }}">
                            🔧 Asbob-uskunalar
                        </a>
                    </nav>

                    <!-- Right: Trade Mode Filter (Chakana / Optom) & Live Map Link -->
                    <div class="flex items-center gap-3 shrink-0 pl-3">
                        
                        <!-- Trade Mode Segmented Badge -->
                        <div class="flex items-center p-0.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-[11px] font-bold">
                            <a href="{{ route('catalog', array_merge(request()->query(), ['trade_mode' => 'all'])) }}" 
                               class="px-2.5 py-1 rounded-lg transition {{ request('trade_mode', 'all') === 'all' ? 'bg-[#123C2F] text-white' : 'text-[#64748B] hover:text-[#172326]' }}">
                                Barchasi
                            </a>
                            <a href="{{ route('catalog', array_merge(request()->query(), ['trade_mode' => 'retail_only'])) }}" 
                               class="px-2.5 py-1 rounded-lg transition {{ request('trade_mode') === 'retail_only' ? 'bg-[#123C2F] text-white' : 'text-[#64748B] hover:text-[#172326]' }}">
                                Chakana
                            </a>
                            <a href="{{ route('catalog', array_merge(request()->query(), ['trade_mode' => 'wholesale_only'])) }}" 
                               class="px-2.5 py-1 rounded-lg transition {{ request('trade_mode') === 'wholesale_only' ? 'bg-[#123C2F] text-white' : 'text-[#64748B] hover:text-[#172326]' }}">
                                Optom
                            </a>
                        </div>

                        <!-- Live Map Link -->
                        <a href="{{ route('tracking') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 font-bold text-[11px] transition whitespace-nowrap">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                            <span>📍 Xarita</span>
                        </a>

                    </div>

                </div>
            </div>
        </div>

        </div>
    </header>

    <!-- Main Content Area -->
    <main id="main-content" class="flex-1 pb-24 md:pb-12">
        {{ $slot }}
    </main>

    <!-- Mobile Bottom Navigation Bar (Fixed) -->
    <nav aria-label="Mobil navigatsiya" class="md:hidden fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-[#E8ECE4] z-50 py-1.5 px-3">
        <div class="flex items-center justify-around">
            <a href="{{ route('home') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl {{ request()->routeIs('home') ? 'text-[#123C2F] font-bold' : 'text-[#64748B]' }}">
                <svg class="w-6 h-6" fill="{{ request()->routeIs('home') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="text-[11px]">Bosh sahifa</span>
            </a>

            <a href="{{ route('catalog') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl {{ request()->routeIs('catalog') ? 'text-[#123C2F] font-bold' : 'text-[#64748B]' }}">
                <svg class="w-6 h-6" fill="{{ request()->routeIs('catalog') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span class="text-[11px]">Katalog</span>
            </a>

            <a href="{{ route('wishlist') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl relative {{ request()->routeIs('wishlist') ? 'text-[#123C2F] font-bold' : 'text-[#64748B]' }}">
                <svg class="w-6 h-6" fill="{{ request()->routeIs('wishlist') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
                <span class="text-[11px]">Sevimlilar</span>
                <span x-data x-show="$store.wishlist.count() > 0" x-text="$store.wishlist.count()" class="absolute top-0 right-2 w-4 h-4 rounded-full bg-[#123C2F] text-white font-bold text-[9px] flex items-center justify-center">0</span>
            </a>

            <a href="{{ route('cart') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl text-[#64748B]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span class="text-[11px]">Savatcha</span>
            </a>

            <a href="{{ route('orders.hub') }}" class="flex flex-col items-center gap-1 py-1 px-2.5 rounded-xl {{ request()->routeIs('orders.*') ? 'text-[#123C2F] font-bold' : 'text-[#64748B]' }}">
                <span class="text-base">📦</span>
                <span class="text-[10px]">Buyurtmalar</span>
            </a>

            <a href="{{ route('profile') }}" class="flex flex-col items-center gap-1 py-1 px-3 rounded-xl {{ request()->routeIs('profile*') ? 'text-[#123C2F] font-bold' : 'text-[#64748B]' }}">
                <svg class="w-6 h-6" fill="{{ request()->routeIs('profile*') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="text-[11px] font-bold">Mening profilim</span>
            </a>
        </div>
    </nav>

    <!-- Footer -->
    <footer class="bg-white border-t border-[#E8ECE4] pt-14 pb-8 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 mb-12">
                
                <!-- Brand col -->
                <div class="col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-[#123C2F] flex items-center justify-center text-white font-bold text-lg">M</div>
                        <span class="text-xl font-black text-[#172326]">MARKET</span>
                    </div>
                    <p class="text-[#64748B] text-sm leading-relaxed max-w-sm mb-6">
                        O'zbekistondagi zamonaviy chakana va ulgurji savdo platformasi. Birinchi qo'ldan kafolatlangan mahsulotlar va xavfsiz hisob-kitob.
                    </p>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs font-semibold text-[#123C2F]">
                            ✓ Litsenziyalangan sotuvchilar
                        </span>
                        <span class="px-3 py-1.5 rounded-xl bg-[#FAFBF7] border border-[#E8ECE4] text-xs font-semibold text-[#123C2F]">
                            ✓ Haqiqiy GPS kuzatuv
                        </span>
                    </div>
                </div>

                <!-- Column 1 -->
                <div>
                    <h4 class="font-bold text-[#172326] mb-4 text-sm uppercase tracking-wider">Xaridorlarga</h4>
                    <ul class="space-y-2.5 text-[#64748B]">
                        <li><a href="{{ route('catalog') }}" class="hover:text-[#123C2F] transition-colors">Barcha mahsulotlar</a></li>
                        <li><a href="{{ route('catalog', ['trade_mode' => 'wholesale_only']) }}" class="hover:text-[#123C2F] transition-colors">Optom savdo</a></li>
                        <li><a href="{{ route('wishlist') }}" class="hover:text-[#123C2F] transition-colors">Sevimlilar</a></li>
                        <li><a href="{{ route('tracking') }}" class="hover:text-[#123C2F] transition-colors font-medium text-[#123C2F] flex items-center gap-1.5"><span>📍</span> Jonli yetkazib berish xaritasi</a></li>
                    </ul>
                </div>

                <!-- Column 2 -->
                <div>
                    <h4 class="font-bold text-[#172326] mb-4 text-sm uppercase tracking-wider">Sotuvchilarga</h4>
                    <ul class="space-y-2.5 text-[#64748B]">
                        <li><a href="{{ route('login') }}" class="hover:text-[#123C2F] transition-colors">Sotuvchi kabineti</a></li>
                        <li><a href="#" @click.prevent="alert('Sotuvchi arizasi 07-bosqichda to\'liq panel orqali boshqariladi')" class="hover:text-[#123C2F] transition-colors">Do'kon ochish</a></li>
                        <li><a href="#" class="hover:text-[#123C2F] transition-colors">Komissiya stavkalari</a></li>
                        <li><a href="#" class="hover:text-[#123C2F] transition-colors">Ombor xizmati</a></li>
                    </ul>
                </div>

                <!-- Column 3 -->
                <div>
                    <h4 class="font-bold text-[#172326] mb-4 text-sm uppercase tracking-wider">Platforma</h4>
                    <ul class="space-y-2.5 text-[#64748B]">
                        <li><a href="/health" class="hover:text-[#123C2F] transition-colors">Tizim holati (Health)</a></li>
                        <li><a href="/api/v1/health" class="hover:text-[#123C2F] transition-colors">API Endpoint</a></li>
                        <li><span class="text-xs text-[#64748B]">Versiya: 1.0 (Laravel 13 + PHP 8.4)</span></li>
                    </ul>
                </div>

            </div>

            <div class="pt-8 border-t border-[#E8ECE4] flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#64748B]">
                <p>© {{ date('Y') }} MARKET. Barcha huquqlar himoyalangan.</p>
                <div class="flex items-center gap-6">
                    <span>O'zbekiston, Toshkent</span>
                    <span>Maxfiylik siyosati</span>
                    <span>Foydalanish shartlari</span>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
