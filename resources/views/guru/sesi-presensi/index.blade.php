<x-admin.layout title="Sesi Presensi QR">
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Sesi Presensi QR Dinamis</h1>
            <p class="text-sm text-slate-500">Buka QR Code presensi spesifik mata pelajaran dengan batas waktu hidup (rentang tanggal & jam).</p>
        </div>
    </div>

    <!-- Sesi Aktif Hari Ini -->
    @if ($sesiHariIni->isNotEmpty())
        <div class="mb-8">
            <h2 class="mb-3 text-lg font-bold text-slate-800">Sesi Presensi Hari Ini</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($sesiHariIni as $sesi)
                    @php
                        $isAktif = $sesi->status === 'aktif' && ! $sesi->sudahKedaluwarsa();
                        $isKedaluwarsa = $sesi->status === 'kedaluwarsa' || $sesi->sudahKedaluwarsa();
                    @endphp
                    <div class="rounded-xl border {{ $isAktif ? 'border-teal-300 ring-2 ring-teal-500/10' : 'border-slate-200' }} bg-white p-5 shadow-sm transition hover:shadow-md">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $isAktif ? 'bg-teal-50 text-teal-800 ring-teal-600/20' : ($isKedaluwarsa ? 'bg-rose-50 text-rose-800 ring-rose-600/20' : 'bg-slate-100 text-slate-600 ring-slate-200') }}">
                                {{ $isAktif ? 'Sedang Aktif' : ($isKedaluwarsa ? 'Kedaluwarsa' : 'Sudah Ditutup') }}
                            </span>
                            <span class="text-xs text-slate-400 font-mono font-bold">{{ $sesi->kode_qr }}</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900">{{ $sesi->jadwalPelajaran?->mataPelajaran?->nama_mapel ?: $sesi->mataPelajaran?->nama_mapel }}</h3>
                        <p class="text-sm font-semibold text-teal-700">Kelas: {{ $sesi->jadwalPelajaran?->kelas?->nama_kelas }}</p>
                        
                        <div class="mt-2 rounded-lg bg-slate-50 p-2 text-xs text-slate-600 space-y-1 border border-slate-100">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">Masa Hidup QR:</span>
                                <span class="font-semibold font-mono text-slate-800">
                                    {{ $sesi->waktu_mulai ? $sesi->waktu_mulai->format('H:i') : substr($sesi->jadwalPelajaran?->jam_mulai, 0, 5) }} - 
                                    {{ $sesi->waktu_selesai ? $sesi->waktu_selesai->format('H:i') : substr($sesi->jadwalPelajaran?->jam_selesai, 0, 5) }} WIB
                                </span>
                            </div>
                            @if ($isAktif && $sesi->waktu_selesai)
                                <div class="flex items-center justify-between text-teal-700">
                                    <span>Sisa Waktu:</span>
                                    <span class="font-bold font-mono">
                                        {{ sprintf('%02d:%02d', floor($sesi->sisaDetik() / 60), $sesi->sisaDetik() % 60) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 flex items-center gap-2">
                            <a href="{{ route('admin.sesi-presensi.show', $sesi) }}" class="flex-1 rounded-lg {{ $isAktif ? 'bg-teal-600 text-white hover:bg-teal-700' : 'bg-slate-900 text-white hover:bg-slate-800' }} px-3 py-2 text-center text-xs font-semibold transition">
                                {{ $isAktif ? 'Buka Layar QR' : 'Lihat Rekap' }}
                            </a>
                            @if ($isAktif)
                                <form method="post" action="{{ route('admin.sesi-presensi.tutup', $sesi) }}" data-confirm data-confirm-title="Tutup sesi presensi?" data-confirm-text="Murid tidak dapat lagi melakukan presensi melalui sesi ini." data-confirm-button="Ya, Tutup Sesi" data-confirm-variant="warning">
                                    @csrf
                                    <button class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50">
                                        Tutup
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Jadwal Mengajar Hari Ini -->
    <div class="mb-8">
        <h2 class="mb-3 text-lg font-bold text-slate-800">Jadwal Mengajar Hari Ini ({{ ucfirst($hariIniStr) }})</h2>
        @if ($jadwalHariIni->isEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-8 text-center text-slate-500 shadow-sm">
                <i data-lucide="calendar-x-2" class="mx-auto mb-2 h-10 w-10 text-slate-400"></i>
                <p class="font-medium">Tidak ada jadwal mengajar pada hari ini.</p>
                <p class="text-xs text-slate-400 mt-1">Silakan periksa daftar semua jadwal mengajar di bawah.</p>
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($jadwalHariIni as $j)
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB
                            </span>
                            @if ($j->ruangan)
                                <span class="text-xs text-slate-400">R. {{ $j->ruangan }}</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-slate-900">{{ $j->mataPelajaran?->nama_mapel }}</h3>
                        <p class="text-sm font-semibold text-teal-700">Kelas: {{ $j->kelas?->nama_kelas }}</p>
                        <p class="text-xs text-slate-500 mt-0.5">Guru: {{ $j->guru?->pengguna?->nama }}</p>
                        <div class="mt-4">
                            <button type="button" onclick="bukaModalSesi('{{ $j->id }}', '{{ addslashes($j->mataPelajaran?->nama_mapel) }}', '{{ addslashes($j->kelas?->nama_kelas) }}', '{{ substr($j->jam_mulai, 0, 5) }}', '{{ substr($j->jam_selesai, 0, 5) }}', '{{ addslashes($j->guru?->pengguna?->nama) }}')" class="w-full flex items-center justify-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-teal-700 shadow-sm">
                                <i data-lucide="qr-code" class="h-4 w-4"></i>
                                Buka Sesi QR Dinamis
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Semua Jadwal Mengajar -->
    <div>
        <h2 class="mb-3 text-lg font-bold text-slate-800">Semua Jadwal Mengajar</h2>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-5 py-3">Hari</th>
                            <th class="px-5 py-3">Jam</th>
                            <th class="px-5 py-3">Mata Pelajaran</th>
                            <th class="px-5 py-3">Kelas</th>
                            <th class="px-5 py-3">Guru Pengampu</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($semuaJadwal as $j)
                            <tr class="hover:bg-slate-50/75">
                                <td class="px-5 py-3 font-semibold uppercase text-xs text-teal-800">{{ $j->hari }}</td>
                                <td class="px-5 py-3 text-slate-700">{{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}</td>
                                <td class="px-5 py-3 font-medium text-slate-900">{{ $j->mataPelajaran?->nama_mapel }}</td>
                                <td class="px-5 py-3 font-semibold text-slate-800">{{ $j->kelas?->nama_kelas }}</td>
                                <td class="px-5 py-3">{{ $j->guru?->pengguna?->nama }}</td>
                                <td class="px-5 py-3 text-right">
                                    <button type="button" onclick="bukaModalSesi('{{ $j->id }}', '{{ addslashes($j->mataPelajaran?->nama_mapel) }}', '{{ addslashes($j->kelas?->nama_kelas) }}', '{{ substr($j->jam_mulai, 0, 5) }}', '{{ substr($j->jam_selesai, 0, 5) }}', '{{ addslashes($j->guru?->pengguna?->nama) }}')" class="rounded-md border border-teal-600 bg-white px-2.5 py-1 text-xs font-semibold text-teal-700 shadow-xs transition hover:bg-teal-50">
                                        Buka Sesi QR
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-6 text-center text-slate-400">Belum ada jadwal mengajar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Dialog: Buka Sesi QR Dinamis -->
    <dialog id="modal-buka-sesi" onclick="if (event.target === this) this.close()" class="rounded-2xl border border-slate-200 p-0 shadow-2xl backdrop:bg-slate-900/50 max-w-lg w-full m-auto text-left">
        <div class="p-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Buka Sesi Presensi QR Dinamis</h3>
                    <p class="text-xs text-slate-500">Tentukan batas masa aktif QR dan pastikan mata pelajaran sesuai.</p>
                </div>
                <button type="button" onclick="document.getElementById('modal-buka-sesi').close()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <!-- Kartu Spesifikasi Mata Pelajaran -->
            <div class="mt-4 rounded-xl border border-teal-200 bg-teal-50/70 p-3.5 text-xs text-teal-900">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="font-bold text-[11px] uppercase tracking-wide text-teal-700">Mata Pelajaran:</span>
                        <p id="modal-mapel-nama" class="text-base font-extrabold text-teal-950 mt-0.5">-</p>
                    </div>
                    <span class="rounded bg-teal-200/60 px-2 py-0.5 text-[11px] font-bold text-teal-900">Spesifik Mapel</span>
                </div>
                <div class="mt-2 grid grid-cols-2 gap-2 text-teal-800 pt-2 border-t border-teal-200/50">
                    <div>Kelas: <span id="modal-kelas-nama" class="font-bold">-</span></div>
                    <div>Guru: <span id="modal-guru-nama" class="font-bold">-</span></div>
                </div>
                <p class="mt-2 text-[11px] text-teal-700 italic">
                    * QR ini eksklusif untuk mata pelajaran di atas dan tidak dapat dipakai untuk presensi mata pelajaran lain.
                </p>
            </div>

            <form method="post" action="{{ route('admin.sesi-presensi.store') }}" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="id_jadwal_pelajaran" id="modal-id-jadwal" value="">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Preset Durasi Cepat</label>
                    <div class="grid grid-cols-3 sm:grid-cols-5 gap-1.5">
                        <button type="button" onclick="setDurasiPreset('jadwal')" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:border-teal-300 hover:text-teal-800 transition text-center">
                            Sesuai Jam
                        </button>
                        <button type="button" onclick="setDurasiPreset(15)" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:border-teal-300 hover:text-teal-800 transition text-center">
                            15 Menit
                        </button>
                        <button type="button" onclick="setDurasiPreset(30)" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:border-teal-300 hover:text-teal-800 transition text-center">
                            30 Menit
                        </button>
                        <button type="button" onclick="setDurasiPreset(45)" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:border-teal-300 hover:text-teal-800 transition text-center">
                            45 Menit
                        </button>
                        <button type="button" onclick="setDurasiPreset(60)" class="rounded-lg border border-slate-200 bg-slate-50 px-2 py-1.5 text-xs font-semibold text-slate-700 hover:bg-teal-50 hover:border-teal-300 hover:text-teal-800 transition text-center">
                            60 Menit
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="input-tanggal-mulai" value="{{ today()->toDateString() }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jam Mulai (24 Jam)</label>
                        <div class="relative">
                            <input type="text"
                                   name="jam_mulai"
                                   id="input-jam-mulai"
                                   required
                                   pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                                   placeholder="07:30"
                                   autocomplete="off"
                                   class="timepicker-24 font-mono w-full rounded-lg border border-slate-300 pl-8 pr-3 py-2 text-xs focus:border-teal-500 focus:outline-none">
                            <i data-lucide="clock-3" class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai (Kedaluwarsa)</label>
                        <input type="date" name="tanggal_selesai" id="input-tanggal-selesai" value="{{ today()->toDateString() }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-teal-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jam Selesai (24 Jam)</label>
                        <div class="relative">
                            <input type="text"
                                   name="jam_selesai"
                                   id="input-jam-selesai"
                                   required
                                   pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                                   placeholder="09:30"
                                   autocomplete="off"
                                   class="timepicker-24 font-mono w-full rounded-lg border border-slate-300 pl-8 pr-3 py-2 text-xs focus:border-teal-500 focus:outline-none">
                            <i data-lucide="clock-3" class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 pointer-events-none"></i>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Sesi (Opsional)</label>
                    <input type="text" name="catatan" placeholder="Contoh: Kuis Pertemuan 4 / Materi Integral" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-teal-500 focus:outline-none">
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modal-buka-sesi').close()" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="rounded-lg bg-teal-600 px-5 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow-sm transition inline-flex items-center gap-1.5">
                        <i data-lucide="qr-code" class="h-4 w-4"></i>
                        Buka Sesi QR Sekarang
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    @push('head')
    <script>
        let jadwalJamMulaiDefault = '07:30';
        let jadwalJamSelesaiDefault = '09:30';

        function bukaModalSesi(id, mapel, kelas, jamMulai, jamSelesai, guru) {
            document.getElementById('modal-id-jadwal').value = id;
            document.getElementById('modal-mapel-nama').textContent = mapel;
            document.getElementById('modal-kelas-nama').textContent = kelas;
            document.getElementById('modal-guru-nama').textContent = guru;

            jadwalJamMulaiDefault = jamMulai;
            jadwalJamSelesaiDefault = jamSelesai;

            const now = new Date();
            const jamSekarang = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

            const inputJamMulai = document.getElementById('input-jam-mulai');
            const inputJamSelesai = document.getElementById('input-jam-selesai');

            inputJamMulai.value = jamSekarang;
            if (inputJamMulai._flatpickr) {
                inputJamMulai._flatpickr.setDate(jamSekarang, false);
            }

            inputJamSelesai.value = jamSelesai;
            if (inputJamSelesai._flatpickr) {
                inputJamSelesai._flatpickr.setDate(jamSelesai, false);
            }
            inputJamSelesai.dataset.defaultJadwal = jamSelesai;

            const modal = document.getElementById('modal-buka-sesi');
            modal.showModal();
            if (window.initTimepickers) window.initTimepickers(modal);
            if (window.refreshIcons) window.refreshIcons();
        }

        function setDurasiPreset(preset) {
            const tglMulai = document.getElementById('input-tanggal-mulai').value;
            const tglSelesaiInput = document.getElementById('input-tanggal-selesai');
            const jamMulaiInput = document.getElementById('input-jam-mulai');
            const jamSelesaiInput = document.getElementById('input-jam-selesai');

            tglSelesaiInput.value = tglMulai;

            if (preset === 'jadwal') {
                jamSelesaiInput.value = jadwalJamSelesaiDefault;
                if (jamSelesaiInput._flatpickr) {
                    jamSelesaiInput._flatpickr.setDate(jadwalJamSelesaiDefault, false);
                }
                return;
            }

            const now = new Date();
            if (jamMulaiInput.value) {
                const parts = jamMulaiInput.value.split(':');
                now.setHours(parseInt(parts[0], 10), parseInt(parts[1], 10), 0);
            }

            now.setMinutes(now.getMinutes() + parseInt(preset, 10));
            const hh = String(now.getHours()).padStart(2, '0');
            const mm = String(now.getMinutes()).padStart(2, '0');
            const hasilWaktu = `${hh}:${mm}`;
            jamSelesaiInput.value = hasilWaktu;
            if (jamSelesaiInput._flatpickr) {
                jamSelesaiInput._flatpickr.setDate(hasilWaktu, false);
            }
        }
    </script>
    @endpush
</x-admin.layout>
