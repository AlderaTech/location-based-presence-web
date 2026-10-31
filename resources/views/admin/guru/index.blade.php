<x-admin.layout title="Data Guru">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Data Guru</h1>
            <p class="text-sm text-slate-500">Kelola akun guru, jadwal bawaan, status aktif, dan perangkat terikat.</p>
        </div>
        <a href="{{ route('admin.guru.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Guru
        </a>
    </div>

    <form class="mb-4 grid gap-3 rounded-md border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 md:grid-cols-[1.4fr_180px_220px_1fr_auto_auto]">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama, email, NIP, nomor pegawai" class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/10">
        <select name="status" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
        </select>
        <select name="id_jadwal_kerja" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua jadwal</option>
            @foreach ($jadwalKerja as $jadwal)
                <option value="{{ $jadwal->id }}" @selected(request('id_jadwal_kerja') == $jadwal->id)>{{ $jadwal->nama }}</option>
            @endforeach
        </select>
        <input name="mata_pelajaran" value="{{ request('mata_pelajaran') }}" placeholder="Mata pelajaran" class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/10">
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Terapkan</button>
        <a href="{{ route('admin.guru.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="nama" label="Guru" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="nip" label="NIP" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="mata_pelajaran" label="Mata Pelajaran" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="jadwal" label="Jadwal Kerja" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="status" label="Status" /></th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($guru as $guruItem)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-900">{{ $guruItem->pengguna->nama }}</div>
                            <div class="text-xs text-slate-500">{{ $guruItem->pengguna->email }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $guruItem->nip ?: $guruItem->pengguna->nomor_pegawai ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $guruItem->mata_pelajaran ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $guruItem->jadwalKerjaBawaan?->nama ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <x-badge-aktif :aktif="$guruItem->aktif" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                <a href="{{ route('admin.guru.edit', $guruItem) }}" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">Edit</a>
                                <form method="post" action="{{ route('admin.guru.reset-perangkat', $guruItem) }}" data-confirm data-confirm-title="Reset perangkat guru?" data-confirm-text="Guru perlu masuk ulang dari perangkat yang dipakai berikutnya." data-confirm-button="Reset Perangkat" data-confirm-variant="warning">
                                    @csrf
                                    <button class="rounded-md border border-amber-300 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 shadow-xs transition hover:bg-amber-100">Reset Perangkat</button>
                                </form>
                                <form method="post" action="{{ route('admin.guru.destroy', $guruItem) }}" data-confirm data-confirm-title="Nonaktifkan guru?" data-confirm-text="Akun guru akan dinonaktifkan, tetapi riwayat presensi tetap tersimpan." data-confirm-button="Nonaktifkan" data-confirm-variant="danger">
                                    @csrf
                                    @method('delete')
                                    <button class="rounded-md border border-red-200 bg-white px-2.5 py-1 text-xs font-semibold text-red-600 shadow-xs transition hover:bg-red-50">Nonaktifkan</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada data guru sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $guru->links() }}</div>
</x-admin.layout>
