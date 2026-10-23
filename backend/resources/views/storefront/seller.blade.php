<x-layouts.app :title="$seller->name">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16">

        <!-- Breadcrumb -->
        <nav class="flex flex-wrap items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <a href="{{ route('catalog') }}" class="hover:text-[#123C2F]">Sotuvchilar</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">{{ $seller->name }}</span>
        </nav>

        <!-- Seller Profile Banner Card -->
        <div class="bg-white rounded-3xl border border-[#E8ECE4] p-6 sm:p-10 mb-10 shadow-xs relative overflow-hidden">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                
                <div class="flex items-center gap-5">
                    <div class="w-20 h-20 rounded-3xl bg-[#123C2F] text-[#C9F46B] font-black text-3xl flex items-center justify-center shadow-sm">
                        {{ mb_substr($seller->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h1 class="text-2xl sm:text-3xl font-black text-[#172326] tracking-tight">
                                {{ $seller->name }}
                            </h1>
                            <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 text-[10px] font-bold border border-emerald-200">
                                ✓ Litsenziyalangan
                            </span>
                        </div>
                        <p class="text-xs text-[#64748B] mb-2 font-medium">
                            {{ $seller->legal_name ?? $seller->name }} • INN: {{ $seller->tax_id ?? 'Mavjud emas' }}
                        </p>
                        <div class="flex flex-wrap items-center gap-3 text-xs text-[#172326]">
                            <span class="flex items-center gap-1 font-bold text-amber-500">
                                ★ {{ number_format($seller->rating, 1) }} reyting
                            </span>
                            @if($seller->phone)
                                <span class="text-[#64748B]">• Tel: {{ $seller->phone }}</span>
                            @endif
                            @if($seller->address)
                                <span class="text-[#64748B]">• {{ $seller->address }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Contact Actions & Trust badge -->
                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="flex items-center gap-2">
                        @if($seller->phone)
                            <a href="tel:{{ $seller->phone }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-emerald-600 text-white font-bold text-xs hover:bg-emerald-700 shadow-sm transition">
                                <span>📞</span>
                                <span>{{ $seller->phone }}</span>
                            </a>
                        @endif
                        <a href="https://t.me/market_seller_support?text={{ urlencode('Salom, men ' . $seller->name . ' do\'koni mahsulotlari bo\'yicha murojaat qilyapman.') }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs hover:bg-[#0d2a21] shadow-sm transition">
                            <span>💬</span>
                            <span>Telegramda yozish</span>
                        </a>
                    </div>
                    <div class="px-5 py-3 rounded-2xl bg-[#FAFBF7] border border-[#E8ECE4] text-center text-xs">
                        <span class="text-[#64748B] font-semibold block">Do'konda mavjud:</span>
                        <span class="text-lg font-black text-[#123C2F]">{{ $products->total() }} ta tovar</span>
                    </div>
                </div>

            </div>
        </div>

        <!-- Seller Products Grid -->
        <div>
            <h2 class="text-2xl font-black text-[#172326] tracking-tight mb-6">
                Sotuvchining Mahsulotlari
            </h2>

            @if($products->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                    @foreach($products as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $products->links() }}
                </div>
            @else
                <div class="bg-white rounded-3xl border border-[#E8ECE4] p-12 text-center text-[#64748B] text-sm">
                    Ushbu do'konda hozircha tasdiqlangan mahsulotlar yo'q.
                </div>
            @endif
        </div>

    </div>
</x-layouts.app>
