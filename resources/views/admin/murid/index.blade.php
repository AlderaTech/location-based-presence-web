<x-admin.layout title="Data Murid">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Data Murid</h1>
            <p class="text-sm text-slate-500">Kelola akun murid, penempatan kelas, dan ikatan perangkat HP.</p>
        </div>
        <a href="{{ route('admin.murid.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Murid
        </a>
    </div>

    <!-- Filter & Search -->
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <form method="get" class="flex flex-wrap flex-1 gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, NIS, atau email..." class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
            <select name="id_kelas" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                <option value="">Semua Kelas</option>
                @foreach ($daftarKelas as $k)
                    <option value="{{ $k->id }}" @selected(request('id_kelas') == $k->id)>{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-slate-900 px-3.5 py-2 text-xs font-bold text-white transition hover:bg-slate-800">Filter</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500 border-b border-slate-200">
                <tr>
                    <th class="px-5 py-3.5">NIS</th>
                    <th class="px-5 py-3.5">Nama Murid</th>
                    <th class="px-5 py-3.5">Kelas</th>
                    <th class="px-5 py-3.5">Email / Login</th>
                    <th class="px-5 py-3.5">Perangkat Terikat</th>
                    <th class="px-5 py-3.5">Status</th>
                    <th class="px-5 py-3.5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($murid as $item)
                    @php $perangkatAktif = $item->pengguna?->perangkat?->firstWhere('aktif', true); @endphp
                    <tr class="hover:bg-slate-50/75">
                        <td class="px-5 py-3.5 font-mono text-xs font-bold text-teal-800">{{ $item->nis }}</td>
                        <td class="px-5 py-3.5 font-bold text-slate-900">{{ $item->pengguna?->nama }}</td>
                        <td class="px-5 py-3.5 font-semibold text-slate-800">{{ $item->kelas?->nama_kelas ?: '-' }}</td>
                        <td class="px-5 py-3.5 text-xs text-slate-500">{{ $item->pengguna?->email }}</td>
                        <td class="px-5 py-3.5">
                            @if ($perangkatAktif)
                                <div class="flex items-center gap-1.5 text-xs text-slate-700">
                                    <i data-lucide="smartphone" class="h-3.5 w-3.5 text-teal-600"></i>
                                    <span class="truncate max-w-[130px] font-mono text-[11px]">{{ $perangkatAktif->nama_perangkat ?: $perangkatAktif->id_perangkat }}</span>
                                </div>
                            @else
                                <span class="text-xs text-slate-400 italic">Belum terikat</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <x-badge-aktif :aktif="$item->aktif" />
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                @if ($perangkatAktif)
                                    <form method="post" action="{{ route('admin.murid.reset-perangkat', $item) }}" class="inline-block" data-confirm data-confirm-title="Reset perangkat murid?" data-confirm-text="Murid perlu login ulang dari perangkat barunya." data-confirm-button="Ya, Reset" data-confirm-variant="warning">
                                        @csrf
                                        <button type="submit" class="rounded-md border border-amber-300 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 shadow-xs transition hover:bg-amber-100">
                                            Reset HP
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.murid.edit', $item) }}" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">
                                    Edit
                                </a>
                                <form method="post" action="{{ route('admin.murid.destroy', $item) }}" class="inline-block" data-confirm data-confirm-title="Hapus murid ini?" data-confirm-text="Akun dan riwayat presensi murid akan dihapus." data-confirm-button="Ya, Hapus" data-confirm-variant="danger">
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
                        <td colspan="7" class="px-5 py-8 text-center text-slate-400">Belum ada data murid.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @if ($murid->hasPages())
            <div class="border-t border-slate-200 px-5 py-3">
                {{ $murid->links() }}
            </div>
        @endif
    </div>
</x-admin.layout>
