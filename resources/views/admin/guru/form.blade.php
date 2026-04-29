<x-admin.layout title="Form Guru">
    <div class="mb-5">
        <h1 class="text-2xl font-semibold">{{ $guruItem->exists ? 'Edit Guru' : 'Tambah Guru' }}</h1>
        <p class="text-sm text-slate-500">Akun ini dipakai guru untuk masuk di aplikasi Android.</p>
    </div>
    <form method="post" action="{{ $guruItem->exists ? route('admin.guru.update', $guruItem) : route('admin.guru.store') }}" class="grid gap-4 rounded-md border border-slate-200 bg-white p-4 md:grid-cols-2">
        @csrf
        @if ($guruItem->exists) @method('put') @endif
        <label class="text-sm">Nama<input name="nama" value="{{ old('nama', $pengguna->nama) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Email<input name="email" type="email" value="{{ old('email', $pengguna->email) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Kata Sandi<input name="kata_sandi" type="password" {{ $guruItem->exists ? '' : 'required' }} class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2" placeholder="{{ $guruItem->exists ? 'Kosongkan jika tidak diganti' : '' }}"></label>
        <label class="text-sm">Nomor Pegawai<input name="nomor_pegawai" value="{{ old('nomor_pegawai', $pengguna->nomor_pegawai) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">NIP<input name="nip" value="{{ old('nip', $guruItem->nip) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Telepon<input name="telepon" value="{{ old('telepon', $pengguna->telepon) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Mata Pelajaran<input name="mata_pelajaran" value="{{ old('mata_pelajaran', $guruItem->mata_pelajaran) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Jabatan<input name="jabatan" value="{{ old('jabatan', $guruItem->jabatan) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Jadwal Kerja<select name="id_jadwal_kerja_bawaan" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="">Pilih jadwal</option>@foreach ($jadwalKerja as $jadwal)<option value="{{ $jadwal->id }}" @selected(old('id_jadwal_kerja_bawaan', $guruItem->id_jadwal_kerja_bawaan) == $jadwal->id)>{{ $jadwal->nama }}</option>@endforeach</select></label>
        <label class="text-sm">Status<select name="status" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="aktif" @selected(old('status', $pengguna->status) === 'aktif')>Aktif</option><option value="nonaktif" @selected(old('status', $pengguna->status) === 'nonaktif')>Nonaktif</option></select></label>
        <label class="text-sm md:col-span-2">Jadwal Mengajar<textarea name="jadwal_kerja" rows="3" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">{{ old('jadwal_kerja', $guruItem->jadwal_kerja) }}</textarea></label>
        <label class="text-sm md:col-span-2">Alamat<textarea name="alamat" rows="3" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">{{ old('alamat', $guruItem->alamat) }}</textarea></label>
        <div class="flex gap-2 md:col-span-2">
            <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Simpan</button>
            <a href="{{ route('admin.guru.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Batal</a>
        </div>
    </form>
</x-admin.layout>
