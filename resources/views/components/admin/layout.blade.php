<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dasbor Presensi' }} | Presensi Murid</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/brand/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        window.__konfirmasiDefault = {
            simpan: { title: 'Simpan perubahan?', text: 'Pastikan data yang Anda isi sudah sesuai sebelum disimpan.', button: 'Ya, Simpan', variant: 'primary' },
            hapus: { title: 'Hapus data ini?', text: 'Data yang dihapus tidak dapat dipulihkan kembali.', button: 'Ya, Hapus', variant: 'danger' }
        };
    </script>
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F3F7F6] text-slate-900 antialiased">
    @php
        $peranAktif = auth()->user()->peran ?? null;
        $penggunaAdmin = $peranAktif === 'admin';
        $penggunaGuru = $peranAktif === 'guru';

        $navigasiUtama = $penggunaAdmin ? [
            ['admin.dashboard', 'Dasbor', 'layout-dashboard'],
            ['admin.sesi-presensi.index', 'Sesi Presensi QR', 'qr-code'],
            ['admin.presensi.index', 'Data Presensi', 'clipboard-check'],
            ['admin.pengajuan-izin.index', 'Persetujuan Izin', 'file-check-2'],
            ['admin.laporan.index', 'Laporan Rekap', 'chart-column-big'],
        ] : [
            ['admin.dashboard', 'Dasbor Guru', 'layout-dashboard'],
            ['admin.sesi-presensi.index', 'Buka Sesi QR', 'qr-code'],
            ['admin.presensi.index', 'Presensi Kelas Saya', 'clipboard-check'],
            ['admin.pengajuan-izin.index', 'Persetujuan Izin', 'file-check-2'],
            ['admin.laporan.index', 'Laporan Presensi', 'chart-column-big'],
        ];

        $navigasiMaster = $penggunaAdmin ? [
            ['admin.murid.index', 'Data Murid', 'graduation-cap'],
            ['admin.guru.index', 'Data Guru', 'users-round'],
            ['admin.kelas.index', 'Data Kelas', 'school'],
            ['admin.mata-pelajaran.index', 'Mata Pelajaran', 'book-open'],
            ['admin.jadwal-pelajaran.index', 'Jadwal Pelajaran', 'calendar-clock'],
            ['admin.lokasi-sekolah.index', 'Lokasi Sekolah', 'map-pin'],
            ['admin.hari-libur.index', 'Hari Libur', 'calendar-days'],
            ['admin.pengguna-admin.index', 'Akun Admin', 'shield-check'],
        ] : [];

        $semuaNavigasi = [...$navigasiUtama, ...$navigasiMaster];
    @endphp

    <div class="min-h-screen">
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 border-r border-slate-900/10 bg-slate-950 text-white shadow-2xl shadow-slate-900/20 lg:flex lg:flex-col">
            <div class="border-b border-white/10 px-5 py-5">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/brand/logo.png') }}" alt="Logo Presensi Murid" class="h-11 w-11 rounded-md bg-white object-contain p-1 shadow-sm">
                    <div class="min-w-0">
                        <p class="truncate text-lg font-semibold">Presensi Murid</p>
                        <p class="truncate text-sm text-slate-400">{{ $penggunaAdmin ? 'Panel Administrasi' : 'Portal Guru' }}</p>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-4">
                <div class="mb-5 rounded-md border border-white/10 bg-white/[0.06] p-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('assets/brand/avatar-placeholder.png') }}" alt="Avatar pengguna" class="h-10 w-10 rounded-full bg-white object-cover ring-2 ring-white/10">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium">{{ auth()->user()->nama ?? '' }}</p>
                            <p class="truncate text-xs text-teal-400 font-semibold">{{ \App\Support\PresensiFormatter::labelPeran(auth()->user()->peran ?? '') }}</p>
                        </div>
                    </div>
                </div>

                <nav class="space-y-6 text-sm">
                    <div>
                        <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Operasional</p>
                        <div class="space-y-1">
                            @foreach ($navigasiUtama as [$route, $label, $icon])
                                @php $aktif = request()->routeIs($route); @endphp
                                <a href="{{ route($route) }}" class="group flex items-center gap-3 rounded-md px-3 py-2.5 transition {{ $aktif ? 'bg-teal-400 text-slate-950 shadow-sm shadow-teal-950/20 font-bold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                                    <i data-lucide="{{ $icon }}" class="{{ $aktif ? 'text-slate-950' : 'text-slate-500 group-hover:text-teal-200' }}"></i>
                                    <span class="font-medium">{{ $label }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    @if ($navigasiMaster !== [])
                        <div>
                            <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Master Data</p>
                            <div class="space-y-1">
                                @foreach ($navigasiMaster as [$route, $label, $icon])
                                    @php $aktif = request()->routeIs($route); @endphp
                                    <a href="{{ route($route) }}" class="group flex items-center gap-3 rounded-md px-3 py-2.5 transition {{ $aktif ? 'bg-sky-300 text-slate-950 shadow-sm shadow-sky-950/20 font-bold' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                                        <i data-lucide="{{ $icon }}" class="{{ $aktif ? 'text-slate-950' : 'text-slate-500 group-hover:text-sky-200' }}"></i>
                                        <span class="font-medium">{{ $label }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </nav>
            </div>

            <div class="border-t border-white/10 p-4">
                <form method="post" action="{{ route('admin.keluar') }}" data-confirm data-confirm-title="Keluar dari dasbor?" data-confirm-text="Sesi akan diakhiri dari perangkat ini." data-confirm-button="Keluar" data-confirm-variant="warning">
                    @csrf
                    <button class="flex w-full items-center justify-center gap-2 rounded-md border border-white/10 bg-white/[0.06] px-3 py-2.5 text-sm font-medium text-slate-200 transition hover:bg-red-500/15 hover:text-red-100">
                        <i data-lucide="log-out"></i>
                        Keluar
                    </button>
                </form>
            </div>
        </aside>

        <main class="lg:pl-72">
            <header class="admin-mobile-header sticky top-0 z-20 border-b border-slate-200 bg-white/90 px-4 py-3 shadow-sm shadow-slate-900/5 backdrop-blur lg:hidden">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('assets/brand/logo.png') }}" alt="Logo Presensi Murid" class="h-10 w-10 rounded-md bg-white object-contain p-1 shadow-sm">
                        <div>
                            <p class="font-semibold">Presensi Murid</p>
                            <p class="text-xs text-slate-500">{{ auth()->user()->nama ?? '' }} ({{ \App\Support\PresensiFormatter::labelPeran(auth()->user()->peran ?? '') }})</p>
                        </div>
                    </div>
                    <form method="post" action="{{ route('admin.keluar') }}" data-confirm data-confirm-title="Keluar dari dasbor?" data-confirm-text="Sesi akan diakhiri dari perangkat ini." data-confirm-button="Keluar" data-confirm-variant="warning">
                        @csrf
                        <button class="grid h-10 w-10 place-items-center rounded-md border border-slate-200 text-slate-600">
                            <i data-lucide="log-out"></i>
                        </button>
                    </form>
                </div>
                <div class="admin-mobile-nav mt-3 flex gap-2 overflow-x-auto pb-1 text-sm">
                    @foreach ($semuaNavigasi as [$route, $label, $icon])
                        <a href="{{ route($route) }}" class="flex shrink-0 items-center gap-2 rounded-md border px-3 py-2 {{ request()->routeIs($route) ? 'border-slate-950 bg-slate-950 text-white font-semibold' : 'border-slate-200 bg-white text-slate-700' }}">
                            <i data-lucide="{{ $icon }}"></i>
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </header>

            <div class="px-4 py-6 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-6 flex items-center justify-between gap-3 rounded-md border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-900 shadow-sm">
                        <div class="flex items-center gap-2">
                            <i data-lucide="check-circle" class="h-4 w-4 text-teal-600"></i>
                            <span>{{ session('status') }}</span>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-900 shadow-sm">
                        <p class="font-semibold mb-1">Terjadi kesalahan pada input data:</p>
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
