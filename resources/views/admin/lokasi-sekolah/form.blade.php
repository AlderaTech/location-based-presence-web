<x-admin.layout title="Form Lokasi">
    <h1 class="mb-5 text-2xl font-semibold">{{ $lokasi->exists ? 'Edit Lokasi' : 'Tambah Lokasi' }}</h1>
    <form method="post" action="{{ $lokasi->exists ? route('admin.lokasi-sekolah.update', $lokasi) : route('admin.lokasi-sekolah.store') }}" class="grid gap-4 rounded-md border border-slate-200 bg-white p-4 md:grid-cols-2">
        @csrf @if ($lokasi->exists) @method('put') @endif
        <label class="text-sm">Nama<input name="nama" value="{{ old('nama', $lokasi->nama) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Radius Meter<input name="radius_meter" type="number" value="{{ old('radius_meter', $lokasi->radius_meter) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Latitude<input name="latitude" value="{{ old('latitude', $lokasi->latitude) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Longitude<input name="longitude" value="{{ old('longitude', $lokasi->longitude) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="flex items-center gap-2 text-sm md:col-span-2"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $lokasi->aktif ?? true))> Aktif</label>
        <div class="flex gap-2 md:col-span-2"><button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Simpan</button><a href="{{ route('admin.lokasi-sekolah.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Batal</a></div>
    </form>
</x-admin.layout>
