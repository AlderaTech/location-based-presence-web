<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenIzin extends Model
{
    public const CREATED_AT = 'dibuat_pada';
    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'dokumen_izin';

    protected $fillable = ['id_pengajuan_izin', 'disk', 'path', 'nama_asli', 'tipe_mime', 'ukuran'];

    public function pengajuanIzin(): BelongsTo
    {
        return $this->belongsTo(PengajuanIzin::class, 'id_pengajuan_izin');
    }
}
