<?php

namespace Database\Factories;

use App\Models\Pengguna;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pengguna>
 */
class PenggunaFactory extends Factory
{
    private const KATA_SANDI_DEFAULT = 'password';

    protected $model = Pengguna::class;

    protected static ?string $kataSandi;

    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_terverifikasi_pada' => now(),
            'peran' => 'guru',
            'status' => 'aktif',
            'kata_sandi' => static::$kataSandi ??= Hash::make(self::KATA_SANDI_DEFAULT),
            'token_ingat' => Str::random(10),
        ];
    }

    public function belumTerverifikasi(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_terverifikasi_pada' => null,
        ]);
    }
}
