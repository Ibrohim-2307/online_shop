<x-layouts.app title="Kuzatuv topilmadi">
    <div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
        <div class="w-16 h-16 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
            🔍
        </div>
        <h1 class="text-2xl font-black text-[#172326] mb-2">Trek raqami topilmadi</h1>
        <p class="text-xs text-[#64748B] mb-6">
            Kiritilgan <strong class="font-mono text-[#123C2F]">{{ $trackingNumber ?? 'kod' }}</strong> bo'yicha ma'lumot mavjud emas. Iltimos raqamni to'g'ri kiritganingizni tekshiring yoki demo trekni tanlang:
        </p>

        <a href="{{ route('tracking', ['trackingNumber' => 'TRK-UZ-778899']) }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-[#123C2F] text-[#C9F46B] font-bold text-xs hover:bg-[#0d2a21] transition-all shadow-sm">
            <span>📍 Demo Jonli Xaritani ochish (TRK-UZ-778899)</span>
        </a>
    </div>
</x-layouts.app>