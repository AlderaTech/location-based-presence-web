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
            '/admin/guru?q=Guru&status=aktif&sort=nama&direction=asc',
            '/admin/murid?q=Ahmad&status=aktif&sort=nama&direction=asc',
            '/admin/kelas?q=MIPA&sort=nama_kelas&direction=asc',
            '/admin/mata-pelajaran?q=Matematika&sort=nama_mapel&direction=asc',
            '/admin/jadwal-pelajaran?sort=jam_mulai&direction=asc',
            '/admin/presensi?q=Ahmad&status=hadir&sort=tanggal_presensi&direction=desc',
            '/admin/laporan?q=Ahmad&status=hadir&sort=nama&direction=asc',
            '/admin/pengajuan-izin?q=Dian&status=menunggu&jenis=sakit',
            '/admin/lokasi-sekolah?q=Sekolah&aktif=1&radius_min=10&radius_max=1000&sort=radius&direction=asc',
            '/admin/hari-libur?jenis=sekolah&sort=tanggal&direction=desc',
            '/admin/pengguna-admin?peran=admin&status=aktif&sort=nama&direction=asc',
        ];

        foreach ($halaman as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_form_lokasi_sekolah_memakai_peta_untuk_memilih_koordinat(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/lokasi-sekolah/create')
            ->assertOk()
            ->assertSee('data-location-map', false)
            ->assertSee('data-location-map-canvas', false)
            ->assertSee('id="latitude" type="hidden" name="latitude"', false)
            ->assertSee('id="longitude" type="hidden" name="longitude"', false)
            ->assertDontSee('<label class="text-sm">Latitude', false)
            ->assertDontSee('<label class="text-sm">Longitude', false);
    }
}
