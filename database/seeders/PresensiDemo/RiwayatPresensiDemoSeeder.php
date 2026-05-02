<?php

namespace Database\Seeders\PresensiDemo;

use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\LokasiSekolah;
use App\Models\Pengguna;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Database\Seeders\PresensiDemo\Concerns\MembuatDataDemo;
use Illuminate\Database\Seeder;

class RiwayatPresensiDemoSeeder extends Seeder
{
    use MembuatDataDemo;

    /**
     * @param  array<int, Guru>  $guru
     */
    public function seed(array $guru, Pengguna $admin, LokasiSekolah $lokasi): void
    {
        $guruAktif = collect($guru)->filter(fn (Guru $guruItem) => $guruItem->aktif)->values();
        $periode = CarbonPeriod::create(CarbonImmutable::now()->startOfMonth(), CarbonImmutable::now());
        $hariLibur = HariLibur::query()
            ->pluck('tanggal')
            ->map(fn ($tanggal) => CarbonImmutable::parse($tanggal)->toDateString())
            ->all();

        foreach ($periode as $tanggal) {
            $hari = CarbonImmutable::parse($tanggal);

            if ($hari->isWeekend() || in_array($hari->toDateString(), $hariLibur, true)) {
                continue;
            }

            foreach ($guruAktif as $index => $guruItem) {
                $jadwal = $guruItem->jadwalKerjaBawaan;
                $pola = ($hari->day + $index) % 19;

                if ($pola === 0) {
                    $presensi = $this->presensi($guruItem, $lokasi, $hari, [
                        'status' => 'alpha',
                        'catatan' => 'Tidak hadir tanpa keterangan pada data demo.',
                    ]);
                    $this->audit($presensi, $guruItem->pengguna, null, 'seed_alpha', ['sumber' => 'demo seeder']);

                    continue;
                }

                $terlambat = in_array($pola, [5, 11], true);
                $jamMasuk = $hari
                    ->setTimeFromTimeString($jadwal->jam_masuk)
                    ->addMinutes($terlambat ? $jadwal->toleransi_terlambat_menit + 12 + $index : max(0, $index - 2));
                $jamPulang = $hari
                    ->setTimeFromTimeString($jadwal->jam_pulang)
                    ->addMinutes(($index % 4) * 5);

                $presensi = $this->presensi($guruItem, $lokasi, $hari, [
                    'id_jadwal_kerja' => $jadwal->id,
                    'jam_masuk_pada' => $jamMasuk,
                    'jam_pulang_pada' => $jamPulang,
                    'latitude_masuk' => PresensiDemoConfig::LATITUDE_SEKOLAH + (($index + 1) * 0.0000100),
                    'longitude_masuk' => PresensiDemoConfig::LONGITUDE_SEKOLAH + (($index + 1) * 0.0000120),
                    'akurasi_masuk' => 6 + ($index % 8),
                    'jarak_masuk_meter' => 12 + ($index * 3),
                    'latitude_pulang' => PresensiDemoConfig::LATITUDE_SEKOLAH + (($index + 2) * 0.0000090),
                    'longitude_pulang' => PresensiDemoConfig::LONGITUDE_SEKOLAH + (($index + 2) * 0.0000110),
                    'akurasi_pulang' => 8 + ($index % 6),
                    'jarak_pulang_meter' => 16 + ($index * 2),
                    'status' => $terlambat ? 'terlambat' : 'hadir',
                    'id_perangkat' => 'demo-device-'.$guruItem->nip,
                ]);

                $this->audit($presensi, $guruItem->pengguna, null, 'seed_presensi_masuk', [
                    'jarak_meter' => $presensi->jarak_masuk_meter,
                    'akurasi' => $presensi->akurasi_masuk,
                ]);
                $this->audit($presensi, $guruItem->pengguna, null, 'seed_presensi_pulang', [
                    'jarak_meter' => $presensi->jarak_pulang_meter,
                    'akurasi' => $presensi->akurasi_pulang,
                ]);
            }
        }

        $guruManual = $guruAktif->get(2);
        if ($guruManual) {
            $tanggalManual = CarbonImmutable::now()->subDays(2);
            $presensiManual = $this->presensi($guruManual, $lokasi, $tanggalManual, [
                'id_jadwal_kerja' => $guruManual->id_jadwal_kerja_bawaan,
                'status' => 'hadir',
                'jam_masuk_pada' => $tanggalManual->setTime(7, 3),
                'jam_pulang_pada' => $tanggalManual->setTime(15, 8),
                'catatan' => 'Koreksi demo karena aplikasi guru sempat tidak bisa membuka GPS.',
                'id_pengoreksi' => $admin->id,
                'alasan_koreksi' => 'Simulasi koreksi manual oleh admin.',
            ]);

            $this->audit($presensiManual, $guruManual->pengguna, $admin, 'seed_koreksi_manual', [
                'alasan' => $presensiManual->alasan_koreksi,
            ]);
        }
    }
}
