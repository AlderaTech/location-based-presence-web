<x-admin.layout title="Koreksi Presensi Murid">
    <div class="mb-6">
        <a href="{{ route('admin.presensi.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Kembali ke Data Presensi
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Koreksi Presensi Murid</h1>
    </div>

    <div class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="post" action="{{ route('admin.presensi.update', $presensi) }}" class="space-y-4">
            @csrf
            @method('put')

            <div class="grid grid-cols-2 gap-4 rounded-lg bg-slate-50 p-4 text-xs">
                <div>
                    <span class="text-slate-400">Murid:</span>
                    <p class="font-bold text-slate-900 text-sm">{{ $presensi->pengguna?->nama }}</p>
                    <p class="font-mono text-slate-500">NIS: {{ $presensi->murid?->nis }}</p>
                </div>
                <div>
                    <span class="text-slate-400">Kelas / Mapel:</span>
                    <p class="font-bold text-slate-900 text-sm">{{ $presensi->jadwalPelajaran?->mataPelajaran?->nama_mapel ?: '-' }}</p>
                    <p class="text-teal-700 font-semibold">Kelas: {{ $presensi->murid?->kelas?->nama_kelas ?: '-' }}</p>
                </div>
                <div>
                    <span class="text-slate-400">Tanggal & Waktu Scan:</span>
                    <p class="font-semibold text-slate-800">{{ $presensi->tanggal_presensi?->format('d/m/Y') }} {{ $presensi->waktu_presensi ? '(' . $presensi->waktu_presensi->format('H:i:s') . ')' : '-' }}</p>
                </div>
                <div>
                    <span class="text-slate-400">Metode Presensi:</span>
                    <div class="mt-1">
                        <x-badge-metode :metode="$presensi->metode" />
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600">Status Kehadiran <span class="text-red-500">*</span></label>
                <select name="status" required class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                    <option value="hadir" @selected(old('status', $presensi->status) === 'hadir')>Hadir</option>
                    <option value="terlambat" @selected(old('status', $presensi->status) === 'terlambat')>Terlambat</option>
                    <option value="izin" @selected(old('status', $presensi->status) === 'izin')>Izin</option>
                    <option value="sakit" @selected(old('status', $presensi->status) === 'sakit')>Sakit</option>
                    <option value="alfa" @selected(old('status', $presensi->status) === 'alfa')>Alfa</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-600">Alasan Koreksi <span class="text-red-500">*</span></label>
                <textarea name="alasan_koreksi" rows="3" required placeholder="Jelaskan alasan perubahan status kehadiran murid ini..." class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">{{ old('alasan_koreksi', $presensi->alasan_koreksi) }}</textarea>
                <p class="mt-1 text-xs text-slate-400">Alasan ini akan disimpan di log audit.</p>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.presensi.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-lg bg-teal-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-teal-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</x-admin.layout>
