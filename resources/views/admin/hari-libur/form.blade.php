<x-admin.layout title="Form Hari Libur">
    <h1 class="mb-5 text-2xl font-semibold">{{ $hariLibur->exists ? 'Edit Hari Libur' : 'Tambah Hari Libur' }}</h1>
    <form method="post" action="{{ $hariLibur->exists ? route('admin.hari-libur.update', $hariLibur) : route('admin.hari-libur.store') }}" class="grid gap-4 rounded-md border border-slate-200 bg-white p-4 md:grid-cols-2">
        @csrf @if ($hariLibur->exists) @method('put') @endif
        <label class="text-sm">Tanggal<input name="tanggal" type="date" value="{{ old('tanggal', $hariLibur->tanggal?->format('Y-m-d')) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Jenis<select name="jenis" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"><option value="sekolah" @selected(old('jenis', $hariLibur->jenis) === 'sekolah')>Sekolah</option><option value="nasional" @selected(old('jenis', $hariLibur->jenis) === 'nasional')>Nasional</option></select></label>
        <label class="text-sm md:col-span-2">Nama Libur<input name="nama" value="{{ old('nama', $hariLibur->nama) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <div class="flex gap-2 md:col-span-2"><button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Simpan</button><a href="{{ route('admin.hari-libur.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Batal</a></div>
    </form>
</x-admin.layout>
