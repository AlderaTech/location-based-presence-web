<x-admin.layout title="Akun Admin">
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Akun Admin</h1>
            <p class="text-sm text-slate-500">Kelola akun administrator sistem presensi.</p>
        </div>
        <a href="{{ route('admin.pengguna-admin.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700">
            <i data-lucide="plus" class="h-4 w-4"></i>
            Tambah Akun
        </a>
    </div>

    <form class="mb-4 grid gap-3 rounded-md border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 md:grid-cols-[1fr_180px_auto_auto]">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama, email, telepon" class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/10">
        <select name="status" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua status</option>
            <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
            <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
        </select>
        <button class="rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Terapkan</button>
        <a href="{{ route('admin.pengguna-admin.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
    </form>

    <div class="overflow-x-auto rounded-md border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="nama" label="Nama" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="email" label="Email" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="peran" label="Peran" /></th>
                    <th class="px-4 py-3"><x-admin.sort-link kolom="status" label="Status" /></th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($penggunaAdmin as $pengguna)
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3 font-semibold">{{ $pengguna->nama }}</td>
                        <td class="px-4 py-3">{{ $pengguna->email }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ring-slate-200 text-slate-700">Admin</span>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge-aktif :aktif="$pengguna->status === 'aktif'" />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1.5 whitespace-nowrap">
                                @if (auth()->id() === $pengguna->id)
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-500 ring-1 ring-inset ring-slate-200">
                                        Akun Anda
                                    </span>
                                @else
                                    <a href="{{ route('admin.pengguna-admin.edit', $pengguna) }}" class="rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50">Edit</a>
                                    <form method="post" action="{{ route('admin.pengguna-admin.destroy', $pengguna) }}" data-confirm data-confirm-title="Nonaktifkan akun admin?" data-confirm-text="Akun pengelola tidak bisa digunakan untuk masuk setelah dinonaktifkan." data-confirm-button="Nonaktifkan" data-confirm-variant="danger">
                                        @csrf
                                        @method('delete')
                                        <button class="rounded-md border border-red-200 bg-white px-2.5 py-1 text-xs font-semibold text-red-600 shadow-xs transition hover:bg-red-50">Nonaktifkan</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada akun admin sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $penggunaAdmin->links() }}</div>
</x-admin.layout>
