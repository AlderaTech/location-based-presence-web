<?php

namespace Tests\Feature;

use App\Models\LokasiSekolah;
use App\Models\Pengguna;
use Database\Seeders\PresensiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeederPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_semua_pengguna_dari_seeder_memakai_password_default(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $pengguna = Pengguna::query()->get();

        $this->assertGreaterThan(0, $pengguna->count());
        $pengguna->each(fn (Pengguna $item) => $this->assertTrue(Hash::check('password', $item->kata_sandi)));
    }

    public function test_seeder_lokasi_utama_memakai_koordinat_universitas_teknologi_yogyakarta(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $this->assertDatabaseHas('lokasi_sekolah', [
            'nama' => 'Universitas Teknologi Yogyakarta',
            'latitude' => '-7.8058236',
            'longitude' => '110.3889591',
            'aktif' => true,
        ]);

        $this->assertFalse(LokasiSekolah::query()->where('nama', 'Sekolah Utama')->exists());
    }

    public function test_akun_guru_demo_tidak_memiliki_perangkat_aktif_dari_seeder(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $guruDemo = Pengguna::query()
            ->where('email', 'guru@presensi.test')
            ->firstOrFail();

        $this->assertFalse($guruDemo->perangkat()->where('aktif', true)->exists());
    }
}
