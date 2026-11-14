@props([
    'tab',
    'searchName',
    'searchValue' => '',
    'placeholder' => 'Qidirish...',
    'statusName' => null,
    'statusValue' => '',
    'statuses' => [],
])

{{-- Search box and status filter for one admin list; submitting reloads the page on the same tab --}}
<form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <input type="search" name="{{ $searchName }}" value="{{ $searchValue }}" placeholder="{{ $placeholder }}"
           class="h-9 flex-1 sm:w-64 min-w-0 px-3 rounded-lg bg-slate-50 border border-slate-300 text-xs outline-none focus:border-slate-900">
    @if($statusName)
        <select name="{{ $statusName }}" onchange="this.form.submit()"
                class="h-9 px-2 rounded-lg bg-slate-50 border border-slate-300 text-xs font-semibold text-slate-800 outline-none focus:border-slate-900">
            <option value="">Barcha holatlar</option>
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" @selected($statusValue === $value)>{{ $label }}</option>
            @endforeach
        </select>
    @endif
    <button type="submit" class="h-9 px-3 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold">Topish</button>
    @if($searchValue !== '' || $statusValue !== '')
        <a href="{{ route('admin.dashboard', ['tab' => $tab]) }}" class="h-9 px-3 inline-flex items-center rounded-lg border border-slate-300 text-xs font-semibold text-slate-600 hover:bg-slate-100">Tozalash</a>
    @endif
</form>
