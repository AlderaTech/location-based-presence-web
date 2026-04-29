<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogAuditPresensi extends Model
{
    public $timestamps = false;

    protected $table = 'log_audit_presensi';

    protected $fillable = ['id_presensi', 'id_pengguna', 'id_aktor', 'aksi', 'payload', 'dibuat_pada'];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'dibuat_pada' => 'datetime',
        ];
    }
}
