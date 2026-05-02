<?php

namespace Database\Seeders;

use App\Models\LogAuditPresensi;
use Database\Seeders\PresensiDemo\AuditLokasiPalsuDemoSeeder;
use Database\Seeders\PresensiDemo\HariLiburDemoSeeder;
use Database\Seeders\PresensiDemo\JadwalKerjaDemoSeeder;
use Database\Seeders\PresensiDemo\LokasiSekolahDemoSeeder;
use Database\Seeders\PresensiDemo\PengajuanIzinDemoSeeder;
use Database\Seeders\PresensiDemo\PenggunaDemoSeeder;
use Database\Seeders\PresensiDemo\RiwayatPresensiDemoSeeder;
use Illuminate\Database\Seeder;

class PresensiDemoSeeder extends Seeder
{
    public function run(): void
    {
        LogAuditPresensi::query()->where('aksi', 'like', 'seed_%')->delete();

        $jadwalKerja = app(JadwalKerjaDemoSeeder::class)->seed();
        $lokasiSekolah = app(LokasiSekolahDemoSeeder::class)->seed();
        $penggunaAdmin = app(PenggunaDemoSeeder::class)->seedPenggunaAdmin();
        $guru = app(PenggunaDemoSeeder::class)->seedGuru($jadwalKerja);

        app(PenggunaDemoSeeder::class)->seedPerangkat($guru);
        app(HariLiburDemoSeeder::class)->seed();
        app(RiwayatPresensiDemoSeeder::class)->seed($guru, $penggunaAdmin['admin'], $lokasiSekolah['utama']);
        app(PengajuanIzinDemoSeeder::class)->seed($guru, $penggunaAdmin['admin']);
        app(AuditLokasiPalsuDemoSeeder::class)->seed($guru);
    }
}
