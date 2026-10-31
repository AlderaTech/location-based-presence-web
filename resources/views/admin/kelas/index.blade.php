<x-admin.layout title="Data Kelas">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Data Kelas</h1>
            <p class="text-sm text-slate-500">Kelola daftar kelas siswa, tingkat pendidikan, dan tahun ajaran.</p>
        </div>
        <a href="{{ route('admin.kelas.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Kelas
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="mb-5 flex items-center justify-between gap-3">
        <form method="get" class="flex flex-1 max-w-sm gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama kelas atau jurusan..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
            <button type="submit" class="rounded-lg bg-slate-900 px-3.5 py-2 text-xs font-bold text-white transition hover:bg-slate-800">Cari</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">Nama Kelas</th>
                    <th class="px-5 py-3.5">Tingkat</th>
                    <th class="px-5 py-3.5">Jurusan</th>
                    <th class="px-5 py-3.5">Tahun Ajaran</th>
                    <th class="px-5 py-3.5">Jumlah Murid</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($kelas as $item)
                    <tr class="hover:bg-slate-50/75">
                        <td class="px-5 py-3.5 font-bold text-slate-900">{{ $item->nama_kelas }}</td>
                        <td class="px-5 py-3.5">Kelas {{ $item->tingkat }}</td>
                        <td class="px-5 py-3.5">{{ $item->jurusan ?: '-' }}</td>
                        <td class="px-5 py-3.5">{{ $item->tahun_ajaran }}</td>
                        <td class="px-5 py-3.5 font-semibold text-slate-700">{{ $item->murid_count }} Siswa</td>
                        <td class="px-5 py-3.5">
                            <x-badge-aktif :aktif="$item->aktif" />
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                <a href="{{ route('admin.kelas.edit', $item) }}" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                                    Edit
                                </a>
                                <form method="post" action="{{ route('admin.kelas.destroy', $item) }}" class="inline-block" data-confirm data-confirm-title="Hapus kelas ini?" data-confirm-text="Semua jadwal terkait kelas ini akan terpengaruh." data-confirm-button="Ya, Hapus" data-confirm-variant="danger">
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
                        <td colspan="7" class="px-5 py-8 text-center text-slate-400">Belum ada data kelas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($kelas->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">
                {{ $kelas->links() }}
            </div>
        @endif
    </div>
</x-admin.layout>
