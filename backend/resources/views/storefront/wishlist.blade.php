<x-layouts.app title="Sevimlilar">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16"
         x-data="{
             wishlistItems: [],
             loading: true,
             init() {
                 this.wishlistItems = $store.wishlist.items;
                 this.loading = false;
             }
         }">

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-xs text-[#64748B] mb-6" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-[#123C2F]">Bosh sahifa</a>
            <span>/</span>
            <span class="text-[#172326] font-semibold">Sevimlilar</span>
        </nav>

        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-black text-[#172326] tracking-tight">Saqlangan Mahsulotlar</h1>
                <p class="text-xs text-[#64748B] mt-1">Sizga yoqqan va keyinchalik xarid qilish uchun saqlangan tovarlar</p>
            </div>
            <a href="{{ route('catalog') }}" class="text-xs font-bold text-[#123C2F] hover:underline">
                Katalogga qaytish →
            </a>
        </div>

        <!-- Empty Wishlist State -->
        <div x-show="!loading && $store.wishlist.count() === 0" class="bg-white rounded-3xl border border-[#E8ECE4] p-12 text-center max-w-lg mx-auto">
            <div class="w-16 h-16 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </div>
            <h3 class="font-bold text-lg text-[#172326] mb-1">Sevimlilar ro'yxati bo'sh</h3>
            <p class="text-xs text-[#64748B] mb-6">
                Siz hali birorta ham mahsulotni saqlamadingiz. Katalogdagi yurakcha belgisini bosib, mahsulotlarni bu yerga qo'shishingiz mumkin.
            </p>
            <a href="{{ route('catalog') }}" class="inline-flex px-6 py-3 rounded-2xl bg-[#123C2F] text-white font-bold text-xs hover:bg-[#0d2a21] transition-colors">
                Katalogni ko'rish
            </a>
        </div>

        <!-- Informative note when items exist in localStorage -->
        <div x-show="!loading && $store.wishlist.count() > 0" class="p-6 rounded-3xl bg-white border border-[#E8ECE4] text-center max-w-md mx-auto space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-[#C9F46B] text-[#123C2F] flex items-center justify-center mx-auto font-black text-xl">
                ❤️
            </div>
            <div>
                <h3 class="font-bold text-base text-[#172326]">
                    <span x-text="$store.wishlist.count()"></span> ta mahsulot saqlangan
                </h3>
                <p class="text-xs text-[#64748B] mt-1">
                    Brauzeringizda saqlangan mahsulotlar ro'yxati faol.
                </p>
            </div>
            <a href="{{ route('catalog') }}" class="inline-block px-5 py-2.5 rounded-xl bg-[#123C2F] text-white text-xs font-bold hover:bg-[#0d2a21]">
                Katalogda davom etish
            </a>
        </div>

    </div>
</x-layouts.app>
