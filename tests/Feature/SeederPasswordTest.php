<?php

namespace Tests\Feature;

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
}
