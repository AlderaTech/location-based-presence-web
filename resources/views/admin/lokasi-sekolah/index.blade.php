<x-admin.layout title="Lokasi Sekolah">
    <div class="mb-5 flex items-center justify-between">
        <div><h1 class="text-2xl font-semibold">Lokasi Sekolah</h1><p class="text-sm text-slate-500">Atur titik sekolah dan radius presensi.</p></div>
        <a href="{{ route('admin.lokasi-sekolah.create') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Tambah Lokasi</a>
    </div>
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Koordinat</th><th class="px-4 py-3">Radius</th><th class="px-4 py-3">Status</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($lokasiSekolah as $lokasi)
                    <tr><td class="px-4 py-3">{{ $lokasi->nama }}</td><td class="px-4 py-3">{{ $lokasi->latitude }}, {{ $lokasi->longitude }}</td><td class="px-4 py-3">{{ $lokasi->radius_meter }} m</td><td class="px-4 py-3">{{ $lokasi->aktif ? 'Aktif' : 'Nonaktif' }}</td><td class="px-4 py-3"><div class="flex justify-end gap-2"><a href="{{ route('admin.lokasi-sekolah.edit', $lokasi) }}" class="rounded-md border border-slate-300 px-3 py-1.5">Edit</a><form method="post" action="{{ route('admin.lokasi-sekolah.destroy', $lokasi) }}">@csrf @method('delete')<button class="rounded-md border border-red-300 px-3 py-1.5 text-red-700">Nonaktifkan</button></form></div></td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada lokasi sekolah.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $lokasiSekolah->links() }}</div>
</x-admin.layout>
