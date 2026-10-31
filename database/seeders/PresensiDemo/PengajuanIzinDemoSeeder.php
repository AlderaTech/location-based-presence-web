<?php

namespace Database\Seeders\PresensiDemo;

use App\Models\JadwalPelajaran;
use App\Models\Murid;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PengajuanIzinDemoSeeder extends Seeder
{
    /**
     * @param  array<string, Murid>  $daftarMurid
     * @param  array<string, JadwalPelajaran>  $daftarJadwal
     */
    public function seed(array $daftarMurid, array $daftarJadwal, Pengguna $admin, Pengguna $guru): void
    {
        Storage::disk('public')->makeDirectory('dokumen-izin');

        // Mock document file
        $fileDummy = '%PDF-1.4 Mock Surat Keterangan Dokter untuk Pengujian Demo Presensi Murid';
        Storage::disk('public')->put('dokumen-izin/surat-dokter-demo.pdf', $fileDummy);
        Storage::disk('public')->put('dokumen-izin/surat-izin-ortu.pdf', $fileDummy);

        $jadwal = $daftarJadwal['hari_ini_mat'] ?? null;
        $dian = $daftarMurid['dian'] ?? null;
        $eko = $daftarMurid['eko'] ?? null;
        $fitri = $daftarMurid['fitri'] ?? null;

        // 1. Dian Sastro: Menunggu Review
        if ($dian) {
            $pengajuan = PengajuanIzin::query()->updateOrCreate(
                [
                    'id_murid' => $dian->id,
                    'tanggal_mulai' => today()->toDateString(),
                ],
                [
                    'id_pengguna' => $dian->id_pengguna,
                    'id_jadwal_pelajaran' => $jadwal?->id,
                    'jenis' => 'sakit',
                    'tanggal_selesai' => today()->toDateString(),
                    'alasan' => 'Demam tinggi dan flu, disarankan istirahat oleh dokter klinik.',
                    'status' => 'menunggu',
                ],
            );

            $pengajuan->dokumen()->firstOrCreate(
                ['path' => 'dokumen-izin/surat-dokter-demo.pdf'],
                [
                    'disk' => 'public',
                    'nama_asli' => 'surat-dokter-klinik-dian.pdf',
                    'tipe_mime' => 'application/pdf',
                    'ukuran' => 1024,
                ],
            );
        }

        // 2. Eko Kurniawan: Disetujui
        if ($eko) {
            $tglIzin = today()->subDays(2);
            $pengajuan = PengajuanIzin::query()->updateOrCreate(
                [
                    'id_murid' => $eko->id,
                    'tanggal_mulai' => $tglIzin->toDateString(),
                ],
                [
                    'id_pengguna' => $eko->id_pengguna,
                    'id_jadwal_pelajaran' => $jadwal?->id,
                    'jenis' => 'izin',
                    'tanggal_selesai' => $tglIzin->toDateString(),
                    'alasan' => 'Menghadiri acara pernikahan keluarga di luar kota.',
                    'status' => 'disetujui',
                    'id_peninjau' => $guru->id,
                    'ditinjau_pada' => $tglIzin->setTime(8, 0),
                ],
            );

            $pengajuan->dokumen()->firstOrCreate(
                ['path' => 'dokumen-izin/surat-izin-ortu.pdf'],
                [
                    'disk' => 'public',
                    'nama_asli' => 'surat-izin-orangtua-eko.pdf',
                    'tipe_mime' => 'application/pdf',
                    'ukuran' => 1024,
                ],
            );

            if ($jadwal) {
                Presensi::query()->updateOrCreate(
                    [
                        'id_murid' => $eko->id,
                        'id_jadwal_pelajaran' => $jadwal->id,
                        'tanggal_presensi' => $tglIzin->toDateString(),
                    ],
                    [
                        'id_pengguna' => $eko->id_pengguna,
                        'status' => 'izin',
                        'metode' => 'izin_disetujui',
                        'waktu_presensi' => $tglIzin->setTime(8, 0),
                        'catatan' => $pengajuan->alasan,
                        'id_pengoreksi' => $guru->id,
                        'alasan_koreksi' => 'Persetujuan pengajuan izin #'.$pengajuan->id,
                    ],
                );
            }
        }

        // 3. Fitri Handayani: Ditolak
        if ($fitri) {
            $tglTolak = today()->subDays(4);
            $pengajuan = PengajuanIzin::query()->updateOrCreate(
                [
                    'id_murid' => $fitri->id,
                    'tanggal_mulai' => $tglTolak->toDateString(),
                ],
                [
                    'id_pengguna' => $fitri->id_pengguna,
                    'id_jadwal_pelajaran' => $jadwal?->id,
                    'jenis' => 'izin',
                    'tanggal_selesai' => $tglTolak->toDateString(),
                    'alasan' => 'Ada acara pribadi.',
                    'status' => 'ditolak',
                    'id_peninjau' => $admin->id,
                    'ditinjau_pada' => $tglTolak->setTime(9, 30),
                    'alasan_penolakan' => 'Dokumen pendukung surat orang tua tidak dilampirkan.',
                ],
            );
        }
    }
}
