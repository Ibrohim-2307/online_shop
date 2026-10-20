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
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased font-sans" x-data="{ currentTab: 'products' }">

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
            <button @click="currentTab = 'orders'" :class="currentTab === 'orders' ? 'bg-[#123C2F] text-white' : 'text-slate-600 hover:text-slate-900 bg-white'" class="px-4 py-2 rounded-xl text-xs font-bold transition">
                Buyurtmalar ({{ $suborders->count() }})
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
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Do'kon Mahsulotlari va Optom Narx Zinapoyasi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
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

        <!-- TAB 2: ORDERS -->
        <div x-show="currentTab === 'orders'" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-900 text-sm">Kelib Tushgan Buyurtmalar (Suborders)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="p-3.5">Ichki Buyurtma #</th>
                            <th class="p-3.5">Ota Buyurtma</th>
                            <th class="p-3.5">Yetkazish Holati</th>
                            <th class="p-3.5">Summa</th>
                            <th class="p-3.5">Komissiya</th>
                            <th class="p-3.5">Sana</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($suborders as $sub)
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3.5 font-bold text-slate-900 font-mono">{{ $sub->suborder_number }}</td>
                                <td class="p-3.5 font-mono text-slate-500">{{ $sub->order->order_number ?? '—' }}</td>
                                <td class="p-3.5">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-800">
                                        {{ $sub->shipment->status ?? $sub->status }}
                                    </span>
                                </td>
                                <td class="p-3.5 font-bold text-slate-900">{{ number_format($sub->total_amount) }} UZS</td>
                                <td class="p-3.5 text-slate-500">{{ number_format($sub->commission_amount) }} UZS</td>
                                <td class="p-3.5 text-slate-400">{{ $sub->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-slate-400">Do'konga buyurtmalar hali kelib tushmagan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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

    </main>

</body>
</html>
