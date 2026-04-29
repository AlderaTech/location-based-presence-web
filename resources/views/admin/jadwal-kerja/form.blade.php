<x-admin.layout title="Form Jadwal Kerja">
    <h1 class="mb-5 text-2xl font-semibold">{{ $jadwal->exists ? 'Edit Jadwal Kerja' : 'Tambah Jadwal Kerja' }}</h1>
    <form method="post" action="{{ $jadwal->exists ? route('admin.jadwal-kerja.update', $jadwal) : route('admin.jadwal-kerja.store') }}" class="grid gap-4 rounded-md border border-slate-200 bg-white p-4 md:grid-cols-2">
        @csrf @if ($jadwal->exists) @method('put') @endif
        <label class="text-sm">Nama<input name="nama" value="{{ old('nama', $jadwal->nama) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Toleransi Terlambat<input name="toleransi_terlambat_menit" type="number" value="{{ old('toleransi_terlambat_menit', $jadwal->toleransi_terlambat_menit) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Jam Masuk<input name="jam_masuk" type="time" value="{{ old('jam_masuk', $jadwal->jam_masuk) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Jam Pulang<input name="jam_pulang" type="time" value="{{ old('jam_pulang', $jadwal->jam_pulang) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="flex items-center gap-2 text-sm md:col-span-2"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $jadwal->aktif ?? true))> Aktif</label>
        <div class="flex gap-2 md:col-span-2"><button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Simpan</button><a href="{{ route('admin.jadwal-kerja.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Batal</a></div>
    </form>
</x-admin.layout>
