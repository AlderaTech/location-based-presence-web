<x-admin.layout title="Dasbor Presensi">
    @php
        $labelStatus = [
            'hadir' => 'Tepat Waktu',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            'alpha' => 'Alpha',
        ];

        $warnaStatus = [
            'hadir' => '#0F766E',
            'terlambat' => '#D97706',
            'izin' => '#2563EB',
            'sakit' => '#0284C7',
            'cuti' => '#7C3AED',
            'alpha' => '#DC2626',
        ];

        $statusBulanan = collect($labelStatus)->map(fn ($label, $key) => [
            'label' => $label,
            'nilai' => (int) ($bulanan[$key] ?? 0),
            'warna' => $warnaStatus[$key],
        ])->values();

        $totalHariIni = array_sum($ringkasan);
        $totalBulanan = $statusBulanan->sum('nilai');
        $kartuRingkasan = [
            ['label' => 'Tepat Waktu', 'nilai' => $ringkasan['hadir'], 'warna' => 'teal', 'icon' => 'circle-check-big', 'deskripsi' => 'Presensi masuk sesuai jadwal'],
            ['label' => 'Terlambat', 'nilai' => $ringkasan['terlambat'], 'warna' => 'amber', 'icon' => 'clock-3', 'deskripsi' => 'Melewati batas toleransi'],
            ['label' => 'Izin/Sakit/Cuti', 'nilai' => $ringkasan['izin'], 'warna' => 'blue', 'icon' => 'file-check-2', 'deskripsi' => 'Keterangan disetujui/tercatat'],
            ['label' => 'Alpha', 'nilai' => $ringkasan['alpha'], 'warna' => 'red', 'icon' => 'triangle-alert', 'deskripsi' => 'Belum memiliki catatan hari ini'],
        ];

        $pengajuanChart = [
            'labels' => ['Menunggu', 'Disetujui', 'Ditolak'],
            'data' => [
                (int) ($pengajuanRingkasan['menunggu'] ?? 0),
                (int) ($pengajuanRingkasan['disetujui'] ?? 0),
                (int) ($pengajuanRingkasan['ditolak'] ?? 0),
            ],
        ];

        $dataDashboard = [
            'trenHarian' => $trenHarian,
            'statusBulanan' => [
                'labels' => $statusBulanan->pluck('label'),
                'data' => $statusBulanan->pluck('nilai'),
                'colors' => $statusBulanan->pluck('warna'),
            ],
            'pengajuan' => $pengajuanChart,
        ];
    @endphp

    <script>
        window.presensiDashboard = {{ Illuminate\Support\Js::from($dataDashboard) }};
    </script>

    <div class="space-y-6">
        <section class="overflow-hidden rounded-md border border-slate-900 bg-slate-950 text-white shadow-xl shadow-slate-900/15">
            <div class="grid lg:grid-cols-[minmax(0,1fr)_320px]">
                <div class="p-5 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <p class="text-sm font-medium text-teal-200">{{ now()->translatedFormat('l, d F Y') }}</p>
                            <h1 class="mt-2 text-2xl font-semibold tracking-tight sm:text-3xl">Dasbor Presensi</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Pantau kehadiran guru, keterlambatan, pengajuan izin, dan tindak lanjut harian dari satu layar.</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.presensi.index') }}" class="rounded-md border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white hover:bg-white/15">Data Presensi</a>
                            <a href="{{ route('admin.laporan.index') }}" class="rounded-md bg-teal-300 px-4 py-2 text-sm font-semibold text-slate-950 shadow-sm hover:bg-teal-200">Buka Laporan</a>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-md border border-white/10 bg-white/[0.07] p-4">
                            <p class="text-sm text-slate-300">Guru Aktif</p>
                            <p class="mt-2 text-3xl font-semibold">{{ $jumlahGuru }}</p>
                        </div>
                        <div class="rounded-md border border-teal-300/20 bg-teal-300/10 p-4">
                            <p class="text-sm text-teal-100">Tingkat Kehadiran</p>
                            <p class="mt-2 text-3xl font-semibold text-teal-100">{{ $tingkatKehadiran }}%</p>
                        </div>
                        <div class="rounded-md border border-amber-300/20 bg-amber-300/10 p-4">
                            <p class="text-sm text-amber-100">Pengajuan Menunggu</p>
                            <p class="mt-2 text-3xl font-semibold text-amber-100">{{ $pengajuanMenunggu }}</p>
                        </div>
                    </div>
                </div>

                <div class="border-t border-white/10 bg-white/[0.06] p-5 lg:border-l lg:border-t-0">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-slate-300">Komposisi hari ini</p>
                        <span class="rounded-full bg-white/10 px-2.5 py-1 text-xs font-medium text-slate-200">{{ $totalHariIni }} catatan</span>
                    </div>
                    <div class="mt-5 space-y-4">
                        @foreach ($kartuRingkasan as $item)
                            @php
                                $persen = $totalHariIni > 0 ? min(100, round(($item['nilai'] / $totalHariIni) * 100)) : 0;
                                $bar = [
                                    'teal' => 'bg-teal-300',
                                    'amber' => 'bg-amber-300',
                                    'blue' => 'bg-sky-300',
                                    'red' => 'bg-red-300',
                                ][$item['warna']];
                            @endphp
                            <div>
                                <div class="mb-1 flex items-center justify-between gap-3 text-sm">
                                    <span class="text-slate-300">{{ $item['label'] }}</span>
                                    <span class="font-semibold">{{ $item['nilai'] }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-white/10">
                                    <div class="h-2 rounded-full {{ $bar }}" style="width: {{ $persen }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kartuRingkasan as $item)
                @php
                    $panel = [
                        'teal' => ['bg' => 'bg-teal-50', 'border' => 'border-teal-200', 'text' => 'text-teal-800', 'icon' => 'bg-teal-700 text-white', 'bar' => 'bg-teal-600'],
                        'amber' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'icon' => 'bg-amber-600 text-white', 'bar' => 'bg-amber-500'],
                        'blue' => ['bg' => 'bg-sky-50', 'border' => 'border-sky-200', 'text' => 'text-sky-800', 'icon' => 'bg-sky-700 text-white', 'bar' => 'bg-sky-600'],
                        'red' => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'text' => 'text-rose-800', 'icon' => 'bg-rose-700 text-white', 'bar' => 'bg-rose-600'],
                    ][$item['warna']];
                    $persenKartu = $totalHariIni > 0 ? min(100, round(($item['nilai'] / $totalHariIni) * 100)) : 0;
                @endphp
                <div class="rounded-md border {{ $panel['border'] }} {{ $panel['bg'] }} p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm {{ $panel['text'] }}">{{ $item['label'] }}</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $item['nilai'] }}</p>
                            <p class="mt-1 text-xs text-slate-600">{{ $item['deskripsi'] }}</p>
                        </div>
                        <div class="grid h-10 w-10 place-items-center rounded-md {{ $panel['icon'] }}">
                            <i data-lucide="{{ $item['icon'] }}"></i>
                        </div>
                    </div>
                    <div class="mt-4 h-1.5 rounded-full bg-white/70">
                        <div class="h-1.5 rounded-full {{ $panel['bar'] }}" style="width: {{ $persenKartu }}%"></div>
                    </div>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.75fr)]">
            <div class="rounded-md border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 sm:p-5">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-semibold">Tren Kehadiran Bulan Ini</h2>
                        <p class="text-sm text-slate-500">Pergerakan presensi harian berdasarkan status utama.</p>
                    </div>
                    <span class="text-sm text-slate-500">{{ now()->translatedFormat('F Y') }}</span>
                </div>
                <div class="mt-5 h-80">
                    <canvas id="grafikTrenPresensi" aria-label="Grafik tren presensi bulan ini"></canvas>
                </div>
            </div>

            <div class="grid gap-6">
                <div class="rounded-md border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 sm:p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h2 class="font-semibold">Status Bulanan</h2>
                            <p class="text-sm text-slate-500">{{ $totalBulanan }} catatan presensi</p>
                        </div>
                    </div>
                    <div class="mt-4 h-60">
                        <canvas id="grafikStatusBulanan" aria-label="Grafik distribusi status bulan ini"></canvas>
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5 sm:p-5">
                    <h2 class="font-semibold">Pengajuan Izin</h2>
                    <div class="mt-4 h-52">
                        <canvas id="grafikPengajuanIzin" aria-label="Grafik pengajuan izin"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(360px,0.85fr)]">
            <div class="rounded-md border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
                    <div>
                        <h2 class="font-semibold">Presensi Terbaru</h2>
                        <p class="text-sm text-slate-500">Aktivitas masuk, pulang, izin, dan koreksi terbaru.</p>
                    </div>
                    <a href="{{ route('admin.presensi.index') }}" class="text-sm font-medium text-teal-700 hover:text-teal-900">Lihat semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="px-4 py-3">Guru</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Masuk</th>
                                <th class="px-4 py-3">Pulang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($presensiTerbaru as $item)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-600">{{ $item->tanggal_presensi?->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ $item->pengguna?->nama }}</div>
                                        <div class="text-xs text-slate-500">{{ $item->jadwalKerja?->nama ?: 'Tanpa jadwal' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{{ $labelStatus[$item->status] ?? $item->status }}</span>
                                    </td>
                                    <td class="px-4 py-3">{{ $item->jam_masuk_pada?->format('H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $item->jam_pulang_pada?->format('H:i') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada data presensi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="grid gap-6">
                <div class="rounded-md border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold">Belum Presensi Hari Ini</h2>
                        <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700">{{ $ringkasan['alpha'] }} guru</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($guruBelumPresensi as $guru)
                            <div class="flex items-center gap-3 rounded-md border border-slate-200 p-3">
                                <div class="grid h-9 w-9 place-items-center rounded-md bg-slate-100 text-sm font-semibold text-slate-700">{{ str($guru->pengguna?->nama ?? '?')->substr(0, 1)->upper() }}</div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium">{{ $guru->pengguna?->nama }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $guru->nip ?: $guru->pengguna?->nomor_pegawai ?: '-' }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-md bg-teal-50 px-3 py-4 text-sm text-teal-800">Semua guru aktif sudah memiliki catatan presensi hari ini.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-md border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex items-center justify-between">
                        <h2 class="font-semibold">Izin Menunggu</h2>
                        <a href="{{ route('admin.pengajuan-izin.index', ['status' => 'menunggu']) }}" class="text-sm font-medium text-teal-700 hover:text-teal-900">Tinjau</a>
                    </div>
                    <div class="mt-4 space-y-3">
                        @forelse ($pengajuanMenungguTerbaru as $pengajuan)
                            <div class="rounded-md border border-slate-200 p-3">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium">{{ $pengajuan->pengguna?->nama }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ ucfirst($pengajuan->jenis) }} • {{ $pengajuan->tanggal_mulai->format('Y-m-d') }}</p>
                                    </div>
                                    <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">Menunggu</span>
                                </div>
                            </div>
                        @empty
                            <p class="rounded-md bg-slate-50 px-3 py-4 text-sm text-slate-500">Tidak ada pengajuan izin yang menunggu.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-admin.layout>
