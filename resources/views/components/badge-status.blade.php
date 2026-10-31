@props(['status'])

@php
    $label = \App\Support\PresensiFormatter::labelStatus($status);
    $class = \App\Support\PresensiFormatter::badgeStatusClass($status);
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ' . $class]) }}>
    {{ $label }}
</span>
