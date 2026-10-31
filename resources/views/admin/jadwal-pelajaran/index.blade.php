<x-admin.layout title="Jadwal Pelajaran">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Jadwal Pelajaran</h1>
            <p class="text-sm text-slate-500">Atur jadwal pelajaran, hubungan guru pengampu, kelas, waktu mengajar, dan toleransi keterlambatan.</p>
        </div>
        <a href="{{ route('admin.jadwal-pelajaran.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Jadwal
        </a>
    </div>

    <!-- Filter -->
    <div class="mb-5 flex flex-wrap items-center gap-2">
        <form method="get" class="flex flex-wrap flex-1 gap-2">
            <select name="hari" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                <option value="">Semua Hari</option>
                @foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $h)
                    <option value="{{ $h }}" @selected(request('hari') === $h)>{{ ucfirst($h) }}</option>
                @endforeach
            </select>
            <select name="id_kelas" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                <option value="">Semua Kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}" @selected(request('id_kelas') == $k->id)>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
            <select name="id_mata_pelajaran" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                <option value="">Semua Mapel</option>
                @foreach ($daftarMapel as $mp)
                    <option value="{{ $mp->id }}" @selected(request('id_mata_pelajaran') == $mp->id)>{{ $mp->nama_mapel }}</option>
                @endforeach
            </select>
            <select name="id_guru" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                <option value="">Semua Guru</option>
                @foreach ($daftarGuru as $g)
                    <option value="{{ $g->id }}" @selected(request('id_guru') == $g->id)>{{ $g->pengguna?->nama }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-slate-900 px-3.5 py-2 text-xs font-bold text-white transition hover:bg-slate-800">Filter</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">Hari</th>
                    <th class="px-5 py-3.5">Jam Pelajaran</th>
                    <th class="px-5 py-3.5">Mata Pelajaran</th>
                    <th class="px-5 py-3.5">Kelas</th>
                    <th class="px-5 py-3.5">Guru Pengampu</th>
                    <th class="px-5 py-3.5">Toleransi</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($jadwal as $item)
                    <tr class="hover:bg-slate-50/75">
                        <td class="px-5 py-3.5 font-bold uppercase text-xs text-teal-800">{{ $item->hari }}</td>
                        <td class="px-5 py-3.5 font-semibold text-slate-900">
                            {{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}
                        </td>
                        <td class="px-5 py-3.5 font-bold text-slate-900">
                            {{ $item->mataPelajaran?->nama_mapel }}
                            @if ($item->ruangan)
                                <span class="block text-xs font-normal text-slate-400">R. {{ $item->ruangan }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $item->kelas?->nama_kelas }}</td>
                        <td class="px-5 py-3.5">{{ $item->guru?->pengguna?->nama }}</td>
                        <td class="px-5 py-3.5 text-xs text-slate-500">{{ $item->toleransi_terlambat_menit }} menit</td>
                        <td class="px-5 py-3.5">
                            <x-badge-aktif :aktif="$item->aktif" />
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                <a href="{{ route('admin.jadwal-pelajaran.edit', $item) }}" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                                    Edit
                                </a>
                                <form method="post" action="{{ route('admin.jadwal-pelajaran.destroy', $item) }}" class="inline-block" data-confirm data-confirm-title="Hapus jadwal ini?" data-confirm-text="Data jadwal akan dihapus dari sistem." data-confirm-button="Ya, Hapus" data-confirm-variant="danger">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="rounded-md border border-red-200 bg-white px-2.5 py-1 text-xs font-semibold text-red-600 shadow-xs transition hover:bg-red-50">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-8 text-center text-slate-400">Belum ada jadwal pelajaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($jadwal->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">
                {{ $jadwal->links() }}
            </div>
        @endif
    </div>
</x-admin.layout>
