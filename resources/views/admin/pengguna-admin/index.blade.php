<x-admin.layout title="Akun Admin">
    @php
        $labelPeran = [
            'admin' => 'Admin',
            'kepala_sekolah' => 'Kepala Sekolah',
        ];
    @endphp

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Akun Admin</h1>
            <p class="text-sm text-slate-500">Kelola Admin dan Kepala Sekolah.</p>
        </div>
        <a href="{{ route('admin.pengguna-admin.create') }}" class="inline-flex items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Tambah Akun</a>
    </div>

    <form class="mb-4 grid gap-3 rounded-md border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 md:grid-cols-[1fr_190px_170px_auto_auto]">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama, email, telepon" class="rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/10">
        <select name="peran" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm">
            <option value="">Semua peran</option>
            @foreach ($labelPeran as $peran => $label)
                <option value="{{ $peran }}" @selected(request('peran') === $peran)>{{ $label }}</option>
            @endforeach
        </select>
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
                        <td class="px-4 py-3">{{ $labelPeran[$pengguna->peran] ?? str_replace('_', ' ', $pengguna->peran) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $pengguna->status === 'aktif' ? 'bg-teal-50 text-teal-800 ring-1 ring-teal-200' : 'bg-slate-100 text-slate-600 ring-1 ring-slate-200' }}">
                                {{ ucfirst($pengguna->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.pengguna-admin.edit', $pengguna) }}" class="rounded-md border border-slate-300 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">Edit</a>
                                <form method="post" action="{{ route('admin.pengguna-admin.destroy', $pengguna) }}" data-confirm data-confirm-title="Nonaktifkan akun admin?" data-confirm-text="Akun pengelola tidak bisa digunakan untuk masuk setelah dinonaktifkan." data-confirm-button="Nonaktifkan" data-confirm-variant="danger">
                                    @csrf
                                    @method('delete')
                                    <button class="rounded-md border border-red-300 px-3 py-1.5 font-medium text-red-700 hover:bg-red-50">Nonaktifkan</button>
                                </form>
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
