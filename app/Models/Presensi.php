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
        'pengguna_id',
        'guru_id',
        'lokasi_sekolah_id',
        'jadwal_kerja_id',
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
        'dikoreksi_oleh',
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
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function lokasiSekolah(): BelongsTo
    {
        return $this->belongsTo(LokasiSekolah::class, 'lokasi_sekolah_id');
    }

    public function jadwalKerja(): BelongsTo
    {
        return $this->belongsTo(JadwalKerja::class, 'jadwal_kerja_id');
    }
}
