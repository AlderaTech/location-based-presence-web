<?php

namespace Database\Seeders\PresensiDemo;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Murid;
use App\Models\Pengguna;
use Database\Seeders\PresensiDemo\Concerns\CreatesDemoData;
use Illuminate\Database\Seeder;

class PenggunaDemoSeeder extends Seeder
{
    use CreatesDemoData;

    public function seedAdmin(): array
    {
        return [
            'admin' => $this->pengguna('admin@presensi.test', 'Admin Sekolah', 'admin', 'ADM-001', '081200000001'),
        ];
    }

    /**
     * @return array<string, Guru>
     */
    public function seedGuru(): array
    {
        $dataGuru = [
            'guru' => ['guru@presensi.test', 'Budi Santoso, M.Pd.', '198501012010011001', 'Matematika', '081211110001'],
            'siti' => ['siti@presensi.test', 'Dra. Siti Aminah', '198802022012022002', 'Bahasa Indonesia', '081211110002'],
            'hendra' => ['hendra@presensi.test', 'Hendra Saputra, S.Pd.', '199003032015031003', 'Fisika', '081211110003'],
            'dewi' => ['dewi@presensi.test', 'Dewi Lestari, S.Pd.', '199204042018042004', 'Bahasa Inggris', '081211110004'],
        ];

        $hasil = [];
        foreach ($dataGuru as $key => [$email, $nama, $nip, $mapel, $telepon]) {
            $pengguna = $this->pengguna($email, $nama, 'guru', $nip, $telepon, 'aktif');

            $guru = Guru::query()->updateOrCreate(
                ['id_pengguna' => $pengguna->id],
                [
                    'nip' => $nip,
                    'mata_pelajaran' => $mapel,
                    'jabatan' => 'Guru Mata Pelajaran',
                    'alamat' => 'Jl. Pendidikan No. 10, Jakarta',
                    'aktif' => true,
                ],
            );

            $hasil[$key] = $guru->fresh('pengguna');
        }

        return $hasil;
    }

    /**
     * @return array<string, Kelas>
     */
    public function seedKelas(): array
    {
        $items = [
            'X-MIPA-1' => ['X-MIPA-1', 10, 'IPA', '2026/2027'],
            'X-MIPA-2' => ['X-MIPA-2', 10, 'IPA', '2026/2027'],
            'XI-MIPA-1' => ['XI-MIPA-1', 11, 'IPA', '2026/2027'],
        ];

        $hasil = [];
        foreach ($items as $key => [$nama, $tingkat, $jurusan, $tahun]) {
            $hasil[$key] = Kelas::query()->updateOrCreate(
                ['nama_kelas' => $nama],
                [
                    'tingkat' => $tingkat,
                    'jurusan' => $jurusan,
                    'tahun_ajaran' => $tahun,
                    'aktif' => true,
                ],
            );
        }

        return $hasil;
    }

    /**
     * @return array<string, MataPelajaran>
     */
    public function seedMataPelajaran(): array
    {
        $items = [
            'MAT' => ['MAT', 'Matematika', 'Matematika Wajib Kurikulum Merdeka'],
            'IND' => ['IND', 'Bahasa Indonesia', 'Bahasa dan Sastra Indonesia'],
            'ING' => ['ING', 'Bahasa Inggris', 'English for Communication'],
            'IPA' => ['IPA', 'Fisika', 'Fisika Dasar dan Penerapan'],
            'TIK' => ['TIK', 'Informatika & Pemrograman', 'Pengenalan Komputasi dan Algoritma'],
        ];

        $hasil = [];
        foreach ($items as $key => [$kode, $nama, $deskripsi]) {
            $hasil[$key] = MataPelajaran::query()->updateOrCreate(
                ['kode_mapel' => $kode],
                [
                    'nama_mapel' => $nama,
                    'deskripsi' => $deskripsi,
                    'aktif' => true,
                ],
            );
        }

        return $hasil;
    }

    /**
     * @param  array<string, Kelas>  $daftarKelas
     * @return array<string, Murid>
     */
    public function seedMurid(array $daftarKelas): array
    {
        $items = [
            'murid' => ['murid@presensi.test', 'Ahmad Fauzi', '20261001', '0012345671', 'L', 'X-MIPA-1', 'demo-device-20261001'],
            'bima' => ['bima@presensi.test', 'Bima Pratama', '20261002', '0012345672', 'L', 'X-MIPA-1', 'demo-device-20261002'],
            'citra' => ['citra@presensi.test', 'Citra Kirana', '20261003', '0012345673', 'P', 'X-MIPA-1', 'demo-device-20261003'],
            'dian' => ['dian@presensi.test', 'Dian Sastro', '20261004', '0012345674', 'P', 'X-MIPA-1', 'demo-device-20261004'],
            'eko' => ['eko@presensi.test', 'Eko Kurniawan', '20261005', '0012345675', 'L', 'X-MIPA-1', 'demo-device-20261005'],
            'fitri' => ['fitri@presensi.test', 'Fitri Handayani', '20261006', '0012345676', 'P', 'X-MIPA-1', 'demo-device-20261006'],
            'gilang' => ['gilang@presensi.test', 'Gilang Ramadhan', '20261007', '0012345677', 'L', 'X-MIPA-2', 'demo-device-20261007'],
            'hana' => ['hana@presensi.test', 'Hana Wijaya', '20261008', '0012345678', 'P', 'X-MIPA-2', 'demo-device-20261008'],
            'indra' => ['indra@presensi.test', 'Indra Lesmana', '20261009', '0012345679', 'L', 'X-MIPA-2', 'demo-device-20261009'],
            'joko' => ['joko@presensi.test', 'Joko Widodo', '20251001', '0012345680', 'L', 'XI-MIPA-1', 'demo-device-20251001'],
            'kartika' => ['kartika@presensi.test', 'Kartika Putri', '20251002', '0012345681', 'P', 'XI-MIPA-1', 'demo-device-20251002'],
            'lukman' => ['lukman@presensi.test', 'Lukman Sardi', '20251003', '0012345682', 'L', 'XI-MIPA-1', 'demo-device-20251003'],
        ];

        $hasil = [];
        foreach ($items as $key => [$email, $nama, $nis, $nisn, $jk, $kelasKode, $deviceId]) {
            $pengguna = $this->pengguna($email, $nama, 'murid', $nis, '0813'.str_pad($nis, 8, '0', STR_PAD_LEFT), 'aktif');
            $kelas = $daftarKelas[$kelasKode] ?? null;

            $murid = Murid::query()->updateOrCreate(
                ['id_pengguna' => $pengguna->id],
                [
                    'id_kelas' => $kelas?->id,
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'jenis_kelamin' => $jk,
                    'telepon_wali' => '08128888'.substr($nis, -4),
                    'alamat' => 'Jl. Siswa No. '.substr($nis, -2).', Jakarta',
                    'aktif' => true,
                ],
            );

            // Bind device ID untuk murid
            $pengguna->perangkat()->updateOrCreate(
                ['id_perangkat' => $deviceId],
                [
                    'nama_perangkat' => 'Android '.$nama,
                    'platform' => 'android',
                    'pertama_terlihat_pada' => now()->subDays(10),
                    'terakhir_terlihat_pada' => now(),
                    'aktif' => true,
                ],
            );

            $hasil[$key] = $murid->fresh(['pengguna', 'kelas']);
        }

        return $hasil;
    }
}
