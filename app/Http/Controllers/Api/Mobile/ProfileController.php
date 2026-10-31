<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\JadwalPelajaran;
use App\Models\LokasiSekolah;
use App\Models\Presensi;
use App\Support\PresensiFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProfileController extends Controller
{
    public function tampil(Request $request): JsonResponse
    {
        return response()->json([
            'pengguna' => PresensiFormatter::pengguna($request->user()->load('murid.kelas')),
        ]);
    }

    public function ubahPengaturanNotifikasi(Request $request): JsonResponse
    {
        $data = $request->validate(['notifikasi_aktif' => ['required', 'boolean']]);
        if ($request->user()->murid) {
            $request->user()->murid->update($data);
        } elseif ($request->user()->guru) {
            $request->user()->guru->update($data);
        }

        return $this->tampil($request);
    }

    public function jadwalHariIni(Request $request): JsonResponse
    {
        $murid = $request->user()->murid;
        $hariMap = [
            0 => 'minggu',
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ];

        $hariIniIndex = (int) now()->dayOfWeek;
        $hariIniStr = $hariMap[$hariIniIndex] ?? 'senin';

        $jadwalQuery = JadwalPelajaran::query()
            ->with(['mataPelajaran', 'guru.pengguna', 'lokasiSekolah', 'kelas'])
            ->where('aktif', true);

        if ($murid && $murid->id_kelas) {
            $jadwalQuery->where('id_kelas', $murid->id_kelas);
        }

        $jadwalList = $jadwalQuery
            ->where('hari', $hariIniStr)
            ->orderBy('jam_mulai')
            ->get();

        $presensiHariIni = [];
        if ($murid) {
            $presensiHariIni = Presensi::query()
                ->where('id_murid', $murid->id)
                ->whereDate('tanggal_presensi', today())
                ->get()
                ->keyBy('id_jadwal_pelajaran');
        }

        $lokasiDefault = LokasiSekolah::query()->where('aktif', true)->first();

        $dataJadwal = $jadwalList->map(function (JadwalPelajaran $j) use ($presensiHariIni, $lokasiDefault) {
            $presensi = $presensiHariIni->get($j->id);

            return [
                'id' => $j->id,
                'hari' => $j->hari,
                'jam_mulai' => substr($j->jam_mulai, 0, 5),
                'jam_selesai' => substr($j->jam_selesai, 0, 5),
                'toleransi_terlambat_menit' => $j->toleransi_terlambat_menit,
                'ruangan' => $j->ruangan,
                'mata_pelajaran' => [
                    'id' => $j->mataPelajaran?->id,
                    'kode' => $j->mataPelajaran?->kode_mapel,
                    'nama' => $j->mataPelajaran?->nama_mapel,
                ],
                'kelas' => [
                    'id' => $j->kelas?->id,
                    'nama' => $j->kelas?->nama_kelas,
                ],
                'guru' => [
                    'id' => $j->guru?->id,
                    'nama' => $j->guru?->pengguna?->nama,
                ],
                'lokasi_sekolah' => [
                    'id' => $j->lokasiSekolah?->id ?? $lokasiDefault?->id,
                    'nama' => $j->lokasiSekolah?->nama ?? $lokasiDefault?->nama,
                    'latitude' => (float) ($j->lokasiSekolah?->latitude ?? $lokasiDefault?->latitude ?? 0),
                    'longitude' => (float) ($j->lokasiSekolah?->longitude ?? $lokasiDefault?->longitude ?? 0),
                    'radius_meter' => (int) ($j->lokasiSekolah?->radius_meter ?? $lokasiDefault?->radius_meter ?? 100),
                ],
                'sudah_presensi' => $presensi !== null,
                'status_presensi' => $presensi?->status,
                'waktu_presensi' => $presensi?->waktu_presensi?->toIso8601String(),
            ];
        });

        return response()->json([
            'hari' => $hariIniStr,
            'tanggal' => today()->toDateString(),
            'lokasi_sekolah' => $lokasiDefault ? [
                'id' => $lokasiDefault->id,
                'nama' => $lokasiDefault->nama,
                'latitude' => (float) $lokasiDefault->latitude,
                'longitude' => (float) $lokasiDefault->longitude,
                'radius_meter' => $lokasiDefault->radius_meter,
            ] : null,
            'jadwal' => $dataJadwal,
        ]);
    }

    public function jadwalKerja(Request $request): JsonResponse
    {
        return $this->jadwalHariIni($request);
    }

    public function hariLibur(Request $request): JsonResponse
    {
        $bulan = $request->query('bulan', now()->format('Y-m'));
        $items = HariLibur::query()
            ->whereBetween('tanggal', [$bulan.'-01', Carbon::parse($bulan.'-01')->endOfMonth()->toDateString()])
            ->orderBy('tanggal')
            ->get()
            ->map(fn (HariLibur $hariLibur) => [
                'tanggal' => $hariLibur->tanggal->toDateString(),
                'nama' => $hariLibur->nama,
            ]);

        return response()->json(['data' => $items]);
    }
}
