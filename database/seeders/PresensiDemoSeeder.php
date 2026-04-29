<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\HariLibur;
use App\Models\JadwalKerja;
use App\Models\LogAuditPresensi;
use App\Models\LokasiSekolah;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Presensi;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PresensiDemoSeeder extends Seeder
{
    private const LATITUDE_SEKOLAH = -6.2000000;
    private const LONGITUDE_SEKOLAH = 106.8166660;

    public function run(): void
    {
        LogAuditPresensi::query()->where('aksi', 'like', 'seed_%')->delete();

        $jadwalKerja = $this->seedJadwalKerja();
        $lokasiSekolah = $this->seedLokasiSekolah();
        $penggunaAdmin = $this->seedPenggunaAdmin();
        $guru = $this->seedGuru($jadwalKerja);

        $this->seedPerangkat($guru);
        $this->seedHariLibur();
        $this->seedPresensi($guru, $penggunaAdmin['admin'], $lokasiSekolah['utama']);
        $this->seedPengajuanIzin($guru, $penggunaAdmin['tata_usaha']);
        $this->seedAuditLokasiPalsu($guru);
    }

    /**
     * @return array<string, JadwalKerja>
     */
    private function seedJadwalKerja(): array
    {
        $items = [
            'reguler' => ['Jadwal Reguler', '07:00', '15:00', 10, true],
            'pagi' => ['Jadwal Pagi', '06:30', '14:30', 15, true],
            'siang' => ['Jadwal Siang', '10:00', '18:00', 10, true],
            'ekstrakurikuler' => ['Pembina Ekstrakurikuler', '13:00', '17:00', 5, false],
        ];

        $jadwalKerja = [];

        foreach ($items as $kode => [$nama, $jamMasuk, $jamPulang, $toleransi, $aktif]) {
            $jadwalKerja[$kode] = JadwalKerja::query()->updateOrCreate(
                ['nama' => $nama],
                [
                    'jam_masuk' => $jamMasuk,
                    'jam_pulang' => $jamPulang,
                    'toleransi_terlambat_menit' => $toleransi,
                    'aktif' => $aktif,
                ],
            );
        }

        return $jadwalKerja;
    }

    /**
     * @return array<string, LokasiSekolah>
     */
    private function seedLokasiSekolah(): array
    {
        return [
            'utama' => LokasiSekolah::query()->updateOrCreate(
                ['nama' => 'Sekolah Utama'],
                [
                    'latitude' => self::LATITUDE_SEKOLAH,
                    'longitude' => self::LONGITUDE_SEKOLAH,
                    'radius_meter' => 150,
                    'aktif' => true,
                ],
            ),
            'olahraga' => LokasiSekolah::query()->updateOrCreate(
                ['nama' => 'Gedung Olahraga'],
                [
                    'latitude' => -6.1994300,
                    'longitude' => 106.8172100,
                    'radius_meter' => 80,
                    'aktif' => true,
                ],
            ),
            'lama' => LokasiSekolah::query()->updateOrCreate(
                ['nama' => 'Kampus Lama'],
                [
                    'latitude' => -6.2042000,
                    'longitude' => 106.8129000,
                    'radius_meter' => 100,
                    'aktif' => false,
                ],
            ),
        ];
    }

    /**
     * @return array<string, Pengguna>
     */
    private function seedPenggunaAdmin(): array
    {
        return [
            'admin' => $this->pengguna('admin@presensi.test', 'Admin Presensi', 'admin', 'ADM-001', '081200000001'),
            'tata_usaha' => $this->pengguna('tu@presensi.test', 'Tata Usaha', 'tata_usaha', 'ADM-002', '081200000002'),
            'kepala_sekolah' => $this->pengguna('kepsek@presensi.test', 'Kepala Sekolah', 'kepala_sekolah', 'ADM-003', '081200000003'),
        ];
    }

    /**
     * @param  array<string, JadwalKerja>  $jadwalKerja
     * @return array<int, Guru>
     */
    private function seedGuru(array $jadwalKerja): array
    {
        $items = [
            ['G-001', 'Guru Demo', 'guru@presensi.test', 'Matematika', 'Wali Kelas 7A', 'reguler', true],
            ['G-002', 'Siti Aminah', 'siti.aminah@presensi.test', 'Bahasa Indonesia', 'Guru', 'reguler', true],
            ['G-003', 'Budi Santoso', 'budi.santoso@presensi.test', 'IPA', 'Guru', 'pagi', true],
            ['G-004', 'Dewi Lestari', 'dewi.lestari@presensi.test', 'IPS', 'Guru', 'reguler', true],
            ['G-005', 'Rizky Pratama', 'rizky.pratama@presensi.test', 'Bahasa Inggris', 'Guru', 'siang', true],
            ['G-006', 'Nur Hasanah', 'nur.hasanah@presensi.test', 'PAI', 'Guru', 'reguler', true],
            ['G-007', 'Agus Salim', 'agus.salim@presensi.test', 'PJOK', 'Pembina OSIS', 'pagi', true],
            ['G-008', 'Maya Kartika', 'maya.kartika@presensi.test', 'Seni Budaya', 'Guru', 'siang', true],
            ['G-009', 'Fajar Nugroho', 'fajar.nugroho@presensi.test', 'Prakarya', 'Guru', 'reguler', true],
            ['G-010', 'Indah Permata', 'indah.permata@presensi.test', 'BK', 'Guru BK', 'reguler', true],
            ['G-011', 'Rani Wijaya', 'rani.wijaya@presensi.test', 'TIK', 'Operator Lab', 'pagi', true],
            ['G-012', 'Hendra Saputra', 'hendra.saputra@presensi.test', 'Fisika', 'Guru Nonaktif', 'reguler', false],
        ];

        $guru = [];

        foreach ($items as $index => [$nomor, $nama, $email, $mataPelajaran, $jabatan, $kodeJadwal, $aktif]) {
            $pengguna = $this->pengguna(
                $email,
                $nama,
                'guru',
                $nomor,
                '08123'.str_pad((string) ($index + 1), 7, '0', STR_PAD_LEFT),
                $aktif ? 'aktif' : 'nonaktif',
            );

            $guru[] = Guru::query()->updateOrCreate(
                ['pengguna_id' => $pengguna->id],
                [
                    'jadwal_kerja_bawaan_id' => $jadwalKerja[$kodeJadwal]->id,
                    'nip' => $nomor,
                    'mata_pelajaran' => $mataPelajaran,
                    'jabatan' => $jabatan,
                    'alamat' => 'Jl. Pendidikan No. '.($index + 10).', Jakarta',
                    'jadwal_kerja' => $kodeJadwal === 'siang'
                        ? 'Senin-Jumat, jadwal siang dan kegiatan tambahan'
                        : 'Senin-Jumat, jadwal reguler sekolah',
                    'notifikasi_aktif' => $index % 3 !== 0,
                    'aktif' => $aktif,
                ],
            )->fresh(['pengguna', 'jadwalKerjaBawaan']);
        }

        return $guru;
    }

    /**
     * @param  array<int, Guru>  $guru
     */
    private function seedPerangkat(array $guru): void
    {
        foreach ($guru as $index => $guruItem) {
            if (! $guruItem->aktif || $index > 7) {
                continue;
            }

            $guruItem->pengguna->perangkat()->updateOrCreate(
                ['id_perangkat' => 'demo-device-'.$guruItem->nip],
                [
                    'nama_perangkat' => 'Android Demo '.($index + 1),
                    'platform' => 'android',
                    'pertama_terlihat_pada' => now()->subDays(14 - $index),
                    'terakhir_terlihat_pada' => now()->subHours($index + 1),
                    'aktif' => true,
                ],
            );
        }
    }

    private function seedHariLibur(): void
    {
        $awalBulan = CarbonImmutable::now()->startOfMonth();
        $items = [
            [$awalBulan->addDays(4), 'Libur Sekolah Awal Bulan', 'sekolah'],
            [$awalBulan->addDays(12), 'Rapat Kerja Guru', 'sekolah'],
            [$awalBulan->addDays(20), 'Libur Nasional Simulasi', 'nasional'],
            [CarbonImmutable::now()->addMonth()->startOfMonth()->addDays(2), 'Libur Sekolah Bulan Depan', 'sekolah'],
        ];

        foreach ($items as [$tanggal, $nama, $jenis]) {
            HariLibur::query()->updateOrCreate(
                ['tanggal' => $tanggal->toDateString()],
                ['nama' => $nama, 'jenis' => $jenis],
            );
        }
    }

    /**
     * @param  array<int, Guru>  $guru
     */
    private function seedPresensi(array $guru, Pengguna $admin, LokasiSekolah $lokasi): void
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
                    'jadwal_kerja_id' => $jadwal->id,
                    'jam_masuk_pada' => $jamMasuk,
                    'jam_pulang_pada' => $jamPulang,
                    'latitude_masuk' => self::LATITUDE_SEKOLAH + (($index + 1) * 0.0000100),
                    'longitude_masuk' => self::LONGITUDE_SEKOLAH + (($index + 1) * 0.0000120),
                    'akurasi_masuk' => 6 + ($index % 8),
                    'jarak_masuk_meter' => 12 + ($index * 3),
                    'latitude_pulang' => self::LATITUDE_SEKOLAH + (($index + 2) * 0.0000090),
                    'longitude_pulang' => self::LONGITUDE_SEKOLAH + (($index + 2) * 0.0000110),
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
                'jadwal_kerja_id' => $guruManual->jadwal_kerja_bawaan_id,
                'status' => 'hadir',
                'jam_masuk_pada' => $tanggalManual->setTime(7, 3),
                'jam_pulang_pada' => $tanggalManual->setTime(15, 8),
                'catatan' => 'Koreksi demo karena aplikasi guru sempat tidak bisa membuka GPS.',
                'dikoreksi_oleh' => $admin->id,
                'alasan_koreksi' => 'Simulasi koreksi manual oleh admin.',
            ]);

            $this->audit($presensiManual, $guruManual->pengguna, $admin, 'seed_koreksi_manual', [
                'alasan' => $presensiManual->alasan_koreksi,
            ]);
        }
    }

    /**
     * @param  array<int, Guru>  $guru
     */
    private function seedPengajuanIzin(array $guru, Pengguna $peninjau): void
    {
        Storage::disk('public')->makeDirectory('dokumen-izin/demo');

        $guruAktif = collect($guru)->filter(fn (Guru $guruItem) => $guruItem->aktif)->values();
        $awalBulan = CarbonImmutable::now()->startOfMonth();

        $items = [
            [
                'guru' => $guruAktif->get(0),
                'jenis' => 'sakit',
                'tanggal_mulai' => $awalBulan->addDays(7),
                'tanggal_selesai' => $awalBulan->addDays(7),
                'alasan' => 'Demam dan perlu istirahat satu hari.',
                'status' => 'disetujui',
                'dokumen' => 'surat-sakit-g001.txt',
            ],
            [
                'guru' => $guruAktif->get(1),
                'jenis' => 'izin',
                'tanggal_mulai' => $awalBulan->addDays(9),
                'tanggal_selesai' => $awalBulan->addDays(9),
                'alasan' => 'Mengurus administrasi keluarga.',
                'status' => 'ditolak',
                'alasan_penolakan' => 'Dokumen pendukung belum lengkap.',
                'dokumen' => 'izin-g002.txt',
            ],
            [
                'guru' => $guruAktif->get(3),
                'jenis' => 'cuti',
                'tanggal_mulai' => $awalBulan->addDays(14),
                'tanggal_selesai' => $awalBulan->addDays(15),
                'alasan' => 'Cuti keluarga yang sudah dijadwalkan.',
                'status' => 'disetujui',
                'dokumen' => 'cuti-g004.txt',
            ],
            [
                'guru' => $guruAktif->get(4),
                'jenis' => 'izin',
                'tanggal_mulai' => CarbonImmutable::now()->addDays(2),
                'tanggal_selesai' => CarbonImmutable::now()->addDays(2),
                'alasan' => 'Menghadiri undangan pelatihan MGMP.',
                'status' => 'menunggu',
                'dokumen' => 'izin-pelatihan-g005.txt',
            ],
            [
                'guru' => $guruAktif->get(5),
                'jenis' => 'sakit',
                'tanggal_mulai' => CarbonImmutable::now()->addDays(4),
                'tanggal_selesai' => CarbonImmutable::now()->addDays(4),
                'alasan' => 'Kontrol kesehatan lanjutan.',
                'status' => 'menunggu',
                'dokumen' => null,
            ],
        ];

        foreach ($items as $item) {
            /** @var Guru|null $guruItem */
            $guruItem = $item['guru'];
            if (! $guruItem) {
                continue;
            }

            $pengajuan = PengajuanIzin::query()
                ->where('guru_id', $guruItem->id)
                ->where('jenis', $item['jenis'])
                ->whereDate('tanggal_mulai', $item['tanggal_mulai']->toDateString())
                ->first() ?: new PengajuanIzin([
                    'guru_id' => $guruItem->id,
                    'jenis' => $item['jenis'],
                    'tanggal_mulai' => $item['tanggal_mulai']->toDateString(),
                ]);

            $pengajuan->fill([
                'pengguna_id' => $guruItem->pengguna_id,
                'tanggal_selesai' => $item['tanggal_selesai']->toDateString(),
                'alasan' => $item['alasan'],
                'status' => $item['status'],
                'ditinjau_oleh' => $item['status'] === 'menunggu' ? null : $peninjau->id,
                'ditinjau_pada' => $item['status'] === 'menunggu' ? null : now()->subDays(2),
                'alasan_penolakan' => $item['alasan_penolakan'] ?? null,
            ])->save();

            if ($item['dokumen']) {
                $this->seedDokumenIzin($pengajuan, $item['dokumen']);
            }

            if ($item['status'] === 'disetujui') {
                foreach (CarbonPeriod::create($item['tanggal_mulai'], $item['tanggal_selesai']) as $tanggal) {
                    if (CarbonImmutable::parse($tanggal)->isWeekend()) {
                        continue;
                    }

                    $presensi = $this->presensi($guruItem, null, CarbonImmutable::parse($tanggal), [
                        'jadwal_kerja_id' => $guruItem->jadwal_kerja_bawaan_id,
                        'status' => $item['jenis'],
                        'catatan' => $item['alasan'],
                        'dikoreksi_oleh' => $peninjau->id,
                        'alasan_koreksi' => 'Persetujuan pengajuan demo #'.$pengajuan->id,
                    ]);

                    $this->audit($presensi, $guruItem->pengguna, $peninjau, 'seed_pengajuan_disetujui', [
                        'pengajuan_izin_id' => $pengajuan->id,
                        'jenis' => $item['jenis'],
                    ]);
                }
            }
        }
    }

    /**
     * @param  array<int, Guru>  $guru
     */
    private function seedAuditLokasiPalsu(array $guru): void
    {
        $guruItem = collect($guru)->first(fn (Guru $item) => $item->aktif);
        if (! $guruItem) {
            return;
        }

        LogAuditPresensi::query()->create([
            'pengguna_id' => $guruItem->pengguna_id,
            'aksi' => 'seed_lokasi_palsu_diblokir',
            'payload' => [
                'id_perangkat' => 'demo-device-'.$guruItem->nip,
                'latitude' => -6.1800000,
                'longitude' => 106.8000000,
                'lokasi_palsu' => true,
                'pesan' => 'Simulasi percobaan presensi dengan lokasi palsu.',
            ],
            'dibuat_pada' => now()->subDays(1),
        ]);
    }

    private function pengguna(
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
                'kata_sandi' => Hash::make('password'),
                'peran' => $peran,
                'nomor_pegawai' => $nomorPegawai,
                'telepon' => $telepon,
                'status' => $status,
            ],
        );
    }

    private function presensi(
        Guru $guru,
        ?LokasiSekolah $lokasi,
        CarbonImmutable $tanggal,
        array $atribut,
    ): Presensi {
        $presensi = Presensi::query()
            ->where('guru_id', $guru->id)
            ->whereDate('tanggal_presensi', $tanggal->toDateString())
            ->first() ?: new Presensi([
                'guru_id' => $guru->id,
                'tanggal_presensi' => $tanggal->toDateString(),
            ]);

        $presensi->fill(array_merge([
            'pengguna_id' => $guru->pengguna_id,
            'lokasi_sekolah_id' => $lokasi?->id,
            'jadwal_kerja_id' => $guru->jadwal_kerja_bawaan_id,
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
            'dikoreksi_oleh' => null,
            'alasan_koreksi' => null,
        ], $atribut));

        $presensi->save();

        return $presensi;
    }

    private function audit(Presensi $presensi, Pengguna $pengguna, ?Pengguna $aktor, string $aksi, array $payload): void
    {
        LogAuditPresensi::query()->create([
            'presensi_id' => $presensi->id,
            'pengguna_id' => $pengguna->id,
            'aktor_id' => $aktor?->id,
            'aksi' => $aksi,
            'payload' => $payload,
            'dibuat_pada' => now(),
        ]);
    }

    private function seedDokumenIzin(PengajuanIzin $pengajuan, string $namaFile): void
    {
        $path = 'dokumen-izin/demo/'.$namaFile;
        $isi = implode(PHP_EOL, [
            'Dokumen pendukung demo',
            'Nomor pengajuan: '.$pengajuan->id,
            'Guru: '.$pengajuan->pengguna->nama,
            'Jenis: '.$pengajuan->jenis,
            'Tanggal: '.$pengajuan->tanggal_mulai->toDateString().' s/d '.$pengajuan->tanggal_selesai->toDateString(),
        ]);

        Storage::disk('public')->put($path, $isi);

        $pengajuan->dokumen()->updateOrCreate(
            ['path' => $path],
            [
                'disk' => 'public',
                'nama_asli' => $namaFile,
                'tipe_mime' => 'text/plain',
                'ukuran' => strlen($isi),
            ],
        );
    }
}
