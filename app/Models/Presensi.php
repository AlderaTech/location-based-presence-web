<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    public const CREATED_AT = 'dibuat_pada';
    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'presensi';

    protected $fillable = [
        'id_pengguna',
        'id_guru',
        'id_lokasi_sekolah',
        'id_jadwal_kerja',
        'tanggal_presensi',
        'jam_masuk_pada',
        'jam_pulang_pada',
        'latitude_masuk',
        'longitude_masuk',
        'akurasi_masuk',
        'jarak_masuk_meter',
        'latitude_pulang',
        'longitude_pulang',
        'akurasi_pulang',
        'jarak_pulang_meter',
        'status',
        'terdeteksi_lokasi_palsu',
        'id_perangkat',
        'catatan',
        'id_pengoreksi',
        'alasan_koreksi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_presensi' => 'date',
            'jam_masuk_pada' => 'datetime',
            'jam_pulang_pada' => 'datetime',
            'terdeteksi_lokasi_palsu' => 'boolean',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'id_guru');
    }

    public function lokasiSekolah(): BelongsTo
    {
        return $this->belongsTo(LokasiSekolah::class, 'id_lokasi_sekolah');
    }

    public function jadwalKerja(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class, 'id_jadwal_kerja');
    }
}
