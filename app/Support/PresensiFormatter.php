<?php

namespace App\Support;

use App\Models\Pengguna;
use App\Models\Presensi;

class PresensiFormatter
{
    /**
     * Pilihan status kehadiran untuk dropdown/filter.
     */
    public static function daftarPilihanStatus(): array
    {
        return [
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alfa' => 'Alfa',
        ];
    }

    /**
     * Pilihan metode presensi untuk dropdown/filter.
     */
    public static function daftarPilihanMetode(): array
    {
        return [
            'qr_lokasi' => 'Scan QR & GPS',
            'lokasi' => 'Radius GPS',
            'izin_disetujui' => 'Izin Disetujui',
            'manual' => 'Koreksi Manual',
        ];
    }

    /**
     * Konversi metode presensi teknis (e.g. qr_lokasi) ke label ramah pengguna.
     */
    public static function labelMetode(?string $metode): string
    {
        if ($metode === null || $metode === '' || $metode === '-') {
            return '-';
        }

        return match (strtolower(trim($metode))) {
            'qr_lokasi' => 'Scan QR & GPS',
            'lokasi' => 'Radius GPS',
            'izin_disetujui' => 'Izin Disetujui',
            'manual' => 'Koreksi Manual',
            default => ucwords(str_replace('_', ' ', $metode)),
        };
    }

    /**
     * CSS class badge untuk metode presensi.
     */
    public static function badgeMetodeClass(?string $metode): string
    {
        return match (strtolower((string) $metode)) {
            'qr_lokasi' => 'bg-teal-50 text-teal-800 ring-1 ring-teal-600/20',
            'lokasi' => 'bg-sky-50 text-sky-800 ring-1 ring-sky-600/20',
            'izin_disetujui' => 'bg-blue-50 text-blue-800 ring-1 ring-blue-600/20',
            'manual' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-600/20',
            default => 'bg-slate-50 text-slate-700 ring-1 ring-slate-300',
        };
    }

    /**
     * Ikon Lucide untuk metode presensi.
     */
    public static function iconMetode(?string $metode): string
    {
        return match (strtolower((string) $metode)) {
            'qr_lokasi' => 'qr-code',
            'lokasi' => 'map-pin',
            'izin_disetujui' => 'file-check-2',
            'manual' => 'clipboard-check',
            default => 'check',
        };
    }

    /**
     * Konversi status presensi teknis (hadir, terlambat, izin, sakit, alfa) ke label ramah pengguna.
     */
    public static function labelStatus(?string $status): string
    {
        if ($status === null || $status === '' || $status === '-') {
            return '-';
        }

        return match (strtolower(trim($status))) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alfa', 'alpa' => 'Alfa',
            'belum', 'belum_hadir' => 'Belum Hadir',
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }

    /**
     * CSS class badge untuk status presensi atau pengajuan.
     */
    public static function badgeStatusClass(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'hadir' => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-600/20',
            'terlambat' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-600/20',
            'izin' => 'bg-sky-50 text-sky-800 ring-1 ring-sky-600/20',
            'sakit' => 'bg-blue-50 text-blue-800 ring-1 ring-blue-600/20',
            'alfa', 'alpa' => 'bg-rose-50 text-rose-800 ring-1 ring-rose-600/20',
            'belum', 'belum_hadir' => 'bg-slate-100 text-slate-500 ring-1 ring-slate-200',
            'menunggu' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-600/20',
            'disetujui' => 'bg-emerald-50 text-emerald-800 ring-1 ring-emerald-600/20',
            'ditolak' => 'bg-rose-50 text-rose-800 ring-1 ring-rose-600/20',
            default => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
        };
    }

    /**
     * Label ramah pengguna untuk jenis izin.
     */
    public static function labelJenisIzin(?string $jenis): string
    {
        return match (strtolower((string) $jenis)) {
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            default => ucwords(str_replace('_', ' ', (string) $jenis)),
        };
    }

    /**
     * CSS class badge untuk jenis izin.
     */
    public static function badgeJenisIzinClass(?string $jenis): string
    {
        return match (strtolower((string) $jenis)) {
            'izin' => 'bg-sky-50 text-sky-800 ring-1 ring-sky-600/20',
            'sakit' => 'bg-violet-50 text-violet-800 ring-1 ring-violet-600/20',
            default => 'bg-slate-50 text-slate-700 ring-1 ring-slate-200',
        };
    }

    /**
     * Label ramah pengguna untuk peran.
     */
    public static function labelPeran(?string $peran): string
    {
        return match (strtolower((string) $peran)) {
            'admin' => 'Admin',
            'guru' => 'Guru',
            'murid' => 'Murid',
            default => ucwords(str_replace('_', ' ', (string) $peran)),
        };
    }

    /**
     * Label status akun aktif / nonaktif.
     */
    public static function labelStatusAkun(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'aktif' => 'Aktif',
            'nonaktif' => 'Nonaktif',
            default => ucwords(str_replace('_', ' ', (string) $status)),
        };
    }

    public static function presensi(Presensi $presensi): array
    {
        $jadwal = $presensi->jadwalPelajaran;

        return [
            'id' => $presensi->id,
            'tanggal' => $presensi->tanggal_presensi?->toDateString(),
            'waktu_presensi' => $presensi->waktu_presensi?->toIso8601String(),
            'status' => $presensi->status,
            'label_status' => self::labelStatus($presensi->status),
            'metode' => $presensi->metode,
            'label_metode' => self::labelMetode($presensi->metode),
            'jarak_meter' => $presensi->jarak_meter,
            'terdeteksi_lokasi_palsu' => (bool) $presensi->terdeteksi_lokasi_palsu,
            'catatan' => $presensi->catatan,
            'mata_pelajaran' => $jadwal?->mataPelajaran ? [
                'id' => $jadwal->mataPelajaran->id,
                'kode' => $jadwal->mataPelajaran->kode_mapel,
                'nama' => $jadwal->mataPelajaran->nama_mapel,
            ] : null,
            'kelas' => $jadwal?->kelas ? [
                'id' => $jadwal->kelas->id,
                'nama' => $jadwal->kelas->nama_kelas,
            ] : null,
            'guru' => $jadwal?->guru?->pengguna ? [
                'id' => $jadwal->guru->id,
                'nama' => $jadwal->guru->pengguna->nama,
            ] : null,
            'lokasi_sekolah' => $presensi->lokasiSekolah ? [
                'id' => $presensi->lokasiSekolah->id,
                'nama' => $presensi->lokasiSekolah->nama,
                'latitude' => (float) $presensi->lokasiSekolah->latitude,
                'longitude' => (float) $presensi->lokasiSekolah->longitude,
                'radius_meter' => $presensi->lokasiSekolah->radius_meter,
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
            'murid' => $pengguna->murid ? [
                'id' => $pengguna->murid->id,
                'nis' => $pengguna->murid->nis,
                'nisn' => $pengguna->murid->nisn,
                'jenis_kelamin' => $pengguna->murid->jenis_kelamin,
                'telepon_wali' => $pengguna->murid->telepon_wali,
                'alamat' => $pengguna->murid->alamat,
                'notifikasi_aktif' => (bool) ($pengguna->murid->notifikasi_aktif ?? true),
                'kelas' => $pengguna->murid->kelas ? [
                    'id' => $pengguna->murid->kelas->id,
                    'nama' => $pengguna->murid->kelas->nama_kelas,
                    'tingkat' => $pengguna->murid->kelas->tingkat,
                ] : null,
            ] : null,
            'guru' => $pengguna->guru ? [
                'id' => $pengguna->guru->id,
                'nip' => $pengguna->guru->nip,
                'mata_pelajaran' => $pengguna->guru->mata_pelajaran,
                'jabatan' => $pengguna->guru->jabatan,
                'notifikasi_aktif' => (bool) $pengguna->guru->notifikasi_aktif,
            ] : null,
        ];
    }
}
