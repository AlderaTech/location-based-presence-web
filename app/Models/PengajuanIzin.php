<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PengajuanIzin extends Model
{
    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'pengajuan_izin';

    protected $fillable = [
        'id_pengguna',
        'id_murid',
        'id_jadwal_pelajaran',
        'jenis',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'status',
        'id_peninjau',
        'ditinjau_pada',
        'alasan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'ditinjau_pada' => 'datetime',
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

    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_peninjau');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(DokumenIzin::class, 'id_pengajuan_izin');
    }
}
