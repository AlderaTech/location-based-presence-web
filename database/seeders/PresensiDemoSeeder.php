<?php

namespace Database\Seeders;

use App\Models\LogAuditPresensi;
use Database\Seeders\PresensiDemo\HariLiburDemoSeeder;
use Database\Seeders\PresensiDemo\JadwalPelajaranDemoSeeder;
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

        $lokasiSekolah = app(LokasiSekolahDemoSeeder::class)->seed();
        $admin = app(PenggunaDemoSeeder::class)->seedAdmin();
        $guru = app(PenggunaDemoSeeder::class)->seedGuru();
        $kelas = app(PenggunaDemoSeeder::class)->seedKelas();
        $mapel = app(PenggunaDemoSeeder::class)->seedMataPelajaran();
        $murid = app(PenggunaDemoSeeder::class)->seedMurid($kelas);

        app(HariLiburDemoSeeder::class)->seed();

        $jadwal = app(JadwalPelajaranDemoSeeder::class)->seed($guru, $kelas, $mapel, $lokasiSekolah['utama'] ?? null);

        if (isset($lokasiSekolah['utama'])) {
            app(RiwayatPresensiDemoSeeder::class)->seed($murid, $jadwal, $admin['admin'], $lokasiSekolah['utama']);
            app(PengajuanIzinDemoSeeder::class)->seed($murid, $jadwal, $admin['admin'], $guru['guru']->pengguna);
        }
    }
}
