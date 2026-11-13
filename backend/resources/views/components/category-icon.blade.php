@props([
    'slug' => '',
    'class' => 'w-7 h-7 sm:w-8 sm:h-8',
    'variant' => 'svg', // 'svg', 'pod', 'chip', 'mini'
    'active' => false,
    'emoji' => null,
])

@php
    $normalized = strtolower(trim((string)$slug));

    // Refined theme configuration per category (Apple / Stripe / Uzum grade)
    $theme = match(true) {
        str_contains($normalized, 'qurilish') || str_contains($normalized, 'sement') || str_contains($normalized, 'gisht') || str_contains($normalized, 'beton') || str_contains($normalized, 'armatura') => [
            'type' => 'construction',
            'emoji' => '🧱',
            'label' => 'Qurilish materiallari',
            'podBg' => 'bg-gradient-to-br from-amber-500/15 via-orange-500/10 to-amber-500/5',
            'podBorder' => 'border-amber-200/80 group-hover:border-amber-400/90',
            'podShadow' => 'group-hover:shadow-amber-500/15',
            'glow' => 'bg-amber-400/25',
            'iconStroke' => 'stroke-amber-700',
            'iconFill' => 'fill-amber-500/20',
            'iconFillLight' => 'fill-amber-500/10',
            'emojiBadge' => 'bg-amber-50/95 border-amber-200/90 text-amber-950',
            'chipActive' => 'bg-amber-600 text-white border-amber-600',
        ],
        str_contains($normalized, 'sanoat') || str_contains($normalized, 'mashina') || (str_contains($normalized, 'uskuna') && !str_contains($normalized, 'asbob')) || str_contains($normalized, 'stanok') || str_contains($normalized, 'generator') => [
            'type' => 'machinery',
            'emoji' => '🏭',
            'label' => 'Sanoat uskunalari',
            'podBg' => 'bg-gradient-to-br from-blue-600/15 via-indigo-500/10 to-sky-500/5',
            'podBorder' => 'border-blue-200/80 group-hover:border-blue-400/90',
            'podShadow' => 'group-hover:shadow-blue-500/15',
            'glow' => 'bg-blue-400/25',
            'iconStroke' => 'stroke-blue-700',
            'iconFill' => 'fill-blue-500/20',
            'iconFillLight' => 'fill-blue-500/10',
            'emojiBadge' => 'bg-blue-50/95 border-blue-200/90 text-blue-950',
            'chipActive' => 'bg-blue-600 text-white border-blue-600',
        ],
        str_contains($normalized, 'oziq-ovqat') || str_contains($normalized, 'ichimlik') || str_contains($normalized, 'don') || str_contains($normalized, 'un') || str_contains($normalized, 'yog') || str_contains($normalized, 'shakar') => [
            'type' => 'food',
            'emoji' => '🌾',
            'label' => 'Oziq-ovqat va ichimliklar',
            'podBg' => 'bg-gradient-to-br from-emerald-600/15 via-teal-500/10 to-emerald-500/5',
            'podBorder' => 'border-emerald-200/80 group-hover:border-emerald-400/90',
            'podShadow' => 'group-hover:shadow-emerald-500/15',
            'glow' => 'bg-emerald-400/25',
            'iconStroke' => 'stroke-emerald-700',
            'iconFill' => 'fill-emerald-500/20',
            'iconFillLight' => 'fill-emerald-500/10',
            'emojiBadge' => 'bg-emerald-50/95 border-emerald-200/90 text-emerald-950',
            'chipActive' => 'bg-emerald-600 text-white border-emerald-600',
        ],
        str_contains($normalized, 'elektr') || str_contains($normalized, 'yoritish') || str_contains($normalized, 'chiroq') || str_contains($normalized, 'kabel') || str_contains($normalized, 'rozetka') => [
            'type' => 'electrical',
            'emoji' => '💡',
            'label' => 'Elektr va yoritish',
            'podBg' => 'bg-gradient-to-br from-yellow-500/20 via-amber-400/10 to-yellow-500/5',
            'podBorder' => 'border-yellow-300/80 group-hover:border-amber-400/90',
            'podShadow' => 'group-hover:shadow-yellow-500/20',
            'glow' => 'bg-yellow-400/25',
            'iconStroke' => 'stroke-amber-600',
            'iconFill' => 'fill-yellow-400/25',
            'iconFillLight' => 'fill-yellow-400/10',
            'emojiBadge' => 'bg-yellow-50/95 border-yellow-200/90 text-yellow-950',
            'chipActive' => 'bg-amber-600 text-white border-amber-600',
        ],
        str_contains($normalized, 'boyoq') || str_contains($normalized, 'kimyo') || str_contains($normalized, 'gruntovka') || str_contains($normalized, 'lak') => [
            'type' => 'chemicals',
            'emoji' => '🎨',
            'label' => 'Bo\'yoqlar va kimyo',
            'podBg' => 'bg-gradient-to-br from-purple-600/15 via-fuchsia-500/10 to-violet-500/5',
            'podBorder' => 'border-purple-200/80 group-hover:border-purple-400/90',
            'podShadow' => 'group-hover:shadow-purple-500/15',
            'glow' => 'bg-purple-400/25',
            'iconStroke' => 'stroke-purple-700',
            'iconFill' => 'fill-purple-500/20',
            'iconFillLight' => 'fill-purple-500/10',
            'emojiBadge' => 'bg-purple-50/95 border-purple-200/90 text-purple-950',
            'chipActive' => 'bg-purple-600 text-white border-purple-600',
        ],
        str_contains($normalized, 'santexnika') || str_contains($normalized, 'quvur') || str_contains($normalized, 'suv') || str_contains($normalized, 'kran') => [
            'type' => 'plumbing',
            'emoji' => '🚰',
            'label' => 'Santexnika va quvurlar',
            'podBg' => 'bg-gradient-to-br from-cyan-600/15 via-sky-500/10 to-cyan-500/5',
            'podBorder' => 'border-cyan-200/80 group-hover:border-cyan-400/90',
            'podShadow' => 'group-hover:shadow-cyan-500/15',
            'glow' => 'bg-cyan-400/25',
            'iconStroke' => 'stroke-cyan-700',
            'iconFill' => 'fill-cyan-500/20',
            'iconFillLight' => 'fill-cyan-500/10',
            'emojiBadge' => 'bg-cyan-50/95 border-cyan-200/90 text-cyan-950',
            'chipActive' => 'bg-cyan-600 text-white border-cyan-600',
        ],
        str_contains($normalized, 'asbob') || str_contains($normalized, 'instrument') || str_contains($normalized, 'drel') || str_contains($normalized, 'bolgarka') => [
            'type' => 'tools',
            'emoji' => '🛠️',
            'label' => 'Asbob-uskunalar',
            'podBg' => 'bg-gradient-to-br from-orange-600/15 via-amber-500/10 to-orange-500/5',
            'podBorder' => 'border-orange-200/80 group-hover:border-orange-400/90',
            'podShadow' => 'group-hover:shadow-orange-500/15',
            'glow' => 'bg-orange-400/25',
            'iconStroke' => 'stroke-orange-700',
            'iconFill' => 'fill-orange-500/20',
            'iconFillLight' => 'fill-orange-500/10',
            'emojiBadge' => 'bg-orange-50/95 border-orange-200/90 text-orange-950',
            'chipActive' => 'bg-orange-600 text-white border-orange-600',
        ],
        str_contains($normalized, 'avto') || str_contains($normalized, 'mashina-qism') => [
            'type' => 'automotive',
            'emoji' => '🚗',
            'label' => 'Avto ehtiyot qismlar',
            'podBg' => 'bg-gradient-to-br from-rose-600/15 via-red-500/10 to-pink-500/5',
            'podBorder' => 'border-rose-200/80 group-hover:border-rose-400/90',
            'podShadow' => 'group-hover:shadow-rose-500/15',
            'glow' => 'bg-rose-400/25',
            'iconStroke' => 'stroke-rose-700',
            'iconFill' => 'fill-rose-500/20',
            'iconFillLight' => 'fill-rose-500/10',
            'emojiBadge' => 'bg-rose-50/95 border-rose-200/90 text-rose-950',
            'chipActive' => 'bg-rose-600 text-white border-rose-600',
        ],
        str_contains($normalized, 'mebel') || str_contains($normalized, 'interyer') => [
            'type' => 'furniture',
            'emoji' => '🛋️',
            'label' => 'Mebel va interyer',
            'podBg' => 'bg-gradient-to-br from-indigo-600/15 via-slate-500/10 to-indigo-500/5',
            'podBorder' => 'border-indigo-200/80 group-hover:border-indigo-400/90',
            'podShadow' => 'group-hover:shadow-indigo-500/15',
            'glow' => 'bg-indigo-400/25',
            'iconStroke' => 'stroke-indigo-700',
            'iconFill' => 'fill-indigo-500/20',
            'iconFillLight' => 'fill-indigo-500/10',
            'emojiBadge' => 'bg-indigo-50/95 border-indigo-200/90 text-indigo-950',
            'chipActive' => 'bg-indigo-600 text-white border-indigo-600',
        ],
        str_contains($normalized, 'kiyim') || str_contains($normalized, 'forma') => [
            'type' => 'workwear',
            'emoji' => '🦺',
            'label' => 'Maxsus kiyimlar',
            'podBg' => 'bg-gradient-to-br from-emerald-600/15 via-lime-500/10 to-teal-500/5',
            'podBorder' => 'border-lime-300/80 group-hover:border-emerald-400/90',
            'podShadow' => 'group-hover:shadow-lime-500/15',
            'glow' => 'bg-lime-400/25',
            'iconStroke' => 'stroke-emerald-800',
            'iconFill' => 'fill-lime-400/25',
            'iconFillLight' => 'fill-lime-400/10',
            'emojiBadge' => 'bg-lime-50/95 border-lime-200/90 text-lime-950',
            'chipActive' => 'bg-emerald-700 text-white border-emerald-700',
        ],
        default => [
            'type' => 'general',
            'emoji' => '📦',
            'label' => 'Boshqa toifalar',
            'podBg' => 'bg-gradient-to-br from-[#123C2F]/10 via-[#C9F46B]/15 to-transparent',
            'podBorder' => 'border-[#E2E8F0] group-hover:border-[#123C2F]/40',
            'podShadow' => 'group-hover:shadow-[#123C2F]/10',
            'glow' => 'bg-[#C9F46B]/25',
            'iconStroke' => 'stroke-[#123C2F]',
            'iconFill' => 'fill-[#C9F46B]/20',
            'iconFillLight' => 'fill-[#C9F46B]/10',
            'emojiBadge' => 'bg-white/95 border-emerald-200/90 text-[#123C2F]',
            'chipActive' => 'bg-[#123C2F] text-[#C9F46B] border-[#123C2F]',
        ],
    };

    $activeEmoji = $emoji ?: $theme['emoji'];
    $svgClass = $variant === 'pod' ? 'w-8 h-8 sm:w-10 sm:h-10' : ($variant === 'mini' ? 'w-3.5 h-3.5' : $class);
@endphp

@if($variant === 'pod')
    {{-- High-Tier Marketplace Icon Pod (Apple / Stripe / Telegram Grade) --}}
    <div {{ $attributes->merge(['class' => "relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl sm:rounded-3xl {$theme['podBg']} border {$theme['podBorder']} flex items-center justify-center mb-2.5 sm:mb-3 transition-all duration-300 group-hover:scale-105 shadow-2xs group-hover:shadow-lg {$theme['podShadow']}"]) }}>
        
        <!-- Ambient Tinted Glow on Hover -->
        <div class="absolute inset-0 rounded-2xl sm:rounded-3xl {{ $theme['glow'] }} blur-md opacity-0 group-hover:opacity-75 transition-opacity duration-300 pointer-events-none"></div>

        <!-- Crisp Dual-Tone SVG Vector Centered -->
        <div class="relative z-10 flex items-center justify-center transition-transform duration-300 group-hover:scale-110">
            @include('components.category-icon-svg', ['type' => $theme['type'], 'theme' => $theme, 'svgClass' => $svgClass])
        </div>

        <!-- Sleek Floating Glassmorphic Emoji Accent Badge -->
        <div class="absolute -top-1.5 -right-1.5 z-20 w-6 h-6 sm:w-7 sm:h-7 rounded-full {{ $theme['emojiBadge'] }} backdrop-blur-md shadow-xs border flex items-center justify-center text-xs sm:text-sm select-none transition-transform duration-300 group-hover:scale-115 group-hover:-rotate-6" title="{{ $theme['label'] }}">
            <span class="leading-none transform-gpu">{{ $activeEmoji }}</span>
        </div>
    </div>

@elseif($variant === 'chip')
    {{-- Compact Chip Pod for Horizontal Category Navigation --}}
    <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl {{ $active ? 'bg-white/20' : $theme['podBg'] }} border {{ $active ? 'border-white/30' : $theme['podBorder'] }} text-xs shrink-0 transition-transform duration-200 group-hover:scale-110">
        <span class="leading-none select-none">{{ $activeEmoji }}</span>
    </span>

@elseif($variant === 'mini')
    {{-- Refined Mini Pod with Micro Emoji for Catalog Sidebar Lists --}}
    <span class="relative inline-flex items-center justify-center w-8 h-8 rounded-xl {{ $active ? 'bg-white/20' : $theme['podBg'] }} border {{ $active ? 'border-white/30' : $theme['podBorder'] }} shrink-0 transition-transform duration-200 group-hover:scale-105">
        <span class="scale-90 flex items-center justify-center">
            @include('components.category-icon-svg', ['type' => $theme['type'], 'theme' => $theme, 'svgClass' => $svgClass])
        </span>
        <span class="absolute -top-1 -right-1 text-[11px] leading-none select-none drop-shadow-xs">
            {{ $activeEmoji }}
        </span>
    </span>

@else
    {{-- Standalone SVG Vector (Backward Compatible with all layouts & navbars) --}}
    @include('components.category-icon-svg', ['type' => $theme['type'], 'theme' => $theme, 'svgClass' => $attributes->merge(['class' => $class])->get('class')])
@endif
