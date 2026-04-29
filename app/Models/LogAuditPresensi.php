<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAuditPresensi extends Model
{
    public $timestamps = false;

    protected $table = 'log_audit_presensi';

    protected $fillable = ['presensi_id', 'pengguna_id', 'aktor_id', 'aksi', 'payload', 'dibuat_pada'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'dibuat_pada' => 'datetime',
        ];
    }
}
