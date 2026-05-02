<x-admin.layout title="Laporan Presensi">
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

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Laporan & Ekspor</h1>
            <p class="text-sm text-slate-500">Saring, urutkan, dan ekspor rekap presensi untuk arsip atau penggajian.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.laporan.ekspor', array_merge(request()->query(), ['format' => 'xlsx'])) }}" class="rounded-md border border-teal-200 bg-teal-50 px-4 py-2 text-sm font-semibold text-teal-800 hover:bg-teal-100">Ekspor XLSX</a>
            <a href="{{ route('admin.laporan.ekspor', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Ekspor PDF</a>
        </div>
    </div>

    <form class="mb-4 grid gap-3 rounded-md border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 md:grid-cols-4 xl:grid-cols-8">
        <input name="q" value="{{ request('q') }}" placeholder="Cari guru, email, NIP" class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/10 md:col-span-2">
        <select name="id_guru" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua guru</option>
            @foreach ($guru as $guruItem)
                <option value="{{ $guruItem->id }}" @selected(request('id_guru') == $guruItem->id)>{{ $guruItem->pengguna->nama }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua status</option>
            @foreach ($labelStatus as $status => $label)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="id_jadwal_kerja" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua jadwal</option>
            @foreach ($jadwalKerja as $jadwal)
                <option value="{{ $jadwal->id }}" @selected(request('id_jadwal_kerja') == $jadwal->id)>{{ $jadwal->nama }}</option>
            @endforeach
        </select>
        <select name="id_lokasi_sekolah" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua lokasi</option>
            @foreach ($lokasiSekolah as $lokasi)
                <option value="{{ $lokasi->id }}" @selected(request('id_lokasi_sekolah') == $lokasi->id)>{{ $lokasi->nama }}</option>
            @endforeach
        </select>
        <select name="lokasi_palsu" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua validasi</option>
            <option value="1" @selected(request('lokasi_palsu') === '1')>Lokasi palsu</option>
            <option value="0" @selected(request('lokasi_palsu') === '0')>Lokasi valid</option>
        </select>
        <div class="grid grid-cols-2 gap-3 md:col-span-2">
            <input name="tanggal_mulai" type="date" value="{{ request('tanggal_mulai') }}" placeholder="Tanggal mulai" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
            <input name="tanggal_selesai" type="date" value="{{ request('tanggal_selesai') }}" placeholder="Tanggal selesai" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        </div>
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Terapkan</button>
        <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="tanggal_presensi" label="Tanggal" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="nama" label="Guru" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="status" label="Status" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="jam_masuk_pada" label="Masuk" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="jam_pulang_pada" label="Pulang" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="jadwal" label="Jadwal" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="lokasi" label="Lokasi" /></th>
                    <th class="px-4 py-3">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($presensi as $item)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $item->tanggal_presensi?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="font-semibold">{{ $item->pengguna?->nama }}</div>
                            <div class="text-xs text-slate-500">{{ $item->guru?->nip ?: $item->pengguna?->nomor_pegawai ?: '-' }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $labelStatus[$item->status] ?? $item->status }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $item->jam_masuk_pada?->format('H:i') ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $item->jam_pulang_pada?->format('H:i') ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->jadwalKerja?->nama ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $item->lokasiSekolah?->nama ?: '-' }}</td>
                        <td class="px-4 py-3 max-w-xs">
                            <span class="line-clamp-2">{{ $item->terdeteksi_lokasi_palsu ? 'Lokasi palsu terdeteksi. ' : '' }}{{ $item->catatan ?: '-' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada data laporan sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $presensi->links() }}</div>
</x-admin.layout>
