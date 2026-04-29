<x-admin.layout title="Lokasi Sekolah">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Lokasi Sekolah</h1>
            <p class="text-sm text-slate-500">Atur titik koordinat sekolah dan radius validasi presensi.</p>
        </div>
        <a href="{{ route('admin.lokasi-sekolah.create') }}" class="inline-flex items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Tambah Lokasi</a>
    </div>

    <form class="mb-4 grid gap-3 rounded-md border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 md:grid-cols-[1fr_170px_150px_150px_auto_auto]">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama lokasi" class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/10">
        <select name="aktif" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="1" @selected(request('aktif') === '1')>Aktif</option>
            <option value="0" @selected(request('aktif') === '0')>Nonaktif</option>
        </select>
        <input name="radius_min" type="number" min="0" value="{{ request('radius_min') }}" placeholder="Radius min" class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/10">
        <input name="radius_max" type="number" min="0" value="{{ request('radius_max') }}" placeholder="Radius max" class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/10">
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Terapkan</button>
        <a href="{{ route('admin.lokasi-sekolah.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="nama" label="Nama" /></th>
                    <th class="px-4 py-3">Koordinat</th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="radius" label="Radius" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="status" label="Status" /></th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($lokasiSekolah as $lokasi)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3 font-medium">{{ $lokasi->nama }}</td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $lokasi->latitude }}, {{ $lokasi->longitude }}</td>
                        <td class="px-4 py-3">{{ $lokasi->radius_meter }} m</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $lokasi->aktif ? 'bg-teal-50 text-teal-800 ring-1 ring-teal-200' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200' }}">
                                {{ $lokasi->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.lokasi-sekolah.edit', $lokasi) }}" class="rounded-md border border-slate-300 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">Edit</a>
                                <form method="post" action="{{ route('admin.lokasi-sekolah.destroy', $lokasi) }}" data-confirm data-confirm-title="Nonaktifkan lokasi?" data-confirm-text="Lokasi ini tidak lagi dipakai untuk validasi presensi." data-confirm-button="Nonaktifkan" data-confirm-variant="danger">
                                    @csrf
                                    @method('delete')
                                    <button class="rounded-md border border-red-300 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Nonaktifkan</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada lokasi sekolah sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $lokasiSekolah->links() }}</div>
</x-admin.layout>
