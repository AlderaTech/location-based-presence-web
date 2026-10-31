<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Services\PresensiService;
use App\Support\PresensiFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PresensiController extends Controller
{
    public function hariIni(Request $request): JsonResponse
    {
        $muridId = $request->user()->murid?->id;
        $items = Presensi::query()
            ->with(['lokasiSekolah', 'jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.guru.pengguna', 'jadwalPelajaran.kelas'])
            ->where('id_murid', $muridId)
            ->whereDate('tanggal_presensi', today())
            ->orderByDesc('waktu_presensi')
            ->get();

        $terakhir = $items->first();

        return response()->json([
            'presensi' => $terakhir ? PresensiFormatter::presensi($terakhir) : null,
            'daftar_presensi' => $items->map(fn (Presensi $p) => PresensiFormatter::presensi($p)),
        ]);
    }

    public function catat(Request $request, PresensiService $presensiService): JsonResponse
    {
        $data = $request->validate([
            'kode_qr' => ['nullable', 'string', 'max:2000'],
            'id_jadwal_pelajaran' => ['nullable', 'integer'],
            'id_mata_pelajaran' => ['nullable', 'integer'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'akurasi' => ['nullable', 'integer', 'min:0'],
            'id_perangkat' => ['required', 'string', 'max:191'],
            'lokasi_palsu' => ['nullable', 'boolean'],
            'waktu_klien' => ['nullable', 'string'],
        ]);

        if (empty($data['kode_qr']) && empty($data['id_jadwal_pelajaran'])) {
            return response()->json([
                'pesan' => 'Kode QR atau jadwal pelajaran harus disertakan.',
            ], 422);
        }

        $presensi = $presensiService->catat($request->user()->load('murid.kelas'), $data);

        $namaMapel = $presensi->jadwalPelajaran?->mataPelajaran?->nama_mapel ?? 'Pelajaran';
        $statusStr = ucfirst($presensi->status);

        return response()->json([
            'pesan' => "Presensi $namaMapel berhasil dicatat ($statusStr).",
            'presensi' => PresensiFormatter::presensi($presensi),
        ]);
    }

    public function riwayat(Request $request): JsonResponse
    {
        $bulan = $request->query('bulan', now()->format('Y-m'));
        $muridId = $request->user()->murid?->id;

        $items = Presensi::query()
            ->with(['lokasiSekolah', 'jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.guru.pengguna', 'jadwalPelajaran.kelas'])
            ->where('id_murid', $muridId)
            ->whereBetween('tanggal_presensi', [$bulan.'-01', Carbon::parse($bulan.'-01')->endOfMonth()->toDateString()])
            ->orderByDesc('tanggal_presensi')
            ->orderByDesc('waktu_presensi')
            ->get()
            ->map(fn (Presensi $presensi) => PresensiFormatter::presensi($presensi));

        return response()->json(['data' => $items]);
    }
}
