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
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class SesiPresensiDinamisPayloadTest extends TestCase
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

    public function test_buka_sesi_menghasilkan_payload_qr_terstruktur_dengan_jam_tanggal_mapel_dan_kode_unik(): void
    {
        $this->seed(PresensiDemoSeeder::class);
        SesiPresensi::query()->delete();

        $guru = Pengguna::query()->where('email', 'guru@presensi.test')->firstOrFail();
        $jadwal = JadwalPelajaran::query()->where('id_guru', $guru->guru->id)->firstOrFail();

        $response = $this->actingAs($guru)->post('/admin/sesi-presensi', [
            'id_jadwal_pelajaran' => $jadwal->id,
            'tanggal_mulai' => today()->toDateString(),
            'jam_mulai' => '08:00',
            'tanggal_selesai' => today()->toDateString(),
            'jam_selesai' => '09:00',
        ]);

        $response->assertRedirect();

        $sesi = SesiPresensi::query()->latest('id')->firstOrFail();
        $this->assertNotEmpty($sesi->kode_unik);
        $this->assertNotEmpty($sesi->payload_qr);

        $payload = json_decode($sesi->payload_qr, true);
        $this->assertIsArray($payload);
        $this->assertSame('PRESENSI_QR', $payload['tipe']);
        $this->assertSame($sesi->id, $payload['id_sesi']);
        $this->assertSame(today()->toDateString(), $payload['tanggal']);
        $this->assertSame('08:00', $payload['jam_mulai']);
        $this->assertSame('09:00', $payload['jam_selesai']);
        $this->assertSame($jadwal->id_mata_pelajaran, $payload['mata_pelajaran']['id']);
        $this->assertSame($sesi->kode_unik, $payload['kode_unik']);
        $this->assertNotEmpty($payload['sig']);
    }

    public function test_murid_bisa_presensi_menggunakan_payload_qr_dinamis(): void
    {
        $this->seed(PresensiDemoSeeder::class);
        SesiPresensi::query()->delete();

        $murid = Murid::query()->where('nis', '20261001')->firstOrFail();
        $token = $this->masukMurid($murid);
        $idPerangkat = $murid->pengguna->perangkat()->first()?->id_perangkat ?? 'demo-device-20261001';

        $jadwal = JadwalPelajaran::query()->where('id_kelas', $murid->id_kelas)->firstOrFail();
        $lokasi = LokasiSekolah::query()->where('aktif', true)->firstOrFail();

        $sesi = SesiPresensi::create([
            'id_jadwal_pelajaran' => $jadwal->id,
            'id_mata_pelajaran' => $jadwal->id_mata_pelajaran,
            'id_guru' => $jadwal->id_guru,
            'tanggal' => today(),
            'kode_qr' => 'SESI-MTK-DEMO',
            'kode_unik' => strtoupper(Str::random(10)),
            'waktu_buka' => now()->subMinutes(10),
            'waktu_mulai' => now()->subMinutes(10),
            'waktu_selesai' => now()->addMinutes(50),
            'status' => 'aktif',
        ]);
        $sesi->generateDynamicPayload();
        $sesi->save();

        $response = $this->postJson('/api/mobile/presensi/scan', [
            'kode_qr' => $sesi->payload_qr,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'akurasi' => 5,
            'id_perangkat' => $idPerangkat,
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertOk()
            ->assertJsonPath('presensi.tanggal', today()->toDateString());

        $this->assertDatabaseHas('presensi', [
            'id_murid' => $murid->id,
            'id_sesi_presensi' => $sesi->id,
        ]);
    }

    public function test_perpanjangan_sesi_menghasilkan_kode_unik_baru_dan_menolak_qr_lama(): void
    {
        $this->seed(PresensiDemoSeeder::class);
        SesiPresensi::query()->delete();

        $murid = Murid::query()->where('nis', '20261001')->firstOrFail();
        $token = $this->masukMurid($murid);
        $idPerangkat = $murid->pengguna->perangkat()->first()?->id_perangkat ?? 'demo-device-20261001';

        $jadwal = JadwalPelajaran::query()->where('id_kelas', $murid->id_kelas)->firstOrFail();
        $lokasi = LokasiSekolah::query()->where('aktif', true)->firstOrFail();

        // 1. Sesi awal: 08:00 - 09:00
        $sesi = SesiPresensi::create([
            'id_jadwal_pelajaran' => $jadwal->id,
            'id_mata_pelajaran' => $jadwal->id_mata_pelajaran,
            'id_guru' => $jadwal->id_guru,
            'tanggal' => today(),
            'kode_qr' => 'SESI-AWAL',
            'kode_unik' => 'TOKEN_AWAL_123',
            'waktu_buka' => now()->subMinutes(60),
            'waktu_mulai' => now()->subMinutes(60),
            'waktu_selesai' => now()->subMinutes(10), // Sudah lewat
            'status' => 'aktif',
        ]);
        $sesi->generateDynamicPayload();
        $sesi->save();

        $payloadQrLama = $sesi->payload_qr;

        // 2. Guru memperpanjang sesi / buka sesi baru (menghasilkan kode_unik & payload_qr baru)
        $guru = Pengguna::query()->where('email', 'guru@presensi.test')->firstOrFail();
        $this->actingAs($guru)->post("/admin/sesi-presensi/{$sesi->id}/perpanjang", [
            'menit_tambahan' => 30,
        ]);

        $sesi->refresh();
        $payloadQrBaru = $sesi->payload_qr;

        $this->assertNotSame($payloadQrLama, $payloadQrBaru);
        $this->assertNotSame('TOKEN_AWAL_123', $sesi->kode_unik);

        // 3. Siswa menggunakan foto QR lama -> Ditolak!
        $responseLama = $this->postJson('/api/mobile/presensi/scan', [
            'kode_qr' => $payloadQrLama,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'akurasi' => 5,
            'id_perangkat' => $idPerangkat,
        ], ['Authorization' => 'Bearer '.$token]);

        $responseLama->assertStatus(422)
            ->assertJsonValidationErrors('kode_qr');

        // 4. Siswa menggunakan QR baru yang aktif -> Berhasil!
        $responseBaru = $this->postJson('/api/mobile/presensi/scan', [
            'kode_qr' => $payloadQrBaru,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'akurasi' => 5,
            'id_perangkat' => $idPerangkat,
        ], ['Authorization' => 'Bearer '.$token]);

        $responseBaru->assertOk();
    }

    public function test_memanipulasi_jam_selesai_di_dalam_payload_ditolak_karena_signature_invalid(): void
    {
        $this->seed(PresensiDemoSeeder::class);
        SesiPresensi::query()->delete();

        $murid = Murid::query()->where('nis', '20261001')->firstOrFail();
        $token = $this->masukMurid($murid);
        $idPerangkat = $murid->pengguna->perangkat()->first()?->id_perangkat ?? 'demo-device-20261001';

        $jadwal = JadwalPelajaran::query()->where('id_kelas', $murid->id_kelas)->firstOrFail();
        $lokasi = LokasiSekolah::query()->where('aktif', true)->firstOrFail();

        $sesi = SesiPresensi::create([
            'id_jadwal_pelajaran' => $jadwal->id,
            'id_mata_pelajaran' => $jadwal->id_mata_pelajaran,
            'id_guru' => $jadwal->id_guru,
            'tanggal' => today(),
            'kode_qr' => 'SESI-TAMPER-TEST',
            'kode_unik' => 'TOKEN_123',
            'waktu_buka' => now()->subMinutes(10),
            'waktu_mulai' => now()->subMinutes(10),
            'waktu_selesai' => now()->addMinutes(50),
            'status' => 'aktif',
        ]);
        $sesi->generateDynamicPayload();
        $sesi->save();

        // Siswa memanipulasi jam_selesai di dalam JSON payload
        $data = json_decode($sesi->payload_qr, true);
        $data['jam_selesai'] = '23:59'; // Diubah agar masa berlaku panjang
        $tamperedJson = json_encode($data);

        $response = $this->postJson('/api/mobile/presensi/scan', [
            'kode_qr' => $tamperedJson,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'akurasi' => 5,
            'id_perangkat' => $idPerangkat,
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('kode_qr');

        $this->assertStringContainsString('Tanda tangan digital', $response->json('errors.kode_qr.0'));
    }

    public function test_murid_bisa_presensi_manual_menggunakan_kode_unik(): void
    {
        $this->seed(PresensiDemoSeeder::class);
        SesiPresensi::query()->delete();

        $murid = Murid::query()->where('nis', '20261001')->firstOrFail();
        $token = $this->masukMurid($murid);
        $idPerangkat = $murid->pengguna->perangkat()->first()?->id_perangkat ?? 'demo-device-20261001';

        $jadwal = JadwalPelajaran::query()->where('id_kelas', $murid->id_kelas)->firstOrFail();
        $lokasi = LokasiSekolah::query()->where('aktif', true)->firstOrFail();

        $kodeUnikManual = 'MANUAL1234';

        $sesi = SesiPresensi::create([
            'id_jadwal_pelajaran' => $jadwal->id,
            'id_mata_pelajaran' => $jadwal->id_mata_pelajaran,
            'id_guru' => $jadwal->id_guru,
            'tanggal' => today(),
            'kode_qr' => 'SESI-MANUAL-FALLBACK',
            'kode_unik' => $kodeUnikManual,
            'waktu_buka' => now()->subMinutes(5),
            'waktu_mulai' => now()->subMinutes(5),
            'waktu_selesai' => now()->addMinutes(45),
            'status' => 'aktif',
        ]);
        $sesi->generateDynamicPayload();
        $sesi->save();

        // Murid memasukkan kode singkat 10 karakter secara manual
        $response = $this->postJson('/api/mobile/presensi/scan', [
            'kode_qr' => $kodeUnikManual,
            'latitude' => $lokasi->latitude,
            'longitude' => $lokasi->longitude,
            'akurasi' => 5,
            'id_perangkat' => $idPerangkat,
        ], ['Authorization' => 'Bearer '.$token]);

        $response->assertOk();

        $this->assertDatabaseHas('presensi', [
            'id_murid' => $murid->id,
            'id_sesi_presensi' => $sesi->id,
        ]);
    }
}
