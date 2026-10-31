<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Database\Seeders\PresensiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanIzinAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_pengajuan_izin_admin_menampilkan_ringkasan_dan_daftar_pengajuan(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/pengajuan-izin')
            ->assertOk()
            ->assertSee('Total Pengajuan')
            ->assertSee('Setujui Izin / Sakit');
    }

    public function test_halaman_pengajuan_izin_bisa_difilter_berdasarkan_pencarian(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/pengajuan-izin?q=Dian')
            ->assertOk()
            ->assertSee('Dian Sastro');
    }

    public function test_halaman_pengajuan_izin_dengan_sorting_dan_filter_tidak_menyebabkan_error_group_by(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/pengajuan-izin?status=menunggu&sort=dibuat_pada&direction=desc')
            ->assertOk()
            ->assertSee('Setujui Izin / Sakit');
    }
}
