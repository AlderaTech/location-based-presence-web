<x-admin.layout title="Data Presensi Murid">
    @php
        $labelStatus = \App\Support\PresensiFormatter::daftarPilihanStatus();
        $bolehKoreksi = auth()->user()->peran === 'admin';
    @endphp

    <div class="mb-5">
        <h1 class="text-2xl font-bold text-slate-900">Data Presensi Murid</h1>
        <p class="text-sm text-slate-500">Pantau catatan kehadiran siswa per mata pelajaran, status verifikasi GPS, dan koreksi manual.</p>
    </div>

    <form class="mb-5 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4 xl:grid-cols-6">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama murid, NIS, mapel..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none md:col-span-2">
        <select name="id_kelas" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
            <option value="">Semua kelas</option>
            @foreach ($daftarKelas as $k)
                <option value="{{ $k->id }}" @selected(request('id_kelas') == $k->id)>{{ $k->nama_kelas }}</option>
            @endforeach
        </select>
        <select name="id_mata_pelajaran" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
            <option value="">Semua mapel</option>
            @foreach ($daftarMapel as $mp)
                <option value="{{ $mp->id }}" @selected(request('id_mata_pelajaran') == $mp->id)>{{ $mp->nama_mapel }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
            <option value="">Semua status</option>
            @foreach ($labelStatus as $status => $label)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-bold text-white hover:bg-teal-700">Filter</button>
            <a href="{{ route('admin.presensi.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3.5"><x-admin.sort-link kolom="tanggal_presensi" label="Tanggal" /></th>
                    <th class="px-4 py-3.5">Murid</th>
                    <th class="px-4 py-3.5">Kelas</th>
                    <th class="px-4 py-3.5">Mata Pelajaran</th>
                    <th class="px-4 py-3.5">Guru Pengampu</th>
                    <th class="px-4 py-3.5"><x-admin.sort-link kolom="status" label="Status" /></th>
                    <th class="px-4 py-3.5">Waktu & Metode</th>
                    <th class="px-4 py-3.5">Jarak GPS</th>
                    @if ($bolehKoreksi)
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($presensi as $item)
                    @php $jadwal = $item->jadwalPelajaran; @endphp
                    <tr class="hover:bg-slate-50/75">
                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-800">
                            {{ $item->tanggal_presensi?->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-900 block">{{ $item->pengguna?->nama }}</span>
                            <span class="font-mono text-xs text-slate-400">NIS: {{ $item->murid?->nis }}</span>
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-800">
                            {{ $item->murid?->kelas?->nama_kelas ?: $jadwal?->kelas?->nama_kelas ?: '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-bold text-slate-900 block">{{ $jadwal?->mataPelajaran?->nama_mapel ?: '-' }}</span>
                            @if ($jadwal)
                                <span class="text-xs text-slate-400">{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $jadwal?->guru?->pengguna?->nama ?: '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <x-badge-status :status="$item->status" />
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs font-semibold text-slate-800 block">
                                {{ $item->waktu_presensi ? $item->waktu_presensi->format('H:i:s') : '-' }}
                            </span>
                            <div class="mt-1">
                                <x-badge-metode :metode="$item->metode" />
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            @if ($item->jarak_meter !== null)
                                <span class="font-mono text-xs text-slate-700">{{ $item->jarak_meter }} m</span>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        @if ($bolehKoreksi)
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('admin.presensi.edit', $item) }}" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                                    Koreksi
                                </a>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $bolehKoreksi ? 9 : 8 }}" class="px-4 py-8 text-center text-slate-400">
                            Belum ada catatan presensi yang sesuai kriteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($presensi->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $presensi->links() }}
            </div>
        @endif
    </div>
</x-admin.layout>
