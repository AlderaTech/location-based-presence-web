<?php

namespace App\Support;

use App\Models\Pengguna;
use App\Models\Presensi;

class FormatPresensi
{
    public static function presensi(Presensi $presensi): array
    {
        return [
            'id' => $presensi->id,
            'tanggal' => $presensi->tanggal_presensi?->toDateString(),
            'status' => $presensi->status,
            'jam_masuk_pada' => $presensi->jam_masuk_pada?->toIso8601String(),
            'jam_pulang_pada' => $presensi->jam_pulang_pada?->toIso8601String(),
            'jarak_masuk_meter' => $presensi->jarak_masuk_meter,
            'jarak_pulang_meter' => $presensi->jarak_pulang_meter,
            'terdeteksi_lokasi_palsu' => $presensi->terdeteksi_lokasi_palsu,
            'catatan' => $presensi->catatan,
            'lokasi_sekolah' => $presensi->lokasiSekolah ? [
                'id' => $presensi->lokasiSekolah->id,
                'nama' => $presensi->lokasiSekolah->nama,
                'latitude' => (float) $presensi->lokasiSekolah->latitude,
                'longitude' => (float) $presensi->lokasiSekolah->longitude,
                'radius_meter' => $presensi->lokasiSekolah->radius_meter,
            ] : null,
            'jadwal_kerja' => $presensi->jadwalKerja ? [
                'id' => $presensi->jadwalKerja->id,
                'nama' => $presensi->jadwalKerja->nama,
                'jam_masuk' => $presensi->jadwalKerja->jam_masuk,
                'jam_pulang' => $presensi->jadwalKerja->jam_pulang,
                'toleransi_terlambat_menit' => $presensi->jadwalKerja->toleransi_terlambat_menit,
            ] : null,
        ];
    }

    public static function pengguna(Pengguna $pengguna): array
    {
        return [
            'id' => $pengguna->id,
            'nama' => $pengguna->nama,
            'email' => $pengguna->email,
            'peran' => $pengguna->peran,
            'nomor_pegawai' => $pengguna->nomor_pegawai,
            'telepon' => $pengguna->telepon,
            'guru' => $pengguna->guru ? [
                'id' => $pengguna->guru->id,
                'nip' => $pengguna->guru->nip,
                'mata_pelajaran' => $pengguna->guru->mata_pelajaran,
                'jabatan' => $pengguna->guru->jabatan,
                'jadwal_kerja' => $pengguna->guru->jadwal_kerja,
                'notifikasi_aktif' => $pengguna->guru->notifikasi_aktif,
            ] : null,
        ];
    }
}
