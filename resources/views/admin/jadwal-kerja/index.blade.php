<x-admin.layout title="Jadwal Kerja">
    <div class="mb-5 flex items-center justify-between">
        <div><h1 class="text-2xl font-semibold">Jadwal Kerja</h1><p class="text-sm text-slate-500">Atur jam masuk, jam pulang, dan toleransi keterlambatan.</p></div>
        <a href="{{ route('admin.jadwal-kerja.create') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Tambah Jadwal</a>
    </div>
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Masuk</th><th class="px-4 py-3">Pulang</th><th class="px-4 py-3">Toleransi</th><th class="px-4 py-3">Status</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($jadwalKerja as $jadwal)
                    <tr><td class="px-4 py-3">{{ $jadwal->nama }}</td><td class="px-4 py-3">{{ $jadwal->jam_masuk }}</td><td class="px-4 py-3">{{ $jadwal->jam_pulang }}</td><td class="px-4 py-3">{{ $jadwal->toleransi_terlambat_menit }} menit</td><td class="px-4 py-3">{{ $jadwal->aktif ? 'Aktif' : 'Nonaktif' }}</td><td class="px-4 py-3"><div class="flex justify-end gap-2"><a href="{{ route('admin.jadwal-kerja.edit', $jadwal) }}" class="rounded-md border border-slate-300 px-3 py-1.5">Edit</a><form method="post" action="{{ route('admin.jadwal-kerja.destroy', $jadwal) }}">@csrf @method('delete')<button class="rounded-md border border-red-300 px-3 py-1.5 text-red-700">Nonaktifkan</button></form></div></td></tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada jadwal kerja.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $jadwalKerja->links() }}</div>
</x-admin.layout>
