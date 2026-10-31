@props(['jenis'])

@php
    $label = \App\Support\PresensiFormatter::labelJenisIzin($jenis);
    $class = \App\Support\PresensiFormatter::badgeJenisIzinClass($jenis);
    $icon = strtolower((string) $jenis) === 'sakit' ? 'stethoscope' : 'file-text';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ' . $class]) }}>
    <i data-lucide="{{ $icon }}" class="h-3 w-3"></i>
    <span>{{ $label }}</span>
</span>
