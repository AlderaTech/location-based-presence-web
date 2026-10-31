<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Database\Seeders\PresensiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenggunaAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_tidak_melihat_tombol_aksi_pada_akun_sendiri(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $response = $this->actingAs($admin)->get('/admin/pengguna-admin');

        $response->assertOk();
        $response->assertSee('Akun Anda');
        $response->assertDontSee('/admin/pengguna-admin/'.$admin->id.'/edit');
    }

    public function test_admin_melihat_tombol_aksi_pada_akun_admin_lain(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $adminLain = Pengguna::create([
            'nama' => 'Admin Kedua',
            'email' => 'admin2@presensi.test',
            'kata_sandi' => bcrypt('password123'),
            'peran' => 'admin',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($admin)->get('/admin/pengguna-admin');

        $response->assertOk();
        $response->assertSee('/admin/pengguna-admin/'.$adminLain->id.'/edit');
    }

    public function test_admin_tertolak_403_jika_membuka_url_edit_akun_sendiri_secara_manual(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $response = $this->actingAs($admin)->get('/admin/pengguna-admin/'.$admin->id.'/edit');

        $response->assertForbidden();
    }

    public function test_admin_tertolak_403_jika_memaksa_update_akun_sendiri_lewat_form(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $response = $this->actingAs($admin)->put('/admin/pengguna-admin/'.$admin->id, [
            'nama' => 'Nama Baru Admin',
            'email' => $admin->email,
            'peran' => 'admin',
            'status' => 'nonaktif',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('pengguna', [
            'id' => $admin->id,
            'status' => 'aktif',
        ]);
    }

    public function test_admin_tertolak_403_jika_memaksa_menonaktifkan_akun_sendiri(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $response = $this->actingAs($admin)->delete('/admin/pengguna-admin/'.$admin->id);

        $response->assertForbidden();
        $this->assertDatabaseHas('pengguna', [
            'id' => $admin->id,
            'status' => 'aktif',
        ]);
    }

    public function test_admin_bisa_mengedit_dan_menonaktifkan_akun_admin_lain(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $adminLain = Pengguna::create([
            'nama' => 'Admin Kedua',
            'email' => 'admin2@presensi.test',
            'kata_sandi' => bcrypt('password123'),
            'peran' => 'admin',
            'status' => 'aktif',
        ]);

        // Bisa akses edit
        $this->actingAs($admin)->get('/admin/pengguna-admin/'.$adminLain->id.'/edit')->assertOk();

        // Bisa update
        $updateResponse = $this->actingAs($admin)->put('/admin/pengguna-admin/'.$adminLain->id, [
            'nama' => 'Admin Kedua Diperbarui',
            'email' => 'admin2@presensi.test',
            'peran' => 'admin',
            'status' => 'aktif',
        ]);
        $updateResponse->assertRedirect('/admin/pengguna-admin');
        $this->assertDatabaseHas('pengguna', [
            'id' => $adminLain->id,
            'nama' => 'Admin Kedua Diperbarui',
        ]);

        // Bisa nonaktifkan
        $deleteResponse = $this->actingAs($admin)->delete('/admin/pengguna-admin/'.$adminLain->id);
        $deleteResponse->assertRedirect();
        $this->assertDatabaseHas('pengguna', [
            'id' => $adminLain->id,
            'status' => 'nonaktif',
        ]);
    }
}
