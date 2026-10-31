<x-admin.layout title="Persetujuan Izin & Sakit Murid">
    @php
        $labelStatus = ['menunggu' => 'Menunggu', 'disetujui' => 'Disetujui', 'ditolak' => 'Ditolak'];
        $labelJenis = ['izin' => 'Izin', 'sakit' => 'Sakit'];
        $statusAktif = request('status');
        $queryFilter = request()->except(['page']);
        $bolehMeninjau = in_array(auth()->user()->peran, ['admin', 'guru'], true);
    @endphp

    <div class="space-y-6">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="grid lg:grid-cols-[minmax(0,1fr)_340px]">
                <div class="p-5 sm:p-6">
                    <p class="text-sm font-bold text-teal-700">Verifikasi Izin & Sakit Murid</p>
                    <div class="mt-2 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl text-slate-900">Pengajuan Izin & Sakit Siswa</h1>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Periksa surat keterangan dokter atau surat izin orang tua yang diunggah siswa. Setujui untuk memperbarui status presensi menjadi Izin/Sakit.</p>
                        </div>
                        <a href="{{ route('admin.laporan.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-xs font-bold text-white hover:bg-slate-800">
                            <i data-lucide="chart-column-big" class="h-4 w-4"></i>
                            Rekap Presensi
                        </a>
                    </div>

                    <div class="mt-6 grid gap-3 sm:grid-cols-3">
                        @foreach (['menunggu', 'disetujui', 'ditolak'] as $status)
                            <a href="{{ route('admin.pengajuan-izin.index', array_merge($queryFilter, ['status' => $status])) }}" class="rounded-xl border p-4 transition {{ $statusAktif === $status ? 'border-teal-600 bg-teal-50/50 shadow-sm' : 'border-slate-200 bg-slate-50 text-slate-900 hover:border-slate-300 hover:bg-white' }}">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-xs font-bold uppercase text-slate-500">{{ $labelStatus[$status] }}</span>
                                    <span class="h-2.5 w-2.5 rounded-full {{ $status === 'menunggu' ? 'bg-amber-500' : ($status === 'disetujui' ? 'bg-emerald-600' : 'bg-rose-600') }}"></span>
                                </div>
                                <p class="mt-2 text-2xl font-bold">{{ (int) ($ringkasanStatus[$status] ?? 0) }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-slate-200 bg-slate-950 p-5 text-white lg:border-l lg:border-t-0 flex flex-col justify-center">
                    <div class="flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-xl bg-teal-400 text-slate-950">
                            <i data-lucide="inbox"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase text-slate-400">Total Pengajuan</p>
                            <p class="text-3xl font-bold">{{ $totalPengajuan }}</p>
                        </div>
                    </div>
                    <div class="mt-5 space-y-2">
                        @foreach ($labelJenis as $jenis => $label)
                            @php
                                $totalJenis = (int) ($ringkasanJenis[$jenis] ?? 0);
                            @endphp
                            <div class="flex items-center justify-between rounded-lg bg-white/[0.06] p-2.5 text-xs">
                                <span class="text-slate-300 font-medium">{{ $label }}</span>
                                <span class="font-bold text-teal-300">{{ $totalJenis }} pengajuan</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Filter Bar -->
            <form method="get" class="border-t border-slate-200 bg-slate-50/70 p-4 flex flex-wrap gap-2">
                <input name="q" value="{{ request('q') }}" placeholder="Cari nama murid, NIS..." class="w-64 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs focus:border-teal-500 focus:outline-none">
                <select name="status" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs">
                    <option value="">Semua Status</option>
                    @foreach ($labelStatus as $s => $lbl)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $lbl }}</option>
                    @endforeach
                </select>
                <select name="jenis" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs">
                    <option value="">Semua Jenis</option>
                    @foreach ($labelJenis as $j => $lbl)
                        <option value="{{ $j }}" @selected(request('jenis') === $j)>{{ $lbl }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white hover:bg-slate-800">Filter</button>
                <a href="{{ route('admin.pengajuan-izin.index') }}" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
            </form>
        </section>

        <!-- Tabel Pengajuan -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5"><x-admin.sort-link kolom="dibuat_pada" label="Tanggal" /></th>
                        <th class="px-4 py-3.5"><x-admin.sort-link kolom="nama" label="Murid" /></th>
                        <th class="px-4 py-3.5">Kelas & Mapel</th>
                        <th class="px-4 py-3.5"><x-admin.sort-link kolom="jenis" label="Jenis" /></th>
                        <th class="px-4 py-3.5"><x-admin.sort-link kolom="tanggal_mulai" label="Periode" /></th>
                        <th class="px-4 py-3.5">Keterangan & Bukti</th>
                        <th class="px-4 py-3.5"><x-admin.sort-link kolom="status" label="Status" /></th>
                        @if ($bolehMeninjau)
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($pengajuanIzin as $pengajuan)
                        @php
                            $jumlahHari = $pengajuan->tanggal_mulai->diffInDays($pengajuan->tanggal_selesai) + 1;
                        @endphp
                        <tr class="hover:bg-slate-50/75 align-top">
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-semibold text-slate-800 block">{{ $pengajuan->dibuat_pada?->format('d/m/Y') }}</span>
                                <span class="text-xs text-slate-400">{{ $pengajuan->dibuat_pada?->format('H:i') }} WIB</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-bold text-slate-900 block">{{ $pengajuan->pengguna?->nama }}</span>
                                <span class="font-mono text-xs text-slate-400">NIS: {{ $pengajuan->murid?->nis }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-semibold text-slate-800 block">{{ $pengajuan->murid?->kelas?->nama_kelas ?: '-' }}</span>
                                <span class="text-xs text-slate-500">{{ $pengajuan->jadwalPelajaran?->mataPelajaran?->nama_mapel ?: 'Semua Mapel' }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <x-badge-jenis :jenis="$pengajuan->jenis" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <span class="font-semibold text-slate-800 block">
                                    {{ $pengajuan->tanggal_mulai->format('d/m/Y') }} - {{ $pengajuan->tanggal_selesai->format('d/m/Y') }}
                                </span>
                                <span class="text-xs text-slate-400">{{ $jumlahHari }} hari</span>
                            </td>
                            <td class="px-4 py-3.5 min-w-[200px]">
                                <p class="text-xs text-slate-700 leading-relaxed">{{ $pengajuan->alasan }}</p>
                                @if ($pengajuan->dokumen->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($pengajuan->dokumen as $dokumen)
                                            <a href="{{ asset('storage/'.$dokumen->path) }}" target="_blank" class="inline-flex items-center gap-1 rounded-md border border-teal-200 bg-teal-50 px-2 py-0.5 text-[11px] font-semibold text-teal-700 hover:bg-teal-100 transition shadow-2xs" title="{{ $dokumen->nama_asli }}">
                                                <i data-lucide="paperclip" class="h-3 w-3 text-teal-600"></i>
                                                <span class="max-w-[120px] truncate">{{ $dokumen->nama_asli }}</span>
                                                <i data-lucide="external-link" class="h-2.5 w-2.5 text-teal-500"></i>
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                                @if ($pengajuan->alasan_penolakan)
                                    <div class="mt-2 rounded-md bg-rose-50 p-2 text-xs text-rose-700 border border-rose-200">
                                        <span class="font-bold">Alasan ditolak:</span> {{ $pengajuan->alasan_penolakan }}
                                    </div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3.5">
                                <x-badge-status :status="$pengajuan->status" />
                                @if ($pengajuan->peninjau)
                                    <div class="mt-1 text-[11px] text-slate-400" title="Ditinjau pada {{ $pengajuan->ditinjau_pada?->format('d/m/Y H:i') }} WIB">
                                        Oleh: {{ $pengajuan->peninjau->nama }}
                                    </div>
                                @endif
                            </td>
                            @if ($bolehMeninjau)
                                <td class="whitespace-nowrap px-4 py-3.5 text-right">
                                    @if ($pengajuan->status === 'menunggu')
                                        <div class="flex items-center justify-end gap-1.5">
                                            <form method="post" action="{{ route('admin.pengajuan-izin.setujui', $pengajuan) }}" class="inline-block" data-confirm data-confirm-title="Setujui permohonan ini?" data-confirm-text="Status kehadiran siswa pada jadwal pelajaran tersebut akan otomatis diperbarui." data-confirm-button="Ya, Setujui" data-confirm-variant="success">
                                                @csrf
                                                <button type="submit" class="rounded-md border border-teal-600 bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700 shadow-xs hover:bg-teal-100 transition inline-flex items-center gap-1">
                                                    <i data-lucide="check" class="h-3.5 w-3.5"></i>
                                                    <span>Setujui Izin / Sakit</span>
                                                </button>
                                            </form>
                                            <button type="button" onclick="document.getElementById('modal-tolak-{{ $pengajuan->id }}').showModal()" class="rounded-md border border-rose-300 bg-white px-2.5 py-1 text-xs font-semibold text-rose-700 shadow-xs hover:bg-rose-50 transition inline-flex items-center gap-1">
                                                <i data-lucide="x" class="h-3.5 w-3.5"></i>
                                                <span>Tolak</span>
                                            </button>
                                        </div>

                                        <!-- Modal Dialog Tolak -->
                                        <dialog id="modal-tolak-{{ $pengajuan->id }}" onclick="if (event.target === this) this.close()" class="rounded-xl border border-slate-200 p-0 shadow-2xl backdrop:bg-slate-900/50 max-w-md w-full m-auto text-left">
                                            <div class="p-5">
                                                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                                    <h3 class="text-base font-bold text-slate-900">Tolak Pengajuan Izin</h3>
                                                    <button type="button" onclick="document.getElementById('modal-tolak-{{ $pengajuan->id }}').close()" class="text-slate-400 hover:text-slate-600">
                                                        <i data-lucide="x" class="h-4 w-4"></i>
                                                    </button>
                                                </div>
                                                <div class="mt-3 text-xs text-slate-600">
                                                    <p>Murid: <span class="font-bold text-slate-900">{{ $pengajuan->pengguna?->nama }}</span> ({{ $pengajuan->murid?->nis }})</p>
                                                    <p class="mt-0.5">Periode: <span class="font-semibold">{{ $pengajuan->tanggal_mulai->format('d/m/Y') }} - {{ $pengajuan->tanggal_selesai->format('d/m/Y') }}</span></p>
                                                </div>
                                                <form method="post" action="{{ route('admin.pengajuan-izin.tolak', $pengajuan) }}" class="mt-4 space-y-3">
                                                    @csrf
                                                    <div>
                                                        <label class="block text-xs font-semibold text-slate-700 mb-1">Alasan Penolakan <span class="text-rose-500">*</span></label>
                                                        <textarea name="alasan_penolakan" rows="3" required placeholder="Tulis alasan penolakan agar siswa memahami alasannya..." class="w-full rounded-lg border border-slate-300 p-2.5 text-xs focus:border-rose-500 focus:outline-none"></textarea>
                                                    </div>
                                                    <div class="flex justify-end gap-2 pt-2">
                                                        <button type="button" onclick="document.getElementById('modal-tolak-{{ $pengajuan->id }}').close()" class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                                                        <button type="submit" class="rounded-md bg-rose-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-rose-700">Tolak Pengajuan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </dialog>
                                    @else
                                        <span class="text-xs text-slate-400">Selesai</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $bolehMeninjau ? 8 : 7 }}" class="px-4 py-8 text-center text-slate-400">
                                Belum ada pengajuan izin/sakit yang sesuai kriteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($pengajuanIzin->hasPages())
                <div class="border-t border-slate-200 px-4 py-3">
                    {{ $pengajuanIzin->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin.layout>
