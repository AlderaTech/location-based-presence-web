@props(['metode', 'withIcon' => true])

@php
    $label = \App\Support\PresensiFormatter::labelMetode($metode);
    $class = \App\Support\PresensiFormatter::badgeMetodeClass($metode);
    $icon = \App\Support\PresensiFormatter::iconMetode($metode);
@endphp

@if ($label !== '-')
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-semibold ' . $class]) }}>
        @if ($withIcon)
            <i data-lucide="{{ $icon }}" class="h-3 w-3 shrink-0"></i>
        @endif
        <span>{{ $label }}</span>
    </span>
@else
    <span class="text-xs text-slate-400">-</span>
@endif
