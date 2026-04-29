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
            ->assertSee('Kelola Pengajuan Guru')
            ->assertSee('Total Pengajuan')
            ->assertSee('Setujui Pengajuan');
    }

    public function test_halaman_pengajuan_izin_bisa_difilter_berdasarkan_pencarian(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/pengajuan-izin?q=Guru+Demo')
            ->assertOk()
            ->assertSee('Guru Demo');
    }
}
