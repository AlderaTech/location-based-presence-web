<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Services\LayananPresensi;
use App\Support\FormatPresensi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PresensiController extends Controller
{
    public function hariIni(Request $request): JsonResponse
    {
        $presensi = Presensi::query()
            ->with(['lokasiSekolah', 'jadwalKerja'])
            ->where('id_guru', $request->user()->guru?->id)
            ->whereDate('tanggal_presensi', today())
            ->first();

        return response()->json([
            'presensi' => $presensi ? FormatPresensi::presensi($presensi) : null,
        ]);
    }

    public function catat(Request $request, LayananPresensi $layanan): JsonResponse
    {
        $data = $request->validate([
            'jenis' => ['required', 'in:masuk,pulang'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'akurasi' => ['nullable', 'integer', 'min:0'],
            'id_perangkat' => ['required', 'string', 'max:191'],
            'lokasi_palsu' => ['nullable', 'boolean'],
            'waktu_klien' => ['nullable', 'date'],
        ]);

        $presensi = $layanan->catat($request->user()->load('guru.jadwalKerjaBawaan'), $data);

        return response()->json([
            'pesan' => $data['jenis'] === 'masuk' ? 'Presensi masuk berhasil.' : 'Presensi pulang berhasil.',
            'presensi' => FormatPresensi::presensi($presensi),
        ]);
    }

    public function riwayat(Request $request): JsonResponse
    {
        $bulan = $request->query('bulan', now()->format('Y-m'));
        $guruId = $request->user()->guru?->id;
        $items = Presensi::query()
            ->with(['lokasiSekolah', 'jadwalKerja'])
            ->where('id_guru', $guruId)
            ->whereBetween('tanggal_presensi', [$bulan.'-01', Carbon::parse($bulan.'-01')->endOfMonth()->toDateString()])
            ->orderByDesc('tanggal_presensi')
            ->get()
            ->map(fn (Presensi $presensi) => FormatPresensi::presensi($presensi));

        return response()->json(['data' => $items]);
    }
}
