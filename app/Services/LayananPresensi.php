<?php

namespace App\Services;

use App\Models\HariLibur;
use App\Models\JadwalKerja;
use App\Models\LogAuditPresensi;
use App\Models\LokasiSekolah;
use App\Models\Pengguna;
use App\Models\Presensi;
use Illuminate\Validation\ValidationException;

class LayananPresensi
{
    /**
     * @param  array{jenis:string,latitude:float,longitude:float,akurasi?:int,id_perangkat:string,lokasi_palsu?:bool,waktu_klien?:string|null}  $payload
     */
    public function catat(Pengguna $pengguna, array $payload): Presensi
    {
        $guru = $pengguna->guru;

        if (! $pengguna->adalahGuru() || ! $guru || ! $guru->aktif) {
            throw ValidationException::withMessages(['pengguna' => 'Akun guru tidak aktif.']);
        }

        $perangkat = $pengguna->perangkat()->where('aktif', true)->first();

        if (! $perangkat || $perangkat->id_perangkat !== $payload['id_perangkat']) {
            throw ValidationException::withMessages(['id_perangkat' => 'Perangkat tidak sesuai dengan perangkat yang terikat.']);
        }

        if (! empty($payload['lokasi_palsu'])) {
            LogAuditPresensi::create([
                'id_pengguna' => $pengguna->id,
                'aksi' => 'lokasi_palsu_diblokir',
                'payload' => $payload,
                'dibuat_pada' => now(),
            ]);

            throw ValidationException::withMessages(['lokasi_palsu' => 'Lokasi palsu terdeteksi. Presensi diblokir.']);
        }

        $hariIni = today();
        $hariLibur = HariLibur::query()->whereDate('tanggal', $hariIni)->first();

        if ($hariLibur) {
            throw ValidationException::withMessages(['tanggal_presensi' => 'Hari ini libur: '.$hariLibur->nama]);
        }

        $lokasi = LokasiSekolah::query()->where('aktif', true)->first();

        if (! $lokasi) {
            throw ValidationException::withMessages(['lokasi' => 'Lokasi sekolah aktif belum diatur.']);
        }

        $jarak = $this->jarakMeter(
            (float) $payload['latitude'],
            (float) $payload['longitude'],
            (float) $lokasi->latitude,
            (float) $lokasi->longitude,
        );

        if ($jarak > $lokasi->radius_meter) {
            throw ValidationException::withMessages([
                'lokasi' => 'Anda berada di luar radius sekolah. Jarak saat ini '.$jarak.' meter.',
            ]);
        }

        $jadwalKerja = $guru->jadwalKerjaBawaan ?: JadwalKerja::query()->where('aktif', true)->first();

        if (! $jadwalKerja) {
            throw ValidationException::withMessages(['jadwal_kerja' => 'Jadwal kerja aktif belum diatur.']);
        }

        $presensi = Presensi::query()->firstOrNew([
            'id_guru' => $guru->id,
            'tanggal_presensi' => $hariIni->toDateString(),
        ]);

        $presensi->fill([
            'id_pengguna' => $pengguna->id,
            'id_lokasi_sekolah' => $lokasi->id,
            'id_jadwal_kerja' => $jadwalKerja->id,
            'id_perangkat' => $payload['id_perangkat'],
        ]);

        if ($payload['jenis'] === 'masuk') {
            if ($presensi->jam_masuk_pada) {
                throw ValidationException::withMessages(['jenis' => 'Presensi masuk hari ini sudah tercatat.']);
            }

            $presensi->fill([
                'jam_masuk_pada' => now(),
                'latitude_masuk' => $payload['latitude'],
                'longitude_masuk' => $payload['longitude'],
                'akurasi_masuk' => $payload['akurasi'] ?? null,
                'jarak_masuk_meter' => $jarak,
                'status' => $this->tentukanStatus($jadwalKerja),
                'terdeteksi_lokasi_palsu' => false,
            ]);
        } else {
            if (! $presensi->exists || ! $presensi->jam_masuk_pada) {
                throw ValidationException::withMessages(['jenis' => 'Presensi masuk harus dilakukan sebelum presensi pulang.']);
            }

            if ($presensi->jam_pulang_pada) {
                throw ValidationException::withMessages(['jenis' => 'Presensi pulang hari ini sudah tercatat.']);
            }

            $presensi->fill([
                'jam_pulang_pada' => now(),
                'latitude_pulang' => $payload['latitude'],
                'longitude_pulang' => $payload['longitude'],
                'akurasi_pulang' => $payload['akurasi'] ?? null,
                'jarak_pulang_meter' => $jarak,
            ]);
        }

        $presensi->save();

        LogAuditPresensi::create([
            'id_presensi' => $presensi->id,
            'id_pengguna' => $pengguna->id,
            'aksi' => 'presensi_'.$payload['jenis'],
            'payload' => [
                'jarak_meter' => $jarak,
                'akurasi' => $payload['akurasi'] ?? null,
                'waktu_klien' => $payload['waktu_klien'] ?? null,
            ],
            'dibuat_pada' => now(),
        ]);

        $perangkat->forceFill(['terakhir_terlihat_pada' => now()])->save();

        return $presensi->fresh(['lokasiSekolah', 'jadwalKerja']);
    }

    public function jarakMeter(float $dariLat, float $dariLng, float $keLat, float $keLng): int
    {
        $radiusBumi = 6371000;
        $deltaLat = deg2rad($keLat - $dariLat);
        $deltaLng = deg2rad($keLng - $dariLng);
        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($dariLat)) * cos(deg2rad($keLat)) * sin($deltaLng / 2) ** 2;

        return (int) round($radiusBumi * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    private function tentukanStatus(JadwalKerja $jadwalKerja): string
    {
        $batas = today()
            ->setTimeFromTimeString($jadwalKerja->jam_masuk)
            ->addMinutes($jadwalKerja->toleransi_terlambat_menit);

        return now()->greaterThan($batas) ? 'terlambat' : 'hadir';
    }
}
