<x-admin.layout title="Mata Pelajaran">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Mata Pelajaran</h1>
            <p class="text-sm text-slate-500">Kelola daftar mata pelajaran sekolah dan kurikulum.</p>
        </div>
        <a href="{{ route('admin.mata-pelajaran.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Mata Pelajaran
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="mb-5 flex items-center justify-between gap-3">
        <form method="get" class="flex flex-1 max-w-sm gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama atau kode mapel..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
            <button type="submit" class="rounded-lg bg-slate-900 px-3.5 py-2 text-xs font-bold text-white transition hover:bg-slate-800">Cari</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">Kode Mapel</th>
                    <th class="px-5 py-3.5">Nama Mata Pelajaran</th>
                    <th class="px-5 py-3.5">Deskripsi</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($mataPelajaran as $item)
                    <tr class="hover:bg-slate-50/75">
                        <td class="px-5 py-3.5 font-mono text-xs font-bold text-teal-800">{{ $item->kode_mapel }}</td>
                        <td class="px-5 py-3.5 font-bold text-slate-900">{{ $item->nama_mapel }}</td>
                        <td class="px-5 py-3.5 text-slate-500">{{ $item->deskripsi ?: '-' }}</td>
                        <td class="px-5 py-3.5">
                            <x-badge-aktif :aktif="$item->aktif" />
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                <a href="{{ route('admin.mata-pelajaran.edit', $item) }}" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                                    Edit
                                </a>
                                <form method="post" action="{{ route('admin.mata-pelajaran.destroy', $item) }}" class="inline-block" data-confirm data-confirm-title="Hapus mata pelajaran ini?" data-confirm-text="Jadwal yang menggunakan mapel ini akan terpengaruh." data-confirm-button="Ya, Hapus" data-confirm-variant="danger">
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
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">Belum ada mata pelajaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($mataPelajaran->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">
                {{ $mataPelajaran->links() }}
            </div>
        @endif
    </div>
</x-admin.layout>
