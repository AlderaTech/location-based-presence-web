<x-admin.layout title="Jadwal Kerja">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Jadwal Kerja</h1>
            <p class="text-sm text-slate-500">Atur jam masuk, jam pulang, dan batas toleransi keterlambatan.</p>
        </div>
        <a href="{{ route('admin.jadwal-kerja.create') }}" class="inline-flex items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Tambah Jadwal</a>
    </div>

    <form class="mb-4 grid gap-3 rounded-md border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 md:grid-cols-[1fr_180px_auto_auto]">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama jadwal" class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/10">
        <select name="aktif" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="1" @selected(request('aktif') === '1')>Aktif</option>
            <option value="0" @selected(request('aktif') === '0')>Nonaktif</option>
        </select>
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Terapkan</button>
        <a href="{{ route('admin.jadwal-kerja.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="nama" label="Nama" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="jam_masuk" label="Masuk" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="jam_pulang" label="Pulang" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="toleransi" label="Toleransi" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="status" label="Status" /></th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($jadwalKerja as $jadwal)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3 font-medium">{{ $jadwal->nama }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $jadwal->jam_masuk }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $jadwal->jam_pulang }}</td>
                        <td class="px-4 py-3">{{ $jadwal->toleransi_terlambat_menit }} menit</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $jadwal->aktif ? 'bg-teal-50 text-teal-800 ring-1 ring-teal-200' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200' }}">
                                {{ $jadwal->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.jadwal-kerja.edit', $jadwal) }}" class="rounded-md border border-slate-300 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">Edit</a>
                                <form method="post" action="{{ route('admin.jadwal-kerja.destroy', $jadwal) }}" data-confirm data-confirm-title="Nonaktifkan jadwal kerja?" data-confirm-text="Jadwal ini tidak lagi tersedia untuk data guru baru atau pembaruan berikutnya." data-confirm-button="Nonaktifkan" data-confirm-variant="danger">
                                    @csrf
                                    @method('delete')
                                    <button class="rounded-md border border-red-300 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Nonaktifkan</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada jadwal kerja sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $jadwalKerja->links() }}</div>
</x-admin.layout>
