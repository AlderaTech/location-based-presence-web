<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalKerja;
use App\Models\LokasiSekolah;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Support\LaporanPdf;
use App\Support\LaporanXlsx;
use App\Support\SortirAdmin;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.laporan.index', [
            'presensi' => $this->query($request)->paginate(30)->withQueryString(),
            'guru' => Guru::query()->with('pengguna')->where('aktif', true)->get(),
            'jadwalKerja' => JadwalKerja::query()->where('aktif', true)->orderBy('nama')->get(),
            'lokasiSekolah' => LokasiSekolah::query()->orderBy('nama')->get(),
        ]);
    }

    public function ekspor(Request $request, string $format): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $rows = $this->rows($this->query($request)->get());
        $metadata = $this->metadata($request);
        $namaFile = 'rekap-presensi-'.now()->format('Ymd-His');

        if ($format === 'xlsx') {
            $path = LaporanXlsx::make($rows, [
                'judul' => 'Rekap Presensi Guru',
                'metadata' => $metadata,
            ]);

            return response()
                ->download($path, $namaFile.'.xlsx', [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->deleteFileAfterSend(true);
        }

        return response(LaporanPdf::make($rows, [
            'judul' => 'Rekap Presensi Guru',
            'metadata' => $metadata,
        ]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$namaFile.'.pdf"',
        ]);
    }

    private function query(Request $request): Builder
    {
        $query = Presensi::query()
            ->with(['pengguna', 'guru', 'jadwalKerja', 'lokasiSekolah'])
            ->when($request->query('q'), function ($query, string $q): void {
                $query->where(function ($query) use ($q): void {
                    $query->whereHas('pengguna', fn ($pengguna) => $pengguna
                        ->where('nama', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%')
                        ->orWhere('nomor_pegawai', 'like', '%'.$q.'%'))
                        ->orWhereHas('guru', fn ($guru) => $guru->where('nip', 'like', '%'.$q.'%'));
                });
            })
            ->when($request->query('id_guru'), fn ($query, $id) => $query->where('id_guru', $id))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('id_jadwal_kerja'), fn ($query, $id) => $query->where('id_jadwal_kerja', $id))
            ->when($request->query('id_lokasi_sekolah'), fn ($query, $id) => $query->where('id_lokasi_sekolah', $id))
            ->when($request->has('lokasi_palsu') && $request->query('lokasi_palsu') !== '', fn ($query) => $query->where('terdeteksi_lokasi_palsu', $request->boolean('lokasi_palsu')))
            ->when($request->query('tanggal_mulai'), fn ($query, $tanggal) => $query->whereDate('tanggal_presensi', '>=', $tanggal))
            ->when($request->query('tanggal_selesai'), fn ($query, $tanggal) => $query->whereDate('tanggal_presensi', '<=', $tanggal));

        SortirAdmin::terapkan($query, $request, [
            'tanggal_presensi' => 'presensi.tanggal_presensi',
            'nama' => fn ($query, string $arah) => $query->orderBy(
                Pengguna::query()->select('nama')->whereColumn('pengguna.id', 'presensi.id_pengguna'),
                $arah
            ),
            'status' => 'presensi.status',
            'jam_masuk_pada' => 'presensi.jam_masuk_pada',
            'jam_pulang_pada' => 'presensi.jam_pulang_pada',
            'jadwal' => fn ($query, string $arah) => $query->orderBy(
                JadwalKerja::query()->select('nama')->whereColumn('jadwal_kerja.id', 'presensi.id_jadwal_kerja'),
                $arah
            ),
            'lokasi' => fn ($query, string $arah) => $query->orderBy(
                LokasiSekolah::query()->select('nama')->whereColumn('lokasi_sekolah.id', 'presensi.id_lokasi_sekolah'),
                $arah
            ),
        ], 'tanggal_presensi');

        return $query->orderBy('presensi.id_pengguna');
    }

    private function rows(iterable $presensi): array
    {
        $rows = [['Tanggal', 'Nama Guru', 'NIP/Nomor Pegawai', 'Status', 'Jam Masuk', 'Jam Pulang', 'Jadwal Kerja', 'Lokasi', 'Jarak', 'Catatan']];

        foreach ($presensi as $item) {
            $rows[] = [
                $item->tanggal_presensi?->format('d/m/Y') ?: '-',
                $item->pengguna?->nama ?: '-',
                $item->guru?->nip ?: $item->pengguna?->nomor_pegawai ?: '-',
                $this->labelStatus($item->status),
                $item->jam_masuk_pada?->format('H:i') ?: '-',
                $item->jam_pulang_pada?->format('H:i') ?: '-',
                $item->jadwalKerja?->nama ?: '-',
                $item->lokasiSekolah?->nama ?: '-',
                $this->formatJarak($item),
                $this->formatCatatan($item),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private function metadata(Request $request): array
    {
        $guru = $request->query('id_guru')
            ? Guru::query()->with('pengguna')->find($request->query('id_guru'))
            : null;
        $jadwal = $request->query('id_jadwal_kerja')
            ? JadwalKerja::query()->find($request->query('id_jadwal_kerja'))
            : null;
        $lokasi = $request->query('id_lokasi_sekolah')
            ? LokasiSekolah::query()->find($request->query('id_lokasi_sekolah'))
            : null;
        $tanggalMulai = $request->query('tanggal_mulai') ?: null;
        $tanggalSelesai = $request->query('tanggal_selesai') ?: null;
        $periode = match (true) {
            filled($tanggalMulai) && filled($tanggalSelesai) => $tanggalMulai.' s.d. '.$tanggalSelesai,
            filled($tanggalMulai) => 'Mulai '.$tanggalMulai,
            filled($tanggalSelesai) => 'Sampai '.$tanggalSelesai,
            default => 'Semua tanggal',
        };
        $lokasiPalsu = $request->query('lokasi_palsu');

        return [
            'Periode' => $periode,
            'Guru' => $guru?->pengguna?->nama ?: 'Semua guru',
            'Status' => $request->query('status') ? $this->labelStatus((string) $request->query('status')) : 'Semua status',
            'Jadwal Kerja' => $jadwal?->nama ?: 'Semua jadwal',
            'Lokasi' => $lokasi?->nama ?: 'Semua lokasi',
            'Lokasi Palsu' => $lokasiPalsu === null || $lokasiPalsu === '' ? 'Semua' : ($request->boolean('lokasi_palsu') ? 'Ya' : 'Tidak'),
            'Dibuat Pada' => now()->format('d/m/Y H:i'),
        ];
    }

    private function labelStatus(?string $status): string
    {
        return [
            'hadir' => 'Tepat Waktu',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'cuti' => 'Cuti',
            'alpha' => 'Alpha',
        ][$status] ?? (string) $status;
    }

    private function formatJarak(Presensi $item): string
    {
        $masuk = $item->jarak_masuk_meter !== null ? 'Masuk '.$item->jarak_masuk_meter.' m' : null;
        $pulang = $item->jarak_pulang_meter !== null ? 'Pulang '.$item->jarak_pulang_meter.' m' : null;

        return implode(' / ', array_filter([$masuk, $pulang])) ?: '-';
    }

    private function formatCatatan(Presensi $item): string
    {
        $catatan = trim((string) $item->catatan);

        if ($item->terdeteksi_lokasi_palsu) {
            $catatan = trim('Lokasi palsu terdeteksi. '.$catatan);
        }

        return $catatan !== '' ? $catatan : '-';
    }
}
