<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiSekolah extends Model
{
    public const CREATED_AT = 'dibuat_pada';
    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'lokasi_sekolah';

    protected $fillable = ['nama', 'latitude', 'longitude', 'radius_meter', 'aktif'];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'aktif' => 'boolean',
        ];
    }
}
