<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKerja extends Model
{
    public const CREATED_AT = 'dibuat_pada';
    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'jadwal_kerja';

    protected $fillable = ['nama', 'jam_masuk', 'jam_pulang', 'toleransi_terlambat_menit', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }
}
