<?php

namespace Database\Seeders\PresensiDemo\Concerns;

use App\Models\Guru;
use App\Models\LogAuditPresensi;
use App\Models\LokasiSekolah;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\CarbonImmutable;
use Database\Seeders\PresensiDemo\PresensiDemoConfig;
use Illuminate\Support\Facades\Hash;

trait CreatesDemoData
{
    protected function pengguna(
        string $email,
        string $nama,
        string $peran,
        ?string $nomorPegawai,
        ?string $telepon,
        string $status = 'aktif',
    ): Pengguna {
        return Pengguna::query()->updateOrCreate(
            ['email' => $email],
            [
                'nama' => $nama,
                'kata_sandi' => Hash::make(PresensiDemoConfig::KATA_SANDI_DEMO),
                'peran' => $peran,
                'nomor_pegawai' => $nomorPegawai,
                'telepon' => $telepon,
                'status' => $status,
            ],
        );
    }

    protected function presensi(
        Guru $guru,
        ?LokasiSekolah $lokasi,
        CarbonImmutable $tanggal,
        array $atribut,
    ): Presensi {
        $presensi = Presensi::query()
            ->where('id_guru', $guru->id)
            ->whereDate('tanggal_presensi', $tanggal->toDateString())
            ->first() ?: new Presensi([
                'id_guru' => $guru->id,
                'tanggal_presensi' => $tanggal->toDateString(),
            ]);

        $presensi->fill(array_merge([
            'id_pengguna' => $guru->id_pengguna,
            'id_lokasi_sekolah' => $lokasi?->id,
            'id_jadwal_kerja' => $guru->id_jadwal_kerja_bawaan,
            'jam_masuk_pada' => null,
            'jam_pulang_pada' => null,
            'latitude_masuk' => null,
            'longitude_masuk' => null,
            'akurasi_masuk' => null,
            'jarak_masuk_meter' => null,
            'latitude_pulang' => null,
            'longitude_pulang' => null,
            'akurasi_pulang' => null,
            'jarak_pulang_meter' => null,
            'status' => 'hadir',
            'terdeteksi_lokasi_palsu' => false,
            'id_perangkat' => null,
            'catatan' => null,
            'id_pengoreksi' => null,
            'alasan_koreksi' => null,
        ], $atribut));

        $presensi->save();

        return $presensi;
    }

    protected function audit(Presensi $presensi, Pengguna $pengguna, ?Pengguna $aktor, string $aksi, array $payload): void
    {
        LogAuditPresensi::query()->create([
            'id_presensi' => $presensi->id,
            'id_pengguna' => $pengguna->id,
            'id_aktor' => $aktor?->id,
            'aksi' => $aksi,
            'payload' => $payload,
            'dibuat_pada' => now(),
        ]);
    }
}
