<?php

namespace Tests\Feature;

use App\Models\JadwalPelajaran;
use App\Models\Pengguna;
use Database\Seeders\PresensiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AksesGuruWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_bisa_mengakses_dasbor_sesi_presensi_dan_izin(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $guru = Pengguna::query()->where('email', 'guru@presensi.test')->firstOrFail();

        $halamanBoleh = [
            '/admin',
            '/admin/sesi-presensi',
            '/admin/presensi',
            '/admin/pengajuan-izin',
            '/admin/laporan',
        ];

        foreach ($halamanBoleh as $url) {
            $this->actingAs($guru)->get($url)->assertOk();
        }
    }

    public function test_guru_tidak_bisa_mengakses_manajemen_master_data_admin(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $guru = Pengguna::query()->where('email', 'guru@presensi.test')->firstOrFail();

        $halamanDibatasi = [
            '/admin/kelas',
            '/admin/mata-pelajaran',
            '/admin/guru',
            '/admin/murid',
            '/admin/jadwal-pelajaran',
            '/admin/lokasi-sekolah',
            '/admin/hari-libur',
            '/admin/pengguna-admin',
        ];

        foreach ($halamanDibatasi as $url) {
            $this->actingAs($guru)->get($url)->assertForbidden();
        }
    }

    public function test_guru_hanya_bisa_generate_qr_untuk_jadwal_yang_diampunya(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $guru = Pengguna::query()->where('email', 'guru@presensi.test')->firstOrFail();
        
        // Jadwal milik guru lain
        $jadwalLain = JadwalPelajaran::query()->where('id_guru', '!=', $guru->guru->id)->firstOrFail();

        $response = $this->actingAs($guru)->post('/admin/sesi-presensi', [
            'id_jadwal_pelajaran' => $jadwalLain->id,
            'durasi_menit' => 30,
        ]);

        $response->assertForbidden();
    }

    public function test_guru_bisa_login_melalui_halaman_masuk_web(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $response = $this->post('/admin/masuk', [
            'email' => 'guru@presensi.test',
            'kata_sandi' => 'password',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticated();
        $this->assertSame('guru', auth()->user()->peran);
    }

    public function test_murid_tidak_bisa_login_melalui_halaman_masuk_web(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $response = $this->post('/admin/masuk', [
            'email' => 'murid@presensi.test',
            'kata_sandi' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
