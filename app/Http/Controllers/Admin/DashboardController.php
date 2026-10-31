<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Murid;
use App\Models\PengajuanIzin;
use App\Models\Presensi;
use App\Models\SesiPresensi;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $isGuru = $user->adalahGuru();
        $guruId = $user->guru?->id;

        $hariIni = today();
        $hariMap = [
            0 => 'minggu',
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ];
        $hariIniStr = $hariMap[(int) now()->dayOfWeek] ?? 'senin';

        $jumlahMurid = Murid::query()->where('aktif', true)->count();
        $jumlahGuru = Guru::query()->where('aktif', true)->count();
        $jumlahKelas = Kelas::query()->where('aktif', true)->count();
        $jumlahMapel = MataPelajaran::query()->where('aktif', true)->count();

        // Presensi query
        $presensiHariIniQuery = Presensi::query()->whereDate('tanggal_presensi', $hariIni);
        if ($isGuru && $guruId) {
            $presensiHariIniQuery->whereHas('jadwalPelajaran', fn ($j) => $j->where('id_guru', $guruId));
        }
        $presensiHariIni = $presensiHariIniQuery->get();

        $ringkasan = [
            'hadir' => $presensiHariIni->where('status', 'hadir')->count(),
            'terlambat' => $presensiHariIni->where('status', 'terlambat')->count(),
            'izin' => $presensiHariIni->where('status', 'izin')->count(),
            'sakit' => $presensiHariIni->where('status', 'sakit')->count(),
            'alfa' => $presensiHariIni->where('status', 'alfa')->count(),
        ];

        // Tren presensi 7 hari terakhir
        $awalGrafik = CarbonImmutable::now()->subDays(6);
        $akhirGrafik = CarbonImmutable::now();
        $presensiPeriode = Presensi::query()
            ->whereBetween('tanggal_presensi', [$awalGrafik->toDateString(), $akhirGrafik->toDateString()]);
        if ($isGuru && $guruId) {
            $presensiPeriode->whereHas('jadwalPelajaran', fn ($j) => $j->where('id_guru', $guruId));
        }
        $presensiGrouped = $presensiPeriode->get()->groupBy(fn (Presensi $p) => $p->tanggal_presensi?->toDateString());

        $hariDalamPeriode = collect(CarbonPeriod::create($awalGrafik, $akhirGrafik));
        $trenHarian = [
            'labels' => $hariDalamPeriode->map(fn ($tanggal) => $tanggal->format('d M'))->values(),
            'hadir' => $hariDalamPeriode->map(fn ($tanggal) => $presensiGrouped
                ->get($tanggal->toDateString(), collect())
                ->where('status', 'hadir')
                ->count())->values(),
            'terlambat' => $hariDalamPeriode->map(fn ($tanggal) => $presensiGrouped
                ->get($tanggal->toDateString(), collect())
                ->where('status', 'terlambat')
                ->count())->values(),
            'izin' => $hariDalamPeriode->map(fn ($tanggal) => $presensiGrouped
                ->get($tanggal->toDateString(), collect())
                ->whereIn('status', ['izin', 'sakit'])
                ->count())->values(),
        ];

        $pengajuanMenungguQuery = PengajuanIzin::query()->where('status', 'menunggu');
        if ($isGuru && $guruId) {
            $kelasGuru = JadwalPelajaran::query()->where('id_guru', $guruId)->pluck('id_kelas')->unique();
            $pengajuanMenungguQuery->whereHas('murid', fn ($m) => $m->whereIn('id_kelas', $kelasGuru));
        }
        $pengajuanMenunggu = $pengajuanMenungguQuery->count();
        $pengajuanMenungguTerbaru = (clone $pengajuanMenungguQuery)->with(['pengguna', 'murid.kelas'])->latest('dibuat_pada')->limit(5)->get();

        // Presensi terbaru
        $presensiTerbaruQuery = Presensi::query()
            ->with(['pengguna', 'murid.kelas', 'jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.kelas', 'jadwalPelajaran.guru.pengguna'])
            ->latest('waktu_presensi')
            ->limit(8);
        if ($isGuru && $guruId) {
            $presensiTerbaruQuery->whereHas('jadwalPelajaran', fn ($j) => $j->where('id_guru', $guruId));
        }
        $presensiTerbaru = $presensiTerbaruQuery->get();

        // Khusus Guru: Jadwal hari ini & Sesi Aktif
        $jadwalGuruHariIni = collect();
        $sesiAktifGuru = collect();
        if ($isGuru && $guruId) {
            $jadwalGuruHariIni = JadwalPelajaran::query()
                ->with(['mataPelajaran', 'kelas', 'lokasiSekolah'])
                ->where('id_guru', $guruId)
                ->where('hari', $hariIniStr)
                ->where('aktif', true)
                ->orderBy('jam_mulai')
                ->get();

            $sesiAktifGuru = SesiPresensi::query()
                ->with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.kelas'])
                ->where('id_guru', $guruId)
                ->whereDate('tanggal', $hariIni)
                ->where('status', 'aktif')
                ->get();
        }

        return view('admin.dashboard', [
            'isGuru' => $isGuru,
            'ringkasan' => $ringkasan,
            'trenHarian' => $trenHarian,
            'jumlahMurid' => $jumlahMurid,
            'jumlahGuru' => $jumlahGuru,
            'jumlahKelas' => $jumlahKelas,
            'jumlahMapel' => $jumlahMapel,
            'pengajuanMenunggu' => $pengajuanMenunggu,
            'pengajuanMenungguTerbaru' => $pengajuanMenungguTerbaru,
            'presensiTerbaru' => $presensiTerbaru,
            'jadwalGuruHariIni' => $jadwalGuruHariIni,
            'sesiAktifGuru' => $sesiAktifGuru,
        ]);
    }
}
