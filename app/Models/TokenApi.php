<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenApi extends Model
{
    public const CREATED_AT = 'dibuat_pada';
    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'token_api';

    protected $fillable = [
        'id_pengguna',
        'nama',
        'hash_token',
        'id_perangkat',
        'terakhir_dipakai_pada',
        'kedaluwarsa_pada',
    ];

    protected function casts(): array
    {
        return [
            'terakhir_dipakai_pada' => 'datetime',
            'kedaluwarsa_pada' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }
}
