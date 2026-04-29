<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Presensi' }}</title>
    <script>
        window.presensiNotifikasi = @json([
            'sukses' => session('status'),
            'galat' => $errors->any() ? ($errors->count() > 1 ? 'Periksa kembali isian formulir. '.$errors->first() : $errors->first()) : null,
        ]);
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="min-h-screen">
        <aside class="fixed inset-y-0 left-0 hidden w-64 border-r border-slate-200 bg-white px-4 py-5 lg:block">
            <div class="mb-8">
                <p class="text-lg font-semibold">Presensi Guru</p>
                <p class="text-sm text-slate-500">{{ auth()->user()->nama ?? '' }}</p>
            </div>
            <nav class="space-y-1 text-sm">
                @php
                    $links = [
                        ['admin.dashboard', 'Dasbor'],
                        ['admin.guru.index', 'Data Guru'],
                        ['admin.lokasi-sekolah.index', 'Lokasi Sekolah'],
                        ['admin.jadwal-kerja.index', 'Jadwal Kerja'],
                        ['admin.hari-libur.index', 'Hari Libur'],
                        ['admin.presensi.index', 'Data Presensi'],
                        ['admin.pengajuan-izin.index', 'Persetujuan Izin'],
                        ['admin.laporan.index', 'Laporan'],
                        ['admin.pengguna-admin.index', 'Akun Admin'],
                    ];
                @endphp
                @foreach ($links as [$route, $label])
                    <a href="{{ route($route) }}" class="block rounded-md px-3 py-2 {{ request()->routeIs($route) ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">{{ $label }}</a>
                @endforeach
            </nav>
            <form method="post" action="{{ route('admin.keluar') }}" class="mt-8">
                @csrf
                <button class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">Keluar</button>
            </form>
        </aside>

        <main class="lg:pl-64">
            <header class="border-b border-slate-200 bg-white px-4 py-4 lg:hidden">
                <div class="flex items-center justify-between">
                    <span class="font-semibold">Presensi Guru</span>
                    <form method="post" action="{{ route('admin.keluar') }}">
                        @csrf
                        <button class="text-sm text-slate-600">Keluar</button>
                    </form>
                </div>
                <div class="mt-3 flex gap-2 overflow-x-auto text-sm">
                    @foreach ($links as [$route, $label])
                        <a href="{{ route($route) }}" class="shrink-0 rounded-md border border-slate-200 px-3 py-2">{{ $label }}</a>
                    @endforeach
                </div>
            </header>

            <section class="px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </section>
        </main>
    </div>
</body>
</html>
