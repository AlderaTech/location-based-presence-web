<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerangkatPengguna extends Model
{
    public const CREATED_AT = 'dibuat_pada';
    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'perangkat_pengguna';

    protected $fillable = [
        'pengguna_id',
        'id_perangkat',
        'nama_perangkat',
        'platform',
        'pertama_terlihat_pada',
        'terakhir_terlihat_pada',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'pertama_terlihat_pada' => 'datetime',
            'terakhir_terlihat_pada' => 'datetime',
            'aktif' => 'boolean',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }
}
