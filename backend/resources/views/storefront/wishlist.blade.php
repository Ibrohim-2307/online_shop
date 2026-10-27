<x-layouts.app title="Sevimlilar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-20"
         x-data="{
             init() {
                 // Sync guest localStorage favorites with server if authenticated
                 @auth
                     try {
                         let localItems = JSON.parse(localStorage.getItem('wishlist') || '[]');
                         if (Array.isArray(localItems) && localItems.length > 0) {
                             fetch('{{ route('wishlist.sync') }}', {
                                 method: 'POST',
                                 headers: {
                                     'Content-Type': 'application/json',
                                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                     'Accept': 'application/json'
                                 },
                                 body: JSON.stringify({ product_ids: localItems })
                             }).then(res => res.json()).then(data => {
                                 if (data.success && Array.isArray(data.product_ids)) {
                                     if (window.Alpine && Alpine.store('wishlist')) {
                                         Alpine.store('wishlist').items = data.product_ids;
                                     }
                                 }
                             }).catch(() => {});
                         }
                     } catch(e) {}
                 @endauth
             }
         }">

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-1.5 text-xs text-[#64748B] mb-3 sm:mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">Sevimlilar</span>
        </nav>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4 sm:mb-8">
            <div>
                <h1 class="text-xl sm:text-3xl font-black text-[#172326] tracking-tight flex items-center gap-2">
                    <span>Saqlangan Mahsulotlar</span>
                    <span class="text-xs sm:text-sm font-bold px-2 py-0.5 rounded-full bg-[#123C2F] text-[#C9F46B]">
                        {{ $products->total() }}
                    </span>
                </h1>
                <p class="text-[11px] sm:text-xs text-[#64748B] mt-0.5 sm:mt-1">
                    Siz tanlagan va keyinchalik xarid qilish uchun hisobingizda saqlangan tovarlar
                </p>
            </div>
            <a href="{{ route('catalog') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#123C2F] hover:underline">
                <span>Katalogga qaytish</span>
                <span>→</span>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($products->count() > 0)
            <!-- Product Grid: 2 columns on mobile, 4 on desktop -->
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-6">
                @foreach($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8 sm:mt-10">
                {{ $products->links() }}
            </div>
        @else
            <!-- Empty Wishlist State (Compact on mobile) -->
            <div class="bg-white rounded-2xl sm:rounded-3xl border border-[#E8ECE4] p-6 sm:p-12 text-center max-w-lg mx-auto shadow-2xs">
                <div class="w-14 h-14 sm:w-20 sm:h-20 rounded-2xl sm:rounded-3xl bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-3 sm:mb-5 shadow-2xs">
                    <svg class="w-7 h-7 sm:w-10 sm:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                </div>
                <h3 class="font-black text-lg sm:text-xl text-[#172326] mb-1.5 sm:mb-2">Sevimlilar ro'yxati bo'sh</h3>
                <p class="text-xs text-[#64748B] leading-relaxed mb-4 sm:mb-6">
                    Siz hali birorta ham mahsulotni saqlamadingiz. Katalogdagi yurakcha belgisini bosib, o'zingizga ma'qul tovarlarni ushbu ro'yxatga qo'shib borishingiz mumkin.
                </p>
                <a href="{{ route('catalog') }}" class="inline-flex px-5 py-2.5 sm:px-6 sm:py-3.5 rounded-xl sm:rounded-2xl bg-[#123C2F] text-[#C9F46B] font-black text-xs hover:brightness-110 transition-all shadow-md">
                    Katalogni ko'rish va tanlash →
                </a>
            </div>
        @endif

    </div>
</x-layouts.app>
