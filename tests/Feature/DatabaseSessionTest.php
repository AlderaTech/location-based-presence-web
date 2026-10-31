<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sesi_database_menyimpan_id_pengguna_saat_admin_masuk(): void
    {
        config(['session.driver' => 'database']);
        app('session')->forgetDrivers();

        $admin = Pengguna::create([
            'nama' => 'Admin Test',
            'email' => 'admin@example.test',
            'kata_sandi' => Hash::make('password'),
            'peran' => 'admin',
            'status' => 'aktif',
        ]);

        $this->post('/admin/masuk', [
            'email' => $admin->email,
            'kata_sandi' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('sesi', [
            'id_pengguna' => $admin->id,
        ]);
    }
}
