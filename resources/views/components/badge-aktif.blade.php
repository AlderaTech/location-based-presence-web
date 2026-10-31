@props(['aktif'])

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ' . ($aktif ? 'bg-emerald-50 text-emerald-800 ring-emerald-600/20' : 'bg-slate-100 text-slate-600 ring-slate-200')]) }}>
    {{ $aktif ? 'Aktif' : 'Nonaktif' }}
</span>
