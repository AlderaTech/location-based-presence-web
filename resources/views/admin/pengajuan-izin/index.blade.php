<x-admin.layout title="Persetujuan Izin">
    @php
        $labelStatus = ['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'];
        $labelJenis = ['izin' => 'Izin', 'sakit' => 'Sakit', 'cuti' => 'Cuti'];
    @endphp

    <div class="mb-5">
        <h1 class="text-2xl font-semibold">Persetujuan Izin</h1>
        <p class="text-sm text-slate-500">Verifikasi pengajuan izin, sakit, dan cuti guru.</p>
    </div>
    <form class="mb-4 flex max-w-sm gap-2"><select name="status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"><option value="">Semua status</option>@foreach ($labelStatus as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select><button class="rounded-md border border-slate-300 px-4 py-2 text-sm">Terapkan</button></form>
    <div class="space-y-4">
        @forelse ($pengajuanIzin as $pengajuan)
            <div class="rounded-md border border-slate-200 bg-white p-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2"><h2 class="font-semibold">{{ $pengajuan->pengguna?->nama }}</h2><span class="rounded bg-slate-100 px-2 py-1 text-xs">{{ $labelJenis[$pengajuan->jenis] ?? $pengajuan->jenis }}</span><span class="rounded bg-slate-100 px-2 py-1 text-xs">{{ $labelStatus[$pengajuan->status] ?? $pengajuan->status }}</span></div>
                        <p class="mt-1 text-sm text-slate-500">{{ $pengajuan->tanggal_mulai->format('Y-m-d') }} s/d {{ $pengajuan->tanggal_selesai->format('Y-m-d') }}</p>
                        <p class="mt-3 text-sm">{{ $pengajuan->alasan }}</p>
                        @if ($pengajuan->dokumen->isNotEmpty())<div class="mt-3 text-sm text-slate-600">Dokumen: @foreach ($pengajuan->dokumen as $dokumen)<a class="underline" href="{{ asset('storage/'.$dokumen->path) }}" target="_blank">{{ $dokumen->nama_asli }}</a>@endforeach</div>@endif
                        @if ($pengajuan->alasan_penolakan)<p class="mt-2 text-sm text-red-700">Alasan tolak: {{ $pengajuan->alasan_penolakan }}</p>@endif
                    </div>
                    @if ($pengajuan->status === 'menunggu')
                        <div class="flex min-w-80 flex-col gap-2">
                            <form method="post" action="{{ route('admin.pengajuan-izin.setujui', $pengajuan) }}">@csrf<button class="w-full rounded-md bg-emerald-700 px-4 py-2 text-sm font-medium text-white">Setujui</button></form>
                            <form method="post" action="{{ route('admin.pengajuan-izin.tolak', $pengajuan) }}">@csrf<textarea name="alasan_penolakan" rows="2" required placeholder="Alasan penolakan" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm"></textarea><button class="mt-2 w-full rounded-md border border-red-300 px-4 py-2 text-sm font-medium text-red-700">Tolak</button></form>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="rounded-md border border-slate-200 bg-white p-6 text-center text-sm text-slate-500">Belum ada pengajuan.</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $pengajuanIzin->links() }}</div>
</x-admin.layout>
