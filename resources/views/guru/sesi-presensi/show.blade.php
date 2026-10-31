<x-admin.layout title="Layar QR Presensi Kelas">
    @php
        $mapel = $sesi->jadwalPelajaran?->mataPelajaran ?: $sesi->mataPelajaran;
        $isAktif = $sesi->status === 'aktif' && ! $sesi->sudahKedaluwarsa();
        $isKedaluwarsa = $sesi->status === 'kedaluwarsa' || $sesi->sudahKedaluwarsa();
    @endphp

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sesi-presensi.index') }}" class="rounded-lg border border-slate-200 bg-white p-2 text-slate-600 transition hover:bg-slate-50">
                <i data-lucide="arrow-left" class="h-5 w-5"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-bold text-slate-900">
                        {{ $mapel?->nama_mapel ?? 'Mata Pelajaran' }}
                    </h1>
                    @if ($mapel?->kode_mapel)
                        <span class="rounded bg-teal-100 px-2 py-0.5 font-mono text-xs font-bold text-teal-800">
                            {{ $mapel->kode_mapel }}
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Kelas {{ $sesi->jadwalPelajaran?->kelas?->nama_kelas }} &bull; Guru: {{ $sesi->guru?->pengguna?->nama }} &bull; {{ $sesi->tanggal->format('d M Y') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="bukaModalPerpanjang()" class="flex items-center gap-1.5 rounded-lg border border-teal-600 bg-teal-50 px-3.5 py-2 text-xs font-bold text-teal-700 transition hover:bg-teal-100 shadow-2xs">
                <i data-lucide="clock-3" class="h-4 w-4"></i>
                Perpanjang Waktu
            </button>

            @if ($sesi->status === 'aktif')
                <form method="post" action="{{ route('admin.sesi-presensi.tutup', $sesi) }}" data-confirm data-confirm-title="Tutup sesi presensi?" data-confirm-text="Murid tidak dapat lagi melakukan presensi melalui sesi ini." data-confirm-button="Ya, Tutup Sesi" data-confirm-variant="warning">
                    @csrf
                    <button type="submit" class="flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-3.5 py-2 text-xs font-bold text-red-700 transition hover:bg-red-100">
                        <i data-lucide="lock" class="h-4 w-4"></i>
                        Tutup Sesi
                    </button>
                </form>
            @else
                <span class="rounded-lg bg-slate-200 px-3 py-2 text-xs font-bold text-slate-700">
                    Sesi Ditutup
                </span>
            @endif

            <button onclick="toggleFullscreen()" class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50">
                <i data-lucide="maximize" class="h-4 w-4"></i>
                Fullscreen
            </button>
        </div>
    </div>

    <!-- Ringkasan Kehadiran -->
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Total Siswa</p>
            <p id="counter-total" class="mt-1 text-2xl font-bold text-slate-900">{{ $rekap['total'] }}</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-4 shadow-sm">
            <p class="text-xs font-medium text-emerald-700">Hadir</p>
            <p id="counter-hadir" class="mt-1 text-2xl font-bold text-emerald-700">{{ $rekap['hadir'] }}</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50/50 p-4 shadow-sm">
            <p class="text-xs font-medium text-amber-700">Terlambat</p>
            <p id="counter-terlambat" class="mt-1 text-2xl font-bold text-amber-700">{{ $rekap['terlambat'] }}</p>
        </div>
        <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-4 shadow-sm">
            <p class="text-xs font-medium text-blue-700">Izin / Sakit</p>
            <p id="counter-izin" class="mt-1 text-2xl font-bold text-blue-700">{{ $rekap['izin'] }}</p>
        </div>
        <div class="col-span-2 sm:col-span-1 rounded-xl border border-rose-200 bg-rose-50/50 p-4 shadow-sm">
            <p class="text-xs font-medium text-rose-700">Belum Presensi</p>
            <p id="counter-belum" class="mt-1 text-2xl font-bold text-rose-700">{{ $rekap['belum'] }}</p>
        </div>
    </div>

    <!-- Konten Utama: Layar QR Code Dinamis & Daftar Live -->
    <div class="grid gap-6 lg:grid-cols-12">
        <!-- Panel QR Code -->
        <div class="lg:col-span-5 flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white p-6 shadow-sm text-center relative overflow-hidden">
            
            <!-- Banner Spesifik Mata Pelajaran -->
            <div class="mb-4 w-full rounded-xl bg-teal-50 border border-teal-200 p-3 text-center">
                <span class="text-[11px] font-bold uppercase tracking-wider text-teal-600 block">Presensi Eksklusif</span>
                <p class="text-base font-extrabold text-teal-950 mt-0.5">{{ $mapel?->nama_mapel }}</p>
                <p class="text-xs text-teal-700 mt-1 font-medium">
                    Berlaku: <span class="font-mono font-bold">{{ $sesi->waktu_mulai ? $sesi->waktu_mulai->format('H:i') : '-' }} - {{ $sesi->waktu_selesai ? $sesi->waktu_selesai->format('H:i') : '-' }} WIB</span>
                </p>
            </div>

            <!-- Countdown Timer & Status Badge -->
            <div class="mb-4 flex flex-col items-center gap-1.5">
                <div id="status-chip" class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $isAktif ? 'bg-emerald-100 text-emerald-800 animate-pulse' : ($isKedaluwarsa ? 'bg-rose-100 text-rose-800' : 'bg-slate-200 text-slate-700') }}">
                    <span id="status-dot" class="h-2 w-2 rounded-full {{ $isAktif ? 'bg-emerald-600' : ($isKedaluwarsa ? 'bg-rose-600' : 'bg-slate-500') }}"></span>
                    <span id="status-text">{{ $isAktif ? 'QR Code Aktif - Siap Discan' : ($isKedaluwarsa ? 'QR Code Kedaluwarsa' : 'Sesi Telah Ditutup') }}</span>
                </div>

                <!-- Timer Display -->
                <div id="timer-box" class="flex items-center gap-2 font-mono text-xl font-extrabold {{ $isAktif ? 'text-teal-700' : 'text-slate-400' }}">
                    <i data-lucide="clock-3" class="h-5 w-5"></i>
                    <span>Sisa Waktu: </span>
                    <span id="timer-countdown">--:--</span>
                </div>
            </div>

            <!-- SVG QR Code Container with Overlay on Expire -->
            <div class="relative w-full flex items-center justify-center my-2">
                <div id="qr-wrapper" class="rounded-2xl border-4 border-slate-900 bg-white p-4 shadow-xl transition duration-300 {{ $isKedaluwarsa ? 'filter blur-sm select-none pointer-events-none' : '' }}">
                    {!! $qrSvg !!}
                </div>

                <!-- Expired Overlay -->
                <div id="expired-overlay" class="absolute inset-0 flex flex-col items-center justify-center p-6 text-center bg-slate-900/60 backdrop-blur-xs rounded-2xl {{ $isKedaluwarsa ? 'flex' : 'hidden' }}">
                    <div class="grid h-14 w-14 place-items-center rounded-full bg-rose-500 text-white shadow-lg mb-3">
                        <i data-lucide="circle-x" class="h-8 w-8"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-white">QR Code Telah Kedaluwarsa</h3>
                    <p class="text-xs text-rose-100 mt-1 max-w-xs">
                        Waktu hidup QR telah berakhir. Foto atau simpanan QR siswa tidak dapat digunakan lagi.
                    </p>
                    <button type="button" onclick="bukaModalPerpanjang()" class="mt-4 rounded-lg bg-teal-500 px-4 py-2 text-xs font-bold text-white hover:bg-teal-600 shadow transition">
                        Perpanjang Waktu QR
                    </button>
                </div>
            </div>

            <div class="mt-3">
                <p class="text-xs font-medium text-slate-400">Kode Sesi Manual:</p>
                <div class="mt-1 inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1.5 font-mono text-sm font-bold text-slate-800">
                    <span id="manual-code-text">{{ $sesi->kode_unik ?: $sesi->kode_qr }}</span>
                </div>
            </div>

            <p class="mt-3 text-[11px] text-slate-500 max-w-xs leading-relaxed">
                * Siswa harus berada di radius sekolah saat memindai kode ini. Kode ini tidak berlaku untuk mata pelajaran lain.
            </p>
        </div>

        <!-- Panel Daftar Siswa Live -->
        <div class="lg:col-span-7 flex flex-col rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 flex items-center justify-between">
                <div>
                    <h2 class="font-bold text-slate-900">Kehadiran Siswa Kelas {{ $sesi->jadwalPelajaran?->kelas?->nama_kelas }}</h2>
                    <p class="text-xs text-slate-500">Daftar diperbarui secara otomatis setiap 3 detik.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="live-indicator" class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-teal-500"></span>
                    </span>
                    <span class="text-xs font-semibold text-slate-500">Live</span>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto max-h-[500px]">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="sticky top-0 bg-slate-50 text-xs font-semibold uppercase text-slate-500 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-2.5">NIS</th>
                            <th class="px-4 py-2.5">Nama Siswa</th>
                            <th class="px-4 py-2.5">Status</th>
                            <th class="px-4 py-2.5 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="tabel-murid-body" class="divide-y divide-slate-100">
                        @foreach ($daftarMurid as $m)
                            @php
                                $p = $presensiList->get($m->id);
                                $status = $p ? $p->status : 'belum';
                            @endphp
                            <tr id="row-murid-{{ $m->id }}" class="hover:bg-slate-50/60">
                                <td class="px-4 py-2.5 font-mono text-xs text-slate-600">{{ $m->nis }}</td>
                                <td class="px-4 py-2.5 font-medium text-slate-900">{{ $m->pengguna?->nama }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="status-badge inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ \App\Support\PresensiFormatter::badgeStatusClass($status) }}">
                                        {{ \App\Support\PresensiFormatter::labelStatus($status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono text-xs text-slate-500">
                                    {{ $p?->waktu_presensi ? $p->waktu_presensi->format('H:i:s') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Dialog: Perpanjang Waktu QR -->
    <dialog id="modal-perpanjang" onclick="if (event.target === this) this.close()" class="rounded-2xl border border-slate-200 p-0 shadow-2xl backdrop:bg-slate-900/50 max-w-md w-full m-auto text-left">
        <div class="p-6">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Perpanjang Waktu Hidup QR</h3>
                <button type="button" onclick="document.getElementById('modal-perpanjang').close()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="h-5 w-5"></i>
                </button>
            </div>

            <form method="post" action="{{ route('admin.sesi-presensi.perpanjang', $sesi) }}" class="mt-4 space-y-4">
                @csrf
                <p class="text-xs text-slate-600">
                    Pilih durasi tambahan waktu agar siswa yang belum sempat scan dapat menyelesaikan presensi:
                </p>

                <div class="grid grid-cols-4 gap-2">
                    <label class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-teal-400 hover:bg-teal-50/50 cursor-pointer text-xs font-bold transition has-checked:border-teal-600 has-checked:bg-teal-50 has-checked:text-teal-800">
                        <input type="radio" name="menit_tambahan" value="5" class="sr-only">
                        +5 Menit
                    </label>
                    <label class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-teal-400 hover:bg-teal-50/50 cursor-pointer text-xs font-bold transition has-checked:border-teal-600 has-checked:bg-teal-50 has-checked:text-teal-800">
                        <input type="radio" name="menit_tambahan" value="10" checked class="sr-only">
                        +10 Menit
                    </label>
                    <label class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-teal-400 hover:bg-teal-50/50 cursor-pointer text-xs font-bold transition has-checked:border-teal-600 has-checked:bg-teal-50 has-checked:text-teal-800">
                        <input type="radio" name="menit_tambahan" value="15" class="sr-only">
                        +15 Menit
                    </label>
                    <label class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-200 hover:border-teal-400 hover:bg-teal-50/50 cursor-pointer text-xs font-bold transition has-checked:border-teal-600 has-checked:bg-teal-50 has-checked:text-teal-800">
                        <input type="radio" name="menit_tambahan" value="30" class="sr-only">
                        +30 Menit
                    </label>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Atau Tentukan Jam Selesai Baru (24 Jam)</label>
                    <div class="relative">
                        <input type="text"
                               name="jam_selesai_baru"
                               id="input-jam-selesai-baru"
                               pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"
                               placeholder="Contoh: 10:30 (Format 24 Jam)"
                               autocomplete="off"
                               class="timepicker-24 font-mono w-full rounded-lg border border-slate-300 pl-8 pr-3 py-2 text-xs focus:border-teal-500 focus:outline-none">
                        <i data-lucide="clock-3" class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400 pointer-events-none"></i>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Biarkan kosong jika memilih salah satu durasi di atas.</p>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modal-perpanjang').close()" class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2 text-xs font-bold text-white hover:bg-teal-700 shadow transition">
                        Simpan Perpanjangan
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    @push('head')
    <script>
        function bukaModalPerpanjang() {
            const modal = document.getElementById('modal-perpanjang');
            if (modal) {
                modal.showModal();
                if (window.initTimepickers) window.initTimepickers(modal);
                if (window.refreshIcons) window.refreshIcons();
            }
        }

        function toggleFullscreen() {
            const doc = document;
            const docEl = document.documentElement;

            const fullscreenElement = doc.fullscreenElement || doc.webkitFullscreenElement || doc.mozFullScreenElement || doc.msFullscreenElement;
            const requestFs = docEl.requestFullscreen || docEl.webkitRequestFullscreen || docEl.mozRequestFullScreen || docEl.msRequestFullscreen;
            const exitFs = doc.exitFullscreen || doc.webkitExitFullscreen || doc.mozCancelFullScreen || doc.msExitFullscreen;

            if (!fullscreenElement) {
                if (typeof requestFs === 'function') {
                    requestFs.call(docEl).catch(err => console.warn('Gagal fullscreen:', err));
                } else if (window.presensiToast) {
                    window.presensiToast('Layar penuh tidak didukung di perangkat/browser ini.', 'info');
                }
            } else {
                if (typeof exitFs === 'function') {
                    exitFs.call(doc);
                }
            }
        }

        let sisaDetik = {{ (int) $sisaDetik }};
        let isExpired = {{ $isKedaluwarsa ? 'true' : 'false' }};
        let statusSesi = "{{ $sesi->status }}";

        function updateTimerDisplay() {
            const timerEl = document.getElementById('timer-countdown');
            const timerBox = document.getElementById('timer-box');
            const qrWrapper = document.getElementById('qr-wrapper');
            const overlay = document.getElementById('expired-overlay');
            const statusChip = document.getElementById('status-chip');
            const statusDot = document.getElementById('status-dot');
            const statusText = document.getElementById('status-text');

            if (statusSesi === 'ditutup') {
                timerEl.textContent = 'Ditutup';
                timerBox.className = 'flex items-center gap-2 font-mono text-xl font-extrabold text-slate-400';
                statusChip.className = 'inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold bg-slate-200 text-slate-700';
                statusDot.className = 'h-2 w-2 rounded-full bg-slate-500';
                statusText.textContent = 'Sesi Telah Ditutup';
                return;
            }

            if (sisaDetik <= 0 || isExpired) {
                timerEl.textContent = '00:00 (Habis)';
                timerBox.className = 'flex items-center gap-2 font-mono text-xl font-extrabold text-rose-600';
                statusChip.className = 'inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold bg-rose-100 text-rose-800';
                statusDot.className = 'h-2 w-2 rounded-full bg-rose-600';
                statusText.textContent = 'QR Code Kedaluwarsa';
                qrWrapper.className = 'rounded-2xl border-4 border-slate-900 bg-white p-4 shadow-xl transition duration-300 filter blur-sm select-none pointer-events-none';
                overlay.className = 'absolute inset-0 flex flex-col items-center justify-center p-6 text-center bg-slate-900/60 backdrop-blur-xs rounded-2xl';
                return;
            }

            const m = Math.floor(sisaDetik / 60);
            const s = sisaDetik % 60;
            timerEl.textContent = `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`;

            if (sisaDetik <= 60) {
                timerBox.className = 'flex items-center gap-2 font-mono text-xl font-extrabold text-rose-600 animate-pulse';
            } else if (sisaDetik <= 300) {
                timerBox.className = 'flex items-center gap-2 font-mono text-xl font-extrabold text-amber-600';
            } else {
                timerBox.className = 'flex items-center gap-2 font-mono text-xl font-extrabold text-teal-700';
            }

            qrWrapper.className = 'rounded-2xl border-4 border-slate-900 bg-white p-4 shadow-xl transition duration-300';
            overlay.className = 'absolute inset-0 flex-col items-center justify-center p-6 text-center bg-slate-900/60 backdrop-blur-xs rounded-2xl hidden';
        }

        // Jalankan countdown lokal per detik
        setInterval(() => {
            if (sisaDetik > 0 && statusSesi === 'aktif') {
                sisaDetik--;
                if (sisaDetik === 0) {
                    isExpired = true;
                }
                updateTimerDisplay();
            }
        }, 1000);

        updateTimerDisplay();

        // Live Polling dengan perlindungan runaway & tab background throttling
        const liveUrl = "{{ route('admin.sesi-presensi.live', $sesi) }}";
        let livePollingTimer = null;
        let isFetchingLive = false;

        async function pollPresensi() {
            if (statusSesi === 'ditutup') {
                return;
            }

            if (document.hidden) {
                livePollingTimer = setTimeout(pollPresensi, 10000);
                return;
            }

            if (!isFetchingLive) {
                isFetchingLive = true;
                try {
                    const res = await fetch(liveUrl, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();

                        if (data.status_sesi) {
                            statusSesi = data.status_sesi;
                        }
                        if (typeof data.sisa_detik === 'number') {
                            sisaDetik = data.sisa_detik;
                        }
                        if (typeof data.is_expired === 'boolean') {
                            isExpired = data.is_expired;
                        }
                        if (data.kode_unik) {
                            const manualEl = document.getElementById('manual-code-text');
                            if (manualEl) manualEl.textContent = data.kode_unik;
                        }
                        if (data.qr_svg) {
                            const qrWrapper = document.getElementById('qr-wrapper');
                            if (qrWrapper) qrWrapper.innerHTML = data.qr_svg;
                        }
                        updateTimerDisplay();

                        if (data.status_sesi === 'ditutup') {
                            return; // Stop polling jika sesi ditutup
                        }

                        // Update rekap counter
                        if (data.rekap) {
                            document.getElementById('counter-total').textContent = data.rekap.total;
                            document.getElementById('counter-hadir').textContent = data.rekap.hadir;
                            document.getElementById('counter-terlambat').textContent = data.rekap.terlambat;
                            document.getElementById('counter-izin').textContent = data.rekap.izin;
                            document.getElementById('counter-belum').textContent = data.rekap.belum;
                        }

                        // Update rows
                        if (data.murid && Array.isArray(data.murid)) {
                            data.murid.forEach(item => {
                                const row = document.getElementById('row-murid-' + item.id_murid);
                                if (row) {
                                    const badge = row.querySelector('.status-badge');
                                    const waktuCell = row.cells[3];
                                    if (badge) {
                                        badge.className = 'status-badge inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ';
                                        if (item.status === 'hadir') {
                                            badge.className += 'bg-emerald-50 text-emerald-800 ring-emerald-600/20';
                                            badge.textContent = 'Hadir';
                                        } else if (item.status === 'terlambat') {
                                            badge.className += 'bg-amber-50 text-amber-800 ring-amber-600/20';
                                            badge.textContent = 'Terlambat';
                                        } else if (item.status === 'izin') {
                                            badge.className += 'bg-sky-50 text-sky-800 ring-sky-600/20';
                                            badge.textContent = 'Izin';
                                        } else if (item.status === 'sakit') {
                                            badge.className += 'bg-blue-50 text-blue-800 ring-blue-600/20';
                                            badge.textContent = 'Sakit';
                                        } else if (item.status === 'alfa' || item.status === 'alpa') {
                                            badge.className += 'bg-rose-50 text-rose-800 ring-rose-600/20';
                                            badge.textContent = 'Alfa';
                                        } else {
                                            badge.className += 'bg-slate-100 text-slate-500 ring-slate-200';
                                            badge.textContent = 'Belum Hadir';
                                        }
                                    }
                                    if (waktuCell) {
                                        waktuCell.textContent = item.waktu ? item.waktu : '-';
                                    }
                                }
                            });
                        }
                    }
                } catch (e) {
                    console.error('Polling presensi error:', e);
                } finally {
                    isFetchingLive = false;
                }
            }

            livePollingTimer = setTimeout(pollPresensi, 3000);
        }

        livePollingTimer = setTimeout(pollPresensi, 3000);

        window.addEventListener('beforeunload', () => {
            if (livePollingTimer) clearTimeout(livePollingTimer);
        });
    </script>
    @endpush
</x-admin.layout>
