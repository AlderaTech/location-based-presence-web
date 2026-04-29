<x-admin.layout title="Persetujuan Izin">
    @php
        $labelStatus = ['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'];
        $labelJenis = ['izin' => 'Izin', 'sakit' => 'Sakit', 'cuti' => 'Cuti'];
        $statusMeta = [
            'menunggu' => ['chip' => 'bg-amber-50 text-amber-800 ring-amber-200', 'dot' => 'bg-amber-500', 'icon' => 'clock-3'],
            'disetujui' => ['chip' => 'bg-teal-50 text-teal-800 ring-teal-200', 'dot' => 'bg-teal-600', 'icon' => 'circle-check-big'],
            'ditolak' => ['chip' => 'bg-rose-50 text-rose-800 ring-rose-200', 'dot' => 'bg-rose-600', 'icon' => 'circle-x'],
        ];
        $jenisMeta = [
            'izin' => ['chip' => 'bg-sky-50 text-sky-800 ring-sky-200', 'icon' => 'file-text'],
            'sakit' => ['chip' => 'bg-violet-50 text-violet-800 ring-violet-200', 'icon' => 'stethoscope'],
            'cuti' => ['chip' => 'bg-indigo-50 text-indigo-800 ring-indigo-200', 'icon' => 'calendar-range'],
        ];
        $statusAktif = request('status');
        $jenisAktif = request('jenis');
        $queryFilter = request()->except(['page']);
    @endphp

    <div class="space-y-6">
        <section class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
            <div class="grid lg:grid-cols-[minmax(0,1fr)_340px]">
                <div class="p-5 sm:p-6">
                    <p class="text-sm font-medium text-teal-700">Persetujuan Izin</p>
                    <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold tracking-tight sm:text-3xl">Kelola Pengajuan Guru</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Tinjau izin, sakit, dan cuti dengan konteks tanggal, dokumen pendukung, status, dan alasan penolakan.</p>
                        </div>
                        <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">
                            <i data-lucide="chart-column-big"></i>
                            Laporan
                        </a>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        @foreach (['menunggu', 'disetujui', 'ditolak'] as $status)
                            <a href="{{ route('admin.pengajuan-izin.index', array_merge($queryFilter, ['status' => $status])) }}" class="rounded-md border p-4 transition {{ $statusAktif === $status ? 'border-slate-950 bg-slate-950 text-white' : 'border-slate-200 bg-slate-50 text-slate-900 hover:border-slate-300 hover:bg-white' }}">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-sm {{ $statusAktif === $status ? 'text-slate-300' : 'text-slate-500' }}">{{ $labelStatus[$status] }}</span>
                                    <span class="h-2.5 w-2.5 rounded-full {{ $statusMeta[$status]['dot'] }}"></span>
                                </div>
                                <p class="mt-2 text-3xl font-semibold">{{ (int) ($ringkasanStatus[$status] ?? 0) }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-slate-200 bg-slate-950 p-5 text-white lg:border-l lg:border-t-0">
                    <div class="flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-md bg-teal-300 text-slate-950">
                            <i data-lucide="inbox"></i>
                        </div>
                        <div>
                            <p class="text-sm text-slate-300">Total Pengajuan</p>
                            <p class="text-3xl font-semibold">{{ $totalPengajuan }}</p>
                        </div>
                    </div>
                    <div class="mt-5 space-y-3">
                        @foreach ($labelJenis as $jenis => $label)
                            @php
                                $totalJenis = (int) ($ringkasanJenis[$jenis] ?? 0);
                                $persenJenis = $totalPengajuan > 0 ? min(100, round(($totalJenis / $totalPengajuan) * 100)) : 0;
                                $barJenis = ['izin' => 'bg-sky-300', 'sakit' => 'bg-violet-300', 'cuti' => 'bg-indigo-300'][$jenis];
                            @endphp
                            <div>
                                <div class="mb-1 flex justify-between text-sm">
                                    <span class="text-slate-300">{{ $label }}</span>
                                    <span class="font-semibold">{{ $totalJenis }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-white/10">
                                    <div class="h-2 rounded-full {{ $barJenis }}" style="width: {{ $persenJenis }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-md border border-slate-200 bg-white p-4 shadow-sm shadow-slate-900/5">
            <form class="grid gap-3 md:grid-cols-4 xl:grid-cols-8">
                <div class="relative">
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input name="q" value="{{ request('q') }}" placeholder="Cari nama, email, NIP" class="w-full rounded-md border border-slate-300 bg-white py-2.5 pl-10 pr-3 text-sm focus:border-teal-700 focus:outline-none focus:ring-4 focus:ring-teal-700/10">
                </div>
                <select name="status" class="rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm">
                    <option value="">Semua status</option>
                    @foreach ($labelStatus as $status => $label)
                        <option value="{{ $status }}" @selected($statusAktif === $status)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="jenis" class="rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm">
                    <option value="">Semua jenis</option>
                    @foreach ($labelJenis as $jenis => $label)
                        <option value="{{ $jenis }}" @selected($jenisAktif === $jenis)>{{ $label }}</option>
                    @endforeach
                </select>
                <input name="tanggal_mulai" type="date" value="{{ request('tanggal_mulai') }}" title="Tanggal izin mulai" class="rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm">
                <input name="tanggal_selesai" type="date" value="{{ request('tanggal_selesai') }}" title="Tanggal izin selesai" class="rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm">
                <input name="diajukan_mulai" type="date" value="{{ request('diajukan_mulai') }}" title="Tanggal pengajuan mulai" class="rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm">
                <input name="diajukan_selesai" type="date" value="{{ request('diajukan_selesai') }}" title="Tanggal pengajuan selesai" class="rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm">
                <select name="sort" class="rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm">
                    <option value="dibuat_pada" @selected(request('sort', 'dibuat_pada') === 'dibuat_pada')>Urut: Diajukan</option>
                    <option value="nama" @selected(request('sort') === 'nama')>Urut: Nama Guru</option>
                    <option value="tanggal_mulai" @selected(request('sort') === 'tanggal_mulai')>Urut: Tanggal Mulai</option>
                    <option value="tanggal_selesai" @selected(request('sort') === 'tanggal_selesai')>Urut: Tanggal Selesai</option>
                    <option value="jenis" @selected(request('sort') === 'jenis')>Urut: Jenis</option>
                    <option value="status" @selected(request('sort') === 'status')>Urut: Status</option>
                </select>
                <select name="direction" class="rounded-md border border-slate-300 bg-white px-3 py-2.5 text-sm">
                    <option value="desc" @selected(request('direction', 'desc') === 'desc')>Menurun</option>
                    <option value="asc" @selected(request('direction') === 'asc')>Menaik</option>
                </select>
                <button class="inline-flex items-center justify-center gap-2 rounded-md bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">
                    <i data-lucide="filter"></i>
                    Terapkan
                </button>
                <a href="{{ route('admin.pengajuan-izin.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
            </form>
        </section>

        <section class="space-y-4">
            @forelse ($pengajuanIzin as $pengajuan)
                @php
                    $status = $pengajuan->status;
                    $jenis = $pengajuan->jenis;
                    $statusClass = $statusMeta[$status]['chip'] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                    $jenisClass = $jenisMeta[$jenis]['chip'] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                    $jumlahHari = $pengajuan->tanggal_mulai->diffInDays($pengajuan->tanggal_selesai) + 1;
                @endphp
                <article class="overflow-hidden rounded-md border border-slate-200 bg-white shadow-sm shadow-slate-900/5">
                    <div class="grid lg:grid-cols-[minmax(0,1fr)_340px]">
                        <div class="p-4 sm:p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex min-w-0 items-start gap-3">
                                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-md bg-slate-950 text-sm font-semibold text-white">{{ str($pengajuan->pengguna?->nama ?? '?')->substr(0, 1)->upper() }}</div>
                                    <div class="min-w-0">
                                        <h2 class="truncate text-lg font-semibold">{{ $pengajuan->pengguna?->nama }}</h2>
                                        <p class="mt-1 text-sm text-slate-500">{{ $pengajuan->guru?->nip ?: $pengajuan->pengguna?->nomor_pegawai ?: 'Nomor pegawai belum diisi' }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $jenisClass }}">
                                        <i data-lucide="{{ $jenisMeta[$jenis]['icon'] ?? 'file-text' }}"></i>
                                        {{ $labelJenis[$jenis] ?? ucfirst($jenis) }}
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                                        <i data-lucide="{{ $statusMeta[$status]['icon'] ?? 'circle' }}"></i>
                                        {{ $labelStatus[$status] ?? ucfirst($status) }}
                                    </span>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-md bg-slate-50 px-3 py-3">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Mulai</p>
                                    <p class="mt-1 text-sm font-semibold">{{ $pengajuan->tanggal_mulai->format('d M Y') }}</p>
                                </div>
                                <div class="rounded-md bg-slate-50 px-3 py-3">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Selesai</p>
                                    <p class="mt-1 text-sm font-semibold">{{ $pengajuan->tanggal_selesai->format('d M Y') }}</p>
                                </div>
                                <div class="rounded-md bg-slate-50 px-3 py-3">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Durasi</p>
                                    <p class="mt-1 text-sm font-semibold">{{ $jumlahHari }} hari</p>
                                </div>
                            </div>

                            <div class="mt-5">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Alasan</p>
                                <p class="mt-2 text-sm leading-6 text-slate-700">{{ $pengajuan->alasan }}</p>
                            </div>

                            @if ($pengajuan->dokumen->isNotEmpty())
                                <div class="mt-5 flex flex-wrap gap-2">
                                    @foreach ($pengajuan->dokumen as $dokumen)
                                        <a class="inline-flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:border-sky-300 hover:bg-sky-50 hover:text-sky-800" href="{{ asset('storage/'.$dokumen->path) }}" target="_blank">
                                            <i data-lucide="paperclip"></i>
                                            {{ $dokumen->nama_asli }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @if ($pengajuan->alasan_penolakan)
                                <div class="mt-5 rounded-md bg-rose-50 px-3 py-3 text-sm text-rose-800">
                                    <span class="font-semibold">Alasan ditolak:</span> {{ $pengajuan->alasan_penolakan }}
                                </div>
                            @endif
                        </div>

                        <aside class="border-t border-slate-200 bg-slate-50 p-4 sm:p-5 lg:border-l lg:border-t-0">
                            <div class="mb-4">
                                <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Diajukan</p>
                                <p class="mt-1 text-sm font-semibold">{{ $pengajuan->dibuat_pada?->format('d M Y, H:i') }}</p>
                            </div>

                            @if ($pengajuan->status === 'menunggu')
                                <div class="space-y-3">
                                    <form method="post" action="{{ route('admin.pengajuan-izin.setujui', $pengajuan) }}" data-confirm data-confirm-title="Setujui pengajuan?" data-confirm-text="Sistem akan membuat atau memperbarui catatan presensi sesuai tanggal pengajuan." data-confirm-button="Setujui" data-confirm-variant="success">
                                        @csrf
                                        <button class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">
                                            <i data-lucide="check"></i>
                                            Setujui Pengajuan
                                        </button>
                                    </form>

                                    <form method="post" action="{{ route('admin.pengajuan-izin.tolak', $pengajuan) }}" data-confirm data-confirm-title="Tolak pengajuan?" data-confirm-text="Pastikan alasan penolakan sudah jelas sebelum dikirim." data-confirm-button="Tolak" data-confirm-variant="danger">
                                        @csrf
                                        <textarea name="alasan_penolakan" rows="3" required placeholder="Alasan penolakan" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-rose-600 focus:outline-none focus:ring-4 focus:ring-rose-600/10"></textarea>
                                        <button class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-md border border-rose-300 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 hover:bg-rose-50">
                                            <i data-lucide="x"></i>
                                            Tolak Pengajuan
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="rounded-md bg-white p-3">
                                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Ditinjau oleh</p>
                                    <p class="mt-1 text-sm font-semibold">{{ $pengajuan->peninjau?->nama ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $pengajuan->ditinjau_pada?->format('d M Y, H:i') ?: '-' }}</p>
                                </div>
                            @endif
                        </aside>
                    </div>
                </article>
            @empty
                <div class="rounded-md border border-slate-200 bg-white p-10 text-center shadow-sm">
                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-md bg-slate-100 text-slate-500">
                        <i data-lucide="inbox"></i>
                    </div>
                    <h2 class="mt-4 font-semibold">Belum ada pengajuan</h2>
                    <p class="mt-1 text-sm text-slate-500">Data akan tampil di sini setelah guru mengirim pengajuan izin, sakit, atau cuti.</p>
                </div>
            @endforelse
        </section>

        <div>{{ $pengajuanIzin->links() }}</div>
    </div>
</x-admin.layout>
