<?php

namespace Database\Seeders\PresensiDemo;

use App\Models\Guru;
use App\Models\JadwalKerja;
use App\Models\Pengguna;
use Database\Seeders\PresensiDemo\Concerns\MembuatDataDemo;
use Illuminate\Database\Seeder;

class PenggunaDemoSeeder extends Seeder
{
    use MembuatDataDemo;

    public function run(): void
    {
        $jadwalKerja = app(JadwalKerjaDemoSeeder::class)->seed();
        $guru = $this->seedGuru($jadwalKerja);

        $this->seedPenggunaAdmin();
        $this->seedPerangkat($guru);
    }

    /**
     * @return array<string, Pengguna>
     */
    public function seedPenggunaAdmin(): array
    {
        return [
            'admin' => $this->pengguna('admin@presensi.test', 'Admin Presensi', 'admin', 'ADM-001', '081200000001'),
            'kepala_sekolah' => $this->pengguna('kepsek@presensi.test', 'Kepala Sekolah', 'kepala_sekolah', 'ADM-003', '081200000003'),
        ];
    }

    /**
     * @param  array<string, JadwalKerja>  $jadwalKerja
     * @return array<int, Guru>
     */
    public function seedGuru(array $jadwalKerja): array
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
                ['id_pengguna' => $pengguna->id],
                [
                    'id_jadwal_kerja_bawaan' => $jadwalKerja[$kodeJadwal]->id,
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
    public function seedPerangkat(array $guru): void
    {
        foreach ($guru as $index => $guruItem) {
            if (! $guruItem->aktif || $index > 7) {
                continue;
            }

            if ($guruItem->pengguna->email === 'guru@presensi.test') {
                $guruItem->pengguna->perangkat()->update(['aktif' => false]);

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
}
