<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Guru extends Model
{
    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'guru';

    protected $fillable = [
        'id_pengguna',
        'id_jadwal_kerja_bawaan',
        'nip',
        'mata_pelajaran',
        'jabatan',
        'alamat',
        'jadwal_kerja',
        'notifikasi_aktif',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'notifikasi_aktif' => 'boolean',
            'aktif' => 'boolean',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }

    public function jadwalKerjaBawaan(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class, 'id_jadwal_kerja_bawaan');
    }

    public function jadwalPelajaran(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class, 'id_guru');
    }

    public function sesiPresensi(): HasMany
    {
        return $this->hasMany(SesiPresensi::class, 'id_guru');
    }

    public function presensi(): HasManyThrough
    {
        return $this->hasManyThrough(Presensi::class, JadwalPelajaran::class, 'id_guru', 'id_jadwal_pelajaran');
    }
}
