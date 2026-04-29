<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Models\LokasiSekolah;
use App\Support\FormatPresensi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ProfileController extends Controller
{
    public function tampil(Request $request): JsonResponse
    {
        return response()->json(['pengguna' => FormatPresensi::pengguna($request->user()->load('guru.jadwalKerjaBawaan'))]);
    }

    public function ubahPengaturanNotifikasi(Request $request): JsonResponse
    {
        $data = $request->validate(['notifikasi_aktif' => ['required', 'boolean']]);
        $request->user()->guru->update($data);

        return $this->tampil($request);
    }

    public function jadwalKerja(Request $request): JsonResponse
    {
        $guru = $request->user()->guru()->with('jadwalKerjaBawaan')->first();
        $jadwalKerja = $guru?->jadwalKerjaBawaan;
        $lokasi = LokasiSekolah::query()->where('aktif', true)->first();

        return response()->json([
            'jadwal_kerja' => $jadwalKerja ? [
                'id' => $jadwalKerja->id,
                'nama' => $jadwalKerja->nama,
                'jam_masuk' => $jadwalKerja->jam_masuk,
                'jam_pulang' => $jadwalKerja->jam_pulang,
                'toleransi_terlambat_menit' => $jadwalKerja->toleransi_terlambat_menit,
            ] : null,
            'lokasi_sekolah' => $lokasi ? [
                'id' => $lokasi->id,
                'nama' => $lokasi->nama,
                'latitude' => (float) $lokasi->latitude,
                'longitude' => (float) $lokasi->longitude,
                'radius_meter' => $lokasi->radius_meter,
            ] : null,
        ]);
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
                'jenis' => $hariLibur->jenis,
            ]);

        return response()->json(['data' => $items]);
    }
}
