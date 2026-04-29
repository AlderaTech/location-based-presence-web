<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAuditPresensi;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Support\SortirAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\CarbonPeriod;
use Illuminate\View\View;

class PersetujuanIzinController extends Controller
{
    public function index(Request $request): View
    {
        $query = PengajuanIzin::query()
            ->with(['pengguna', 'guru', 'dokumen', 'peninjau'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('jenis'), fn ($query, $jenis) => $query->where('jenis', $jenis))
            ->when($request->query('tanggal_mulai'), fn ($query, $tanggal) => $query->whereDate('tanggal_selesai', '>=', $tanggal))
            ->when($request->query('tanggal_selesai'), fn ($query, $tanggal) => $query->whereDate('tanggal_mulai', '<=', $tanggal))
            ->when($request->query('diajukan_mulai'), fn ($query, $tanggal) => $query->whereDate('dibuat_pada', '>=', $tanggal))
            ->when($request->query('diajukan_selesai'), fn ($query, $tanggal) => $query->whereDate('dibuat_pada', '<=', $tanggal))
            ->when($request->query('q'), function ($query, string $q): void {
                $query->where(function ($query) use ($q): void {
                    $query
                        ->whereHas('pengguna', fn ($pengguna) => $pengguna
                            ->where('nama', 'like', '%'.$q.'%')
                            ->orWhere('email', 'like', '%'.$q.'%')
                            ->orWhere('nomor_pegawai', 'like', '%'.$q.'%'))
                        ->orWhereHas('guru', fn ($guru) => $guru->where('nip', 'like', '%'.$q.'%'));
                });
            });

        SortirAdmin::terapkan($query, $request, [
            'dibuat_pada' => 'pengajuan_izin.dibuat_pada',
            'nama' => fn ($query, string $arah) => $query->orderBy(
                Pengguna::query()->select('nama')->whereColumn('pengguna.id', 'pengajuan_izin.id_pengguna'),
                $arah
            ),
            'tanggal_mulai' => 'pengajuan_izin.tanggal_mulai',
            'tanggal_selesai' => 'pengajuan_izin.tanggal_selesai',
            'jenis' => 'pengajuan_izin.jenis',
            'status' => 'pengajuan_izin.status',
        ], 'dibuat_pada', 'desc');

        return view('admin.pengajuan-izin.index', [
            'pengajuanIzin' => $query->orderByDesc('pengajuan_izin.id')->paginate(12)->withQueryString(),
            'ringkasanStatus' => PengajuanIzin::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'ringkasanJenis' => PengajuanIzin::query()
                ->selectRaw('jenis, count(*) as total')
                ->groupBy('jenis')
                ->pluck('total', 'jenis'),
            'totalPengajuan' => PengajuanIzin::query()->count(),
        ]);
    }

    public function setujui(Request $request, PengajuanIzin $pengajuanIzin): RedirectResponse
    {
        $pengajuanIzin->update([
            'status' => 'disetujui',
            'id_peninjau' => $request->user()->id,
            'ditinjau_pada' => now(),
            'alasan_penolakan' => null,
        ]);

        foreach (CarbonPeriod::create($pengajuanIzin->tanggal_mulai, $pengajuanIzin->tanggal_selesai) as $tanggal) {
            $presensi = Presensi::updateOrCreate(
                [
                    'id_guru' => $pengajuanIzin->id_guru,
                    'tanggal_presensi' => $tanggal->toDateString(),
                ],
                [
                    'id_pengguna' => $pengajuanIzin->id_pengguna,
                    'status' => $pengajuanIzin->jenis,
                    'catatan' => $pengajuanIzin->alasan,
                    'id_pengoreksi' => $request->user()->id,
                    'alasan_koreksi' => 'Persetujuan pengajuan #'.$pengajuanIzin->id,
                ],
            );

            LogAuditPresensi::create([
                'id_presensi' => $presensi->id,
                'id_pengguna' => $pengajuanIzin->id_pengguna,
                'id_aktor' => $request->user()->id,
                'aksi' => 'pengajuan_izin_disetujui',
                'payload' => ['id_pengajuan_izin' => $pengajuanIzin->id, 'jenis' => $pengajuanIzin->jenis],
                'dibuat_pada' => now(),
            ]);
        }

        return back()->with('status', 'Pengajuan berhasil disetujui.');
    }

    public function tolak(Request $request, PengajuanIzin $pengajuanIzin): RedirectResponse
    {
        $data = $request->validate(['alasan_penolakan' => ['required', 'string', 'max:2000']]);
        $pengajuanIzin->update([
            'status' => 'ditolak',
            'id_peninjau' => $request->user()->id,
            'ditinjau_pada' => now(),
            'alasan_penolakan' => $data['alasan_penolakan'],
        ]);

        return back()->with('status', 'Pengajuan berhasil ditolak.');
    }
}
