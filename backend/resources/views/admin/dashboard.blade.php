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
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased font-sans" x-data="{ currentTab: 'sellers' }">

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

        <!-- Navigation Tabs -->
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 mb-6 pb-2">
            <button @click="currentTab = 'sellers'" :class="currentTab === 'sellers' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
                💼 Sotuvchilar ({{ $sellers->count() }})
            </button>
            <button @click="currentTab = 'products'" :class="currentTab === 'products' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
                📦 Mahsulotlar ({{ $products->total() }})
            </button>
            <button @click="currentTab = 'users'" :class="currentTab === 'users' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
                👥 Foydalanuvchilar & Rollar ({{ $users->total() }})
            </button>
            <button @click="currentTab = 'shipments'" :class="currentTab === 'shipments' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
                🚚 Yetkazish & Kuryerlar ({{ $shipments->total() }})
            </button>
            <button @click="currentTab = 'orders'" :class="currentTab === 'orders' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
                🛒 Buyurtmalar ({{ $recentOrders->count() }})
            </button>
            <button @click="currentTab = 'audit'" :class="currentTab === 'audit' ? 'bg-slate-900 text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
                🔍 Audit Jurnali
            </button>
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
