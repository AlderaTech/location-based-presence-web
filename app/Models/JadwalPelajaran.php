<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalPelajaran extends Model
{
    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'jadwal_pelajaran';

    protected $fillable = [
        'id_mata_pelajaran',
        'id_kelas',
        'id_guru',
        'id_lokasi_sekolah',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'toleransi_terlambat_menit',
        'ruangan',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'toleransi_terlambat_menit' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mata_pelajaran');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'id_guru');
    }

    public function lokasiSekolah(): BelongsTo
    {
        return $this->belongsTo(LokasiSekolah::class, 'id_lokasi_sekolah');
    }

    public function sesiPresensi(): HasMany
    {
        return $this->hasMany(SesiPresensi::class, 'id_jadwal_pelajaran');
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'id_jadwal_pelajaran');
    }
}
