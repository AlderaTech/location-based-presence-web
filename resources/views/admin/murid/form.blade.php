<x-admin.layout :title="$murid->exists ? 'Edit Murid' : 'Tambah Murid'">
    <div class="mb-6">
        <a href="{{ route('admin.murid.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Kembali ke Data Murid
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $murid->exists ? 'Edit Murid: '.$pengguna->nama : 'Tambah Murid Baru' }}</h1>
    </div>

    <div class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="post" action="{{ $murid->exists ? route('admin.murid.update', $murid) : route('admin.murid.store') }}" class="space-y-4">
            @csrf
            @if ($murid->exists)
                @method('put')
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Nama Lengkap Murid <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama', $pengguna->nama) }}" required placeholder="Contoh: Ahmad Fauzi" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Kelas <span class="text-red-500">*</span></label>
                    <select name="id_kelas" required class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <option value="">Pilih Kelas</option>
                        @foreach ($daftarKelas as $k)
                            <option value="{{ $k->id }}" @selected(old('id_kelas', $murid->id_kelas) == $k->id)>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">NIS (Nomor Induk Siswa) <span class="text-red-500">*</span></label>
                    <input type="text" name="nis" value="{{ old('nis', $murid->nis) }}" required placeholder="Contoh: 20261001" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">NISN</label>
                    <input type="text" name="nisn" value="{{ old('nisn', $murid->nisn) }}" placeholder="10 digit NISN" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="jenis_kelamin" required class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <option value="L" @selected(old('jenis_kelamin', $murid->jenis_kelamin) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin', $murid->jenis_kelamin) === 'P')>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Email Akun Murid <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $pengguna->email) }}" required placeholder="murid@sekolah.sch.id" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">
                        Kata Sandi
                        @if ($murid->exists) <span class="font-normal text-slate-400 normal-case">(kosongkan jika tidak diubah)</span> @else <span class="text-red-500">*</span> @endif
                    </label>
                    <input type="password" name="kata_sandi" {{ $murid->exists ? '' : 'required' }} minlength="6" placeholder="Minimal 6 karakter" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Telepon Murid</label>
                    <input type="text" name="telepon" value="{{ old('telepon', $pengguna->telepon) }}" placeholder="08xxxxxxxxxx" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Telepon Orang Tua / Wali</label>
                    <input type="text" name="telepon_wali" value="{{ old('telepon_wali', $murid->telepon_wali) }}" placeholder="08xxxxxxxxxx" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600">Alamat Tempat Tinggal</label>
                <textarea name="alamat" rows="2" placeholder="Alamat lengkap murid..." class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">{{ old('alamat', $murid->alamat) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600">Status Akun <span class="text-red-500">*</span></label>
                <select name="aktif" class="mt-1 w-48 rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    <option value="1" @selected(old('aktif', $murid->aktif) == 1)>Aktif</option>
                    <option value="0" @selected(old('aktif', $murid->aktif) == 0)>Nonaktif</option>
                </select>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.murid.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-lg bg-teal-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-teal-700">Simpan Data Murid</button>
            </div>
        </form>
    </div>
</x-admin.layout>
