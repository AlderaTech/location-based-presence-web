<x-admin.layout title="Form Jadwal Kerja">
    <h1 class="mb-5 text-2xl font-semibold">{{ $jadwal->exists ? 'Edit Jadwal Kerja' : 'Tambah Jadwal Kerja' }}</h1>
    <form method="post" action="{{ $jadwal->exists ? route('admin.jadwal-kerja.update', $jadwal) : route('admin.jadwal-kerja.store') }}" class="grid gap-4 rounded-md border border-slate-200 bg-white p-4 md:grid-cols-2">
        @csrf @if ($jadwal->exists) @method('put') @endif
        <label class="text-sm">Nama<input name="nama" value="{{ old('nama', $jadwal->nama) }}" required placeholder="Contoh: Jadwal Reguler" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Toleransi Terlambat<input name="toleransi_terlambat_menit" type="number" value="{{ old('toleransi_terlambat_menit', $jadwal->toleransi_terlambat_menit) }}" required placeholder="Contoh: 10" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <div>
            <label class="text-sm block font-medium text-slate-700">
                Jam Masuk (Format 24 Jam) <span class="text-red-500">*</span>
            </label>
            <div class="relative mt-1">
                <input name="jam_masuk"
                       type="text"
                       value="{{ old('jam_masuk', $jadwal->jam_masuk ? substr($jadwal->jam_masuk, 0, 5) : '') }}"
                       required
                       pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                       placeholder="07:00"
                       autocomplete="off"
                       class="timepicker-24 font-mono w-full rounded-md border border-slate-300 pl-9 pr-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                <i data-lucide="clock-3" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none"></i>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Contoh: 07:00 (Format 24 jam 00:00 - 23:59)</p>
        </div>
        <div>
            <label class="text-sm block font-medium text-slate-700">
                Jam Pulang (Format 24 Jam) <span class="text-red-500">*</span>
            </label>
            <div class="relative mt-1">
                <input name="jam_pulang"
                       type="text"
                       value="{{ old('jam_pulang', $jadwal->jam_pulang ? substr($jadwal->jam_pulang, 0, 5) : '') }}"
                       required
                       pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                       placeholder="15:00"
                       autocomplete="off"
                       class="timepicker-24 font-mono w-full rounded-md border border-slate-300 pl-9 pr-3 py-2 text-sm focus:border-teal-500 focus:outline-none">
                <i data-lucide="clock-3" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none"></i>
            </div>
            <p class="text-[11px] text-slate-400 mt-1">Contoh: 15:00 (Format 24 jam 00:00 - 23:59)</p>
        </div>
        <label class="flex items-center gap-2 text-sm md:col-span-2"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $jadwal->aktif ?? true))> Aktif</label>
        <div class="flex gap-2 md:col-span-2"><button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Simpan</button><a href="{{ route('admin.jadwal-kerja.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Batal</a></div>
    </form>
</x-admin.layout>
