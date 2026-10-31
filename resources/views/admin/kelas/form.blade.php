<x-admin.layout :title="$kelas->exists ? 'Edit Kelas' : 'Tambah Kelas'">
    <div class="mb-6">
        <a href="{{ route('admin.kelas.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Kembali ke Data Kelas
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $kelas->exists ? 'Edit Kelas: '.$kelas->nama_kelas : 'Tambah Kelas Baru' }}</h1>
    </div>

    <div class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="post" action="{{ $kelas->exists ? route('admin.kelas.update', $kelas) : route('admin.kelas.store') }}" class="space-y-4">
            @csrf
            @if ($kelas->exists)
                @method('put')
            @endif

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600">Nama Kelas <span class="text-red-500">*</span></label>
                <input type="text" name="nama_kelas" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required placeholder="Contoh: X-MIPA-1, VII-A" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Tingkat Kelas <span class="text-red-500">*</span></label>
                    <input type="number" name="tingkat" value="{{ old('tingkat', $kelas->tingkat) }}" required min="1" max="13" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    <p class="mt-1 text-xs text-slate-400">Contoh: 10 untuk SMA kelas 1, 7 untuk SMP</p>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Jurusan / Peminatan</label>
                    <input type="text" name="jurusan" value="{{ old('jurusan', $kelas->jurusan) }}" placeholder="Contoh: IPA, IPS, RPL" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Tahun Ajaran <span class="text-red-500">*</span></label>
                    <input type="text" name="tahun_ajaran" value="{{ old('tahun_ajaran', $kelas->tahun_ajaran) }}" required placeholder="2026/2027" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Status <span class="text-red-500">*</span></label>
                    <select name="aktif" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <option value="1" @selected(old('aktif', $kelas->aktif) == 1)>Aktif</option>
                        <option value="0" @selected(old('aktif', $kelas->aktif) == 0)>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.kelas.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-lg bg-teal-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-teal-700">Simpan Kelas</button>
            </div>
        </form>
    </div>
</x-admin.layout>
