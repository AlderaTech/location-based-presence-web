<?php

namespace App\Models;

use Database\Factories\PenggunaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    /** @use HasFactory<PenggunaFactory> */
    use HasFactory, Notifiable;

    public const CREATED_AT = 'dibuat_pada';
    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'pengguna';

    protected $fillable = [
        'nama',
        'email',
        'kata_sandi',
        'peran',
        'nomor_pegawai',
        'telepon',
        'status',
    ];

    protected $hidden = ['kata_sandi', 'token_ingat'];

    protected function casts(): array
    {
        return [
            'email_terverifikasi_pada' => 'datetime',
            'kata_sandi' => 'hashed',
        ];
    }

    public function getAuthPasswordName(): string
    {
        return 'kata_sandi';
    }

    public function getRememberTokenName(): string
    {
        return 'token_ingat';
    }

    public function guru(): HasOne
    {
        return $this->hasOne(Guru::class, 'pengguna_id');
    }

    public function perangkat(): HasMany
    {
        return $this->hasMany(PerangkatPengguna::class, 'pengguna_id');
    }

    public function tokenApi(): HasMany
    {
        return $this->hasMany(TokenApi::class, 'pengguna_id');
    }

    public function adalahAdmin(): bool
    {
        return in_array($this->peran, ['admin', 'tata_usaha', 'kepala_sekolah'], true);
    }

    public function adalahGuru(): bool
    {
        return $this->peran === 'guru';
    }

    public function aktif(): bool
    {
        return $this->status === 'aktif';
    }
}
