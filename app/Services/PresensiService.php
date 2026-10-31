<?php

namespace App\Services;

use App\Models\HariLibur;
use App\Models\JadwalPelajaran;
use App\Models\LogAuditPresensi;
use App\Models\LokasiSekolah;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Models\SesiPresensi;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PresensiService
{
    /**
     * @param  array{kode_qr?:string,latitude:float,longitude:float,akurasi?:int,id_perangkat:string,lokasi_palsu?:bool,waktu_klien?:string|null,id_jadwal_pelajaran?:int}  $payload
     */
    public function catat(Pengguna $pengguna, array $payload): Presensi
    {
        $murid = $pengguna->murid;

        if (! $pengguna->adalahMurid() || ! $murid || ! $murid->aktif) {
            throw ValidationException::withMessages(['pengguna' => 'Akun murid tidak aktif atau tidak ditemukan.']);
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

        // Cari sesi presensi berdasarkan kode_qr jika diberikan
        $sesi = null;
        $jadwal = null;

        if (! empty($payload['kode_qr'])) {
            $rawInput = trim((string) $payload['kode_qr']);
            $isJson = str_starts_with($rawInput, '{') && str_ends_with($rawInput, '}');
            $dataQr = null;

            if ($isJson) {
                $decoded = json_decode($rawInput, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && ($decoded['tipe'] ?? '') === 'PRESENSI_QR') {
                    $dataQr = $decoded;
                }
            }

            if ($dataQr) {
                $idSesi = $dataQr['id_sesi'] ?? null;
                if (! is_scalar($idSesi) || ! is_numeric($idSesi)) {
                    throw ValidationException::withMessages(['kode_qr' => 'Format sesi QR tidak valid.']);
                }

                // Sesi dicari berdasarkan id_sesi di dalam QR
                $sesi = SesiPresensi::query()
                    ->with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.lokasiSekolah', 'jadwalPelajaran.guru.pengguna', 'mataPelajaran'])
                    ->find((int) $idSesi);

                if (! $sesi) {
                    throw ValidationException::withMessages(['kode_qr' => 'Sesi presensi QR tidak ditemukan di sistem.']);
                }

                // 1. Verifikasi HMAC Signature data QR (Anti-Tampering)
                $expectedSig = SesiPresensi::buatSignature(
                    $sesi->id,
                    $sesi->id_mata_pelajaran ?: $sesi->jadwalPelajaran?->id_mata_pelajaran,
                    (string) ($dataQr['tanggal'] ?? ''),
                    (string) ($dataQr['jam_mulai'] ?? ''),
                    (string) ($dataQr['jam_selesai'] ?? ''),
                    (string) ($dataQr['kode_unik'] ?? '')
                );

                if (! hash_equals($expectedSig, (string) ($dataQr['sig'] ?? ''))) {
                    throw ValidationException::withMessages(['kode_qr' => 'Tanda tangan digital QR Code tidak valid atau data QR telah diubah.']);
                }

                // 2. Verifikasi Tanggal di dalam QR (mendukung sesi lintas tengah malam jika waktu_selesai besok hari)
                $tanggalQr = (string) ($dataQr['tanggal'] ?? '');
                $isSameDay = $tanggalQr === today()->toDateString();
                $isCrossMidnight = $sesi->waktu_selesai && $sesi->waktu_mulai && $sesi->waktu_selesai->toDateString() !== $sesi->waktu_mulai->toDateString() && today()->toDateString() === $sesi->waktu_selesai->toDateString();

                if (! $isSameDay && ! $isCrossMidnight) {
                    throw ValidationException::withMessages([
                        'kode_qr' => 'QR Code ini untuk tanggal '.$tanggalQr.', bukan untuk hari ini.',
                    ]);
                }

                // 3. Verifikasi Jam Selesai di dalam QR (Anti-Reuse Foto Lama)
                $waktuSelesaiPayload = $sesi->waktu_selesai ?: Carbon::parse("{$tanggalQr} {$dataQr['jam_selesai']}");
                if (now()->gt($waktuSelesaiPayload)) {
                    throw ValidationException::withMessages([
                        'kode_qr' => 'QR Code ini telah kedaluwarsa pada pukul '.($dataQr['jam_selesai'] ?? '').' WIB dan tidak dapat digunakan lagi.',
                    ]);
                }

                // 4. Verifikasi Jam Mulai di dalam QR
                $waktuMulaiPayload = $sesi->waktu_mulai ?: Carbon::parse("{$tanggalQr} {$dataQr['jam_mulai']}");
                if (now()->lt($waktuMulaiPayload)) {
                    throw ValidationException::withMessages([
                        'kode_qr' => 'Sesi presensi QR belum dimulai. QR baru dapat digunakan mulai pukul '.($dataQr['jam_mulai'] ?? '').' WIB.',
                    ]);
                }

                // 5. Verifikasi Kode Unik cocok dengan sesi aktif saat ini
                if ($sesi->kode_unik && ($dataQr['kode_unik'] ?? '') !== $sesi->kode_unik) {
                    throw ValidationException::withMessages([
                        'kode_qr' => 'QR Code ini berasal dari sesi sebelumnya yang sudah diperbarui. Silakan scan QR terbaru di layar.',
                    ]);
                }
            } else {
                // Fallback pencarian kode manual atau kode_qr legacy
                $sesi = SesiPresensi::query()
                    ->with(['jadwalPelajaran.kelas', 'jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.lokasiSekolah', 'jadwalPelajaran.guru.pengguna', 'mataPelajaran'])
                    ->where(function ($q) use ($rawInput) {
                        $q->where('kode_qr', $rawInput)->orWhere('kode_unik', $rawInput);
                    })
                    ->first();

                if (! $sesi) {
                    throw ValidationException::withMessages(['kode_qr' => 'Sesi presensi QR tidak ditemukan.']);
                }
            }

            if ($sesi->status === 'ditutup') {
                throw ValidationException::withMessages(['kode_qr' => 'Sesi presensi QR telah ditutup oleh guru/admin.']);
            }

            // 1. Cek waktu mulai sesi
            if ($sesi->waktu_mulai && now()->lt($sesi->waktu_mulai)) {
                throw ValidationException::withMessages([
                    'kode_qr' => 'Sesi presensi QR belum dimulai. QR baru dapat digunakan mulai pukul '.$sesi->waktu_mulai->format('H:i').' WIB.',
                ]);
            }

            // 2. Cek waktu kedaluwarsa sesi (anti-reuse foto/simpanan QR)
            if ($sesi->waktu_selesai && now()->gt($sesi->waktu_selesai)) {
                if ($sesi->status === 'aktif') {
                    $sesi->update(['status' => 'kedaluwarsa']);
                }
                throw ValidationException::withMessages([
                    'kode_qr' => 'Sesi presensi QR telah kedaluwarsa pada pukul '.$sesi->waktu_selesai->format('H:i').' WIB. QR ini sudah tidak dapat digunakan.',
                ]);
            }

            if (! $sesi->aktif()) {
                throw ValidationException::withMessages(['kode_qr' => 'Sesi presensi QR tidak valid atau sudah tidak aktif.']);
            }

            // 3. Validasi spesifik mata pelajaran: 1 QR tidak bisa digunakan di mata pelajaran yang berbeda
            if (! empty($payload['id_jadwal_pelajaran']) && (int) $payload['id_jadwal_pelajaran'] !== (int) $sesi->id_jadwal_pelajaran) {
                $jadwalTarget = JadwalPelajaran::query()->with('mataPelajaran')->find($payload['id_jadwal_pelajaran']);
                $mapelSesi = $sesi->jadwalPelajaran?->mataPelajaran?->nama_mapel ?? 'lain';
                $mapelTarget = $jadwalTarget?->mataPelajaran?->nama_mapel ?? 'yang dipilih';

                throw ValidationException::withMessages([
                    'kode_qr' => "Kode QR ini khusus untuk mata pelajaran $mapelSesi, tidak dapat digunakan untuk presensi mata pelajaran $mapelTarget.",
                ]);
            }

            if (! empty($payload['id_mata_pelajaran']) && (int) $payload['id_mata_pelajaran'] !== (int) ($sesi->id_mata_pelajaran ?: $sesi->jadwalPelajaran?->id_mata_pelajaran)) {
                $mapelSesi = $sesi->jadwalPelajaran?->mataPelajaran?->nama_mapel ?? 'terkait';

                throw ValidationException::withMessages([
                    'kode_qr' => "Kode QR ini khusus untuk mata pelajaran $mapelSesi dan tidak dapat digunakan untuk mata pelajaran lain.",
                ]);
            }

            $jadwal = $sesi->jadwalPelajaran;
        } elseif (! empty($payload['id_jadwal_pelajaran'])) {
            if ($pengguna->adalahMurid()) {
                throw ValidationException::withMessages(['kode_qr' => 'Kode QR sesi presensi wajib dipindai.']);
            }
            $jadwal = JadwalPelajaran::query()
                ->with(['kelas', 'mataPelajaran', 'lokasiSekolah', 'guru.pengguna'])
                ->find($payload['id_jadwal_pelajaran']);
        } else {
            throw ValidationException::withMessages(['kode_qr' => 'Kode QR sesi presensi wajib dipindai.']);
        }

        if (! $jadwal || ! $jadwal->aktif) {
            throw ValidationException::withMessages(['jadwal_pelajaran' => 'Jadwal pelajaran tidak ditemukan atau tidak aktif.']);
        }

        if (! $murid->id_kelas || (int) $murid->id_kelas !== (int) $jadwal->id_kelas) {
            throw ValidationException::withMessages([
                'kelas' => 'Anda bukan murid kelas '.($jadwal->kelas?->nama_kelas ?: 'ini').'.',
            ]);
        }

        $lokasi = $jadwal->lokasiSekolah ?: LokasiSekolah::query()->where('aktif', true)->first();

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
                'lokasi' => 'Anda berada di luar radius sekolah. Jarak saat ini '.$jarak.' meter (maksimal '.$lokasi->radius_meter.' meter).',
            ]);
        }

        $sudahAda = Presensi::query()
            ->where('id_murid', $murid->id)
            ->where('id_jadwal_pelajaran', $jadwal->id)
            ->whereDate('tanggal_presensi', $hariIni)
            ->first();

        if ($sudahAda) {
            throw ValidationException::withMessages([
                'presensi' => 'Presensi untuk mata pelajaran '.$jadwal->mataPelajaran?->nama_mapel.' hari ini sudah tercatat.',
            ]);
        }

        $status = $this->tentukanStatusJadwal($jadwal, $sesi);

        try {
            $presensi = DB::transaction(function () use ($pengguna, $murid, $jadwal, $sesi, $lokasi, $hariIni, $status, $payload, $jarak, $perangkat) {
                $baru = Presensi::create([
                    'id_pengguna' => $pengguna->id,
                    'id_murid' => $murid->id,
                    'id_jadwal_pelajaran' => $jadwal->id,
                    'id_sesi_presensi' => $sesi?->id,
                    'id_lokasi_sekolah' => $lokasi->id,
                    'tanggal_presensi' => $hariIni->toDateString(),
                    'waktu_presensi' => now(),
                    'status' => $status,
                    'metode' => $sesi ? 'qr_lokasi' : 'lokasi',
                    'latitude' => $payload['latitude'],
                    'longitude' => $payload['longitude'],
                    'akurasi' => $payload['akurasi'] ?? null,
                    'jarak_meter' => $jarak,
                    'terdeteksi_lokasi_palsu' => false,
                    'id_perangkat' => $payload['id_perangkat'],
                ]);

                LogAuditPresensi::create([
                    'id_presensi' => $baru->id,
                    'id_pengguna' => $pengguna->id,
                    'aksi' => 'presensi_murid_'.$status,
                    'payload' => [
                        'id_jadwal' => $jadwal->id,
                        'mapel' => $jadwal->mataPelajaran?->nama_mapel,
                        'jarak_meter' => $jarak,
                        'akurasi' => $payload['akurasi'] ?? null,
                        'waktu_klien' => $payload['waktu_klien'] ?? null,
                        'sesi_qr' => $sesi?->kode_qr,
                    ],
                    'dibuat_pada' => now(),
                ]);

                $perangkat->forceFill(['terakhir_terlihat_pada' => now()])->save();

                return $baru;
            });
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages([
                'presensi' => 'Presensi untuk mata pelajaran '.$jadwal->mataPelajaran?->nama_mapel.' hari ini sudah tercatat.',
            ]);
        }

        return $presensi->fresh([
            'murid.pengguna',
            'murid.kelas',
            'jadwalPelajaran.mataPelajaran',
            'jadwalPelajaran.guru.pengguna',
            'jadwalPelajaran.kelas',
            'lokasiSekolah',
        ]);
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

    private function tentukanStatusJadwal(JadwalPelajaran $jadwal, ?SesiPresensi $sesi = null): string
    {
        $jamAcuan = today()->setTimeFromTimeString($jadwal->jam_mulai);
        if ($sesi && $sesi->waktu_mulai && $sesi->waktu_mulai->gt($jamAcuan)) {
            $jamAcuan = $sesi->waktu_mulai->copy();
        }

        $batas = $jamAcuan->addMinutes((int) $jadwal->toleransi_terlambat_menit);

        return now()->greaterThan($batas) ? 'terlambat' : 'hadir';
    }
}
