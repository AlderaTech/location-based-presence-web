@props(['kolom', 'label'])

@php
    $sortirAktif = request('sort');
    $arahAktif = request('direction') === 'desc' ? 'desc' : 'asc';
    $aktif = $sortirAktif === $kolom;
    $arahBerikutnya = $aktif && $arahAktif === 'asc' ? 'desc' : 'asc';
    $query = request()->query();
    unset($query['page']);
    $query['sort'] = $kolom;
    $query['direction'] = $arahBerikutnya;
    $url = request()->url().'?'.http_build_query($query);
    $ikon = $aktif ? ($arahAktif === 'asc' ? 'arrow-up' : 'arrow-down') : 'arrow-up-down';
@endphp

<a href="{{ $url }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 font-semibold text-slate-600 hover:text-slate-950']) }}>
    <span>{{ $label }}</span>
    <i data-lucide="{{ $ikon }}" class="h-3.5 w-3.5 {{ $aktif ? 'text-teal-700' : 'text-slate-400' }}"></i>
</a>
