<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalKerja;
use App\Models\LokasiSekolah;
use App\Models\Pengguna;
use App\Models\Presensi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PresensiMobileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_masuk_mengikat_perangkat_pertama(): void
    {
        $guru = $this->buatGuru();

        $response = $this->postJson('/api/mobile/autentikasi/masuk', [
            'email' => $guru->pengguna->email,
            'kata_sandi' => 'password',
            'id_perangkat' => 'perangkat-1',
            'nama_perangkat' => 'Android Demo',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'tipe_token', 'pengguna' => ['guru']]);

        $this->assertDatabaseHas('perangkat_pengguna', [
            'id_pengguna' => $guru->id_pengguna,
            'id_perangkat' => 'perangkat-1',
            'aktif' => true,
        ]);
    }

    public function test_masuk_dari_perangkat_berbeda_ditolak(): void
    {
        $guru = $this->buatGuru();
        $guru->pengguna->perangkat()->create([
            'id_perangkat' => 'perangkat-1',
            'nama_perangkat' => 'Android Demo',
            'platform' => 'android',
            'aktif' => true,
            'pertama_terlihat_pada' => now(),
            'terakhir_terlihat_pada' => now(),
        ]);

        $this->postJson('/api/mobile/autentikasi/masuk', [
            'email' => $guru->pengguna->email,
            'kata_sandi' => 'password',
            'id_perangkat' => 'perangkat-2',
        ])->assertStatus(423);
    }

    public function test_guru_bisa_presensi_masuk_di_dalam_geofence(): void
    {
        $guru = $this->buatGuru();
        $token = $this->masuk($guru);

        $this->postJson('/api/mobile/presensi/catat', [
            'jenis' => 'masuk',
            'latitude' => -6.2,
            'longitude' => 106.816666,
            'akurasi' => 8,
            'id_perangkat' => 'perangkat-1',
            'lokasi_palsu' => false,
        ], ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonPath('presensi.tanggal', today()->toDateString());

        $this->assertTrue(Presensi::query()
            ->where('id_guru', $guru->id)
            ->whereDate('tanggal_presensi', today())
            ->exists());
    }

    public function test_lokasi_palsu_diblokir_dan_dicatat_di_log(): void
    {
        $guru = $this->buatGuru();
        $token = $this->masuk($guru);

        $this->postJson('/api/mobile/presensi/catat', [
            'jenis' => 'masuk',
            'latitude' => -6.2,
            'longitude' => 106.816666,
            'akurasi' => 8,
            'id_perangkat' => 'perangkat-1',
            'lokasi_palsu' => true,
        ], ['Authorization' => 'Bearer '.$token])
            ->assertStatus(422)
            ->assertJsonValidationErrors('lokasi_palsu');

        $this->assertDatabaseMissing('presensi', ['id_guru' => $guru->id]);
        $this->assertDatabaseHas('log_audit_presensi', [
            'id_pengguna' => $guru->id_pengguna,
            'aksi' => 'lokasi_palsu_diblokir',
        ]);
    }

    private function buatGuru(): Guru
    {
        $jadwal = JadwalKerja::create([
            'nama' => 'Reguler',
            'jam_masuk' => now()->addHour()->format('H:i'),
            'jam_pulang' => now()->addHours(8)->format('H:i'),
            'toleransi_terlambat_menit' => 10,
            'aktif' => true,
        ]);

        LokasiSekolah::create([
            'nama' => 'Sekolah',
            'latitude' => -6.2,
            'longitude' => 106.816666,
            'radius_meter' => 100,
            'aktif' => true,
        ]);

        $pengguna = Pengguna::create([
            'nama' => 'Guru Test',
            'email' => 'guru@example.test',
            'kata_sandi' => Hash::make('password'),
            'peran' => 'guru',
            'status' => 'aktif',
        ]);

        return Guru::create([
            'id_pengguna' => $pengguna->id,
            'id_jadwal_kerja_bawaan' => $jadwal->id,
            'nip' => 'T-001',
            'mata_pelajaran' => 'Matematika',
            'aktif' => true,
        ]);
    }

    private function masuk(Guru $guru): string
    {
        return $this->postJson('/api/mobile/autentikasi/masuk', [
            'email' => $guru->pengguna->email,
            'kata_sandi' => 'password',
            'id_perangkat' => 'perangkat-1',
        ])->json('token');
    }
}
