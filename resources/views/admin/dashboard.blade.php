<x-admin.layout title="Dasbor Presensi">
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

    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Dasbor</h1>
            <p class="text-sm text-slate-500">Ringkasan kehadiran {{ now()->translatedFormat('d F Y') }}</p>
        </div>
        <a href="{{ route('admin.laporan.index') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Buka Laporan</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([['Hadir', $ringkasan['hadir']], ['Terlambat', $ringkasan['terlambat']], ['Izin/Sakit/Cuti', $ringkasan['izin']], ['Alpha', $ringkasan['alpha']], ['Menunggu Izin', $pengajuanMenunggu]] as [$label, $nilai])
            <div class="rounded-md border border-slate-200 bg-white p-4">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-3xl font-semibold">{{ $nilai }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_420px]">
        <div class="rounded-md border border-slate-200 bg-white">
            <div class="border-b border-slate-200 px-4 py-3">
                <h2 class="font-semibold">Presensi Terbaru</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Guru</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Masuk</th>
                            <th class="px-4 py-3">Pulang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($presensiTerbaru as $item)
                            <tr>
                                <td class="px-4 py-3">{{ $item->tanggal_presensi?->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">{{ $item->pengguna?->nama }}</td>
                                <td class="px-4 py-3">{{ $labelStatus[$item->status] ?? $item->status }}</td>
                                <td class="px-4 py-3">{{ $item->jam_masuk_pada?->format('H:i') ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $item->jam_pulang_pada?->format('H:i') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada data presensi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-4">
            <h2 class="font-semibold">Statistik Bulan Ini</h2>
            <div class="mt-4 space-y-3 text-sm">
                @foreach ($labelStatus as $key => $label)
                    @php $nilai = (int) ($bulanan[$key] ?? 0); @endphp
                    <div>
                        <div class="mb-1 flex justify-between"><span>{{ $label }}</span><span>{{ $nilai }}</span></div>
                        <div class="h-2 rounded bg-slate-100"><div class="h-2 rounded bg-slate-800" style="width: {{ min(100, $jumlahGuru ? ($nilai / max($jumlahGuru, 1) * 100) : 0) }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-admin.layout>
