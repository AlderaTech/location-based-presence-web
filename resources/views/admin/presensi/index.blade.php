<x-admin.layout title="Data Presensi">
    @php
        $labelStatus = [
            'hadir' => 'Tepat Waktu',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            'alpha' => 'Alpha',
        ];
    @endphp

    <div class="mb-5">
        <h1 class="text-2xl font-semibold">Data Presensi</h1>
        <p class="text-sm text-slate-500">Pantau presensi masuk/pulang, lokasi, status, dan koreksi manual.</p>
    </div>
    <form class="mb-4 grid gap-3 rounded-md border border-slate-200 bg-white p-4 md:grid-cols-5">
        <select name="guru_id" class="rounded-md border border-slate-300 px-3 py-2 text-sm"><option value="">Semua guru</option>@foreach ($guru as $guruItem)<option value="{{ $guruItem->id }}" @selected(request('guru_id') == $guruItem->id)>{{ $guruItem->pengguna->nama }}</option>@endforeach</select>
        <select name="status" class="rounded-md border border-slate-300 px-3 py-2 text-sm"><option value="">Semua status</option>@foreach ($labelStatus as $status => $label)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>@endforeach</select>
        <input name="tanggal_mulai" type="date" value="{{ request('tanggal_mulai') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        <input name="tanggal_selesai" type="date" value="{{ request('tanggal_selesai') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Terapkan</button>
    </form>
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Guru</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Masuk</th><th class="px-4 py-3">Pulang</th><th class="px-4 py-3">Lokasi</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($presensi as $item)
                    <tr>
                        <td class="px-4 py-3">{{ $item->tanggal_presensi?->format('Y-m-d') }}</td>
                        <td class="px-4 py-3">{{ $item->pengguna?->nama }}</td>
                        <td class="px-4 py-3">{{ $labelStatus[$item->status] ?? $item->status }} @if($item->terdeteksi_lokasi_palsu)<span class="text-red-600">Lokasi Palsu</span>@endif</td>
                        <td class="px-4 py-3">{{ $item->jam_masuk_pada?->format('H:i') ?? '-' }}<div class="text-xs text-slate-500">{{ $item->jarak_masuk_meter ? $item->jarak_masuk_meter.' m' : '' }}</div></td>
                        <td class="px-4 py-3">{{ $item->jam_pulang_pada?->format('H:i') ?? '-' }}<div class="text-xs text-slate-500">{{ $item->jarak_pulang_meter ? $item->jarak_pulang_meter.' m' : '' }}</div></td>
                        <td class="px-4 py-3">{{ $item->lokasiSekolah?->nama ?: '-' }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('admin.presensi.edit', $item) }}" class="rounded-md border border-slate-300 px-3 py-1.5">Koreksi</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">Belum ada data presensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $presensi->links() }}</div>
</x-admin.layout>
