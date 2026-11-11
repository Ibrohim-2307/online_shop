@props([
    'slug' => '',
    'class' => 'w-7 h-7 sm:w-8 sm:h-8',
])

@php
    $normalized = strtolower(trim((string)$slug));
@endphp

@if(str_contains($normalized, 'qurilish-material') || str_contains($normalized, 'sement') || str_contains($normalized, 'gisht'))
    {{-- Construction Materials: Modern Brickwork / Structural Blocks --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="4" width="18" height="16" rx="2.5" class="fill-amber-500/10 stroke-amber-600" />
        <path d="M3 9.5h18" class="stroke-amber-600" />
        <path d="M3 15h18" class="stroke-amber-600" />
        <path d="M9 4v5.5" class="stroke-amber-600" />
        <path d="M15 4v5.5" class="stroke-amber-600" />
        <path d="M6 9.5V15" class="stroke-amber-600" />
        <path d="M12 9.5V15" class="stroke-amber-600" />
        <path d="M18 9.5V15" class="stroke-amber-600" />
        <path d="M9 15v5" class="stroke-amber-600" />
        <path d="M15 15v5" class="stroke-amber-600" />
    </svg>

@elseif(str_contains($normalized, 'sanoat') || str_contains($normalized, 'mashina') || str_contains($normalized, 'uskuna') && !str_contains($normalized, 'asbob'))
    {{-- Industrial Machinery & Equipment: Modern Precision Turbine / Cogwheel --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="3" class="fill-blue-500/20 stroke-blue-700" />
        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" class="fill-blue-500/10 stroke-blue-700" />
    </svg>

@elseif(str_contains($normalized, 'oziq-ovqat') || str_contains($normalized, 'ichimlik') || str_contains($normalized, 'don'))
    {{-- Food & Beverages / Agriculture: Golden Wheat Spike & Leaf --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M2 22 17 7" class="stroke-emerald-600" />
        <path d="M4 14.5a3.5 3.5 0 0 0 4.95 0L10.5 13l-3.5-3.5L5.45 11A3.5 3.5 0 0 0 4 14.5z" class="fill-emerald-500/15 stroke-emerald-600" />
        <path d="M8.5 10a3.5 3.5 0 0 0 4.95 0L15 8.5l-3.5-3.5L10 6.45A3.5 3.5 0 0 0 8.5 10z" class="fill-emerald-500/15 stroke-emerald-600" />
        <path d="M13 5.5a3.5 3.5 0 0 0 4.95 0L19.5 4l-3.5-3.5L14.5 2A3.5 3.5 0 0 0 13 5.5z" class="fill-emerald-500/15 stroke-emerald-600" />
        <path d="M19 8.5c0 3.04-2.46 5.5-5.5 5.5l-2-.5 4.5-4.5 3-.5z" class="fill-emerald-500/10 stroke-emerald-600" />
        <path d="M15 13c0 2.5-2 4.5-4.5 4.5l-2-.5 4-4 2.5z" class="fill-emerald-500/10 stroke-emerald-600" />
    </svg>

@elseif(str_contains($normalized, 'elektr') || str_contains($normalized, 'yoritish') || str_contains($normalized, 'chiroq'))
    {{-- Electrical & Lighting: Minimalist Modern Smart Bulb & Ray --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 18h6" class="stroke-amber-600" />
        <path d="M10 21h4" class="stroke-amber-600" />
        <path d="M12 2v2M4.93 4.93l1.41 1.41M19.07 4.93l-1.41 1.41" class="stroke-amber-500" />
        <path d="M12 7a5.5 5.5 0 0 0-4.8 8.2c.8 1.4 1.8 2.8 1.8 3.8h6c0-1 1-2.4 1.8-3.8A5.5 5.5 0 0 0 12 7z" class="fill-amber-500/15 stroke-amber-600" />
        <path d="M10 12l2 2 2-2" class="stroke-amber-600" />
    </svg>

@elseif(str_contains($normalized, 'boyoq') || str_contains($normalized, 'kimyo'))
    {{-- Paints & Chemicals: Sleek Paint Roller & Drop --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3.5" y="3.5" width="14" height="5.5" rx="1.5" class="fill-purple-500/20 stroke-purple-600" />
        <path d="M17.5 6.25H19a2 2 0 0 1 2 2v2.5a2 2 0 0 1-2 2h-9a2 2 0 0 0-2 2v2" class="stroke-purple-600" />
        <rect x="6.5" y="17" width="3" height="4.5" rx="1" class="fill-purple-500/30 stroke-purple-600" />
        <path d="M5.5 9v2a1 1 0 0 0 2 0V9" class="fill-purple-500/15 stroke-purple-600" />
        <circle cx="18" cy="18" r="2" class="fill-purple-500/20 stroke-purple-600" />
    </svg>

@elseif(str_contains($normalized, 'santexnika') || str_contains($normalized, 'quvur') || str_contains($normalized, 'suv'))
    {{-- Plumbing & Pipes: Water Valve & Droplet Flow --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 13h10a4 4 0 0 0 4-4V7" class="stroke-cyan-600" />
        <path d="M14 4h6M17 2v4" class="stroke-cyan-600" />
        <path d="M3 9v8" class="stroke-cyan-600" />
        <path d="M17 11v2a2 2 0 0 1-2 2h-1" class="stroke-cyan-600" />
        <path d="M7 13v-3a2 2 0 0 0-2-2H3" class="stroke-cyan-600" />
        <path d="M17 17.5a2.5 2.5 0 0 1-5 0c0-1.5 2.5-4 2.5-4s2.5 2.5 2.5 4z" class="fill-cyan-500/25 stroke-cyan-600" />
    </svg>

@elseif(str_contains($normalized, 'asbob') || str_contains($normalized, 'instrument'))
    {{-- Tools & Instruments: Professional Crossed Wrench & Hardware --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" class="fill-orange-500/15 stroke-orange-600" />
        <path d="M9 15l-4.5 4.5" class="stroke-orange-600" />
        <circle cx="16" cy="8" r="1.5" class="fill-orange-600 stroke-none" />
    </svg>

@elseif(str_contains($normalized, 'avto'))
    {{-- Automotive: Car / Chassis --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2 11 2 11.2 2 11.5V16c0 .6.4 1 1 1h2" class="fill-rose-500/10 stroke-rose-600" />
        <circle cx="7" cy="17" r="2.5" class="fill-white stroke-rose-600" />
        <circle cx="17" cy="17" r="2.5" class="fill-white stroke-rose-600" />
    </svg>

@elseif(str_contains($normalized, 'mebel'))
    {{-- Furniture: Scandinavian Armchair --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 9V6a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3" class="stroke-indigo-600" />
        <path d="M3 13a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4z" class="fill-indigo-500/15 stroke-indigo-600" />
        <path d="M5 19v2M19 19v2M5 11V9M19 11V9" class="stroke-indigo-600" />
    </svg>

@elseif(str_contains($normalized, 'kiyim') || str_contains($normalized, 'forma'))
    {{-- Safety Workwear & Helmet --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M2 13a10 10 0 0 1 20 0H2z" class="fill-emerald-500/15 stroke-emerald-600" />
        <path d="M12 3v10M6 13a6 6 0 0 1 12 0" class="stroke-emerald-600" />
        <path d="M1 13h22" class="stroke-emerald-600" />
    </svg>

@else
    {{-- Default / General Category: Modern 3D Package Cube --}}
    <svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z" class="fill-emerald-500/10 stroke-[#123C2F]" />
        <path d="M12 12l8-4.5M12 12v9M12 12L4 7.5" class="stroke-[#123C2F]" />
    </svg>
@endif
