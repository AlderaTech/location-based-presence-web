<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAuditPresensi;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\CarbonPeriod;
use Illuminate\View\View;

class PersetujuanIzinController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.pengajuan-izin.index', [
            'pengajuanIzin' => PengajuanIzin::query()
                ->with(['pengguna', 'guru', 'dokumen', 'peninjau'])
                ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
                ->latest('dibuat_pada')
                ->paginate(20)
                ->withQueryString(),
        ]);
    }

    public function setujui(Request $request, PengajuanIzin $pengajuanIzin): RedirectResponse
    {
        $pengajuanIzin->update([
            'status' => 'disetujui',
            'ditinjau_oleh' => $request->user()->id,
            'ditinjau_pada' => now(),
            'alasan_penolakan' => null,
        ]);

        foreach (CarbonPeriod::create($pengajuanIzin->tanggal_mulai, $pengajuanIzin->tanggal_selesai) as $tanggal) {
            $presensi = Presensi::updateOrCreate(
                [
                    'guru_id' => $pengajuanIzin->guru_id,
                    'tanggal_presensi' => $tanggal->toDateString(),
                ],
                [
                    'pengguna_id' => $pengajuanIzin->pengguna_id,
                    'status' => $pengajuanIzin->jenis,
                    'catatan' => $pengajuanIzin->alasan,
                    'dikoreksi_oleh' => $request->user()->id,
                    'alasan_koreksi' => 'Persetujuan pengajuan #'.$pengajuanIzin->id,
                ],
            );

            LogAuditPresensi::create([
                'presensi_id' => $presensi->id,
                'pengguna_id' => $pengajuanIzin->pengguna_id,
                'aktor_id' => $request->user()->id,
                'aksi' => 'pengajuan_izin_disetujui',
                'payload' => ['pengajuan_izin_id' => $pengajuanIzin->id, 'jenis' => $pengajuanIzin->jenis],
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
            'ditinjau_oleh' => $request->user()->id,
            'ditinjau_pada' => now(),
            'alasan_penolakan' => $data['alasan_penolakan'],
        ]);

        return back()->with('status', 'Pengajuan berhasil ditolak.');
    }
}
