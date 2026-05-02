<?php

namespace Database\Seeders\PresensiDemo;

use App\Models\HariLibur;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class HariLiburDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed();
    }

    public function seed(): void
    {
        $awalBulan = CarbonImmutable::now()->startOfMonth();
        $items = [
            [$awalBulan->addDays(4), 'Libur Sekolah Awal Bulan', 'sekolah'],
            [$awalBulan->addDays(12), 'Rapat Kerja Guru', 'sekolah'],
            [$awalBulan->addDays(20), 'Libur Nasional Simulasi', 'nasional'],
            [CarbonImmutable::now()->addMonth()->startOfMonth()->addDays(2), 'Libur Sekolah Bulan Depan', 'sekolah'],
        ];

        foreach ($items as [$tanggal, $nama, $jenis]) {
            HariLibur::query()->updateOrCreate(
                ['tanggal' => $tanggal->toDateString()],
                ['nama' => $nama, 'jenis' => $jenis],
            );
        }
    }
}
