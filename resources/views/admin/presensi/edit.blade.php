<x-admin.layout title="Koreksi Presensi">
    @php
        $labelStatus = [
            'hadir' => 'Tepat Waktu',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            'alpha' => 'Alpha',
        ];
    @endphp

    <h1 class="mb-5 text-2xl font-semibold">Koreksi Presensi</h1>
    <form method="post" action="{{ route('admin.presensi.update', $presensi) }}" class="grid gap-4 rounded-md border border-slate-200 bg-white p-4 md:grid-cols-2">
        @csrf @method('put')
        <div class="text-sm"><span class="text-slate-500">Guru</span><div class="font-medium">{{ $presensi->pengguna?->nama }}</div></div>
        <div class="text-sm"><span class="text-slate-500">Tanggal</span><div class="font-medium">{{ $presensi->tanggal_presensi?->format('Y-m-d') }}</div></div>
        <label class="text-sm">Status<select name="status" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">@foreach ($labelStatus as $status => $label)<option value="{{ $status }}" @selected(old('status', $presensi->status) === $status)>{{ $label }}</option>@endforeach</select></label>
        <label class="text-sm">Presensi Masuk<input name="jam_masuk_pada" type="datetime-local" value="{{ old('jam_masuk_pada', $presensi->jam_masuk_pada?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm">Presensi Pulang<input name="jam_pulang_pada" type="datetime-local" value="{{ old('jam_pulang_pada', $presensi->jam_pulang_pada?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2"></label>
        <label class="text-sm md:col-span-2">Catatan<textarea name="catatan" rows="3" class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">{{ old('catatan', $presensi->catatan) }}</textarea></label>
        <label class="text-sm md:col-span-2">Alasan Koreksi<textarea name="alasan_koreksi" rows="3" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2">{{ old('alasan_koreksi', $presensi->alasan_koreksi) }}</textarea></label>
        <div class="flex gap-2 md:col-span-2"><button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Simpan Koreksi</button><a href="{{ route('admin.presensi.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm">Batal</a></div>
    </form>
</x-admin.layout>
