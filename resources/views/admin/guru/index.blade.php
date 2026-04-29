<x-admin.layout title="Data Guru">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Data Guru</h1>
            <p class="text-sm text-slate-500">Kelola akun guru dan reset perangkat terikat.</p>
        </div>
        <a href="{{ route('admin.guru.create') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Tambah Guru</a>
    </div>

    <form class="mb-4 flex max-w-lg gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama, email, atau NIP" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm">
        <button class="rounded-md border border-slate-300 px-4 py-2 text-sm">Terapkan</button>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr><th class="px-4 py-3">Guru</th><th class="px-4 py-3">NIP</th><th class="px-4 py-3">Mata Pelajaran</th><th class="px-4 py-3">Jadwal Kerja</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($guru as $guruItem)
                    <tr>
                        <td class="px-4 py-3"><div class="font-medium">{{ $guruItem->pengguna->nama }}</div><div class="text-xs text-slate-500">{{ $guruItem->pengguna->email }}</div></td>
                        <td class="px-4 py-3">{{ $guruItem->nip ?: $guruItem->pengguna->nomor_pegawai ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $guruItem->mata_pelajaran ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $guruItem->jadwalKerjaBawaan?->nama ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $guruItem->aktif ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.guru.edit', $guruItem) }}" class="rounded-md border border-slate-300 px-3 py-1.5">Edit</a>
                                <form method="post" action="{{ route('admin.guru.reset-perangkat', $guruItem) }}">@csrf<button class="rounded-md border border-amber-300 px-3 py-1.5 text-amber-700">Reset Perangkat</button></form>
                                <form method="post" action="{{ route('admin.guru.destroy', $guruItem) }}">@csrf @method('delete')<button class="rounded-md border border-red-300 px-3 py-1.5 text-red-700">Nonaktifkan</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada data guru.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $guru->links() }}</div>
</x-admin.layout>
