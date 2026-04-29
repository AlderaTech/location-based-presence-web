<x-admin.layout title="Hari Libur">
    <div class="mb-5 flex items-center justify-between">
        <div><h1 class="text-2xl font-semibold">Hari Libur</h1><p class="text-sm text-slate-500">Kalender libur nasional dan sekolah.</p></div>
        <a href="{{ route('admin.hari-libur.create') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Tambah Libur</a>
    </div>
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Jenis</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($hariLibur as $libur)
                    <tr><td class="px-4 py-3">{{ $libur->tanggal->format('Y-m-d') }}</td><td class="px-4 py-3">{{ $libur->nama }}</td><td class="px-4 py-3">{{ $libur->jenis === 'nasional' ? 'Nasional' : 'Sekolah' }}</td><td class="px-4 py-3"><div class="flex justify-end gap-2"><a href="{{ route('admin.hari-libur.edit', $libur) }}" class="rounded-md border border-slate-300 px-3 py-1.5">Edit</a><form method="post" action="{{ route('admin.hari-libur.destroy', $libur) }}">@csrf @method('delete')<button class="rounded-md border border-red-300 px-3 py-1.5 text-red-700">Hapus</button></form></div></td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum ada hari libur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $hariLibur->links() }}</div>
</x-admin.layout>
