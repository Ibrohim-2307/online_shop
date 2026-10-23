<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sotuvchi Kabineti - {{ $seller->name }}</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased font-sans" x-data="{ currentTab: 'products', showAddModal: false }">

    <!-- Top Seller Header -->
    <header class="bg-[#123C2F] text-white border-b border-[#0d2a21] sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/" class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-lg bg-[#C9F46B] text-slate-950 font-black flex items-center justify-center text-base">M</span>
                    <span class="font-extrabold text-lg tracking-tight">MARKET</span>
                </a>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-[#C9F46B]/20 text-[#C9F46B] border border-[#C9F46B]/30 uppercase tracking-wider">
                    Sotuvchi Kabineti
                </span>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <span class="text-xs font-bold text-white block">{{ $seller->name }}</span>
                    <span class="text-[11px] text-emerald-200">Komissiya: {{ $seller->commission_rate }}%</span>
                </div>
                <div class="h-4 w-px bg-emerald-800"></div>
                <a href="/" class="text-xs font-semibold text-emerald-100 hover:text-white transition">
                    Do'konga o'tish →
                </a>
                <div class="h-4 w-px bg-emerald-800"></div>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-900 hover:bg-emerald-800 text-white transition">
                        Chiqish
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800">
                <div class="font-bold text-sm mb-1">Xatoliklar yuz berdi:</div>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Store Overview Banner -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm mb-8 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider mb-2">
                    Rasmiy Do'kon
                </span>
                <h1 class="text-2xl font-black text-slate-900">{{ $seller->name }}</h1>
                <p class="text-xs text-slate-500 mt-1">
                    {{ $seller->legal_name }} | STIR: {{ $seller->tax_id }} | {{ $seller->phone }}
                </p>
            </div>

            <!-- Highlights -->
            <div class="flex items-center gap-6">
                <div class="text-right">
                    <span class="text-xs text-slate-500 block">Do'kon Balansi (Ledger)</span>
                    <span class="text-2xl font-black text-emerald-600">{{ number_format($stats['current_balance']) }} <span class="text-xs text-slate-500">UZS</span></span>
                </div>
                <div class="h-10 w-px bg-slate-200"></div>
                <div class="text-right">
                    <span class="text-xs text-slate-500 block">Mijozlar Bahosi</span>
                    <span class="text-2xl font-black text-amber-500">★ {{ number_format($stats['rating'], 1) }}</span>
                </div>
            </div>
        </div>

        <!-- Metric Badges -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Tovarlar Soni</span>
                <span class="text-2xl font-black text-slate-900">{{ number_format($stats['total_products']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Jami Buyurtmalar</span>
                <span class="text-2xl font-black text-slate-900">{{ number_format($stats['total_suborders']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Kutilayotgan</span>
                <span class="text-2xl font-black text-amber-600">{{ number_format($stats['pending_orders']) }}</span>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block mb-1">Omborlar</span>
                <span class="text-2xl font-black text-slate-900">{{ $warehouses->count() }}</span>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-slate-200 mb-6 pb-2">
            <button @click="currentTab = 'products'" :class="currentTab === 'products' ? 'bg-[#123C2F] text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
                Tovarlar & Narx Pog'onalari ({{ $products->count() }})
            </button>
            <button @click="currentTab = 'orders'" :class="currentTab === 'orders' ? 'bg-[#123C2F] text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                <span>📦 Buyurtmalar & Ta'minot</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-[#C9F46B] text-[#123C2F] font-black">
                    {{ $suborders->count() + $procurementOrders->count() }}
                </span>
            </button>
            <button @click="currentTab = 'rfqs'" :class="currentTab === 'rfqs' ? 'bg-[#123C2F] text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
                Optom Narx So'rovlari (RFQ) ({{ $rfqs->count() }})
            </button>
            <button @click="currentTab = 'warehouses'" :class="currentTab === 'warehouses' ? 'bg-[#123C2F] text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
                Ombor Qoldiqlari
            </button>
            <button @click="currentTab = 'ledger'" :class="currentTab === 'ledger' ? 'bg-[#123C2F] text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
                Moliyaviy Hisob (Ledger)
            </button>
        </div>

        <!-- TAB 1: PRODUCTS & PRICING TIERS -->
        <div x-show="currentTab === 'products'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Do'kon Mahsulotlari va Optom Narx Zinapoyasi</h3>
                    <p class="text-xs text-slate-400">Tovarlarning ulgurji va chakana narxlari hamda ombordagi qoldiqlari</p>
                </div>
                <button @click="showAddModal = true" type="button" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#123C2F] hover:bg-[#0e2f25] text-white text-xs font-bold shadow-sm transition">
                    <svg class="w-4 h-4 text-[#C9F46B]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Yangi Mahsulot Qo'shish</span>
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3.5">Rasm</th>
                            <th class="p-3.5">Mahsulot Nomi / SKU</th>
                            <th class="p-3.5">Savdo Rejimi</th>
                            <th class="p-3.5">Birligi & Qadam</th>
                            <th class="p-3.5">Optom Narx Pog'onalari (Tiers)</th>
                            <th class="p-3.5">Ombor Qoldig'i</th>
                            <th class="p-3.5">Holati</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($products as $p)
                            @foreach($p->variants as $v)
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3.5">
                                        <div class="w-12 h-12 rounded-xl bg-slate-100 overflow-hidden border border-slate-200 flex items-center justify-center flex-shrink-0">
                                            @if($p->image_url)
                                                <img src="{{ $p->image_url }}" alt="{{ $p->name }}" class="w-full h-full object-cover">
                                            @else
                                                <span class="text-slate-400 text-[10px]">Rasm yo'q</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-3.5 font-bold text-slate-900">
                                        <a href="{{ route('product.show', $p->slug) }}" class="hover:text-emerald-700 hover:underline">
                                            {{ $p->name }}
                                        </a>
                                        <span class="block text-[11px] font-mono text-slate-500 font-normal">SKU: {{ $v->sku }} ({{ $v->name }})</span>
                                    </td>
                                    <td class="p-3.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $p->trade_mode === 'both' ? 'bg-purple-100 text-purple-800' : ($p->trade_mode === 'wholesale_only' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800') }}">
                                            {{ $p->trade_mode }}
                                        </span>
                                    </td>
                                    <td class="p-3.5">
                                        {{ $p->unit }}
                                        <span class="block text-[11px] text-slate-400">MOQ: {{ $p->min_order_quantity }} | Qadam: {{ $p->unit_step }}</span>
                                    </td>
                                    <td class="p-3.5">
                                        <div class="space-y-1">
                                            @foreach($v->priceTiers as $pt)
                                                <div class="text-[11px] font-mono">
                                                    <span class="font-bold text-slate-700">{{ $pt->min_quantity }}{{ $pt->max_quantity ? ' - ' . $pt->max_quantity : '+' }} dona:</span>
                                                    <span class="text-emerald-700 font-bold">{{ number_format($pt->unit_price) }} UZS</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="p-3.5">
                                        @foreach($v->inventoryBalances as $ib)
                                            <span class="block font-bold text-slate-900">
                                                {{ number_format($ib->quantity) }} {{ $p->unit }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">{{ $ib->warehouse->name ?? 'Ombor' }}</span>
                                        @endforeach
                                    </td>
                                    <td class="p-3.5">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            {{ $p->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: DUAL ORDERS & PROCUREMENT HUB -->
        <div x-show="currentTab === 'orders'" class="space-y-4" x-data="{ orderSubTab: 'inbound' }">
            
            <!-- Top sub-tab controller & External link -->
            <div class="bg-white rounded-2xl border border-slate-200 p-3 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2 p-1 bg-slate-100 rounded-xl">
                    <button @click="orderSubTab = 'inbound'" 
                            :class="orderSubTab === 'inbound' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                        <span>🛒 Do'kon Xaridlari (Sotib olgan tovarlarimiz)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-[#C9F46B] text-[#123C2F] font-black">
                            {{ $procurementOrders->count() }}
                        </span>
                    </button>
                    <button @click="orderSubTab = 'outbound'" 
                            :class="orderSubTab === 'outbound' ? 'bg-[#123C2F] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                        <span>📤 Mijozlar Buyurtmalari (Do'kon sotuvlari)</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-[#C9F46B] text-[#123C2F] font-black">
                            {{ $suborders->count() }}
                        </span>
                    </button>
                </div>

                <a href="{{ route('orders.hub') }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-50 text-emerald-800 hover:bg-emerald-100 text-xs font-bold flex items-center gap-1.5 transition">
                    <span>🚀 To'liq Buyurtmalar Markazini Ochish</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>

            <!-- SUB-TAB 1: INBOUND PURCHASES (Sotuvchining o'zi xarid qilgan tovarlari) -->
            <div x-show="orderSubTab === 'inbound'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Do'koningiz Sotib Olgan Tovar va Ta'minot Partiyalari</h3>
                        <p class="text-xs text-slate-400">Ishlab chiqaruvchi va distribyutorlardan xarid qilingan zaxiralar hamda ularning yetkazilish holati</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="p-3.5">Buyurtma #</th>
                                <th class="p-3.5">Ta'minotchi / Zavod</th>
                                <th class="p-3.5">Xarid qilingan tovarlar</th>
                                <th class="p-3.5">Yetkazilish Holati</th>
                                <th class="p-3.5">Jami Summa</th>
                                <th class="p-3.5">Sana</th>
                                <th class="p-3.5 text-right">Jonli Kuzatuv</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($procurementOrders as $order)
                                @php
                                    $sh = $order->suborders->flatMap->shipments->first();
                                    $isInTransit = $sh && $sh->status === 'in_transit';
                                    $firstSub = $order->suborders->first();
                                @endphp
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3.5 font-bold text-slate-900 font-mono">{{ $order->order_number }}</td>
                                    <td class="p-3.5 font-bold text-slate-800">{{ $firstSub?->seller?->name ?? 'Zavod' }}</td>
                                    <td class="p-3.5 max-w-xs truncate">
                                        @foreach($order->suborders->flatMap->items as $it)
                                            <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-[10px] mr-1">
                                                {{ $it->product_name }} (×{{ $it->quantity }})
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="p-3.5">
                                        @if($isInTransit)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span>
                                                Kuryer Yo'lda
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-800">
                                                {{ $order->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="p-3.5 font-black text-slate-900">{{ number_format($order->grand_total) }} UZS</td>
                                    <td class="p-3.5 text-slate-400">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="p-3.5 text-right">
                                        @if($sh)
                                            <a href="{{ route('tracking', ['trackingNumber' => $sh->tracking_number]) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-[#123C2F] text-[#C9F46B] hover:bg-[#0d2a21] font-bold text-[11px] shadow-xs transition">
                                                <span>📍</span> Xarita
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-[11px]">Trek yo'q</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-slate-400">Do'koningiz hali ta'minotchilardan xarid qilmagan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SUB-TAB 2: OUTBOUND CUSTOMER SALES (Mijozlar buyurtmalari) -->
            <div x-show="orderSubTab === 'outbound'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Mijozlardan Kelib Tushgan Buyurtmalar (Do'kon Sotuvlari)</h3>
                        <p class="text-xs text-slate-400">Xaridorlar sizning do'koningizdan sotib olgan tovarlar ro'yxati</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="p-3.5">Suborder #</th>
                                <th class="p-3.5">Xaridor</th>
                                <th class="p-3.5">Buyurtma qilingan tovarlar</th>
                                <th class="p-3.5">Holati</th>
                                <th class="p-3.5">Summa</th>
                                <th class="p-3.5">Komissiya</th>
                                <th class="p-3.5">Sana</th>
                                <th class="p-3.5 text-right">Amal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($suborders as $sub)
                                @php
                                    $sh = $sub->shipments->first();
                                @endphp
                                <tr class="hover:bg-slate-50/50">
                                    <td class="p-3.5 font-bold text-slate-900 font-mono">{{ $sub->suborder_number }}</td>
                                    <td class="p-3.5">
                                        <div class="font-bold text-slate-800">{{ $sub->order->shipping_address['recipient_name'] ?? $sub->order->user?->name ?? 'Mijoz' }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $sub->order->shipping_address['phone'] ?? '—' }}</div>
                                    </td>
                                    <td class="p-3.5 max-w-xs truncate">
                                        @foreach($sub->items as $it)
                                            <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-[10px] mr-1">
                                                {{ $it->product_name }} (×{{ $it->quantity }})
                                            </span>
                                        @endforeach
                                    </td>
                                    <td class="p-3.5">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-800">
                                            {{ $sh?->status ?? $sub->status }}
                                        </span>
                                    </td>
                                    <td class="p-3.5 font-bold text-slate-900">{{ number_format($sub->total_amount) }} UZS</td>
                                    <td class="p-3.5 text-slate-500">{{ number_format($sub->commission_amount) }} UZS</td>
                                    <td class="p-3.5 text-slate-400">{{ $sub->created_at->format('Y-m-d H:i') }}</td>
                                    <td class="p-3.5 text-right">
                                        @if($sh)
                                            <a href="{{ route('tracking', ['trackingNumber' => $sh->tracking_number]) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-800 hover:bg-emerald-100 font-bold text-[11px] transition">
                                                <span>📍</span> Xarita
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="p-8 text-center text-slate-400">Do'konga buyurtmalar hali kelib tushmagan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- TAB 3: RFQs -->
        <div x-show="currentTab === 'rfqs'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Katta Hajmli Yuklar Uchun Narx So'rovlari (RFQ)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3.5">RFQ Raqami</th>
                            <th class="p-3.5">So'ralgan Miqdor</th>
                            <th class="p-3.5">Yetkazish Manzili</th>
                            <th class="p-3.5">Izoh</th>
                            <th class="p-3.5">Holati</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($rfqs as $rfq)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3.5 font-bold font-mono text-slate-900">{{ $rfq->rfq_number }}</td>
                                <td class="p-3.5 font-bold text-slate-800">{{ number_format($rfq->requested_quantity) }} birlik</td>
                                <td class="p-3.5 text-slate-600">{{ $rfq->delivery_address }}</td>
                                <td class="p-3.5 text-slate-500">{{ $rfq->notes ?? '—' }}</td>
                                <td class="p-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-800">
                                        {{ $rfq->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">Hozircha RFQ so'rovlari mavjud emas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 4: WAREHOUSES -->
        <div x-show="currentTab === 'warehouses'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Do'konga Tegishli Omborlar</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($warehouses as $wh)
                    <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50/50">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-bold text-slate-900 text-sm">{{ $wh->name }}</h4>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-slate-200 text-slate-800">{{ $wh->code }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mb-4">{{ $wh->address }}</p>
                        <div class="text-xs font-semibold text-slate-700">
                            Zaxira tovarlari turlari: <span class="font-bold text-slate-900">{{ $wh->balances->count() }} ta SKU</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- TAB 5: LEDGER -->
        <div x-show="currentTab === 'ledger'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Doimiy Buxgalteriya Jurnali (Double-Entry Ledger)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3.5">ID / Sana</th>
                            <th class="p-3.5">Turi</th>
                            <th class="p-3.5">Izoh</th>
                            <th class="p-3.5">Summa</th>
                            <th class="p-3.5 text-right">Qoldiq Balans</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ledgerEntries as $entry)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3.5 font-mono text-slate-400">#{{ $entry->id }} ({{ $entry->created_at->format('Y-m-d H:i') }})</td>
                                <td class="p-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $entry->type === 'credit' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $entry->type }}
                                    </span>
                                </td>
                                <td class="p-3.5 text-slate-700">{{ $entry->description }}</td>
                                <td class="p-3.5 font-bold {{ $entry->type === 'credit' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $entry->type === 'credit' ? '+' : '-' }}{{ number_format($entry->amount) }} UZS
                                </td>
                                <td class="p-3.5 text-right font-black text-slate-900">{{ number_format($entry->balance_after) }} UZS</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-slate-400">Moliyaviy operatsiyalar mavjud emas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal: Add New Product -->
        <div x-show="showAddModal" 
             style="display: none;"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showAddModal = false" class="bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100 my-8 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-6 sticky top-0 bg-white z-10">
                    <div>
                        <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Katalog Boshqaruvi</span>
                        <h3 class="text-xl font-black text-slate-900">Yangi Mahsulot Qo'shish</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Katalogga tovar qo'shish va pog'onali B2B narxlarni sozlash</p>
                    </div>
                    <button @click="showAddModal = false" type="button" class="w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                        ✕
                    </button>
                </div>

                <form action="{{ route('seller.products.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      class="space-y-5"
                      x-data="productAddForm()">
                    @csrf

                    <!-- 1. Mahsulot Nomi -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Mahsulot Nomi *</label>
                            <span class="text-[11px] text-slate-400">Vitrinalarda va qidiruvda ko'rinadi</span>
                        </div>
                        <input type="text" 
                               name="name" 
                               required 
                               placeholder="Masalan: Knauf Profil CD 60x27 3m yoki Qurilish Sementi M-500" 
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#123C2F] focus:border-[#123C2F]">
                        <p class="text-[11px] text-slate-500 mt-1">Xaridor darhol tushunishi uchun brend, model va asosiy o'lchamlarni kiriting.</p>
                    </div>

                    <!-- 2. Kategoriya & Savdo Rejimi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Kategoriya (Toifa) *</label>
                            </div>
                            <select name="category_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#123C2F] bg-white">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->display_icon }} {{ $cat->name }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">Mahsulot katalogda qaysi bo'lim va filtrlarda aks etishi.</p>
                        </div>

                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Savdo Rejimi (Formati) *</label>
                            </div>
                            <select name="trade_mode" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:outline-none focus:ring-2 focus:ring-[#123C2F] bg-white">
                                <option value="both">Ulgurji va chakana (Ikkisi ham — donalab ham, optom ham)</option>
                                <option value="wholesale">Faqat ulgurji (Faqat yirik hajmda, MOQ bo'yicha)</option>
                                <option value="retail">Faqat chakana (Oddiy do'kondagi kabi donalab)</option>
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">Chakana va ulgurji xaridorlar o'rtasida to'g'ri taqsimlash.</p>
                        </div>
                    </div>

                    <!-- 3. O'lchov va Buyurtma Qoidalari (Birligi, MOQ, Qadam, Qutida) -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                <span>📐</span> O'lchov Va Buyurtma Qoidalari
                            </span>
                            <span class="text-[11px] text-slate-400">Savatcha va hisob-kitob uchun</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Birligi *</label>
                                <select name="unit" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F] bg-white">
                                    <option value="piece">Dona (piece)</option>
                                    <option value="box">Quti (box)</option>
                                    <option value="kg">Kg (kilogramm)</option>
                                    <option value="ton">Tonna (ton)</option>
                                </select>
                                <span class="text-[10px] text-slate-500 block mt-1">Baza o'lchovi</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1" title="Minimum Order Quantity">Min. Buyurtma (MOQ) *</label>
                                <input type="number" step="0.001" name="min_order_quantity" value="1" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F] bg-white">
                                <span class="text-[10px] text-slate-500 block mt-1">Kamida sotiladi</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Qadam (Step) *</label>
                                <input type="number" step="0.001" name="unit_step" value="1" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F] bg-white">
                                <span class="text-[10px] text-slate-500 block mt-1">Savatda oshish</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">Qutida donasi</label>
                                <input type="number" name="items_per_box" value="1" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F] bg-white">
                                <span class="text-[10px] text-slate-500 block mt-1">1 qutida/pachkada</span>
                            </div>
                        </div>
                    </div>

                    <!-- 4. Pog'onali Narx Zinapoyasi (Tiered Pricing - UZS) -->
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="block text-xs font-black text-[#123C2F] uppercase tracking-wider flex items-center gap-1.5">
                                <span>⚡</span> Pog'onali Narx Zinapoyasi (Tiered Pricing - UZS)
                            </span>
                            <span class="text-[11px] font-bold text-emerald-800">Avtomat chegirma</span>
                        </div>
                        <p class="text-[11px] text-slate-600 mb-3">
                            Xaridor qancha ko'p birlik xarid qilsa, har bir dona narxi shuncha arzonlashadi (B2B savdo modeli):
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">1 - 299 birlik (Chakana) *</label>
                                <input type="number" name="retail_price" required placeholder="Masalan: 45000" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F] bg-white">
                                <span class="text-[10px] text-slate-500 block mt-0.5">Asosiy chakana narx</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">300 - 999 birlik (Optom)</label>
                                <input type="number" name="tier_300_price" placeholder="Masalan: 41000" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F] bg-white">
                                <span class="text-[10px] text-slate-500 block mt-0.5">Bo'sh qolsa: -8% avto chegirma</span>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 mb-1">1000+ birlik (Katta Optom)</label>
                                <input type="number" name="tier_1000_price" placeholder="Masalan: 38000" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F] bg-white">
                                <span class="text-[10px] text-slate-500 block mt-0.5">Bo'sh qolsa: -15% dilerlik narxi</span>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Ombor va Zaxira -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">SKU Kodi</label>
                            </div>
                            <input type="text" name="sku" placeholder="Ixtiyoriy (avto yaratiladi)" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F]">
                            <span class="text-[10px] text-slate-400 block mt-0.5">Noyob ombor artiquli</span>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Qaysi Omborga</label>
                            </div>
                            <select name="warehouse_id" class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F] bg-white">
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-[10px] text-slate-400 block mt-0.5">Tovar joylashgan ombor</span>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Boshlang'ich Qoldiq *</label>
                            </div>
                            <input type="number" step="0.001" name="stock_quantity" value="250" required class="w-full px-3 py-2 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F]">
                            <span class="text-[10px] text-slate-400 block mt-0.5">Hozir sotuvda bor miqdor</span>
                        </div>
                    </div>

                    <!-- 6. Mahsulot Rasmi (Rasm yuklash / URL) -->
                    <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <div>
                                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                                    <span>📸</span> Mahsulot Rasmi (Rasm Yuklash) *
                                </label>
                                <p class="text-[11px] text-slate-500">
                                    Vitrinalarda va mahsulot sahifasida <strong>2.6x radiusli kattalashtirish lupasi</strong> bilan ko'rsatiladi.
                                </p>
                            </div>

                            <!-- Tabs: File upload vs URL -->
                            <div class="inline-flex rounded-xl bg-slate-100 p-0.5 shrink-0 text-xs">
                                <button type="button" 
                                        @click="imageMode = 'file'" 
                                        :class="imageMode === 'file' ? 'bg-white text-[#123C2F] shadow-xs font-bold' : 'text-slate-600 font-medium'"
                                        class="px-3 py-1 rounded-lg transition-all">
                                    📁 Qurilmadan yuklash
                                </button>
                                <button type="button" 
                                        @click="imageMode = 'url'" 
                                        :class="imageMode === 'url' ? 'bg-white text-[#123C2F] shadow-xs font-bold' : 'text-slate-600 font-medium'"
                                        class="px-3 py-1 rounded-lg transition-all">
                                    🔗 Havola (URL)
                                </button>
                            </div>
                        </div>

                        <!-- Mode 1: File Upload (Drag & Drop or Picker) -->
                        <div x-show="imageMode === 'file'">
                            <input type="file" 
                                   name="image_file" 
                                   x-ref="fileInput" 
                                   @change="onFileSelect($event)" 
                                   accept="image/png,image/jpeg,image/webp,image/svg+xml" 
                                   class="hidden">

                            <!-- File Selected Preview -->
                            <template x-if="previewUrl">
                                <div class="flex items-center justify-between p-3.5 bg-emerald-50/70 border border-emerald-300 rounded-2xl">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="relative w-16 h-16 rounded-xl overflow-hidden border-2 border-emerald-400 bg-white shadow-xs shrink-0">
                                            <img :src="previewUrl" alt="Rasm ko'rinishi" class="w-full h-full object-cover">
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-black text-slate-800 truncate" x-text="fileName"></p>
                                            <p class="text-[11px] text-emerald-800 font-bold" x-text="fileSize"></p>
                                            <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-bold mt-0.5">
                                                <span>✓</span> Rasm serverga yuklashga tayyor
                                            </span>
                                        </div>
                                    </div>
                                    <button type="button" 
                                            @click="clearFile()" 
                                            class="px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50 rounded-xl font-bold border border-rose-200 transition shrink-0">
                                        ✕ O'chirish
                                    </button>
                                </div>
                            </template>

                            <!-- Dropzone (When no file selected) -->
                            <template x-if="!previewUrl">
                                <div @dragover.prevent="isDragging = true"
                                     @dragleave.prevent="isDragging = false"
                                     @drop.prevent="handleDrop($event)"
                                     @click="$refs.fileInput.click()"
                                     :class="isDragging ? 'border-[#123C2F] bg-emerald-50 scale-[1.01]' : 'border-slate-300 hover:border-[#123C2F] hover:bg-slate-50/80'"
                                     class="border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all duration-200">
                                    <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-100/70 text-[#123C2F] flex items-center justify-center mb-2 shadow-xs">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <p class="text-xs font-bold text-slate-800">
                                        <span class="text-[#123C2F] underline decoration-emerald-500 font-extrabold">Kompyuter yoki telefondan rasm tanlang</span> yoki bu yerga sudrab tashlang
                                    </p>
                                    <p class="text-[11px] text-slate-400 mt-1">PNG, JPG, WEBP, SVG (maks. 10 MB)</p>
                                </div>
                            </template>
                        </div>

                        <!-- Mode 2: URL Input -->
                        <div x-show="imageMode === 'url'" class="space-y-1.5">
                            <input type="text" 
                                   name="image_url" 
                                   placeholder="Masalan: /images/products/sement.jpg yoki https://..." 
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F] bg-white">
                            <span class="text-[11px] text-slate-400 block">
                                Bo'sh qoldirilsa toifaga mos standart rasm avtomatik o'rnatiladi.
                            </span>
                        </div>
                    </div>

                    <!-- 7. Mahsulot Haqida Tavsif -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Mahsulot Haqida Batafsil Tavsif</label>
                            <span class="text-[11px] text-slate-400">Texnik parametrlar, GOST, o'lchamlar</span>
                        </div>
                        <textarea name="description" 
                                  rows="3" 
                                  placeholder="Tovarning o'lchamlari, texnik xususiyatlari, markasi, sertifikatlari, kafolati va foydalanish yo'riqnomasi..." 
                                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:outline-none focus:ring-2 focus:ring-[#123C2F]"></textarea>
                    </div>

                    <!-- Footer actions -->
                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button @click="showAddModal = false" type="button" class="px-5 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs transition">
                            Bekor qilish
                        </button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#123C2F] hover:bg-[#0e2f25] text-white font-bold text-xs shadow-md transition flex items-center gap-2">
                            <span>Katalogga Qo'shish</span>
                            <span class="text-[#C9F46B]">✓</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <script>
    function productAddForm() {
        return {
            imageMode: 'file',
            previewUrl: null,
            fileName: '',
            fileSize: '',
            isDragging: false,

            onFileSelect(event) {
                const files = event.target.files;
                if (files && files[0]) {
                    const file = files[0];
                    this.fileName = file.name;
                    const sizeMb = file.size / (1024 * 1024);
                    this.fileSize = sizeMb >= 1 ? sizeMb.toFixed(2) + ' MB' : (file.size / 1024).toFixed(0) + ' KB';
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.previewUrl = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            handleDrop(event) {
                this.isDragging = false;
                const files = event.dataTransfer.files;
                if (files && files[0] && files[0].type.startsWith('image/')) {
                    this.$refs.fileInput.files = files;
                    this.onFileSelect({ target: { files: files } });
                }
            },

            clearFile() {
                this.previewUrl = null;
                this.fileName = '';
                this.fileSize = '';
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            }
        };
    }
    </script>

</body>
</html>
