<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SesiPresensi extends Model
{
    public const CREATED_AT = 'dibuat_pada';

    public const UPDATED_AT = 'diubah_pada';

    protected $table = 'sesi_presensi';

    protected $fillable = [
        'id_jadwal_pelajaran',
        'id_mata_pelajaran',
        'id_guru',
        'tanggal',
        'kode_qr',
        'kode_unik',
        'payload_qr',
        'waktu_buka',
        'waktu_tutup',
        'waktu_mulai',
        'waktu_selesai',
        'status',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'waktu_buka' => 'datetime',
            'waktu_tutup' => 'datetime',
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
        ];
    }

    public function jadwalPelajaran(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'id_jadwal_pelajaran');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'id_mata_pelajaran');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'id_guru');
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class, 'id_sesi_presensi');
    }

    public function aktif(): bool
    {
        if ($this->status !== 'aktif') {
            return false;
        }

        $now = now();

        if ($this->waktu_mulai && $now->lt($this->waktu_mulai)) {
            return false;
        }

        if ($this->waktu_selesai && $now->gt($this->waktu_selesai)) {
            return false;
        }

        return true;
    }

    public function sudahKedaluwarsa(): bool
    {
        if ($this->status === 'kedaluwarsa') {
            return true;
        }

        return (bool) ($this->waktu_selesai && now()->gt($this->waktu_selesai));
    }

    public function belumDimulai(): bool
    {
        return (bool) ($this->waktu_mulai && now()->lt($this->waktu_mulai));
    }

    public function sisaDetik(): int
    {
        if (! $this->waktu_selesai) {
            return 0;
        }

        return max(0, (int) now()->diffInSeconds($this->waktu_selesai, false));
    }

    public function generateDynamicPayload(): string
    {
        $mapel = $this->mataPelajaran ?: $this->jadwalPelajaran?->mataPelajaran;
        $jamMulai = $this->waktu_mulai ? $this->waktu_mulai->format('H:i') : '07:30';
        $jamSelesai = $this->waktu_selesai ? $this->waktu_selesai->format('H:i') : '09:30';
        $tanggal = $this->tanggal ? $this->tanggal->toDateString() : today()->toDateString();
        $kodeUnik = $this->kode_unik ?: strtoupper(Str::random(10));
        $this->kode_unik = $kodeUnik;

        $sig = self::buatSignature($this->id, $mapel?->id, $tanggal, $jamMulai, $jamSelesai, $kodeUnik);

        $payload = [
            'tipe' => 'PRESENSI_QR',
            'id_sesi' => $this->id,
            'mata_pelajaran' => [
                'id' => $mapel?->id,
                'kode' => $mapel?->kode_mapel ?? 'MPL',
                'nama' => $mapel?->nama_mapel ?? 'Mata Pelajaran',
            ],
            'tanggal' => $tanggal,
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'kode_unik' => $kodeUnik,
            'sig' => $sig,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $this->payload_qr = $json;

        return $json;
    }

    public static function buatSignature($sesiId, $mapelId, string $tanggal, string $jamMulai, string $jamSelesai, string $kodeUnik): string
    {
        $plain = "{$sesiId}|{$mapelId}|{$tanggal}|{$jamMulai}|{$jamSelesai}|{$kodeUnik}";
        $key = (string) config('app.key', 'location-based-presence-secret');

        return substr(hash_hmac('sha256', $plain, $key), 0, 16);
    }
}
