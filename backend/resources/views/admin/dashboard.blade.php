<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Boshqaruv Paneli - Marketplace</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased font-sans" x-data="{ currentTab: '{{ $initialTab ?? 'sellers' }}' }" x-init="$watch('currentTab', t => history.replaceState(null, '', '?tab=' + t))">

    <!-- Top Admin Header -->
    <header class="bg-slate-900 text-white border-b border-slate-800 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/" class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-lime-400 text-slate-950 font-black flex items-center justify-center text-base">M</span>
                    <span class="font-extrabold text-lg tracking-tight">MARKET</span>
                </a>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 uppercase tracking-wider">
                    🛡️ Superadmin & Admin Panel
                </span>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-300 hover:text-white transition flex items-center gap-1">
                    <span>🏠</span> <span>Marketplace</span>
                </a>
                <span class="text-slate-700">|</span>
                <a href="{{ route('profile', ['tab' => 'seller_products']) }}" class="text-xs font-semibold text-lime-300 hover:text-lime-200 transition flex items-center gap-1">
                    <span>💼</span> <span>Sotuvchi</span>
                </a>
                <span class="text-slate-700">|</span>
                <a href="{{ route('profile', ['tab' => 'courier_shipments']) }}" class="text-xs font-semibold text-amber-300 hover:text-amber-200 transition flex items-center gap-1">
                    <span>🛵</span> <span>Kuryer</span>
                </a>
                <div class="h-4 w-px bg-slate-700 mx-1"></div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-300 font-bold">{{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 transition">
                            Chiqish
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-sm font-medium flex items-center justify-between">
                <span>✓ {{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 text-sm font-medium flex items-center justify-between">
                <span>⚠️ {{ session('error') }}</span>
            </div>
        @endif
        @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-rose-50 text-rose-800 border border-rose-200 text-sm font-medium">
                @foreach($errors->all() as $error)
                    <div>⚠️ {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <!-- Summary Stats Grid (7 Key Metrics) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 mb-8">
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Xaridorlar</span>
                <span class="text-xl font-black text-slate-900">{{ number_format($stats['total_users']) }}</span>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Do'konlar</span>
                <span class="text-xl font-black text-slate-900">{{ number_format($stats['total_sellers']) }}</span>
                <span class="text-[10px] text-emerald-600 block">({{ $stats['active_sellers'] }} faol)</span>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Mahsulotlar</span>
                <span class="text-xl font-black text-slate-900">{{ number_format($stats['total_products']) }}</span>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Buyurtmalar</span>
                <span class="text-xl font-black text-slate-900">{{ number_format($stats['total_orders']) }}</span>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Kuryerlar</span>
                <span class="text-xl font-black text-amber-700">{{ number_format($stats['total_couriers']) }}</span>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Faol Yetkazish</span>
                <span class="text-xl font-black text-blue-700">{{ number_format($stats['active_shipments']) }}</span>
            </div>
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200 shadow-xs">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Jami Tushum</span>
                <span class="text-lg font-black text-emerald-600">{{ number_format($stats['total_revenue']) }} <span class="text-[9px] text-slate-400">UZS</span></span>
            </div>
        </div>

        <!-- Navigation Tabs & Fast Actions -->
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 mb-6 pb-2">
            <div class="flex flex-wrap items-center gap-2">
                <button @click="currentTab = 'analytics'" :class="currentTab === 'analytics' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <span>📈</span> <span>Savdo Analitikasi</span>
                </button>
                <button @click="currentTab = 'sellers'" :class="currentTab === 'sellers' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    💼 Sotuvchilar ({{ $sellers->count() }})
                </button>
                <button @click="currentTab = 'products'" :class="currentTab === 'products' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    📦 Mahsulotlar ({{ $products->total() }})
                </button>
                <button @click="currentTab = 'categories'" :class="currentTab === 'categories' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    🗂️ Kategoriyalar ({{ $categories->count() }})
                </button>
                <button @click="currentTab = 'marketing'" :class="currentTab === 'marketing' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    🏷️ Promokod & Banner
                </button>
                <button @click="currentTab = 'users'" :class="currentTab === 'users' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    👥 Foydalanuvchilar ({{ $users->total() }})
                </button>
                <button @click="currentTab = 'shipments'" :class="currentTab === 'shipments' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    🚚 Yetkazish ({{ $shipments->total() }})
                </button>
                <button @click="currentTab = 'orders'" :class="currentTab === 'orders' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    🛒 Buyurtmalar ({{ $recentOrders->count() }})
                </button>
                <button @click="currentTab = 'refunds'" :class="currentTab === 'refunds' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    ↩ Qaytarish
                    @if(($stats['open_refunds'] ?? 0) > 0)
                        <span class="ml-1 px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[10px]">{{ $stats['open_refunds'] }}</span>
                    @endif
                </button>
                @if($canManagePayouts)
                    <button @click="currentTab = 'payouts'" :class="currentTab === 'payouts' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                        💸 To'lov
                        @if(($stats['open_payouts'] ?? 0) > 0)
                            <span class="ml-1 px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[10px]">{{ $stats['open_payouts'] }}</span>
                        @endif
                    </button>
                    <button @click="currentTab = 'finance'" :class="currentTab === 'finance' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                        📊 Moliya
                    </button>
                @endif
                <button @click="currentTab = 'audit'" :class="currentTab === 'audit' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                    🔍 Audit
                </button>
                @if($canManageBackups)
                    <button @click="currentTab = 'backups'" :class="currentTab === 'backups' ? 'bg-slate-900 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-3.5 py-2 rounded-xl text-xs font-bold transition cursor-pointer">
                        💾 Zaxira
                    </button>
                @endif
            </div>

            <!-- Fast Excel / CSV Export Dropdown / Buttons -->
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.export.orders') }}" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold flex items-center gap-1.5 transition shadow-2xs" title="Barcha buyurtmalarni Excel CSV formatida yuklab olish">
                    <span>📊</span>
                    <span>Buyurtmalar Excel</span>
                </a>
                <a href="{{ route('admin.export.sellers') }}" class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-bold flex items-center gap-1.5 transition shadow-2xs" title="Sotuvchilar ro'yxati va balanslarini yuklab olish">
                    <span>📥</span>
                    <span>Sotuvchilar Excel</span>
                </a>
            </div>
        </div>

        <!-- TAB 1: SELLERS -->
        <div x-show="currentTab === 'sellers'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Barcha Ro'yxatdan O'tgan Sotuvchilar</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3.5">ID / Nomi</th>
                            <th class="p-3.5">Yuridik Nomi & STIR</th>
                            <th class="p-3.5">Telefon & Manzil</th>
                            <th class="p-3.5">Komissiya</th>
                            <th class="p-3.5">Reyting</th>
                            <th class="p-3.5">Holati</th>
                            <th class="p-3.5 text-right">Moderatsiya</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($sellers as $s)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3.5 font-bold text-slate-900">
                                    {{ $s->name }}
                                    <span class="block text-[11px] font-mono text-slate-500 font-normal">slug: {{ $s->slug }}</span>
                                </td>
                                <td class="p-3.5">
                                    {{ $s->legal_name ?? '—' }}
                                    <span class="block text-[11px] text-slate-500">STIR: {{ $s->tax_id ?? '—' }}</span>
                                </td>
                                <td class="p-3.5">
                                    {{ $s->phone ?? '—' }}
                                    <span class="block text-[11px] text-slate-500 max-w-xs truncate">{{ $s->address ?? '—' }}</span>
                                </td>
                                <td class="p-3.5 font-bold text-slate-800">{{ $s->commission_rate }}%</td>
                                <td class="p-3.5 font-bold text-amber-600">★ {{ number_format($s->rating, 1) }}</td>
                                <td class="p-3.5">
                                    @if($s->status === 'active')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wide">Faol</span>
                                    @elseif($s->status === 'pending')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase tracking-wide">Kutilmoqda</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 uppercase tracking-wide">To'xtatilgan</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right space-x-1">
                                    @if($s->status !== 'active')
                                        <form action="{{ route('admin.sellers.status', $s->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="active">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px]">
                                                Tasdiqlash
                                            </button>
                                        </form>
                                    @endif
                                    @if($s->status !== 'suspended')
                                        <form action="{{ route('admin.sellers.status', $s->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="suspended">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px]">
                                                To'xtatish
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: PRODUCTS -->
        <div x-show="currentTab === 'products'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Barcha Mahsulotlar Moderatsiyasi va Nazorati</h3>
                <span class="text-xs text-slate-500">Jami: {{ $products->total() }} ta mahsulot</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3.5">Mahsulot / Toifa</th>
                            <th class="p-3.5">Sotuvchi Do'kon</th>
                            <th class="p-3.5">Savdo Rejimi</th>
                            <th class="p-3.5">Birligi & Qoldiq</th>
                            <th class="p-3.5">Boshlang'ich Narx</th>
                            <th class="p-3.5">Holati</th>
                            <th class="p-3.5 text-right">Moderatsiya</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($products as $prod)
                            @php
                                $firstVar = $prod->variants->first();
                                $firstTier = $firstVar ? $firstVar->priceTiers->first() : null;
                                $totalStock = $prod->variants->flatMap->inventoryBalances->sum('quantity');
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl bg-slate-100 overflow-hidden border border-slate-200 flex-shrink-0 flex items-center justify-center">
                                            @if($prod->image_url)
                                                <img src="{{ $prod->image_url }}" alt="{{ $prod->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-[9px] text-slate-400">Rasm yo'q</span>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('product.show', $prod->slug) }}" target="_blank" class="font-bold text-slate-900 hover:text-emerald-700 hover:underline">
                                                {{ $prod->name }}
                                            </a>
                                            <span class="block text-[11px] text-slate-500 font-normal">Toifa: {{ $prod->category->name ?? '—' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3.5">
                                    <span class="font-semibold text-slate-800">{{ $prod->seller->name ?? '—' }}</span>
                                    <span class="block text-[10px] text-slate-400">ID: {{ $prod->seller_id }}</span>
                                </td>
                                <td class="p-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $prod->trade_mode === 'both' ? 'bg-purple-100 text-purple-800' : ($prod->trade_mode === 'wholesale_only' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') }}">
                                        {{ $prod->trade_mode }}
                                    </span>
                                </td>
                                <td class="p-3.5">
                                    <span class="font-bold text-slate-900">{{ number_format($totalStock) }} {{ $prod->unit }}</span>
                                    <span class="block text-[10px] text-slate-400">MOQ: {{ $prod->min_order_quantity }}</span>
                                </td>
                                <td class="p-3.5 font-bold text-slate-900">
                                    @if($firstTier)
                                        {{ number_format($firstTier->unit_price) }} UZS
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="p-3.5">
                                    @if($prod->status === 'approved')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">Tasdiqlangan</span>
                                    @elseif($prod->status === 'pending')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 uppercase">Kutilmoqda</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 uppercase">Rad etilgan</span>
                                    @endif
                                    @if(!$prod->is_active)
                                        <span class="block text-[10px] text-rose-500 font-bold mt-0.5">(Nofaol)</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right space-x-1">
                                    @if($prod->status !== 'approved')
                                        <form action="{{ route('admin.products.status', $prod->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px]">
                                                Tasdiqlash
                                            </button>
                                        </form>
                                    @endif
                                    @if($prod->status !== 'rejected')
                                        <form action="{{ route('admin.products.status', $prod->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-[11px]">
                                                Rad etish
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.products.status', $prod->id) }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="is_active" value="{{ $prod->is_active ? 0 : 1 }}">
                                        <button type="submit" class="px-2 py-1 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 font-semibold text-[11px]">
                                            {{ $prod->is_active ? 'Yashirish' : 'Ko\'rsatish' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        </div>
        <!-- TAB 3: USERS & ROLES -->
        <div x-show="currentTab === 'users'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Foydalanuvchilar va Rollar Boshqaruvi</h3>
                    <p class="text-[11px] text-slate-500">Superadmin: Har qanday foydalanuvchini Xaridor, Sotuvchi, Kuryer yoki Administrator qilib tayinlash mumkin</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3.5">ID / Ism</th>
                            <th class="p-3.5">Email</th>
                            <th class="p-3.5">Telefon</th>
                            <th class="p-3.5">Joriy Roli</th>
                            <th class="p-3.5">Rolni O'zgartirish</th>
                            <th class="p-3.5">Holati</th>
                            <th class="p-3.5 text-right">Amal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3.5 font-bold text-slate-900">
                                    {{ $u->name }}
                                    <span class="block text-[11px] text-slate-400 font-normal">#{{ $u->id }}</span>
                                </td>
                                <td class="p-3.5 font-mono">{{ $u->email }}</td>
                                <td class="p-3.5">{{ $u->phone ?? '—' }}</td>
                                <td class="p-3.5">
                                    @foreach($u->roles as $role)
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200 mr-1">
                                            {{ $role->label ?? $role->name }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="p-3.5">
                                    <form action="{{ route('admin.users.role', $u->id) }}" method="POST" class="flex items-center gap-1.5">
                                        @csrf
                                        <select name="role" class="h-8 px-2 rounded-lg bg-slate-50 border border-slate-300 text-[11px] font-bold text-slate-800 outline-none focus:border-slate-900">
                                            @foreach($roles as $r)
                                                <option value="{{ $r->name }}" {{ $u->hasRole($r->name) ? 'selected' : '' }}>
                                                    {{ $r->label ?? $r->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-2 py-1 rounded-lg bg-slate-800 hover:bg-slate-950 text-white font-bold text-[10px] transition" title="Rolni yangilash">
                                            Saqlash
                                        </button>
                                    </form>
                                </td>
                                <td class="p-3.5">
                                    @if($u->is_active)
                                        <span class="text-emerald-700 font-bold text-[11px]">● Faol</span>
                                    @else
                                        <span class="text-rose-700 font-bold text-[11px]">● Bloklangan</span>
                                    @endif
                                </td>
                                <td class="p-3.5 text-right">
                                    <form action="{{ route('admin.users.status', $u->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $u->is_active ? 'bg-slate-200 hover:bg-rose-100 hover:text-rose-700 text-slate-700' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                            {{ $u->is_active ? 'Bloklash' : 'Faollashtirish' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $users->links() }}
            </div>
        </div>

        <!-- TAB 4: SHIPMENTS & COURIERS -->
        <div x-show="currentTab === 'shipments'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Yetkazib Berish & Kuryerlarni Biriktirish</h3>
                    <p class="text-[11px] text-slate-500">Barcha faol jo'natmalar va kuryerlar boshqaruvi</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3.5">Tracking #</th>
                            <th class="p-3.5">Buyurtma & Do'kon</th>
                            <th class="p-3.5">Xaridor & Manzil</th>
                            <th class="p-3.5">Holati</th>
                            <th class="p-3.5">Biriktirilgan Kuryer</th>
                            <th class="p-3.5 text-right">Amal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($shipments as $sh)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3.5 font-bold font-mono text-slate-900">
                                    {{ $sh->tracking_number }}
                                    <span class="block text-[10px] text-slate-400 font-normal">{{ $sh->shipping_method }}</span>
                                </td>
                                <td class="p-3.5">
                                    <span class="font-bold text-slate-800">{{ $sh->suborder->seller->name ?? 'Do\'kon' }}</span>
                                    <span class="block text-[10px] text-slate-400 font-mono">Buyurtma: #{{ $sh->suborder->order->order_number ?? '—' }}</span>
                                </td>
                                <td class="p-3.5">
                                    <span class="font-medium text-slate-800">{{ $sh->suborder->order->user->name ?? 'Xaridor' }}</span>
                                    <span class="block text-[11px] text-slate-500 max-w-xs truncate">
                                        {{ is_array($sh->delivery_address) ? ($sh->delivery_address['address_line'] ?? ($sh->delivery_address['street'] ?? 'Toshkent sh.')) : 'Toshkent sh.' }}
                                    </span>
                                    <span class="block text-[10px] text-slate-400 font-mono">{{ $sh->suborder->order->user->phone ?? '—' }}</span>
                                </td>
                                <td class="p-3.5">
                                    @php
                                        $statusBadges = [
                                            'pending' => 'bg-slate-100 text-slate-700',
                                            'assigned' => 'bg-blue-100 text-blue-800',
                                            'picked_up' => 'bg-indigo-100 text-indigo-800',
                                            'in_transit' => 'bg-amber-100 text-amber-800',
                                            'out_for_delivery' => 'bg-purple-100 text-purple-800',
                                            'delivered' => 'bg-emerald-100 text-emerald-800',
                                            'failed' => 'bg-rose-100 text-rose-800',
                                        ];
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $statusBadges[$sh->status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $sh->status }}
                                    </span>
                                </td>
                                <td class="p-3.5">
                                    <form action="{{ route('admin.shipments.assign', $sh->id) }}" method="POST" class="flex items-center gap-1.5">
                                        @csrf
                                        <select name="courier_id" class="h-8 px-2 rounded-lg bg-slate-50 border border-slate-300 text-[11px] font-medium text-slate-800 outline-none focus:border-slate-900">
                                            <option value="">-- Kuryer tanlang --</option>
                                            @foreach($couriers as $cr)
                                                <option value="{{ $cr->id }}" {{ $sh->courier_id === $cr->id ? 'selected' : '' }}>
                                                    {{ $cr->name }} ({{ $cr->phone ?? $cr->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] transition whitespace-nowrap">
                                            Biriktirish
                                        </button>
                                    </form>
                                </td>
                                <td class="p-3.5 text-right">
                                    <a href="{{ route('tracking', ['trackingNumber' => $sh->tracking_number]) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[11px] transition">
                                        <span>📍</span> Xarita
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">Jo'natmalar mavjud emas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100">
                {{ $shipments->links() }}
            </div>
        </div>

        <!-- TAB 3: ORDERS -->
        <div x-show="currentTab === 'orders'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Oxirgi Buyurtmalar Tarixi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3.5">Buyurtma Raqami</th>
                            <th class="p-3.5">Holat</th>
                            <th class="p-3.5">Sotuvchilar</th>
                            <th class="p-3.5">Jami Summa</th>
                            <th class="p-3.5">Sana</th>
                            <th class="p-3.5 text-right">Kuzatuv</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3.5 font-bold text-slate-900 font-mono">{{ $order->order_number }}</td>
                                <td class="p-3.5">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-800">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                <td class="p-3.5">
                                    @foreach($order->suborders as $sub)
                                        <span class="inline-block text-[11px] text-slate-700 font-medium mr-2">
                                            {{ $sub->seller->name ?? 'Do\'kon' }}
                                        </span>
                                    @endforeach
                                </td>
                                <td class="p-3.5 font-black text-slate-900">{{ number_format($order->grand_total) }} UZS</td>
                                <td class="p-3.5 text-slate-400">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                <td class="p-3.5 text-right">
                                    @php
                                        $shipment = $order->suborders->flatMap->shipments->first();
                                    @endphp
                                    @if($shipment)
                                        <a href="{{ route('tracking', ['trackingNumber' => $shipment->tracking_number]) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[11px] transition">
                                            <span>📍</span> Jonli Xarita
                                        </a>
                                    @else
                                        <a href="{{ route('tracking') }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-[11px] transition">
                                            <span>📍</span> Xarita
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">Hozircha buyurtmalar mavjud emas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB: RETURNS / REFUNDS -->
        <div x-show="currentTab === 'refunds'" x-cloak class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-900 text-sm">Qaytarish So'rovlari</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    1) So'rovni tasdiqlang yoki sababini yozib rad eting.
                    2) Tovar qaytib kelgach va mijozga pul qaytarilgach (Click/Payme kabineti yoki naqd), "Pul qaytarildi" ni bosing — tovar omborga qaytadi va summa sotuvchi hisobidan ayiriladi.
                </p>
            </div>
            @php
                $refundStatus = [
                    'requested' => ['Yangi', 'bg-amber-100 text-amber-900'],
                    'approved' => ['Tasdiqlangan — tovar kutilmoqda', 'bg-blue-100 text-blue-800'],
                    'goods_returned' => ['Tovar qaytdi', 'bg-blue-100 text-blue-800'],
                    'completed' => ['Pul qaytarildi', 'bg-emerald-100 text-emerald-800'],
                    'rejected' => ['Rad etilgan', 'bg-rose-100 text-rose-800'],
                ];
            @endphp
            <div class="divide-y divide-slate-100">
                @forelse($refunds as $refund)
                    <div class="p-4 grid gap-3 lg:grid-cols-12 text-xs">
                        <div class="lg:col-span-5 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono font-bold text-slate-900">{{ $refund->refund_number }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $refundStatus[$refund->status][1] ?? 'bg-slate-100' }}">
                                    {{ $refundStatus[$refund->status][0] ?? $refund->status }}
                                </span>
                                <span class="text-slate-400">{{ $refund->created_at->format('Y-m-d H:i') }}</span>
                            </div>
                            <div class="text-slate-600">
                                Mijoz: <b class="text-slate-900">{{ $refund->user->name ?? '—' }}</b>
                                @if($refund->user?->phone) • <a href="tel:{{ $refund->user->phone }}" class="underline">{{ $refund->user->phone }}</a> @endif
                            </div>
                            <div class="text-slate-600">
                                Buyurtma:
                                @if($refund->order)
                                    <a href="{{ route('orders.detail', $refund->order->order_number) }}" target="_blank" class="font-mono font-bold text-slate-900 underline">{{ $refund->order->order_number }}</a>
                                @endif
                                • Do'kon: <b class="text-slate-900">{{ $refund->suborder->seller->name ?? '—' }}</b>
                            </div>
                            @if($refund->suborder)
                                <div class="text-slate-500">
                                    @foreach($refund->suborder->items as $it)
                                        {{ $it->product_name }} ×{{ (float) $it->quantity }}@if(!$loop->last), @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="lg:col-span-3 space-y-1">
                            <div class="font-black text-slate-900 text-sm">{{ number_format($refund->amount, 0, '.', ' ') }} UZS</div>
                            <div class="text-slate-700"><span class="text-slate-400">Sabab:</span> {{ $refund->reason }}</div>
                            @if($refund->moderator_notes)
                                <div class="text-slate-500"><span class="text-slate-400">Izoh ({{ $refund->moderator->name ?? 'moderator' }}):</span> {{ $refund->moderator_notes }}</div>
                            @endif
                        </div>
                        <div class="lg:col-span-4 lg:text-right">
                            @if($refund->status === 'requested')
                                <form method="POST" action="{{ route('admin.refunds.review', $refund->id) }}" class="space-y-2">
                                    @csrf
                                    <input type="text" name="notes" maxlength="500" placeholder="Izoh (rad etishda majburiy)"
                                           class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                                    <div class="flex gap-2 lg:justify-end">
                                        <button name="decision" value="approve" class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold">✓ Tasdiqlash</button>
                                        <button name="decision" value="reject" class="px-3 py-2 rounded-lg bg-white border border-rose-300 text-rose-700 hover:bg-rose-50 font-bold">✕ Rad etish</button>
                                    </div>
                                </form>
                            @elseif(in_array($refund->status, ['approved', 'goods_returned'], true))
                                <form method="POST" action="{{ route('admin.refunds.complete', $refund->id) }}"
                                      onsubmit="return confirm('Tovar qaytib keldi va mijozga {{ number_format($refund->amount, 0, '.', ' ') }} UZS qaytarildimi?')">
                                    @csrf
                                    <button class="px-3 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-bold">💸 Tovar qaytdi, pul qaytarildi</button>
                                </form>
                            @else
                                <span class="text-slate-400">Yakunlangan</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400">Hozircha qaytarish so'rovlari yo'q</div>
                @endforelse
            </div>
        </div>

        @if($canManagePayouts)
            <!-- TAB: SELLER PAYOUTS -->
            <div x-show="currentTab === 'payouts'" x-cloak class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-100">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-bold text-slate-900 text-sm">Sotuvchilarning pul yechish so'rovlari</h3>
                        @if(($stats['open_payouts'] ?? 0) > 0)
                            <span class="text-xs text-slate-600">Kutilmoqda: <b class="text-slate-900">{{ number_format($stats['open_payouts_sum'], 0, '.', ' ') }} UZS</b></span>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">
                        1) Pulni sotuvchining kartasi yoki hisob raqamiga o'zingiz o'tkazing.
                        2) Shundan keyin "To'landi" ni bosing — summa do'kon balansidan ayiriladi. Sayt pulni o'zi o'tkazmaydi.
                    </p>
                </div>
                @php
                    $payoutStatus = [
                        'requested' => ['Yangi', 'bg-amber-100 text-amber-900'],
                        'held' => ['Ushlab turilgan', 'bg-amber-100 text-amber-900'],
                        'processing' => ['Jarayonda', 'bg-blue-100 text-blue-800'],
                        'completed' => ["To'langan", 'bg-emerald-100 text-emerald-800'],
                        'rejected' => ['Rad etilgan', 'bg-rose-100 text-rose-800'],
                    ];
                @endphp
                <div class="divide-y divide-slate-100">
                    @forelse($payouts as $payout)
                        @php
                            $details = $payout->bank_details ?? [];
                            $number = (string) ($details['number'] ?? $details['bank_account'] ?? '');
                            $storeBalance = $payoutBalances[$payout->seller_id] ?? null;
                        @endphp
                        <div class="p-4 grid gap-3 lg:grid-cols-12 text-xs">
                            <div class="lg:col-span-4 space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono font-bold text-slate-900">#{{ $payout->id }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $payoutStatus[$payout->status][1] ?? 'bg-slate-100' }}">
                                        {{ $payoutStatus[$payout->status][0] ?? $payout->status }}
                                    </span>
                                    <span class="text-slate-400">{{ $payout->created_at->format('Y-m-d H:i') }}</span>
                                </div>
                                <div class="text-slate-600">Do'kon: <b class="text-slate-900">{{ $payout->seller->name ?? '—' }}</b></div>
                                @if($payout->isOpen() && $storeBalance !== null)
                                    <div class="{{ $storeBalance < $payout->amount ? 'text-rose-700 font-bold' : 'text-slate-500' }}">
                                        Do'kon balansi: {{ number_format($storeBalance, 0, '.', ' ') }} UZS
                                        @if($storeBalance < $payout->amount) — yetarli emas @endif
                                    </div>
                                @endif
                            </div>
                            <div class="lg:col-span-4 space-y-1">
                                <div class="font-black text-slate-900 text-sm">{{ number_format($payout->amount, 0, '.', ' ') }} UZS</div>
                                <div class="text-slate-700">
                                    <span class="text-slate-400">{{ ($details['method'] ?? 'account') === 'card' ? 'Karta:' : 'Hisob raqam:' }}</span>
                                    {{-- The full number is shown only while the transfer still has to be made --}}
                                    <span class="font-mono font-bold">{{ $payout->isOpen() ? trim(chunk_split($number, 4, ' ')) : $payout->maskedAccount() }}</span>
                                </div>
                                @if(!empty($details['holder']))
                                    <div class="text-slate-700"><span class="text-slate-400">Egasi:</span> {{ $details['holder'] }}</div>
                                @endif
                                @if($payout->notes)
                                    <div class="text-slate-500"><span class="text-slate-400">Sotuvchi izohi:</span> {{ $payout->notes }}</div>
                                @endif
                                @if($payout->admin_notes)
                                    <div class="text-slate-500"><span class="text-slate-400">Admin izohi:</span> {{ $payout->admin_notes }}</div>
                                @endif
                            </div>
                            <div class="lg:col-span-4 lg:text-right">
                                @if($payout->isOpen())
                                    <form method="POST" action="{{ route('admin.payouts.review', $payout->id) }}" class="space-y-2">
                                        @csrf
                                        <input type="text" name="notes" maxlength="500" placeholder="Izoh (rad etishda majburiy)"
                                               class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs">
                                        <div class="flex gap-2 lg:justify-end">
                                            <button name="decision" value="paid"
                                                    onclick="return confirm('{{ number_format($payout->amount, 0, '.', ' ') }} UZS sotuvchiga haqiqatan o\'tkazildimi? Bu amalni ortga qaytarib bo\'lmaydi.')"
                                                    class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold">✓ To'landi</button>
                                            <button name="decision" value="reject" class="px-3 py-2 rounded-lg bg-white border border-rose-300 text-rose-700 hover:bg-rose-50 font-bold">✕ Rad etish</button>
                                        </div>
                                    </form>
                                @else
                                    <span class="text-slate-400">Yopilgan{{ $payout->reviewed_at ? ' • ' . $payout->reviewed_at->format('Y-m-d H:i') : '' }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400">Hozircha pul yechish so'rovlari yo'q</div>
                    @endforelse
                </div>
            </div>
        @endif

        @if($canManagePayouts && $finance)
            <!-- TAB: PLATFORM FINANCE -->
            @php $sum = fn($v) => number_format((int) $v, 0, '.', ' '); @endphp
            <div x-show="currentTab === 'finance'" x-cloak class="space-y-4">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="col-span-2 lg:col-span-1 p-4 rounded-2xl bg-slate-900 text-white">
                        <span class="text-[11px] font-bold uppercase text-emerald-300 block">Sayt daromadi (komissiya)</span>
                        <div class="text-xl font-black mt-1">{{ $sum($finance['all']['commission']) }} <span class="text-[10px] font-bold text-white/60">UZS</span></div>
                        <span class="text-[10px] text-white/60 block mt-1">Oxirgi {{ $finance['days'] }} kunda: {{ $sum($finance['recent']['commission']) }}</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border border-slate-200">
                        <span class="text-[11px] font-bold uppercase text-slate-500 block">To'langan savdo</span>
                        <div class="text-lg font-black text-slate-900 mt-1">{{ $sum($finance['all']['sales']) }} <span class="text-[10px] font-bold text-slate-400">UZS</span></div>
                        <span class="text-[10px] text-slate-500 block mt-1">Oxirgi {{ $finance['days'] }} kunda: {{ $sum($finance['recent']['sales']) }}</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border border-slate-200">
                        <span class="text-[11px] font-bold uppercase text-slate-500 block">Sotuvchilarga qarz</span>
                        <div class="text-lg font-black text-amber-700 mt-1">{{ $sum($finance['owed_to_sellers']) }} <span class="text-[10px] font-bold text-slate-400">UZS</span></div>
                        <span class="text-[10px] text-slate-500 block mt-1">Shundan so'ralgan: {{ $sum($finance['pending_payouts']) }}</span>
                    </div>
                    <div class="col-span-2 lg:col-span-1 p-4 rounded-2xl bg-white border border-slate-200">
                        <span class="text-[11px] font-bold uppercase text-slate-500 block">Sotuvchilarga to'langan</span>
                        <div class="text-lg font-black text-slate-900 mt-1">{{ $sum($finance['all']['paid_out']) }} <span class="text-[10px] font-bold text-slate-400">UZS</span></div>
                        <span class="text-[10px] text-slate-500 block mt-1">Qaytarilgan tovarlar: {{ $sum($finance['all']['refunds']) }}</span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                    <div class="p-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900 text-sm">Do'konlar bo'yicha</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Bu yerda faqat puli to'langan buyurtmalar hisoblanadi (naqd to'lovda — yetkazilgach). "Balans" — sayt shu do'konga berishi kerak bo'lgan pul.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50 text-slate-900 font-bold uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="p-3">Do'kon</th>
                                    <th class="p-3 text-right">Savdo</th>
                                    <th class="p-3 text-right">Komissiya</th>
                                    <th class="p-3 text-right">To'langan</th>
                                    <th class="p-3 text-right">Balans</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($finance['sellers'] as $row)
                                    <tr>
                                        <td class="p-3">
                                            <div class="font-bold text-slate-900">{{ $row['name'] }}</div>
                                            <div class="text-[10px] text-slate-400">Komissiya {{ $row['commission_rate'] }}%@if($row['pending_payouts'] > 0) • so'ralgan {{ $sum($row['pending_payouts']) }}@endif</div>
                                        </td>
                                        <td class="p-3 text-right whitespace-nowrap">{{ $sum($row['sales']) }}</td>
                                        <td class="p-3 text-right whitespace-nowrap font-bold text-emerald-700">{{ $sum($row['commission']) }}</td>
                                        <td class="p-3 text-right whitespace-nowrap">{{ $sum($row['paid_out']) }}</td>
                                        <td class="p-3 text-right whitespace-nowrap font-black text-slate-900">{{ $sum($row['balance']) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="p-8 text-center text-slate-400">Hozircha puli to'langan savdo yo'q</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        @if($canManageBackups)
            <!-- TAB: DATABASE BACKUPS -->
            <div x-show="currentTab === 'backups'" x-cloak class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Bazaning zaxira nusxalari</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Har kuni soat 03:00 da va har yangilanishdan oldin avtomatik olinadi.</p>
                    </div>
                    <form action="{{ route('admin.backups.create') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold">Hozir nusxa olish</button>
                    </form>
                </div>

                @if(!$lastDailyBackup)
                    <div class="px-4 py-3 bg-amber-50 text-amber-900 text-xs border-b border-amber-100">Birinchi kunlik nusxa kechasi soat 03:00 da olinadi.</div>
                @elseif($lastDailyBackup['created_at']->diffInHours(now()) > 26)
                    <div class="px-4 py-3 bg-rose-50 text-rose-800 text-xs font-bold border-b border-rose-100">⚠️ Kunlik nusxa bir kundan ko'proq vaqt olinmagan. Serverni tekshiring.</div>
                @endif

                <div class="px-4 py-3 bg-slate-50 text-slate-600 text-xs border-b border-slate-100">Nusxalar sayt bilan bitta diskda turadi. Haftada bir marta yuklab olib, boshqa joyda saqlang. Faylda mijozlar ma'lumoti bor, uni hech kimga bermang.</div>

                @php
                    $backupTypes = ['daily' => 'Kunlik', 'predeploy' => 'Yangilanishdan oldin', 'manual' => "Qo'lda"];
                @endphp
                <div class="divide-y divide-slate-100">
                    @forelse($backups as $backup)
                        <div class="p-4 flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-900">{{ $backup['created_at']->copy()->timezone('Asia/Tashkent')->format('d.m.Y H:i') }}</div>
                                <div class="text-slate-500 mt-0.5">{{ $backupTypes[$backup['type']] }} · {{ number_format($backup['size'] / 1048576, 2, '.', ' ') }} MB</div>
                            </div>
                            <a href="{{ route('admin.backups.download', $backup['name']) }}" class="px-3 py-2 rounded-lg border border-slate-300 text-slate-800 font-bold whitespace-nowrap hover:bg-slate-50">Yuklab olish</a>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-400">Hozircha zaxira nusxalar yo'q</div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- TAB: ANALYTICS (Savdo Analitikasi & Hisobotlar) -->
        <div x-show="currentTab === 'analytics'" class="space-y-6">
            <!-- Analytics Summary KPIs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Jami Savdo Tushumi</span>
                    <span class="text-2xl font-black text-slate-900">{{ number_format($stats['total_revenue']) }} <span class="text-xs text-slate-400 font-normal">UZS</span></span>
                    <span class="text-xs text-emerald-600 block mt-1 font-semibold">✓ Tasdiqlangan buyurtmalar</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Jami Buyurtmalar</span>
                    <span class="text-2xl font-black text-slate-900">{{ number_format($stats['total_orders']) }} <span class="text-xs text-slate-400 font-normal">ta</span></span>
                    <span class="text-xs text-blue-600 block mt-1 font-semibold">Barcha sotuvchilardan</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">O'rtacha Buyurtma Cheki</span>
                    @php
                        $avgCheck = $stats['total_orders'] > 0 ? (int)($stats['total_revenue'] / $stats['total_orders']) : 0;
                    @endphp
                    <span class="text-2xl font-black text-emerald-700">{{ number_format($avgCheck) }} <span class="text-xs text-slate-400 font-normal">UZS</span></span>
                    <span class="text-xs text-slate-500 block mt-1">Har bir xarid uchun</span>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Faol Sotuvchi Do'konlar</span>
                    <span class="text-2xl font-black text-amber-700">{{ number_format($stats['active_sellers']) }} / {{ number_format($stats['total_sellers']) }}</span>
                    <span class="text-xs text-amber-600 block mt-1 font-semibold">Platforma hamkorlari</span>
                </div>
            </div>

            <!-- Daily Sales Dynamic Bar Chart (So'nggi 14 kunlik savdo grafigi) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-slate-900 text-base">So'nggi 14 Kunlik Savdo Dinamikasi</h3>
                        <p class="text-xs text-slate-500">Kunlar kesimida tasdiqlangan va to'langan buyurtmalar</p>
                    </div>
                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl">Jonli Statistika</span>
                </div>

                @php
                    $maxDaily = $dailySales->max('total') ?: 1;
                @endphp
                <div class="pt-6 pb-2">
                    <div class="flex items-end gap-2 sm:gap-4 h-48 sm:h-56 border-b border-slate-100 px-2">
                        @forelse($dailySales as $day)
                            @php
                                $heightPercent = min(100, max(12, (int)(($day->total / $maxDaily) * 100)));
                            @endphp
                            <div class="flex-1 flex flex-col items-center justify-end h-full group relative">
                                <!-- Tooltip on hover -->
                                <div class="absolute -top-12 opacity-0 group-hover:opacity-100 transition-opacity bg-slate-900 text-white text-[10px] font-bold py-1 px-2 rounded-lg pointer-events-none whitespace-nowrap shadow-lg z-20">
                                    <span>{{ number_format($day->total) }} UZS</span> • <span>{{ $day->count }} ta buyurtma</span>
                                </div>
                                <div class="w-full bg-gradient-to-t from-emerald-600 to-emerald-400 rounded-t-lg transition-all duration-300 group-hover:from-emerald-700 group-hover:to-lime-400"
                                     style="height: {{ $heightPercent }}%;">
                                </div>
                                <span class="text-[9px] sm:text-[10px] font-semibold text-slate-500 mt-2 truncate max-w-full">
                                    {{ \Carbon\Carbon::parse($day->date)->format('d.m') }}
                                </span>
                            </div>
                        @empty
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs">
                                So'nggi 14 kunda yangi savdolar qayd etilmagan
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Top 5 Products & Top 5 Sellers Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Top 5 Products -->
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">🔥 Eng Ko'p Sotilgan Mahsulotlar (Top 5)</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="p-3">Mahsulot</th>
                                    <th class="p-3 text-center">Hajm</th>
                                    <th class="p-3 text-right">Jami Tushum</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($topProducts as $idx => $p)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-3 font-medium text-slate-900 truncate max-w-[200px]">
                                            <span class="font-black text-slate-400 mr-1.5">#{{ $idx + 1 }}</span>
                                            {{ $p->product_name }}
                                        </td>
                                        <td class="p-3 text-center font-bold text-slate-700">{{ number_format($p->total_qty) }}</td>
                                        <td class="p-3 text-right font-black text-emerald-700">{{ number_format($p->total_revenue) }} UZS</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="p-6 text-center text-slate-400">Hozircha sotuvlar yo'q</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top 5 Sellers -->
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">🏆 Yetakchi Sotuvchilar (Top 5)</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold uppercase text-[10px]">
                                <tr>
                                    <th class="p-3">Do'kon Nomi</th>
                                    <th class="p-3 text-center">Buyurtmalar</th>
                                    <th class="p-3 text-right">Jami Savdosi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($topSellers as $idx => $s)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-3 font-medium text-slate-900">
                                            <span class="font-black text-slate-400 mr-1.5">#{{ $idx + 1 }}</span>
                                            {{ $s->seller->name ?? "Do'kon #{$s->seller_id}" }}
                                        </td>
                                        <td class="p-3 text-center font-bold text-slate-700">{{ $s->count }} ta</td>
                                        <td class="p-3 text-right font-black text-slate-900">{{ number_format($s->total_revenue) }} UZS</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="p-6 text-center text-slate-400">Hozircha sotuvchilar statistikasi yo'q</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB: CATEGORIES (Kategoriyalar Boshqaruvi) -->
        <div x-show="currentTab === 'categories'" class="space-y-6">
            <!-- Add Category Form Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 shadow-xs">
                <h3 class="font-black text-slate-900 text-base mb-1">Yangi Kategoriya Qo'shish</h3>
                <p class="text-xs text-slate-500 mb-4">Marketpleys katalogi va bosh sahifasida aks etadigan bo'lim</p>

                <form action="{{ route('admin.categories.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Nomi *</label>
                        <input type="text" name="name" required placeholder="Masalan: Avto ehtiyot qismlar" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Slug (URL)</label>
                        <input type="text" name="slug" placeholder="avto-ehtiyot-qismlar (ixtiyoriy)" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Ikonka / Emoji</label>
                        <input type="text" name="icon" value="📦" placeholder="Masalan: 🚗 yoki 🧱" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900 text-center text-base">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tartib Raqami</label>
                        <input type="number" name="sort_order" value="0" class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900">
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded text-slate-900">
                            <span>Faol</span>
                        </label>
                        <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition cursor-pointer shadow-xs">
                            + Qo'shish
                        </button>
                    </div>
                </form>
            </div>

            <!-- Categories List Table -->
            <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900 text-sm">Barcha Mavjud Kategoriyalar ({{ $categories->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="p-3.5">Ikonka</th>
                                <th class="p-3.5">Nomi</th>
                                <th class="p-3.5">Slug (Havola)</th>
                                <th class="p-3.5 text-center">Mahsulotlar</th>
                                <th class="p-3.5 text-center">Tartib</th>
                                <th class="p-3.5 text-center">Holati</th>
                                <th class="p-3.5 text-right">Amallar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($categories as $cat)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3.5 text-xl font-mono">{{ $cat->display_icon }}</td>
                                    <td class="p-3.5 font-bold text-slate-900">{{ $cat->name }}</td>
                                    <td class="p-3.5 font-mono text-[11px] text-slate-500">{{ $cat->slug }}</td>
                                    <td class="p-3.5 text-center font-bold text-slate-700">{{ $cat->products_count }} ta</td>
                                    <td class="p-3.5 text-center font-mono">{{ $cat->sort_order }}</td>
                                    <td class="p-3.5 text-center">
                                        @if($cat->is_active)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Faol</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Nofaol</span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 text-right space-x-1.5">
                                        <form action="{{ route('admin.categories.status', $cat->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border border-slate-200 hover:bg-slate-100 transition">
                                                {{ $cat->is_active ? 'Nofaol qilish' : 'Faollashtirish' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.categories.delete', $cat->id) }}" method="POST" class="inline" onsubmit="return confirm('Kategoriyani o\'chirishni tasdiqlaysizmi?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-rose-600 hover:bg-rose-50 transition">
                                                O'chirish
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB: MARKETING (Promokodlar & Bannerlar) -->
        <div x-show="currentTab === 'marketing'" class="space-y-8">
            <!-- 1. PROMOCODES ENGINE -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Chegirma Promokodlari va Vaucherlar</h3>
                        <p class="text-xs text-slate-500">Xaridorlar checkout sahifasida kiritib chegirma oladigan maxsus kodlar</p>
                    </div>
                </div>

                <!-- Add Promocode Form -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <form action="{{ route('admin.promocodes.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3 items-end">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Kod *</label>
                            <input type="text" name="code" required placeholder="UZUM2026" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs uppercase font-bold focus:ring-2 focus:ring-slate-900">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Turi *</label>
                            <select name="type" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900 bg-white">
                                <option value="fixed">Aniq Summa (UZS)</option>
                                <option value="percentage">Foiz (%)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Qiymat *</label>
                            <input type="number" name="value" required placeholder="50000 yoki 10" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Min. Xarid (UZS)</label>
                            <input type="number" name="min_order_amount" value="100000" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Ishlatish Limiti</label>
                            <input type="number" name="usage_limit" placeholder="100 (ixtiyoriy)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Tugash Sanasi</label>
                            <input type="date" name="expires_at" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900">
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-1 text-xs font-bold text-slate-700">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded">
                                <span>Faol</span>
                            </label>
                            <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition cursor-pointer">
                                + Yaratish
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Promocodes List Table -->
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="p-3.5">Kod</th>
                                    <th class="p-3.5">Chegirma</th>
                                    <th class="p-3.5">Min. Xarid</th>
                                    <th class="p-3.5 text-center">Ishlatildi / Limit</th>
                                    <th class="p-3.5">Amal Qilish Muddati</th>
                                    <th class="p-3.5 text-center">Holati</th>
                                    <th class="p-3.5 text-right">Amallar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($promocodes as $pr)
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="p-3.5 font-mono font-black text-slate-900 text-sm">
                                            <span class="px-2 py-0.5 rounded-lg bg-lime-100 text-slate-900">{{ $pr->code }}</span>
                                        </td>
                                        <td class="p-3.5 font-bold text-emerald-700">
                                            @if($pr->type === 'percentage')
                                                {{ $pr->value }}% chegirma
                                            @else
                                                {{ number_format($pr->value) }} UZS chegirma
                                            @endif
                                        </td>
                                        <td class="p-3.5 font-mono">{{ number_format($pr->min_order_amount) }} UZS</td>
                                        <td class="p-3.5 text-center font-bold">
                                            {{ $pr->used_count }} / {{ $pr->usage_limit ?: 'cheksiz' }}
                                        </td>
                                        <td class="p-3.5 text-slate-500 font-mono text-[11px]">
                                            {{ $pr->expires_at ? $pr->expires_at->format('d.m.Y') : 'Cheklovlarsiz' }}
                                        </td>
                                        <td class="p-3.5 text-center">
                                            @if($pr->is_active)
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Faol</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">To'xtatilgan</span>
                                            @endif
                                        </td>
                                        <td class="p-3.5 text-right space-x-1.5">
                                            <form action="{{ route('admin.promocodes.toggle', $pr->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold border border-slate-200 hover:bg-slate-100 transition">
                                                    {{ $pr->is_active ? 'To\'xtatish' : 'Faollashtirish' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.promocodes.delete', $pr->id) }}" method="POST" class="inline" onsubmit="return confirm('Promokodni o\'chirishni tasdiqlaysizmi?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 rounded-lg text-[11px] font-semibold text-rose-600 hover:bg-rose-50 transition">
                                                    O'chirish
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-slate-400">Hozircha promokodlar yaratilmagan</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 2. MARKETING BANNERS ENGINE -->
            <div class="space-y-4 pt-6 border-t border-slate-200">
                <div>
                    <h3 class="font-black text-slate-900 text-base">Bosh Sahifa va Aksiya Bannerlari</h3>
                    <p class="text-xs text-slate-500">Bosh sahifaning yuqori qismida yoki katalogda aks etadigan katta reklama slayderlari</p>
                </div>

                <!-- Add Banner Form -->
                <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
                    <form action="{{ route('admin.banners.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 items-end">
                        @csrf
                        <div class="lg:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Sarlavha *</label>
                            <input type="text" name="title" required placeholder="Masalan: Katta Bahorgi Chegirmalar 50%" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900">
                        </div>
                        <div class="lg:col-span-2">
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Rasm URL *</label>
                            <input type="text" name="image_url" required placeholder="https://images.unsplash.com/..." class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 uppercase mb-1">Havola URL</label>
                            <input type="text" name="link_url" value="/catalog" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-slate-900">
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="flex items-center gap-1 text-xs font-bold text-slate-700">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded">
                                <span>Faol</span>
                            </label>
                            <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition cursor-pointer">
                                + Joylash
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Banners Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($banners as $bn)
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs flex flex-col justify-between">
                            <div class="relative aspect-16/9 bg-slate-100 overflow-hidden">
                                <img src="{{ $bn->image_url }}" alt="{{ $bn->title }}" class="w-full h-full object-cover">
                                <span class="absolute top-2 right-2 px-2 py-0.5 rounded-full text-[10px] font-bold {{ $bn->is_active ? 'bg-emerald-500 text-white' : 'bg-slate-600 text-white' }}">
                                    {{ $bn->is_active ? 'Faol' : 'Nofaol' }}
                                </span>
                            </div>
                            <div class="p-4 space-y-2">
                                <h4 class="font-bold text-sm text-slate-900">{{ $bn->title }}</h4>
                                <p class="text-xs font-mono text-slate-500 truncate">{{ $bn->link_url }}</p>
                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                    <form action="{{ route('admin.banners.status', $bn->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                                            {{ $bn->is_active ? 'Nofaol qilish' : 'Faollashtirish' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.banners.delete', $bn->id) }}" method="POST" class="inline" onsubmit="return confirm('Bannerni o\'chirmoqchimisiz?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:text-rose-800">
                                            O'chirish
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full p-8 text-center text-slate-400 bg-white rounded-2xl border border-slate-200">
                            Hozircha bannerlar joylanmagan
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- TAB 4: AUDIT LOGS -->
        <div x-show="currentTab === 'audit'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Xavfsizlik va Amallar Audit Jurnali</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3.5">Voqea / Amal</th>
                            <th class="p-3.5">Foydalanuvchi</th>
                            <th class="p-3.5">IP Manzil</th>
                            <th class="p-3.5">Tafsilot</th>
                            <th class="p-3.5">Vaqt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($auditLogs as $log)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3.5 font-mono font-bold text-slate-900">{{ $log->action }}</td>
                                <td class="p-3.5 font-medium text-slate-700">{{ $log->user->name ?? 'Tizim' }}</td>
                                <td class="p-3.5 font-mono text-slate-500">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                                <td class="p-3.5 font-mono text-[11px] text-slate-500 max-w-xs truncate">{{ json_encode($log->details) }}</td>
                                <td class="p-3.5 text-slate-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">Audit yozuvlari mavjud emas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>
