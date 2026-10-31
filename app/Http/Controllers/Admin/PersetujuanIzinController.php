<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\LogAuditPresensi;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Support\AdminSortHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PersetujuanIzinController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isGuru = $user->adalahGuru();
        $guruId = $user->guru?->id;

        $baseQuery = PengajuanIzin::query()
            ->when($request->query('tanggal_mulai'), fn ($query, $tanggal) => $query->whereDate('tanggal_selesai', '>=', $tanggal))
            ->when($request->query('tanggal_selesai'), fn ($query, $tanggal) => $query->whereDate('tanggal_mulai', '<=', $tanggal))
            ->when($request->query('q'), function ($query, string $q): void {
                $query->where(function ($query) use ($q): void {
                    $query
                        ->whereHas('pengguna', fn ($pengguna) => $pengguna
                            ->where('nama', 'like', '%'.$q.'%')
                            ->orWhere('email', 'like', '%'.$q.'%'))
                        ->orWhereHas('murid', fn ($murid) => $murid
                            ->where('nis', 'like', '%'.$q.'%')
                            ->orWhereHas('kelas', fn ($k) => $k->where('nama_kelas', 'like', '%'.$q.'%')));
                });
            });

        // Jika Guru, filter izin untuk kelas/jadwal yang diampunya
        if ($isGuru && $guruId) {
            $kelasGuru = JadwalPelajaran::query()->where('id_guru', $guruId)->pluck('id_kelas')->unique();
            $baseQuery->whereHas('murid', fn ($m) => $m->whereIn('id_kelas', $kelasGuru));
        }

        $ringkasanStatus = (clone $baseQuery)
            ->reorder()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $ringkasanJenis = (clone $baseQuery)
            ->reorder()
            ->selectRaw('jenis, count(*) as total')
            ->groupBy('jenis')
            ->pluck('total', 'jenis');

        $totalPengajuan = (clone $baseQuery)->count();

        $query = (clone $baseQuery)
            ->with(['pengguna', 'murid.kelas', 'jadwalPelajaran.mataPelajaran', 'dokumen', 'peninjau'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('jenis'), fn ($query, $jenis) => $query->where('jenis', $jenis));

        AdminSortHelper::terapkan($query, $request, [
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

        $pengajuanIzin = $query->orderByDesc('pengajuan_izin.id')->paginate(12)->withQueryString();

        return view('admin.pengajuan-izin.index', [
            'pengajuanIzin' => $pengajuanIzin,
            'ringkasanStatus' => $ringkasanStatus,
            'ringkasanJenis' => $ringkasanJenis,
            'totalPengajuan' => $totalPengajuan,
        ]);
    }

    public function setujui(Request $request, PengajuanIzin $pengajuanIzin): RedirectResponse
    {
        if ($request->user()->adalahGuru()) {
            $guruId = $request->user()->guru?->id;
            $kelasGuru = JadwalPelajaran::query()->where('id_guru', $guruId)->pluck('id_kelas')->unique();
            if (! $kelasGuru->contains($pengajuanIzin->murid?->id_kelas)) {
                abort(403, 'Anda tidak berhak memproses izin murid di luar kelas yang Anda ampu.');
            }
        }

        DB::transaction(function () use ($request, $pengajuanIzin) {
            $pengajuanIzin->update([
                'status' => 'disetujui',
                'id_peninjau' => $request->user()->id,
                'ditinjau_pada' => now(),
                'alasan_penolakan' => null,
            ]);

            $murid = $pengajuanIzin->murid;
            $hariMap = [
                0 => 'minggu',
                1 => 'senin',
                2 => 'selasa',
                3 => 'rabu',
                4 => 'kamis',
                5 => 'jumat',
                6 => 'sabtu',
            ];

            foreach (CarbonPeriod::create($pengajuanIzin->tanggal_mulai, $pengajuanIzin->tanggal_selesai) as $tanggal) {
                $hariStr = $hariMap[(int) $tanggal->dayOfWeek] ?? 'senin';

                $jadwalQuery = JadwalPelajaran::query()->where('aktif', true);
                if ($pengajuanIzin->id_jadwal_pelajaran) {
                    $jadwalQuery->where('id', $pengajuanIzin->id_jadwal_pelajaran);
                } elseif ($murid && $murid->id_kelas) {
                    $jadwalQuery->where('id_kelas', $murid->id_kelas)->where('hari', $hariStr);
                }

                $jadwalList = $jadwalQuery->get();

                foreach ($jadwalList as $jadwalItem) {
                    $presensi = Presensi::updateOrCreate(
                        [
                            'id_murid' => $murid->id,
                            'id_jadwal_pelajaran' => $jadwalItem->id,
                            'tanggal_presensi' => $tanggal->toDateString(),
                        ],
                        [
                            'id_pengguna' => $pengajuanIzin->id_pengguna,
                            'status' => $pengajuanIzin->jenis,
                            'metode' => 'izin_disetujui',
                            'waktu_presensi' => now(),
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
                        'payload' => [
                            'id_pengajuan_izin' => $pengajuanIzin->id,
                            'jenis' => $pengajuanIzin->jenis,
                            'id_jadwal' => $jadwalItem->id,
                        ],
                        'dibuat_pada' => now(),
                    ]);
                }
            }
        });

        return back()->with('status', 'Pengajuan izin/sakit berhasil disetujui dan status presensi diperbarui.');
    }

    public function tolak(Request $request, PengajuanIzin $pengajuanIzin): RedirectResponse
    {
        if ($request->user()->adalahGuru()) {
            $guruId = $request->user()->guru?->id;
            $kelasGuru = JadwalPelajaran::query()->where('id_guru', $guruId)->pluck('id_kelas')->unique();
            if (! $kelasGuru->contains($pengajuanIzin->murid?->id_kelas)) {
                abort(403, 'Anda tidak berhak memproses izin murid di luar kelas yang Anda ampu.');
            }
        }

        $data = $request->validate(['alasan_penolakan' => ['required', 'string', 'max:2000']]);
        $pengajuanIzin->update([
            'status' => 'ditolak',
            'id_peninjau' => $request->user()->id,
            'ditinjau_pada' => now(),
            'alasan_penolakan' => $data['alasan_penolakan'],
        ]);

        return back()->with('status', 'Pengajuan izin/sakit berhasil ditolak.');
    }
}
