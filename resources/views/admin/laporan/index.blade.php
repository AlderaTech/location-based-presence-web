<x-admin.layout title="Laporan Presensi Murid">
    @php
        $labelStatus = \App\Support\PresensiFormatter::daftarPilihanStatus();
    @endphp

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Laporan Rekapitulasi Presensi</h1>
            <p class="text-sm text-slate-500">Filter, analisis, dan ekspor data kehadiran siswa per kelas dan mata pelajaran.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.laporan.ekspor', array_merge(request()->query(), ['format' => 'xlsx'])) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-teal-300 bg-teal-50 px-4 py-2 text-xs font-bold text-teal-800 hover:bg-teal-100 shadow-sm transition">
                <i data-lucide="file-spreadsheet" class="h-4 w-4"></i>
                Ekspor Excel (XLSX)
            </a>
            <a href="{{ route('admin.laporan.ekspor', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 shadow-sm transition">
                <i data-lucide="file-text" class="h-4 w-4"></i>
                Ekspor PDF
            </a>
        </div>
    </div>

    <form class="mb-5 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-4 xl:grid-cols-7">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama murid atau NIS" class="rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-teal-500 focus:outline-none md:col-span-2">
        <select name="id_kelas" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs">
            <option value="">Semua Kelas</option>
            @foreach ($daftarKelas as $k)
                <option value="{{ $k->id }}" @selected(request('id_kelas') == $k->id)>{{ $k->nama_kelas }}</option>
            @endforeach
        </select>
        <select name="id_mata_pelajaran" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs">
            <option value="">Semua Mapel</option>
            @foreach ($daftarMapel as $mp)
                <option value="{{ $mp->id }}" @selected(request('id_mata_pelajaran') == $mp->id)>{{ $mp->nama_mapel }}</option>
            @endforeach
        </select>
        <select name="id_guru" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs">
            <option value="">Semua Guru</option>
            @foreach ($daftarGuru as $g)
                <option value="{{ $g->id }}" @selected(request('id_guru') == $g->id)>{{ $g->pengguna?->nama }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs">
            <option value="">Semua Status</option>
            @foreach ($labelStatus as $s => $lbl)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ $lbl }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button class="flex-1 rounded-lg bg-teal-600 px-3 py-2 text-xs font-bold text-white hover:bg-teal-700">Terapkan</button>
            <a href="{{ route('admin.laporan.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3.5">Tanggal</th>
                    <th class="px-4 py-3.5">NIS</th>
                    <th class="px-4 py-3.5">Nama Murid</th>
                    <th class="px-4 py-3.5">Kelas</th>
                    <th class="px-4 py-3.5">Mata Pelajaran</th>
                    <th class="px-4 py-3.5">Guru Pengampu</th>
                    <th class="px-4 py-3.5">Waktu & Metode</th>
                    <th class="px-4 py-3.5">Status</th>
                    <th class="px-4 py-3.5">Jarak</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($presensi as $item)
                    @php $jadwal = $item->jadwalPelajaran; @endphp
                    <tr class="hover:bg-slate-50/75">
                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-800">{{ $item->tanggal_presensi?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $item->murid?->nis }}</td>
                        <td class="px-4 py-3 font-bold text-slate-900">{{ $item->pengguna?->nama }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $item->murid?->kelas?->nama_kelas ?: $jadwal?->kelas?->nama_kelas ?: '-' }}</td>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $jadwal?->mataPelajaran?->nama_mapel ?: '-' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $jadwal?->guru?->pengguna?->nama ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs font-semibold text-slate-800 block">
                                {{ $item->waktu_presensi ? $item->waktu_presensi->format('H:i:s') : '-' }}
                            </span>
                            <div class="mt-1">
                                <x-badge-metode :metode="$item->metode" />
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge-status :status="$item->status" />
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $item->jarak_meter ? $item->jarak_meter.' m' : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-slate-400">Belum ada data presensi.</td>
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
