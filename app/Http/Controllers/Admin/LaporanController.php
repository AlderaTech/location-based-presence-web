<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Presensi;
use App\Support\LaporanPdf;
use App\Support\LaporanXlsx;
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
        ]);
    }

    public function ekspor(Request $request, string $format): Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $rows = $this->rows($this->query($request)->get());

        if ($format === 'xlsx') {
            $path = LaporanXlsx::make($rows);

            return response()
                ->download($path, 'rekap-presensi.xlsx', [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->deleteFileAfterSend(true);
        }

        return response(LaporanPdf::make($rows), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="rekap-presensi.pdf"',
        ]);
    }

    private function query(Request $request): Builder
    {
        return Presensi::query()
            ->with(['pengguna', 'guru', 'jadwalKerja'])
            ->when($request->query('guru_id'), fn ($query, $id) => $query->where('guru_id', $id))
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('tanggal_mulai'), fn ($query, $tanggal) => $query->whereDate('tanggal_presensi', '>=', $tanggal))
            ->when($request->query('tanggal_selesai'), fn ($query, $tanggal) => $query->whereDate('tanggal_presensi', '<=', $tanggal))
            ->orderBy('tanggal_presensi')
            ->orderBy('pengguna_id');
    }

    private function rows(iterable $presensi): array
    {
        $rows = [['Tanggal', 'Guru', 'NIP', 'Status', 'Jam Masuk', 'Jam Pulang', 'Jadwal Kerja', 'Catatan']];

        foreach ($presensi as $item) {
            $rows[] = [
                $item->tanggal_presensi?->format('Y-m-d'),
                $item->pengguna?->nama,
                $item->guru?->nip ?: $item->pengguna?->nomor_pegawai,
                $item->status,
                $item->jam_masuk_pada?->format('H:i:s'),
                $item->jam_pulang_pada?->format('H:i:s'),
                $item->jadwalKerja?->nama,
                $item->catatan,
            ];
        }

        return $rows;
    }
}
