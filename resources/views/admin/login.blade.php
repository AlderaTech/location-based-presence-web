<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Portal Presensi</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <script>
        window.presensiNotifikasi = @json([
            'sukses' => session('status'),
            'galat' => $errors->any() ? $errors->first() : null,
        ]);
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#eef4f2] text-slate-900 antialiased">
    <main class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_520px]">
        <section class="relative hidden overflow-hidden bg-slate-950 px-10 py-10 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-0 opacity-30" aria-hidden="true">
                <div class="h-full w-full bg-[linear-gradient(135deg,#0f766e_0%,#0f172a_42%,#1f2937_100%)]"></div>
                <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,.08)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.08)_1px,transparent_1px)] [background-size:34px_34px]"></div>
            </div>

            <div class="relative">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('assets/brand/logo.png') }}" alt="Logo Presensi Murid" class="h-11 w-11 rounded-md bg-white object-contain p-1 shadow-sm">
                    <div>
                        <p class="text-lg font-semibold">Presensi Murid</p>
                        <p class="text-sm text-slate-300">Portal Admin & Guru</p>
                    </div>
                </div>
            </div>

            <div class="relative max-w-xl">
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-teal-200">Pantauan Hari Ini</p>
                <h1 class="mt-4 max-w-lg text-4xl font-semibold leading-tight">Kontrol presensi siswa berbasis QR & geolokasi per mata pelajaran.</h1>
                <div class="mt-8 grid grid-cols-3 gap-3">
                    <div class="rounded-md border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-3xl font-semibold">86%</p>
                        <p class="mt-1 text-sm text-slate-300">Hadir</p>
                    </div>
                    <div class="rounded-md border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-3xl font-semibold text-amber-200">7</p>
                        <p class="mt-1 text-sm text-slate-300">Terlambat</p>
                    </div>
                    <div class="rounded-md border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-3xl font-semibold text-sky-200">4</p>
                        <p class="mt-1 text-sm text-slate-300">Izin</p>
                    </div>
                </div>

                <div class="mt-5 rounded-md border border-white/10 bg-white/10 p-4 backdrop-blur">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-300">Jadwal Reguler</span>
                        <span class="font-medium">07:00 - 15:00</span>
                    </div>
                    <div class="mt-4 space-y-3">
                        <div>
                            <div class="mb-1 flex justify-between text-xs text-slate-300"><span>Tepat waktu</span><span>74%</span></div>
                            <div class="h-2 rounded-full bg-white/15"><div class="h-2 w-[74%] rounded-full bg-teal-300"></div></div>
                        </div>
                        <div>
                            <div class="mb-1 flex justify-between text-xs text-slate-300"><span>Butuh tindak lanjut</span><span>18%</span></div>
                            <div class="h-2 rounded-full bg-white/15"><div class="h-2 w-[18%] rounded-full bg-amber-300"></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <p class="relative text-sm text-slate-400">Geofencing, persetujuan izin, koreksi manual, dan laporan ekspor.</p>
        </section>

        <section class="auth-admin-panel flex min-h-screen items-center justify-center px-4 py-8 sm:px-6">
            <div class="w-full max-w-md">
                <div class="auth-admin-brand mb-6 flex items-center gap-3 lg:hidden">
                    <img src="{{ asset('assets/brand/logo.png') }}" alt="Logo Presensi Murid" class="h-11 w-11 rounded-md bg-white object-contain p-1 shadow-sm">
                    <div>
                        <p class="text-lg font-semibold">Presensi Murid</p>
                        <p class="text-sm text-slate-500">Portal Admin & Guru</p>
                    </div>
                </div>

                <form method="post" action="{{ route('admin.masuk.store') }}" class="auth-admin-form rounded-md border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/10 sm:p-7">
                    @csrf
                    <div>
                        <p class="text-sm font-medium text-teal-700">Akses Portal</p>
                        <h1 class="mt-2 text-2xl font-semibold">Masuk ke Dasbor</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan akun admin atau guru yang sudah terdaftar.</p>
                    </div>

                    <div class="mt-6 space-y-4">
                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">Email</span>
                            <input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required autofocus placeholder="admin@presensi.test" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-700 focus:ring-4 focus:ring-teal-700/10">
                            @error('email')
                                <span class="mt-1 block text-xs text-red-700">{{ $message }}</span>
                            @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-medium text-slate-700">Kata Sandi</span>
                            <input name="kata_sandi" type="password" autocomplete="current-password" required placeholder="Masukkan kata sandi" class="mt-1 w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-teal-700 focus:ring-4 focus:ring-teal-700/10">
                            @error('kata_sandi')
                                <span class="mt-1 block text-xs text-red-700">{{ $message }}</span>
                            @enderror
                        </label>
                    </div>

                    <div class="mt-5 flex items-center justify-between gap-4">
                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-700">
                            Ingat sesi
                        </label>
                    </div>

                    <button class="mt-6 flex w-full items-center justify-center rounded-md bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus:ring-4 focus:ring-teal-700/20">
                        Masuk
                    </button>
                </form>

                <p class="mt-5 text-center text-xs text-slate-500">Sistem Presensi Murid</p>
            </div>
        </section>
    </main>
</body>
</html>
