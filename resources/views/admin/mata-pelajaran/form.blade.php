<x-admin.layout :title="$mataPelajaran->exists ? 'Edit Mata Pelajaran' : 'Tambah Mata Pelajaran'">
    <div class="mb-6">
        <a href="{{ route('admin.mata-pelajaran.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Kembali ke Mata Pelajaran
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $mataPelajaran->exists ? 'Edit Mapel: '.$mataPelajaran->nama_mapel : 'Tambah Mata Pelajaran Baru' }}</h1>
    </div>

    <div class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="post" action="{{ $mataPelajaran->exists ? route('admin.mata-pelajaran.update', $mataPelajaran) : route('admin.mata-pelajaran.store') }}" class="space-y-4">
            @csrf
            @if ($mataPelajaran->exists)
                @method('put')
            @endif

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Kode Mapel <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_mapel" value="{{ old('kode_mapel', $mataPelajaran->kode_mapel) }}" required placeholder="Contoh: MAT, BIND, BING" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none uppercase">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Status <span class="text-red-500">*</span></label>
                    <select name="aktif" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <option value="1" @selected(old('aktif', $mataPelajaran->aktif) == 1)>Aktif</option>
                        <option value="0" @selected(old('aktif', $mataPelajaran->aktif) == 0)>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600">Nama Mata Pelajaran <span class="text-red-500">*</span></label>
                <input type="text" name="nama_mapel" value="{{ old('nama_mapel', $mataPelajaran->nama_mapel) }}" required placeholder="Contoh: Matematika, Fisika" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600">Deskripsi</label>
                <textarea name="deskripsi" rows="3" placeholder="Keterangan singkat..." class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">{{ old('deskripsi', $mataPelajaran->deskripsi) }}</textarea>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.mata-pelajaran.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-lg bg-teal-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-teal-700">Simpan Mata Pelajaran</button>
            </div>
        </form>
    </div>
</x-admin.layout>
