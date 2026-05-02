<?php

namespace Database\Seeders\PresensiDemo;

use App\Models\LokasiSekolah;
use Illuminate\Database\Seeder;

class LokasiSekolahDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->seed();
    }

    /**
     * @return array<string, LokasiSekolah>
     */
    public function seed(): array
    {
        return [
            'utama' => $this->seedLokasiUtama(),
            'olahraga' => LokasiSekolah::query()->updateOrCreate(
                ['nama' => 'Gedung Olahraga'],
                [
                    'latitude' => -7.8063100,
                    'longitude' => 110.3894500,
                    'radius_meter' => 80,
                    'aktif' => true,
                ],
            ),
            'lama' => LokasiSekolah::query()->updateOrCreate(
                ['nama' => 'Kampus Lama'],
                [
                    'latitude' => -7.8049700,
                    'longitude' => 110.3879200,
                    'radius_meter' => 100,
                    'aktif' => false,
                ],
            ),
        ];
    }

    private function seedLokasiUtama(): LokasiSekolah
    {
        $lokasi = LokasiSekolah::query()
            ->whereIn('nama', ['Sekolah Utama', 'Universitas Teknologi Yogyakarta'])
            ->first() ?? new LokasiSekolah;

        $lokasi->fill([
            'nama' => 'Universitas Teknologi Yogyakarta',
            'latitude' => PresensiDemoConfig::LATITUDE_SEKOLAH,
            'longitude' => PresensiDemoConfig::LONGITUDE_SEKOLAH,
            'radius_meter' => 150,
            'aktif' => true,
        ])->save();

        return $lokasi;
    }
}
