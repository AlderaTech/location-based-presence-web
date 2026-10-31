<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\LogAuditPresensi;
use App\Models\MataPelajaran;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Support\AdminSortHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PresensiController extends Controller
{
    public function index(Request $request): View
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
        ], 'tanggal_presensi', 'desc');

        $presensi = $query->paginate(20)->withQueryString();
        $daftarKelas = Kelas::query()->where('aktif', true)->orderBy('nama_kelas')->get();
        $daftarMapel = MataPelajaran::query()->where('aktif', true)->orderBy('nama_mapel')->get();
        $daftarGuru = Guru::query()->with('pengguna')->where('aktif', true)->get();

        return view('admin.presensi.index', compact('presensi', 'daftarKelas', 'daftarMapel', 'daftarGuru'));
    }

    public function edit(Presensi $presensi): View
    {
        $presensi->load(['pengguna', 'murid.kelas', 'jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.kelas', 'jadwalPelajaran.guru.pengguna']);

        return view('admin.presensi.edit', compact('presensi'));
    }

    public function update(Request $request, Presensi $presensi): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:hadir,terlambat,izin,sakit,alfa'],
            'alasan_koreksi' => ['required', 'string', 'max:500'],
        ]);

        $statusLama = $presensi->status;
        $presensi->update([
            'status' => $data['status'],
            'id_pengoreksi' => $request->user()->id,
            'alasan_koreksi' => $data['alasan_koreksi'],
        ]);

        LogAuditPresensi::create([
            'id_presensi' => $presensi->id,
            'id_pengguna' => $presensi->id_pengguna,
            'id_aktor' => $request->user()->id,
            'aksi' => 'koreksi_status_presensi',
            'payload' => [
                'status_lama' => $statusLama,
                'status_baru' => $data['status'],
                'alasan' => $data['alasan_koreksi'],
            ],
            'dibuat_pada' => now(),
        ]);

        return redirect()->route('admin.presensi.index')->with('status', 'Data presensi berhasil diperbarui.');
    }
}
