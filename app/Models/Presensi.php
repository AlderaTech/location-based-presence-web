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
        'id_murid',
        'id_jadwal_pelajaran',
        'id_sesi_presensi',
        'id_lokasi_sekolah',
        'tanggal_presensi',
        'waktu_presensi',
        'status',
        'metode',
        'latitude',
        'longitude',
        'akurasi',
        'jarak_meter',
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
            'waktu_presensi' => 'datetime',
            'terdeteksi_lokasi_palsu' => 'boolean',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }

    public function murid(): BelongsTo
    {
        return $this->belongsTo(Murid::class, 'id_murid');
    }

    public function jadwalPelajaran(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'id_jadwal_pelajaran');
    }

    public function sesiPresensi(): BelongsTo
    {
        return $this->belongsTo(SesiPresensi::class, 'id_sesi_presensi');
    }

    public function lokasiSekolah(): BelongsTo
    {
        return $this->belongsTo(LokasiSekolah::class, 'id_lokasi_sekolah');
    }

    public function pengoreksi(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengoreksi');
    }
}
