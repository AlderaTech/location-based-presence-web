<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Presensi Guru</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
    <main class="flex min-h-screen items-center justify-center px-4">
        <div class="max-w-md rounded-md border border-slate-200 bg-white p-6 text-center shadow-sm">
            <img src="{{ asset('assets/brand/logo.png') }}" alt="Logo Presensi Guru" class="mx-auto mb-4 h-16 w-16 rounded-md object-contain">
            <h1 class="text-2xl font-semibold">Presensi Guru</h1>
            <p class="mt-2 text-sm text-slate-500">Sistem presensi guru berbasis lokasi sekolah.</p>
            <a href="{{ route('admin.dashboard') }}" class="mt-5 inline-flex rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white">Buka Dasbor</a>
        </div>
    </main>
</body>
</html>
