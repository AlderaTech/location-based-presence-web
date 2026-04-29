<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Admin Presensi</title>
    <script>
        window.presensiNotifikasi = @json([
            'sukses' => session('status'),
            'galat' => $errors->any() ? $errors->first() : null,
        ]);
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <main class="flex min-h-screen items-center justify-center px-4">
        <form method="post" action="{{ route('admin.masuk.store') }}" class="w-full max-w-sm rounded-md border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <h1 class="text-xl font-semibold">Dasbor Presensi</h1>
            <p class="mt-1 text-sm text-slate-500">Masuk sebagai admin, tata usaha, atau kepala sekolah.</p>
            <label class="mt-5 block text-sm font-medium">Email</label>
            <input name="email" type="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
            <label class="mt-4 block text-sm font-medium">Kata Sandi</label>
            <input name="kata_sandi" type="password" required class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
            <label class="mt-4 flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                Ingat sesi
            </label>
            <button class="mt-5 w-full rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Masuk</button>
        </form>
    </main>
</body>
</html>
