<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Support\AdminSortHelper;
use App\Support\PdfReportService;
use App\Support\PresensiFormatter;
use App\Support\XlsxReportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isGuru = $user->adalahGuru();
        $guruId = $user->guru?->id;

        $daftarKelas = Kelas::query()->where('aktif', true)->orderBy('nama_kelas')->get();
        $daftarMapel = MataPelajaran::query()->where('aktif', true)->orderBy('nama_mapel')->get();
        $daftarGuru = Guru::query()->with('pengguna')->where('aktif', true)->get();

        return view('admin.laporan.index', [
            'presensi' => $this->query($request)->paginate(30)->withQueryString(),
            'daftarKelas' => $daftarKelas,
            'daftarMapel' => $daftarMapel,
            'daftarGuru' => $daftarGuru,
        ]);
    }

    public function ekspor(Request $request, string $format): Response|BinaryFileResponse
    {
        $rows = $this->rows($this->query($request)->get());
        $metadata = $this->metadata($request);
        $namaFile = 'rekap-presensi-murid-'.now()->format('Ymd-His');

        if ($format === 'xlsx') {
            $path = XlsxReportService::make($rows, [
                'judul' => 'Rekap Presensi Murid',
                'metadata' => $metadata,
            ]);

            return response()
                ->download($path, $namaFile.'.xlsx', [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->deleteFileAfterSend(true);
        }

        return response(PdfReportService::make($rows, [
            'judul' => 'Rekap Presensi Murid',
            'metadata' => $metadata,
        ]), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$namaFile.'.pdf"',
        ]);
    }

    private function query(Request $request): Builder
    {
        $user = $request->user();
        $isGuru = $user->adalahGuru();
        $guruId = $user->guru?->id;

        $query = Presensi::query()
            ->with(['pengguna', 'murid.kelas', 'jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.kelas', 'jadwalPelajaran.guru.pengguna', 'lokasiSekolah'])
            ->when($request->query('q'), function ($query, string $q): void {
                $query->where(function ($query) use ($q): void {
                    $query->whereHas('pengguna', fn ($p) => $p->where('nama', 'like', '%'.$q.'%')->orWhere('email', 'like', '%'.$q.'%'))
                        ->orWhereHas('murid', fn ($m) => $m->where('nis', 'like', '%'.$q.'%'))
                        ->orWhereHas('jadwalPelajaran.mataPelajaran', fn ($mp) => $mp->where('nama_mapel', 'like', '%'.$q.'%'));
                });
            })
            ->when($request->query('id_kelas'), fn ($q, $k) => $q->whereHas('murid', fn ($m) => $m->where('id_kelas', $k)))
            ->when($request->query('id_mata_pelajaran'), fn ($q, $mp) => $q->whereHas('jadwalPelajaran', fn ($j) => $j->where('id_mata_pelajaran', $mp)))
            ->when($request->query('id_guru'), fn ($q, $g) => $q->whereHas('jadwalPelajaran', fn ($j) => $j->where('id_guru', $g)))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('tanggal_mulai'), fn ($q, $tgl) => $q->whereDate('tanggal_presensi', '>=', $tgl))
            ->when($request->query('tanggal_selesai'), fn ($q, $tgl) => $q->whereDate('tanggal_presensi', '<=', $tgl));

        if ($isGuru && $guruId) {
            $query->whereHas('jadwalPelajaran', fn ($j) => $j->where('id_guru', $guruId));
        }

        AdminSortHelper::terapkan($query, $request, [
            'tanggal_presensi' => 'presensi.tanggal_presensi',
            'waktu_presensi' => 'presensi.waktu_presensi',
            'nama' => fn ($query, string $arah) => $query->orderBy(
                Pengguna::query()->select('nama')->whereColumn('pengguna.id', 'presensi.id_pengguna'),
                $arah
            ),
            'status' => 'presensi.status',
        ], 'tanggal_presensi');

        return $query->orderByDesc('presensi.tanggal_presensi')->orderBy('presensi.id');
    }

    private function rows(iterable $presensi): array
    {
        $rows = [['Tanggal', 'NIS', 'Nama Murid', 'Kelas', 'Mata Pelajaran', 'Guru Pengampu', 'Waktu', 'Status', 'Metode', 'Jarak']];

        foreach ($presensi as $item) {
            $jadwal = $item->jadwalPelajaran;
            $rows[] = [
                $item->tanggal_presensi?->format('d/m/Y') ?: '-',
                $item->murid?->nis ?: '-',
                $item->pengguna?->nama ?: '-',
                $item->murid?->kelas?->nama_kelas ?: $jadwal?->kelas?->nama_kelas ?: '-',
                $jadwal?->mataPelajaran?->nama_mapel ?: '-',
                $jadwal?->guru?->pengguna?->nama ?: '-',
                $item->waktu_presensi?->format('H:i') ?: '-',
                PresensiFormatter::labelStatus($item->status),
                PresensiFormatter::labelMetode($item->metode),
                $item->jarak_meter ? $item->jarak_meter.' m' : '-',
            ];
        }

        return $rows;
    }

    private function metadata(Request $request): array
    {
        $meta = [
            'Dicetak Pada' => now()->format('d/m/Y H:i'),
        ];

        if ($request->filled('tanggal_mulai')) {
            $meta['Periode Mulai'] = $request->query('tanggal_mulai');
        }
        if ($request->filled('tanggal_selesai')) {
            $meta['Periode Selesai'] = $request->query('tanggal_selesai');
        }
        if ($request->filled('status')) {
            $meta['Status'] = ucfirst($request->query('status'));
        }

        return $meta;
    }
}
