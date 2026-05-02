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
        $statusClass = [
            'hadir' => 'bg-teal-50 text-teal-800 ring-teal-200',
            'terlambat' => 'bg-amber-50 text-amber-800 ring-amber-200',
            'izin' => 'bg-sky-50 text-sky-800 ring-sky-200',
            'sakit' => 'bg-violet-50 text-violet-800 ring-violet-200',
            'cuti' => 'bg-indigo-50 text-indigo-800 ring-indigo-200',
            'alpha' => 'bg-rose-50 text-rose-800 ring-rose-200',
        ];
        $bolehKoreksi = auth()->user()->peran === 'admin';
    @endphp

    <div class="mb-5">
        <h1 class="text-2xl font-semibold">Data Presensi</h1>
        <p class="text-sm text-slate-500">{{ $bolehKoreksi ? 'Pantau presensi masuk/pulang, lokasi, status, deteksi lokasi palsu, dan koreksi manual.' : 'Pantau presensi masuk/pulang, lokasi, status, dan deteksi lokasi palsu.' }}</p>
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
        <a href="{{ route('admin.presensi.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
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
                    @if ($bolehKoreksi)
                        <th class="px-4 py-3 text-right">Aksi</th>
                    @endif
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
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1.5">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusClass[$item->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200' }}">{{ $labelStatus[$item->status] ?? $item->status }}</span>
                                @if($item->terdeteksi_lokasi_palsu)
                                    <span class="inline-flex rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 ring-1 ring-red-200">Lokasi Palsu</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $item->jam_masuk_pada?->format('H:i') ?? '-' }}<div class="text-xs text-slate-500">{{ $item->jarak_masuk_meter !== null ? $item->jarak_masuk_meter.' m' : '' }}</div></td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $item->jam_pulang_pada?->format('H:i') ?? '-' }}<div class="text-xs text-slate-500">{{ $item->jarak_pulang_meter !== null ? $item->jarak_pulang_meter.' m' : '' }}</div></td>
                        <td class="px-4 py-3">{{ $item->jadwalKerja?->nama ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $item->lokasiSekolah?->nama ?: '-' }}</td>
                        @if ($bolehKoreksi)
                            <td class="px-4 py-3 text-right"><a href="{{ route('admin.presensi.edit', $item) }}" class="rounded-md border border-slate-300 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">Koreksi</a></td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="{{ $bolehKoreksi ? 8 : 7 }}" class="px-4 py-8 text-center text-slate-500">Belum ada data presensi sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $presensi->links() }}</div>
</x-admin.layout>
