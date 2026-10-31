<x-admin.layout title="Form Akun Admin">
    <h1 class="mb-5 text-2xl font-semibold">{{ $pengguna->exists ? 'Edit Akun Admin' : 'Tambah Akun Admin' }}</h1>
    <form method="post" action="{{ $pengguna->exists ? route('admin.pengguna-admin.update', $pengguna) : route('admin.pengguna-admin.store') }}" class="grid gap-4 rounded-md border border-slate-200 bg-white p-4 md:grid-cols-2">
        @csrf @if ($pengguna->exists) @method('put') @endif
        <label class="text-sm">Nama<input name="nama" value="{{ old('nama', $pengguna->nama) }}" required placeholder="Masukkan nama admin" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Email<input name="email" type="email" value="{{ old('email', $pengguna->email) }}" required placeholder="admin@presensi.test" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Kata Sandi<input name="kata_sandi" type="password" {{ $pengguna->exists ? '' : 'required' }} class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" placeholder="{{ $pengguna->exists ? 'Kosongkan jika tidak diganti' : 'Masukkan kata sandi' }}"></label>
        <label class="text-sm">Telepon<input name="telepon" value="{{ old('telepon', $pengguna->telepon) }}" placeholder="Contoh: 081234567890" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Peran<input type="text" value="Admin" readonly class="mt-1 w-full rounded-md border border-slate-200 bg-slate-50 px-3 py-2 text-slate-500 cursor-not-allowed"><input type="hidden" name="peran" value="admin"></label>
        <label class="text-sm">Status<select name="status" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="aktif" @selected(old('status', $pengguna->status) === 'aktif')>Aktif</option><option value="nonaktif" @selected(old('status', $pengguna->status) === 'nonaktif')>Nonaktif</option></select></label>
        <div class="flex gap-2 md:col-span-2"><button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Simpan</button><a href="{{ route('admin.pengguna-admin.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Batal</a></div>
    </form>
</x-admin.layout>
