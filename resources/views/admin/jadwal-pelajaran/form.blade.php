<x-admin.layout :title="$jadwal->exists ? 'Edit Jadwal Pelajaran' : 'Tambah Jadwal Pelajaran'">
    <div class="mb-6">
        <a href="{{ route('admin.jadwal-pelajaran.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Kembali ke Jadwal Pelajaran
        </a>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">{{ $jadwal->exists ? 'Edit Jadwal Pelajaran' : 'Tambah Jadwal Pelajaran Baru' }}</h1>
    </div>

    <div class="max-w-3xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <form method="post" action="{{ $jadwal->exists ? route('admin.jadwal-pelajaran.update', $jadwal) : route('admin.jadwal-pelajaran.store') }}" class="space-y-4">
            @csrf
            @if ($jadwal->exists)
                @method('put')
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Mata Pelajaran <span class="text-red-500">*</span></label>
                    <select name="id_mata_pelajaran" required class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <option value="">Pilih Mata Pelajaran</option>
                        @foreach ($daftarMapel as $mp)
                            <option value="{{ $mp->id }}" @selected(old('id_mata_pelajaran', $jadwal->id_mata_pelajaran) == $mp->id)>{{ $mp->nama_mapel }} ({{ $mp->kode_mapel }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Kelas <span class="text-red-500">*</span></label>
                    <select name="id_kelas" required class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <option value="">Pilih Kelas</option>
                        @foreach ($daftarKelas as $k)
                            <option value="{{ $k->id }}" @selected(old('id_kelas', $jadwal->id_kelas) == $k->id)>{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Guru Pengampu <span class="text-red-500">*</span></label>
                    <select name="id_guru" required class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <option value="">Pilih Guru</option>
                        @foreach ($daftarGuru as $g)
                            <option value="{{ $g->id }}" @selected(old('id_guru', $jadwal->id_guru) == $g->id)>{{ $g->pengguna?->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Hari Mengajar <span class="text-red-500">*</span></label>
                    <select name="hari" required class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        @foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'] as $h)
                            <option value="{{ $h }}" @selected(old('hari', $jadwal->hari) === $h)>{{ ucfirst($h) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Jam Mulai (24 Jam) <span class="text-red-500">*</span></label>
                    <div class="relative mt-1">
                        <input type="text"
                               name="jam_mulai"
                               value="{{ old('jam_mulai', substr($jadwal->jam_mulai, 0, 5)) }}"
                               required
                               pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                               placeholder="07:30"
                               autocomplete="off"
                               class="timepicker-24 font-mono w-full rounded-lg border border-slate-300 pl-9 pr-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <i data-lucide="clock-3" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none"></i>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Format 24 jam (00:00 - 23:59)</p>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Jam Selesai (24 Jam) <span class="text-red-500">*</span></label>
                    <div class="relative mt-1">
                        <input type="text"
                               name="jam_selesai"
                               value="{{ old('jam_selesai', substr($jadwal->jam_selesai, 0, 5)) }}"
                               required
                               pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                               placeholder="09:30"
                               autocomplete="off"
                               class="timepicker-24 font-mono w-full rounded-lg border border-slate-300 pl-9 pr-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <i data-lucide="clock-3" class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400 pointer-events-none"></i>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Format 24 jam (00:00 - 23:59)</p>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Toleransi Terlambat <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="toleransi_terlambat_menit" value="{{ old('toleransi_terlambat_menit', $jadwal->toleransi_terlambat_menit) }}" required min="0" max="120" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <span class="text-xs text-slate-500 mt-1">menit</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Ruangan / Lab</label>
                    <input type="text" name="ruangan" value="{{ old('ruangan', $jadwal->ruangan) }}" placeholder="Contoh: R. 101, Lab Komputer" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Titik Lokasi Sekolah</label>
                    <select name="id_lokasi_sekolah" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <option value="">Gunakan Lokasi Default</option>
                        @foreach ($daftarLokasi as $lok)
                            <option value="{{ $lok->id }}" @selected(old('id_lokasi_sekolah', $jadwal->id_lokasi_sekolah) == $lok->id)>{{ $lok->nama }} (radius {{ $lok->radius_meter }}m)</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600">Status <span class="text-red-500">*</span></label>
                    <select name="aktif" class="mt-1 w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-teal-500 focus:outline-none">
                        <option value="1" @selected(old('aktif', $jadwal->aktif) == 1)>Aktif</option>
                        <option value="0" @selected(old('aktif', $jadwal->aktif) == 0)>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.jadwal-pelajaran.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Batal</a>
                <button type="submit" class="rounded-lg bg-teal-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-teal-700">Simpan Jadwal</button>
            </div>
        </form>
    </div>
</x-admin.layout>
