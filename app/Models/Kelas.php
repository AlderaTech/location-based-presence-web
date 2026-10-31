<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan',
        'tahun_ajaran',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'tingkat' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function murid(): HasMany
    {
        return $this->hasMany(Murid::class, 'id_kelas');
    }

    public function jadwalPelajaran(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class, 'id_kelas');
    }
}
