<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    public const CREATED_AT = 'dibuat_pada';
    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'hari_libur';

    protected $fillable = ['tanggal', 'nama', 'jenis'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }
}
