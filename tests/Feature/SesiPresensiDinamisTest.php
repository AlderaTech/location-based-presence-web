<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\LokasiSekolah;
use App\Models\MataPelajaran;
use App\Models\Murid;
use App\Models\Pengguna;
use App\Models\Presensi;
use App\Models\SesiPresensi;
use Database\Seeders\PresensiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SesiPresensiDinamisTest extends TestCase
{
    use RefreshDatabase;

    private function masukMurid(Murid $murid): string
    {
        $perangkat = $murid->pengguna->perangkat()->first();
        $idPerangkat = $perangkat ? $perangkat->id_perangkat : 'demo-device-20261001';

        $response = $this->postJson('/api/mobile/autentikasi/masuk', [
            'email' => $murid->pengguna->email,
            'kata_sandi' => 'password',
            'id_perangkat' => $idPerangkat,
            'nama_perangkat' => 'Android Unit Test',
        ]);

        return (string) $response->json('token');
    }

    public function test_guru_bisa_membuka_sesi_qr_dengan_rentang_tanggal_dan_jam_spesifik_mapel(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        SesiPresensi::query()->delete();

        $guru = Pengguna::query()->where('email', 'guru@presensi.test')->firstOrFail();
        $jadwal = JadwalPelajaran::query()->where('id_guru', $guru->guru->id)->firstOrFail();

        $response = $this->actingAs($guru)->post('/admin/sesi-presensi', [
            'id_jadwal_pelajaran' => $jadwal->id,
            'tanggal_mulai' => today()->toDateString(),
            'jam_mulai' => '07:30',
            'tanggal_selesai' => today()->toDateString(),
            'jam_selesai' => '09:00',
        ]);

        $response->assertRedirect();

        $sesi = SesiPresensi::query()->latest('id')->first();
        $this->assertNotNull($sesi);
        $this->assertSame($jadwal->id, $sesi->id_jadwal_pelajaran);
        $this->assertSame($jadwal->id_mata_pelajaran, $sesi->id_mata_pelajaran);
        $this->assertSame('07:30', $sesi->waktu_mulai->format('H:i'));
        $this->assertSame('09:00', $sesi->waktu_selesai->format('H:i'));
        $this->assertSame('aktif', $sesi->status);
        $this->assertTrue(str_starts_with($sesi->kode_qr, 'SESI-'));
    }

    public function test_qr_presensi_spesifik_ke_satu_mata_pelajaran_dan_menolak_penggunaan_di_mapel_berbeda(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $murid = Murid::query()->where('nis', '20261001')->firstOrFail();
        $token = $this->masukMurid($murid);
        $idPerangkat = $murid->pengguna->perangkat()->first()?->id_perangkat ?? 'demo-device-20261001';

        $kelas = $murid->kelas;
        $mapelA = MataPelajaran::query()->firstOrCreate(['kode_mapel' => 'MTK'], ['nama_mapel' => 'Matematika']);
        $mapelB = MataPelajaran::query()->firstOrCreate(['kode_mapel' => 'FIS'], ['nama_mapel' => 'Fisika']);

        $guru = Guru::query()->firstOrFail();
        $lokasi = LokasiSekolah::query()->where('aktif', true)->firstOrFail();

        $jadwalA = JadwalPelajaran::create([
            'id_mata_pelajaran' => $mapelA->id,
            'id_kelas' => $kelas->id,
            'id_guru' => $guru->id,
            'id_lokasi_sekolah' => $lokasi->id,
            'hari' => 'senin',
            'jam_mulai' => '07:30',
            'jam_selesai' => '09:00',
            'aktif' => true,
        ]);

        $jadwalB = JadwalPelajaran::create([
            'id_mata_pelajaran' => $mapelB->id,
            'id_kelas' => $kelas->id,
            'id_guru' => $guru->id,
            'id_lokasi_sekolah' => $lokasi->id,
            'hari' => 'senin',
            'jam_mulai' => '09:15',
            'jam_selesai' => '10:45',
            'aktif' => true,
        ]);

        // Sesi QR dibuka khusus untuk Mapel A (Matematika)
        $sesiA = SesiPresensi::create([
            'id_jadwal_pelajaran' => $jadwalA->id,
            'id_mata_pelajaran' => $mapelA->id,
            'id_guru' => $guru->id,
            'tanggal' => today(),
            'kode_qr' => 'SESI-MTK-'.Str::upper(Str::random(8)),
            'waktu_buka' => now()->subMinutes(10),
            'waktu_mulai' => now()->subMinutes(10),
            'waktu_selesai' => now()->addMinutes(50),
            'status' => 'aktif',
        ]);

        // 1. Murid mencoba menggunakan QR Mapel A tetapi menargetkan Jadwal B (Fisika) -> HARUS DITOLAK
        $responseSalahMapel = $this->postJson('/api/mobile/presensi/scan', [
            'kode_qr' => $sesiA->kode_qr,
            'id_jadwal_pelajaran' => $jadwalB->id,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'akurasi' => 5,
            'id_perangkat' => $idPerangkat,
        ], ['Authorization' => 'Bearer '.$token]);

        $responseSalahMapel->assertStatus(422)
            ->assertJsonValidationErrors('kode_qr');

        // Pastikan tidak ada presensi tercatat di Fisika
        $this->assertDatabaseMissing('presensi', [
            'id_murid' => $murid->id,
            'id_jadwal_pelajaran' => $jadwalB->id,
        ]);

        // 2. Murid scan QR Mapel A untuk Mapel A -> HARUS BERHASIL
        $responseBenar = $this->postJson('/api/mobile/presensi/scan', [
            'kode_qr' => $sesiA->kode_qr,
            'id_jadwal_pelajaran' => $jadwalA->id,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'akurasi' => 5,
            'id_perangkat' => $idPerangkat,
        ], ['Authorization' => 'Bearer '.$token]);

        $responseBenar->assertOk();

        // Presensi Matematika tercatat
        $this->assertDatabaseHas('presensi', [
            'id_murid' => $murid->id,
            'id_jadwal_pelajaran' => $jadwalA->id,
        ]);
        // Presensi Fisika tetap tidak tercatat
        $this->assertDatabaseMissing('presensi', [
            'id_murid' => $murid->id,
            'id_jadwal_pelajaran' => $jadwalB->id,
        ]);
    }

    public function test_qr_yang_disimpan_murid_ditolak_jika_waktu_hidup_qr_telah_kedaluwarsa(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $murid = Murid::query()->where('nis', '20261001')->firstOrFail();
        $token = $this->masukMurid($murid);
        $idPerangkat = $murid->pengguna->perangkat()->first()?->id_perangkat ?? 'demo-device-20261001';

        $jadwal = JadwalPelajaran::query()->where('id_kelas', $murid->id_kelas)->firstOrFail();
        $lokasi = LokasiSekolah::query()->where('aktif', true)->firstOrFail();

        // Sesi dibuat dengan waktu selesai yang SUDAH LEWAT (misal kedaluwarsa 5 menit lalu)
        $sesiKedaluwarsa = SesiPresensi::create([
            'id_jadwal_pelajaran' => $jadwal->id,
            'id_mata_pelajaran' => $jadwal->id_mata_pelajaran,
            'id_guru' => $jadwal->id_guru,
            'tanggal' => today(),
            'kode_qr' => 'SESI-EXPIRED-'.Str::upper(Str::random(8)),
            'waktu_buka' => now()->subMinutes(60),
            'waktu_mulai' => now()->subMinutes(60),
            'waktu_selesai' => now()->subMinutes(5), // Telah berakhir
            'status' => 'aktif',
        ]);

        // Siswa mencoba scan QR yang disimpan / difoto
        $response = $this->postJson('/api/mobile/presensi/scan', [
            'kode_qr' => $sesiKedaluwarsa->kode_qr,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'akurasi' => 5,
            'id_perangkat' => $idPerangkat,
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('kode_qr');

        $this->assertStringContainsString('kedaluwarsa', $response->json('errors.kode_qr.0'));

        // Sesi otomatis berubah status menjadi 'kedaluwarsa' di database
        $this->assertSame('kedaluwarsa', $sesiKedaluwarsa->fresh()->status);

        // Tidak ada catatan presensi dari sesi yang kedaluwarsa
        $this->assertDatabaseMissing('presensi', [
            'id_sesi_presensi' => $sesiKedaluwarsa->id,
        ]);
    }

    public function test_qr_ditolak_jika_belum_memasuki_waktu_mulai(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $murid = Murid::query()->where('nis', '20261001')->firstOrFail();
        $token = $this->masukMurid($murid);
        $idPerangkat = $murid->pengguna->perangkat()->first()?->id_perangkat ?? 'demo-device-20261001';

        $jadwal = JadwalPelajaran::query()->where('id_kelas', $murid->id_kelas)->firstOrFail();
        $lokasi = LokasiSekolah::query()->where('aktif', true)->firstOrFail();

        // Sesi dibuat untuk jam di masa depan
        $sesiMasaDepan = SesiPresensi::create([
            'id_jadwal_pelajaran' => $jadwal->id,
            'id_mata_pelajaran' => $jadwal->id_mata_pelajaran,
            'id_guru' => $jadwal->id_guru,
            'tanggal' => today(),
            'kode_qr' => 'SESI-FUTURE-'.Str::upper(Str::random(8)),
            'waktu_buka' => now(),
            'waktu_mulai' => now()->addMinutes(30), // Belum mulai
            'waktu_selesai' => now()->addMinutes(90),
            'status' => 'aktif',
        ]);

        $response = $this->postJson('/api/mobile/presensi/scan', [
            'kode_qr' => $sesiMasaDepan->kode_qr,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'akurasi' => 5,
            'id_perangkat' => $idPerangkat,
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('kode_qr');

        $this->assertStringContainsString('belum dimulai', $response->json('errors.kode_qr.0'));
    }

    public function test_guru_bisa_memperpanjang_waktu_hidup_sesi_qr(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $guru = Pengguna::query()->where('email', 'guru@presensi.test')->firstOrFail();
        $jadwal = JadwalPelajaran::query()->where('id_guru', $guru->guru->id)->firstOrFail();

        $sesi = SesiPresensi::create([
            'id_jadwal_pelajaran' => $jadwal->id,
            'id_mata_pelajaran' => $jadwal->id_mata_pelajaran,
            'id_guru' => $jadwal->id_guru,
            'tanggal' => today(),
            'kode_qr' => 'SESI-EXT-'.Str::upper(Str::random(8)),
            'waktu_buka' => now()->subMinutes(30),
            'waktu_mulai' => now()->subMinutes(30),
            'waktu_selesai' => now()->addMinutes(5),
            'status' => 'aktif',
        ]);

        $waktuAwal = $sesi->waktu_selesai;

        $response = $this->actingAs($guru)->post("/admin/sesi-presensi/{$sesi->id}/perpanjang", [
            'menit_tambahan' => 15,
        ]);

        $response->assertRedirect();

        $sesi->refresh();
        $this->assertTrue($sesi->waktu_selesai->gt($waktuAwal));
        $this->assertSame('aktif', $sesi->status);
    }
}
