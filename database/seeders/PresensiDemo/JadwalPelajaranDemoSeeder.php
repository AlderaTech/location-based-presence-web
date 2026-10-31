<?php

namespace Database\Seeders\PresensiDemo;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\LokasiSekolah;
use App\Models\MataPelajaran;
use App\Models\SesiPresensi;
use Illuminate\Database\Seeder;

class JadwalPelajaranDemoSeeder extends Seeder
{
    /**
     * @param  array<string, Guru>  $guru
     * @param  array<string, Kelas>  $kelas
     * @param  array<string, MataPelajaran>  $mapel
     * @return array<string, JadwalPelajaran>
     */
    public function seed(array $guru, array $kelas, array $mapel, ?LokasiSekolah $lokasi): array
    {
        $hariMap = [
            0 => 'minggu',
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ];
        $hariIniStr = $hariMap[(int) now()->dayOfWeek] ?? 'senin';

        $items = [
            // Jadwal Hari Ini untuk X-MIPA-1 (Pelajaran Utama Demo)
            'hari_ini_mat' => [
                'id_mata_pelajaran' => $mapel['MAT']->id,
                'id_kelas' => $kelas['X-MIPA-1']->id,
                'id_guru' => $guru['guru']->id,
                'hari' => $hariIniStr,
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:30',
                'toleransi_terlambat_menit' => 15,
                'ruangan' => 'R. 101',
            ],
            'hari_ini_ind' => [
                'id_mata_pelajaran' => $mapel['IND']->id,
                'id_kelas' => $kelas['X-MIPA-1']->id,
                'id_guru' => $guru['siti']->id,
                'hari' => $hariIniStr,
                'jam_mulai' => '10:00',
                'jam_selesai' => '12:00',
                'toleransi_terlambat_menit' => 15,
                'ruangan' => 'R. 101',
            ],
            // Jadwal X-MIPA-1 hari lain
            'senin_mat' => [
                'id_mata_pelajaran' => $mapel['MAT']->id,
                'id_kelas' => $kelas['X-MIPA-1']->id,
                'id_guru' => $guru['guru']->id,
                'hari' => 'senin',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:30',
                'toleransi_terlambat_menit' => 15,
                'ruangan' => 'R. 101',
            ],
            'senin_ind' => [
                'id_mata_pelajaran' => $mapel['IND']->id,
                'id_kelas' => $kelas['X-MIPA-1']->id,
                'id_guru' => $guru['siti']->id,
                'hari' => 'senin',
                'jam_mulai' => '10:00',
                'jam_selesai' => '12:00',
                'toleransi_terlambat_menit' => 15,
                'ruangan' => 'R. 101',
            ],
            'selasa_ipa' => [
                'id_mata_pelajaran' => $mapel['IPA']->id,
                'id_kelas' => $kelas['X-MIPA-1']->id,
                'id_guru' => $guru['hendra']->id,
                'hari' => 'selasa',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:30',
                'toleransi_terlambat_menit' => 15,
                'ruangan' => 'Lab Fisika',
            ],
            'rabu_ing' => [
                'id_mata_pelajaran' => $mapel['ING']->id,
                'id_kelas' => $kelas['X-MIPA-1']->id,
                'id_guru' => $guru['dewi']->id,
                'hari' => 'rabu',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:30',
                'toleransi_terlambat_menit' => 15,
                'ruangan' => 'R. 101',
            ],
            'kamis_tik' => [
                'id_mata_pelajaran' => $mapel['TIK']->id,
                'id_kelas' => $kelas['X-MIPA-1']->id,
                'id_guru' => $guru['guru']->id,
                'hari' => 'kamis',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:30',
                'toleransi_terlambat_menit' => 15,
                'ruangan' => 'Lab Komputer',
            ],
            // Jadwal X-MIPA-2
            'mipa2_senin_ind' => [
                'id_mata_pelajaran' => $mapel['IND']->id,
                'id_kelas' => $kelas['X-MIPA-2']->id,
                'id_guru' => $guru['siti']->id,
                'hari' => 'senin',
                'jam_mulai' => '07:30',
                'jam_selesai' => '09:30',
                'toleransi_terlambat_menit' => 15,
                'ruangan' => 'R. 102',
            ],
            'mipa2_senin_mat' => [
                'id_mata_pelajaran' => $mapel['MAT']->id,
                'id_kelas' => $kelas['X-MIPA-2']->id,
                'id_guru' => $guru['guru']->id,
                'hari' => 'senin',
                'jam_mulai' => '10:00',
                'jam_selesai' => '12:00',
                'toleransi_terlambat_menit' => 15,
                'ruangan' => 'R. 102',
            ],
        ];

        $hasil = [];
        foreach ($items as $key => $atribut) {
            $hasil[$key] = JadwalPelajaran::query()->updateOrCreate(
                [
                    'id_mata_pelajaran' => $atribut['id_mata_pelajaran'],
                    'id_kelas' => $atribut['id_kelas'],
                    'hari' => $atribut['hari'],
                    'jam_mulai' => $atribut['jam_mulai'],
                ],
                array_merge($atribut, [
                    'id_lokasi_sekolah' => $lokasi?->id,
                    'aktif' => true,
                ]),
            );
        }

        // Buka Sesi QR Presensi Aktif untuk Jadwal Hari Ini
        $jadwalUtama = $hasil['hari_ini_mat'];
        $kodeQrDemo = 'SESI-DEMO-'.strtoupper($jadwalUtama->hari).'-MAT';

        SesiPresensi::query()->updateOrCreate(
            ['id_jadwal_pelajaran' => $jadwalUtama->id, 'tanggal' => today()->toDateString()],
            [
                'id_mata_pelajaran' => $jadwalUtama->id_mata_pelajaran,
                'id_guru' => $jadwalUtama->id_guru,
                'kode_qr' => $kodeQrDemo,
                'waktu_buka' => now()->startOfDay()->addHours(7)->addMinutes(20),
                'waktu_mulai' => now()->startOfDay()->addHours(7)->addMinutes(30),
                'waktu_selesai' => now()->startOfDay()->addHours(11)->addMinutes(30),
                'status' => 'aktif',
                'catatan' => 'Sesi presensi aktif demo untuk pengujian QR scanner di kelas X-MIPA-1',
            ],
        );

        return $hasil;
    }
}
