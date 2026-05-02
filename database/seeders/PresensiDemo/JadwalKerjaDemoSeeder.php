<?php

namespace Database\Seeders\PresensiDemo;

use App\Models\JadwalKerja;
use Illuminate\Database\Seeder;

class JadwalKerjaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed();
    }

    /**
     * @return array<string, JadwalKerja>
     */
    public function seed(): array
    {
        $items = [
            'reguler' => ['Jadwal Reguler', '07:00', '15:00', 10, true],
            'pagi' => ['Jadwal Pagi', '06:30', '14:30', 15, true],
            'siang' => ['Jadwal Siang', '10:00', '18:00', 10, true],
            'ekstrakurikuler' => ['Pembina Ekstrakurikuler', '13:00', '17:00', 5, false],
        ];

        $jadwalKerja = [];

        foreach ($items as $kode => [$nama, $jamMasuk, $jamPulang, $toleransi, $aktif]) {
            $jadwalKerja[$kode] = JadwalKerja::query()->updateOrCreate(
                ['nama' => $nama],
                [
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'toleransi_terlambat_menit' => $toleransi,
                    'aktif' => $aktif,
                ],
            );
        }

        return $jadwalKerja;
    }
}
