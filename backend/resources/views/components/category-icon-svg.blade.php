@props([
    'type' => 'general',
    'theme' => [],
    'svgClass' => 'w-7 h-7',
])

@if($type === 'construction')
    {{-- Construction Materials: Precision Masonry Blocks --}}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="{{ $svgClass }}">
        <rect x="3" y="4" width="18" height="16" rx="2.5" class="{{ $theme['iconFillLight'] ?? 'fill-amber-500/10' }} {{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M3 9.5h18" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M3 15h18" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M9 4v5.5" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M15 4v5.5" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M6 9.5V15" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M12 9.5V15" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M18 9.5V15" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M9 15v5" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M15 15v5" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
    </svg>

@elseif($type === 'machinery')
    {{-- Industrial Machinery: Precision Turbine & Gear --}}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="{{ $svgClass }}">
        <circle cx="12" cy="12" r="3.5" class="{{ $theme['iconFill'] ?? 'fill-blue-500/20' }} {{ $theme['iconStroke'] ?? 'stroke-blue-700' }}" />
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" class="{{ $theme['iconFillLight'] ?? 'fill-blue-500/10' }} {{ $theme['iconStroke'] ?? 'stroke-blue-700' }}" />
    </svg>

@elseif($type === 'food')
    {{-- Food & Beverages: Golden Wheat Stalks & Leaf --}}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="{{ $svgClass }}">
        <path d="M2 22 17 7" class="{{ $theme['iconStroke'] ?? 'stroke-emerald-700' }}" />
        <path d="M4 14.5a3.5 3.5 0 0 0 4.95 0L10.5 13l-3.5-3.5L5.45 11A3.5 3.5 0 0 0 4 14.5z" class="{{ $theme['iconFill'] ?? 'fill-emerald-500/20' }} {{ $theme['iconStroke'] ?? 'stroke-emerald-700' }}" />
        <path d="M8.5 10a3.5 3.5 0 0 0 4.95 0L15 8.5l-3.5-3.5L10 6.45A3.5 3.5 0 0 0 8.5 10z" class="{{ $theme['iconFill'] ?? 'fill-emerald-500/20' }} {{ $theme['iconStroke'] ?? 'stroke-emerald-700' }}" />
        <path d="M13 5.5a3.5 3.5 0 0 0 4.95 0L19.5 4l-3.5-3.5L14.5 2A3.5 3.5 0 0 0 13 5.5z" class="{{ $theme['iconFill'] ?? 'fill-emerald-500/20' }} {{ $theme['iconStroke'] ?? 'stroke-emerald-700' }}" />
        <path d="M19 8.5c0 3.04-2.46 5.5-5.5 5.5l-2-.5 4.5-4.5 3-.5z" class="{{ $theme['iconFillLight'] ?? 'fill-emerald-500/10' }} {{ $theme['iconStroke'] ?? 'stroke-emerald-700' }}" />
        <path d="M15 13c0 2.5-2 4.5-4.5 4.5l-2-.5 4-4 2.5z" class="{{ $theme['iconFillLight'] ?? 'fill-emerald-500/10' }} {{ $theme['iconStroke'] ?? 'stroke-emerald-700' }}" />
    </svg>

@elseif($type === 'electrical')
    {{-- Electrical & Lighting: Smart Bulb & Radial Rays --}}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="{{ $svgClass }}">
        <path d="M9 18h6" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M10 21h4" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M12 2v2M4.93 4.93l1.41 1.41M19.07 4.93l-1.41 1.41" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M12 7a5.5 5.5 0 0 0-4.8 8.2c.8 1.4 1.8 2.8 1.8 3.8h6c0-1 1-2.4 1.8-3.8A5.5 5.5 0 0 0 12 7z" class="{{ $theme['iconFill'] ?? 'fill-yellow-400/25' }} {{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
        <path d="M10 12l2 2 2-2" class="{{ $theme['iconStroke'] ?? 'stroke-amber-600' }}" />
    </svg>

@elseif($type === 'chemicals')
    {{-- Paints & Chemicals: Paint Roller & Chemical Drop --}}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="{{ $svgClass }}">
        <rect x="3.5" y="3.5" width="14" height="5.5" rx="1.5" class="{{ $theme['iconFill'] ?? 'fill-purple-500/20' }} {{ $theme['iconStroke'] ?? 'stroke-purple-700' }}" />
        <path d="M17.5 6.25H19a2 2 0 0 1 2 2v2.5a2 2 0 0 1-2 2h-9a2 2 0 0 0-2 2v2" class="{{ $theme['iconStroke'] ?? 'stroke-purple-700' }}" />
        <rect x="6.5" y="17" width="3" height="4.5" rx="1" class="{{ $theme['iconFill'] ?? 'fill-purple-500/20' }} {{ $theme['iconStroke'] ?? 'stroke-purple-700' }}" />
        <path d="M5.5 9v2a1 1 0 0 0 2 0V9" class="{{ $theme['iconFillLight'] ?? 'fill-purple-500/10' }} {{ $theme['iconStroke'] ?? 'stroke-purple-700' }}" />
        <circle cx="18" cy="18" r="2.5" class="{{ $theme['iconFill'] ?? 'fill-purple-500/20' }} {{ $theme['iconStroke'] ?? 'stroke-purple-700' }}" />
    </svg>

@elseif($type === 'plumbing')
    {{-- Plumbing & Pipes: Water Valve & Droplet Flow --}}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="{{ $svgClass }}">
        <path d="M3 13h10a4 4 0 0 0 4-4V7" class="{{ $theme['iconStroke'] ?? 'stroke-cyan-700' }}" />
        <path d="M14 4h6M17 2v4" class="{{ $theme['iconStroke'] ?? 'stroke-cyan-700' }}" />
        <path d="M3 9v8" class="{{ $theme['iconStroke'] ?? 'stroke-cyan-700' }}" />
        <path d="M17 11v2a2 2 0 0 1-2 2h-1" class="{{ $theme['iconStroke'] ?? 'stroke-cyan-700' }}" />
        <path d="M7 13v-3a2 2 0 0 0-2-2H3" class="{{ $theme['iconStroke'] ?? 'stroke-cyan-700' }}" />
        <path d="M17 17.5a2.5 2.5 0 0 1-5 0c0-1.5 2.5-4 2.5-4s2.5 2.5 2.5 4z" class="{{ $theme['iconFill'] ?? 'fill-cyan-500/20' }} {{ $theme['iconStroke'] ?? 'stroke-cyan-700' }}" />
    </svg>

@elseif($type === 'tools')
    {{-- Tools & Instruments: Crossed Wrench & Hardware --}}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="{{ $svgClass }}">
        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" class="{{ $theme['iconFill'] ?? 'fill-orange-500/20' }} {{ $theme['iconStroke'] ?? 'stroke-orange-700' }}" />
        <path d="M9 15l-4.5 4.5" class="{{ $theme['iconStroke'] ?? 'stroke-orange-700' }}" />
        <circle cx="16" cy="8" r="1.5" class="{{ $theme['iconStroke'] ?? 'stroke-orange-700' }} fill-current" />
    </svg>

@elseif($type === 'automotive')
    {{-- Automotive: Modern Car Profile & Alloy Wheel --}}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="{{ $svgClass }}">
        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2 11 2 11.2 2 11.5V16c0 .6.4 1 1 1h2" class="{{ $theme['iconFillLight'] ?? 'fill-rose-500/10' }} {{ $theme['iconStroke'] ?? 'stroke-rose-700' }}" />
        <circle cx="7" cy="17" r="2.5" class="fill-white {{ $theme['iconStroke'] ?? 'stroke-rose-700' }}" />
        <circle cx="17" cy="17" r="2.5" class="fill-white {{ $theme['iconStroke'] ?? 'stroke-rose-700' }}" />
    </svg>

@elseif($type === 'furniture')
    {{-- Furniture: Scandinavian Armchair --}}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="{{ $svgClass }}">
        <path d="M19 9V6a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3" class="{{ $theme['iconStroke'] ?? 'stroke-indigo-700' }}" />
        <path d="M3 13a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4z" class="{{ $theme['iconFill'] ?? 'fill-indigo-500/20' }} {{ $theme['iconStroke'] ?? 'stroke-indigo-700' }}" />
        <path d="M5 19v2M19 19v2M5 11V9M19 11V9" class="{{ $theme['iconStroke'] ?? 'stroke-indigo-700' }}" />
    </svg>

@elseif($type === 'workwear')
    {{-- Workwear: Safety Helmet & Visor --}}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="{{ $svgClass }}">
        <path d="M2 13a10 10 0 0 1 20 0H2z" class="{{ $theme['iconFill'] ?? 'fill-lime-400/25' }} {{ $theme['iconStroke'] ?? 'stroke-emerald-800' }}" />
        <path d="M12 3v10M6 13a6 6 0 0 1 12 0" class="{{ $theme['iconStroke'] ?? 'stroke-emerald-800' }}" />
        <path d="M1 13h22" class="{{ $theme['iconStroke'] ?? 'stroke-emerald-800' }}" />
    </svg>

@else
    {{-- Default / General: Modern Isometric Parcel Cube --}}
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="{{ $svgClass }}">
        <path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z" class="{{ $theme['iconFill'] ?? 'fill-[#C9F46B]/20' }} {{ $theme['iconStroke'] ?? 'stroke-[#123C2F]' }}" />
        <path d="M12 12l8-4.5M12 12v9M12 12L4 7.5" class="{{ $theme['iconStroke'] ?? 'stroke-[#123C2F]' }}" />
    </svg>
@endif
