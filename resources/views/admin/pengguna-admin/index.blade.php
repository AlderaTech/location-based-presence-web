<x-admin.layout title="Akun Admin">
    <div class="mb-5 flex items-center justify-between">
        <div><h1 class="text-2xl font-semibold">Akun Admin</h1><p class="text-sm text-slate-500">Kelola Admin, Tata Usaha, dan Kepala Sekolah.</p></div>
        <a href="{{ route('admin.pengguna-admin.create') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Tambah Akun</a>
    </div>
    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Peran</th><th class="px-4 py-3">Status</th><th></th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($penggunaAdmin as $pengguna)
                    <tr><td class="px-4 py-3">{{ $pengguna->nama }}</td><td class="px-4 py-3">{{ $pengguna->email }}</td><td class="px-4 py-3">{{ str_replace('_', ' ', $pengguna->peran) }}</td><td class="px-4 py-3">{{ $pengguna->status }}</td><td class="px-4 py-3"><div class="flex justify-end gap-2"><a href="{{ route('admin.pengguna-admin.edit', $pengguna) }}" class="rounded-md border border-slate-300 px-3 py-1.5">Edit</a><form method="post" action="{{ route('admin.pengguna-admin.destroy', $pengguna) }}">@csrf @method('delete')<button class="rounded-md border border-red-300 px-3 py-1.5 text-red-700">Nonaktifkan</button></form></div></td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada akun admin.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $penggunaAdmin->links() }}</div>
</x-admin.layout>
