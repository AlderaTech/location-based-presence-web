<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $hariIni = today();
        $awalBulan = CarbonImmutable::now()->startOfMonth();
        $akhirGrafik = CarbonImmutable::now();
        $jumlahGuru = Guru::query()->where('aktif', true)->count();
        $presensiHariIni = Presensi::query()
            ->whereDate('tanggal_presensi', $hariIni)
            ->get();

        $ringkasan = [
            'hadir' => $presensiHariIni->where('status', 'hadir')->count(),
            'terlambat' => $presensiHariIni->where('status', 'terlambat')->count(),
            'izin' => $presensiHariIni->whereIn('status', ['izin', 'sakit', 'cuti'])->count(),
            'alpha' => max($jumlahGuru - $presensiHariIni->count(), 0),
        ];

        $presensiBulanIni = Presensi::query()
            ->whereBetween('tanggal_presensi', [$awalBulan->toDateString(), $akhirGrafik->toDateString()])
            ->get()
            ->groupBy(fn (Presensi $presensi) => $presensi->tanggal_presensi?->toDateString());

        $bulanan = $presensiBulanIni->values()->collapse()->groupBy('status')->map->count();
        $hariDalamBulan = collect(CarbonPeriod::create($awalBulan, $akhirGrafik));
        $trenHarian = [
            'labels' => $hariDalamBulan->map(fn ($tanggal) => $tanggal->format('d M'))->values(),
            'hadir' => $hariDalamBulan->map(fn ($tanggal) => $presensiBulanIni
                ->get($tanggal->toDateString(), collect())
                ->where('status', 'hadir')
                ->count())->values(),
            'terlambat' => $hariDalamBulan->map(fn ($tanggal) => $presensiBulanIni
                ->get($tanggal->toDateString(), collect())
                ->where('status', 'terlambat')
                ->count())->values(),
            'izin' => $hariDalamBulan->map(fn ($tanggal) => $presensiBulanIni
                ->get($tanggal->toDateString(), collect())
                ->whereIn('status', ['izin', 'sakit', 'cuti'])
                ->count())->values(),
        ];

        $totalHadir = $ringkasan['hadir'] + $ringkasan['terlambat'];
        $tingkatKehadiran = $jumlahGuru > 0 ? round(($totalHadir / $jumlahGuru) * 100) : 0;
        $pengajuanRingkasan = PengajuanIzin::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'ringkasan' => $ringkasan,
            'bulanan' => $bulanan,
            'trenHarian' => $trenHarian,
            'tingkatKehadiran' => $tingkatKehadiran,
            'pengajuanRingkasan' => $pengajuanRingkasan,
            'pengajuanMenunggu' => (int) ($pengajuanRingkasan['menunggu'] ?? 0),
            'jumlahGuru' => $jumlahGuru,
            'guruBelumPresensi' => Guru::query()
                ->with('pengguna')
                ->where('aktif', true)
                ->whereDoesntHave('presensi', fn ($query) => $query->whereDate('tanggal_presensi', $hariIni))
                ->orderBy('id')
                ->limit(6)
                ->get(),
            'pengajuanMenungguTerbaru' => PengajuanIzin::query()
                ->with('pengguna')
                ->where('status', 'menunggu')
                ->latest('dibuat_pada')
                ->limit(5)
                ->get(),
            'presensiTerbaru' => Presensi::query()
                ->with('pengguna', 'jadwalKerja', 'lokasiSekolah')
                ->latest('tanggal_presensi')
                ->latest('dibuat_pada')
                ->limit(6)
                ->get(),
        ]);
    }
}
