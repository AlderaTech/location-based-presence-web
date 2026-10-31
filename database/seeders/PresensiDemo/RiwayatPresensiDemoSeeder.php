<?php

namespace Database\Seeders\PresensiDemo;

use App\Models\JadwalPelajaran;
use App\Models\LokasiSekolah;
use App\Models\Murid;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class RiwayatPresensiDemoSeeder extends Seeder
{
    /**
     * @param  array<string, Murid>  $daftarMurid
     * @param  array<string, JadwalPelajaran>  $daftarJadwal
     */
    public function seed(array $daftarMurid, array $daftarJadwal, Pengguna $admin, LokasiSekolah $lokasi): void
    {
        $muridMipa1 = collect($daftarMurid)->filter(fn (Murid $m) => $m->kelas?->nama_kelas === 'X-MIPA-1')->values();
        $jadwalUtama = $daftarJadwal['hari_ini_mat'] ?? null;

        if (! $jadwalUtama) {
            return;
        }

        // 1. Presensi Hari Ini untuk beberapa murid di X-MIPA-1
        // Murid 1 (Bima): Hadir tepat waktu
        if ($m1 = $muridMipa1->get(1)) {
            Presensi::query()->updateOrCreate(
                [
                    'id_murid' => $m1->id,
                    'id_jadwal_pelajaran' => $jadwalUtama->id,
                    'tanggal_presensi' => today()->toDateString(),
                ],
                [
                    'id_pengguna' => $m1->id_pengguna,
                    'id_lokasi_sekolah' => $lokasi->id,
                    'waktu_presensi' => today()->setTime(7, 28),
                    'status' => 'hadir',
                    'metode' => 'qr_lokasi',
                    'latitude' => (float) $lokasi->latitude + 0.0001,
                    'longitude' => (float) $lokasi->longitude + 0.0001,
                    'akurasi' => 5,
                    'jarak_meter' => 15,
                    'terdeteksi_lokasi_palsu' => false,
                    'id_perangkat' => 'demo-device-'.$m1->nis,
                ],
            );
        }

        // Murid 2 (Citra): Terlambat
        if ($m2 = $muridMipa1->get(2)) {
            Presensi::query()->updateOrCreate(
                [
                    'id_murid' => $m2->id,
                    'id_jadwal_pelajaran' => $jadwalUtama->id,
                    'tanggal_presensi' => today()->toDateString(),
                ],
                [
                    'id_pengguna' => $m2->id_pengguna,
                    'id_lokasi_sekolah' => $lokasi->id,
                    'waktu_presensi' => today()->setTime(7, 50),
                    'status' => 'terlambat',
                    'metode' => 'qr_lokasi',
                    'latitude' => (float) $lokasi->latitude + 0.0002,
                    'longitude' => (float) $lokasi->longitude,
                    'akurasi' => 8,
                    'jarak_meter' => 22,
                    'terdeteksi_lokasi_palsu' => false,
                    'id_perangkat' => 'demo-device-'.$m2->nis,
                ],
            );
        }

        // 2. Presensi Historis 7 hari ke belakang untuk semua murid di X-MIPA-1
        $tanggalMulai = CarbonImmutable::now()->subDays(14);
        $statusPilihan = ['hadir', 'hadir', 'hadir', 'hadir', 'terlambat', 'hadir'];

        foreach ($muridMipa1 as $index => $murid) {
            for ($hari = 1; $hari <= 10; $hari++) {
                $tgl = $tanggalMulai->addDays($hari);
                if ($tgl->isWeekend() || $tgl->isToday()) {
                    continue;
                }

                $status = $statusPilihan[($index + $hari) % count($statusPilihan)];
                $jarak = rand(10, 60);

                Presensi::query()->updateOrCreate(
                    [
                        'id_murid' => $murid->id,
                        'id_jadwal_pelajaran' => $jadwalUtama->id,
                        'tanggal_presensi' => $tgl->toDateString(),
                    ],
                    [
                        'id_pengguna' => $murid->id_pengguna,
                        'id_lokasi_sekolah' => $lokasi->id,
                        'waktu_presensi' => $tgl->setTime(7, $status === 'hadir' ? rand(20, 35) : rand(48, 55)),
                        'status' => $status,
                        'metode' => 'qr_lokasi',
                        'latitude' => (float) $lokasi->latitude,
                        'longitude' => (float) $lokasi->longitude,
                        'akurasi' => 6,
                        'jarak_meter' => $jarak,
                        'terdeteksi_lokasi_palsu' => false,
                        'id_perangkat' => 'demo-device-'.$murid->nis,
                    ],
                );
            }
        }
    }
}
