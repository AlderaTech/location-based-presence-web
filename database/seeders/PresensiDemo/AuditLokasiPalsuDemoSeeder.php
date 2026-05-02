<?php

namespace Database\Seeders\PresensiDemo;

use App\Models\Guru;
use App\Models\LogAuditPresensi;
use Illuminate\Database\Seeder;

class AuditLokasiPalsuDemoSeeder extends Seeder
{
    /**
     * @param  array<int, Guru>  $guru
     */
    public function seed(array $guru): void
    {
        $guruItem = collect($guru)->first(fn (Guru $item) => $item->aktif);
        if (! $guruItem) {
            return;
        }

        LogAuditPresensi::query()->create([
            'id_pengguna' => $guruItem->id_pengguna,
            'aksi' => 'seed_lokasi_palsu_diblokir',
            'payload' => [
                'id_perangkat' => 'demo-device-'.$guruItem->nip,
                'latitude' => -6.1800000,
                'longitude' => 106.8000000,
                'lokasi_palsu' => true,
                'pesan' => 'Simulasi percobaan presensi dengan lokasi palsu.',
            ],
            'dibuat_pada' => now()->subDays(1),
        ]);
    }
}
