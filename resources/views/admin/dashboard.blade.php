<x-admin.layout title="Dasbor Presensi Murid">
    @php
        $labelStatus = [
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alfa' => 'Alfa',
        ];

        $warnaStatus = [
            'hadir' => '#0F766E',
            'terlambat' => '#D97706',
            'izin' => '#2563EB',
            'sakit' => '#0284C7',
            'alfa' => '#DC2626',
        ];

        $kartuRingkasan = [
            ['label' => 'Hadir Tepat Waktu', 'nilai' => $ringkasan['hadir'], 'warna' => 'emerald', 'icon' => 'circle-check-big', 'deskripsi' => 'Presensi sebelum batas toleransi'],
            ['label' => 'Terlambat', 'nilai' => $ringkasan['terlambat'], 'warna' => 'amber', 'icon' => 'clock-3', 'deskripsi' => 'Presensi melewati batas toleransi'],
            ['label' => 'Izin / Sakit', 'nilai' => $ringkasan['izin'] + $ringkasan['sakit'], 'warna' => 'blue', 'icon' => 'file-check-2', 'deskripsi' => 'Disetujui dengan surat bukti'],
            ['label' => 'Alfa / Belum Hadir', 'nilai' => $ringkasan['alfa'], 'warna' => 'rose', 'icon' => 'triangle-alert', 'deskripsi' => 'Tanpa keterangan'],
        ];

        $dataDashboard = [
            'trenHarian' => $trenHarian,
        ];
    @endphp

    <script>
        window.presensiDashboard = {{ Illuminate\Support\Js::from($dataDashboard) }};
    </script>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                {{ $isGuru ? 'Dasbor Guru: '.auth()->user()->nama : 'Dasbor Presensi Murid' }}
            </h1>
            <p class="text-sm text-slate-500">
                {{ $isGuru ? 'Kelola sesi presensi QR kelas dan validasi kehadiran siswa yang Anda ampu.' : 'Statistik kehadiran murid sekolah, master data pelajaran, dan persetujuan izin.' }}
            </p>
        </div>

        @if ($isGuru)
            <a href="{{ route('admin.sesi-presensi.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm transition hover:bg-teal-700">
                <i data-lucide="qr-code" class="h-4 w-4"></i>
                Buka Sesi Presensi QR
            </a>
        @endif
    </div>

    <!-- Banner Khusus Guru jika ada Sesi Aktif -->
    @if ($isGuru && $sesiAktifGuru->isNotEmpty())
        <div class="mb-6 rounded-xl border border-teal-200 bg-teal-50/70 p-4 shadow-sm">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-teal-600"></span>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-teal-950">Anda memiliki sesi presensi QR yang sedang aktif!</p>
                        <p class="text-xs text-teal-700">
                            {{ $sesiAktifGuru->first()->jadwalPelajaran?->mataPelajaran?->nama_mapel }} - Kelas {{ $sesiAktifGuru->first()->jadwalPelajaran?->kelas?->nama_kelas }} (Kode: {{ $sesiAktifGuru->first()->kode_qr }})
                        </p>
                    </div>
                </div>
                <a href="{{ route('admin.sesi-presensi.show', $sesiAktifGuru->first()) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-600 px-4 py-2 text-xs font-bold text-white shadow hover:bg-teal-700">
                    <i data-lucide="external-link" class="h-4 w-4"></i>
                    Buka Layar QR
                </a>
            </div>
        </div>
    @endif

    <!-- Kartu Statistik Utama -->
    @if (! $isGuru)
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase text-slate-500">Total Murid</p>
                    <i data-lucide="graduation-cap" class="h-5 w-5 text-teal-600"></i>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $jumlahMurid }}</p>
                <a href="{{ route('admin.murid.index') }}" class="mt-2 block text-xs font-semibold text-teal-600 hover:underline">Kelola Murid &rarr;</a>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase text-slate-500">Total Guru</p>
                    <i data-lucide="users-round" class="h-5 w-5 text-sky-600"></i>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $jumlahGuru }}</p>
                <a href="{{ route('admin.guru.index') }}" class="mt-2 block text-xs font-semibold text-sky-600 hover:underline">Kelola Guru &rarr;</a>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase text-slate-500">Total Kelas</p>
                    <i data-lucide="school" class="h-5 w-5 text-indigo-600"></i>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $jumlahKelas }}</p>
                <a href="{{ route('admin.kelas.index') }}" class="mt-2 block text-xs font-semibold text-indigo-600 hover:underline">Kelola Kelas &rarr;</a>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase text-slate-500">Mata Pelajaran</p>
                    <i data-lucide="book-open" class="h-5 w-5 text-purple-600"></i>
                </div>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $jumlahMapel }}</p>
                <a href="{{ route('admin.mata-pelajaran.index') }}" class="mt-2 block text-xs font-semibold text-purple-600 hover:underline">Kelola Mapel &rarr;</a>
            </div>
        </div>
    @endif

    <!-- Presensi Hari Ini Cards -->
    <div class="mb-8">
        <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-slate-500">Ringkasan Presensi Hari Ini ({{ today()->format('d M Y') }})</h2>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach ($kartuRingkasan as $k)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-600">{{ $k['label'] }}</span>
                        <i data-lucide="{{ $k['icon'] }}" class="h-5 w-5 text-{{ $k['warna'] }}-600"></i>
                    </div>
                    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $k['nilai'] }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $k['deskripsi'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 2 Kolom: Jadwal/Izin & Presensi Terbaru -->
    <div class="grid gap-6 lg:grid-cols-12">
        <!-- Kolom Kiri -->
        <div class="lg:col-span-6 space-y-6">
            @if ($isGuru)
                <!-- Jadwal Mengajar Hari Ini -->
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-bold text-slate-900">Jadwal Mengajar Anda Hari Ini</h3>
                        <a href="{{ route('admin.sesi-presensi.index') }}" class="text-xs font-semibold text-teal-600 hover:underline">Semua Jadwal</a>
                    </div>
                    @if ($jadwalGuruHariIni->isEmpty())
                        <p class="text-xs text-slate-400 py-4 text-center">Tidak ada jadwal mengajar pada hari ini.</p>
                    @else
                        <div class="space-y-3">
                            @foreach ($jadwalGuruHariIni as $j)
                                <div class="flex items-center justify-between rounded-lg border border-slate-100 p-3 hover:bg-slate-50">
                                    <div>
                                        <p class="font-bold text-sm text-slate-900">{{ $j->mataPelajaran?->nama_mapel }}</p>
                                        <p class="text-xs font-semibold text-teal-700">Kelas {{ $j->kelas?->nama_kelas }} &bull; {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }} WIB</p>
                                    </div>
                                    <form method="post" action="{{ route('admin.sesi-presensi.store') }}">
                                        @csrf
                                        <input type="hidden" name="id_jadwal_pelajaran" value="{{ $j->id }}">
                                        <button type="submit" class="rounded-md bg-teal-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-700">
                                            Buka QR
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <!-- Pengajuan Izin Menunggu Review -->
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900">Pengajuan Izin / Sakit</h3>
                        <p class="text-xs text-slate-500">{{ $pengajuanMenunggu }} pengajuan membutuhkan verifikasi bukti.</p>
                    </div>
                    <a href="{{ route('admin.pengajuan-izin.index') }}" class="text-xs font-semibold text-teal-600 hover:underline">Lihat Semua</a>
                </div>

                @if ($pengajuanMenungguTerbaru->isEmpty())
                    <p class="text-xs text-slate-400 py-4 text-center">Tidak ada pengajuan izin yang menunggu peninjauan.</p>
                @else
                    <div class="divide-y divide-slate-100">
                        @foreach ($pengajuanMenungguTerbaru as $izin)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-sm text-slate-900">{{ $izin->pengguna?->nama }}</p>
                                    <p class="text-xs text-slate-500">Kelas {{ $izin->murid?->kelas?->nama_kelas }} &bull; {{ ucfirst($izin->jenis) }} ({{ $izin->tanggal_mulai->format('d/m') }} - {{ $izin->tanggal_selesai->format('d/m') }})</p>
                                </div>
                                <a href="{{ route('admin.pengajuan-izin.index') }}" class="rounded-md border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    Periksa Bukti
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Presensi Terbaru -->
        <div class="lg:col-span-6">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-bold text-slate-900">Catatan Presensi Terkini</h3>
                    <a href="{{ route('admin.presensi.index') }}" class="text-xs font-semibold text-teal-600 hover:underline">Lihat Semua</a>
                </div>

                @if ($presensiTerbaru->isEmpty())
                    <p class="text-xs text-slate-400 py-8 text-center">Belum ada catatan presensi hari ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="text-slate-400 border-b border-slate-100">
                                <tr>
                                    <th class="pb-2">Murid</th>
                                    <th class="pb-2">Kelas / Mapel</th>
                                    <th class="pb-2">Waktu</th>
                                    <th class="pb-2 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($presensiTerbaru as $p)
                                    <tr class="hover:bg-slate-50/60">
                                        <td class="py-2.5 font-bold text-slate-900">{{ $p->pengguna?->nama }}</td>
                                        <td class="py-2.5">
                                            <span class="font-semibold text-slate-800">{{ $p->murid?->kelas?->nama_kelas ?: '-' }}</span>
                                            <span class="block text-slate-400 text-[11px]">{{ $p->jadwalPelajaran?->mataPelajaran?->nama_mapel ?: '-' }}</span>
                                        </td>
                                        <td class="py-2.5 font-mono text-slate-500">{{ $p->waktu_presensi ? $p->waktu_presensi->format('H:i') : '-' }}</td>
                                        <td class="py-2.5 text-right">
                                            <x-badge-status :status="$p->status" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-admin.layout>
