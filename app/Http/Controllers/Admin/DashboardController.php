<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $hariIni = today();
        $presensiHariIni = Presensi::query()
            ->whereDate('tanggal_presensi', $hariIni)
            ->get();

        $ringkasan = [
            'hadir' => $presensiHariIni->where('status', 'hadir')->count(),
            'terlambat' => $presensiHariIni->where('status', 'terlambat')->count(),
            'izin' => $presensiHariIni->whereIn('status', ['izin', 'sakit', 'cuti'])->count(),
            'alpha' => max(Guru::query()->where('aktif', true)->count() - $presensiHariIni->count(), 0),
        ];

        $bulanan = Presensi::query()
            ->whereBetween('tanggal_presensi', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->get()
            ->groupBy('status')
            ->map->count();

        return view('admin.dashboard', [
            'ringkasan' => $ringkasan,
            'bulanan' => $bulanan,
            'pengajuanMenunggu' => PengajuanIzin::query()->where('status', 'menunggu')->count(),
            'jumlahGuru' => Guru::query()->where('aktif', true)->count(),
            'presensiTerbaru' => Presensi::query()
                ->with('pengguna', 'jadwalKerja')
                ->latest('tanggal_presensi')
                ->latest('dibuat_pada')
                ->limit(8)
                ->get(),
        ]);
    }
}
