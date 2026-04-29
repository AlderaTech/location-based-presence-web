<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Database\Seeders\PresensiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManajemenFilterSortTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_manajemen_mendukung_filter_lanjutan_dan_sorting(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $halaman = [
            '/admin/guru?q=Guru&status=aktif&id_jadwal_kerja=1&sort=nama&direction=asc',
            '/admin/presensi?q=Guru&status=hadir&id_jadwal_kerja=1&id_lokasi_sekolah=1&lokasi_palsu=0&sort=tanggal_presensi&direction=desc',
            '/admin/laporan?q=Guru&status=hadir&id_jadwal_kerja=1&id_lokasi_sekolah=1&lokasi_palsu=0&sort=nama&direction=asc',
            '/admin/pengajuan-izin?q=Guru&status=menunggu&jenis=izin&sort=tanggal_mulai&direction=asc',
            '/admin/lokasi-sekolah?q=Sekolah&aktif=1&radius_min=10&radius_max=1000&sort=radius&direction=asc',
            '/admin/jadwal-kerja?q=Reguler&aktif=1&sort=jam_masuk&direction=asc',
            '/admin/hari-libur?jenis=sekolah&sort=tanggal&direction=desc',
            '/admin/pengguna-admin?peran=admin&status=aktif&sort=nama&direction=asc',
        ];

        foreach ($halaman as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
