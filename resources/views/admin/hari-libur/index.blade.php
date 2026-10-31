<x-admin.layout title="Hari Libur">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Hari Libur</h1>
            <p class="text-sm text-slate-500">Kalender libur nasional dan sekolah yang mengecualikan presensi.</p>
        </div>
        <a href="{{ route('admin.hari-libur.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Libur
        </a>
    </div>

    <form class="mb-4 grid gap-3 rounded-md border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 md:grid-cols-[1fr_180px_180px_180px_auto_auto]">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama hari libur" class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/10">
        <select name="jenis" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua jenis</option>
            <option value="nasional" @selected(request('jenis') === 'nasional')>Nasional</option>
            <option value="sekolah" @selected(request('jenis') === 'sekolah')>Sekolah</option>
        </select>
        <input name="tanggal_mulai" type="date" value="{{ request('tanggal_mulai') }}" placeholder="Tanggal mulai" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        <input name="tanggal_selesai" type="date" value="{{ request('tanggal_selesai') }}" placeholder="Tanggal selesai" class="rounded-md border border-slate-300 px-3 py-2 text-sm">
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Terapkan</button>
        <a href="{{ route('admin.hari-libur.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="tanggal" label="Tanggal" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="nama" label="Nama" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="jenis" label="Jenis" /></th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($hariLibur as $libur)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3 whitespace-nowrap">{{ $libur->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-medium">{{ $libur->nama }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $libur->jenis === 'nasional' ? 'bg-amber-50 text-amber-800 ring-amber-600/20' : 'bg-sky-50 text-sky-800 ring-sky-600/20' }}">
                                {{ $libur->jenis === 'nasional' ? 'Nasional' : 'Sekolah' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                <a href="{{ route('admin.hari-libur.edit', $libur) }}" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">Edit</a>
                                <form method="post" action="{{ route('admin.hari-libur.destroy', $libur) }}" data-confirm data-confirm-title="Hapus hari libur?" data-confirm-text="Tanggal ini akan kembali dianggap sebagai hari kerja." data-confirm-button="Hapus" data-confirm-variant="danger">
                                    @csrf
                                    @method('delete')
                                    <button class="rounded-md border border-red-200 bg-white px-2.5 py-1 text-xs font-semibold text-red-600 shadow-xs transition hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada hari libur sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $hariLibur->links() }}</div>
</x-admin.layout>
