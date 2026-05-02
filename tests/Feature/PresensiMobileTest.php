<?php

namespace Tests\Feature;

use App\Models\DokumenIzin;
use App\Models\Guru;
use App\Models\JadwalKerja;
use App\Models\LokasiSekolah;
use App\Models\Pengguna;
use App\Models\Presensi;
use Database\Seeders\PresensiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function test_seeder_membuat_surat_sakit_demo_berformat_pdf(): void
    {
        Storage::fake('public');

        $this->seed(PresensiDemoSeeder::class);

        $this->assertDatabaseHas('dokumen_izin', [
            'nama_asli' => 'surat-sakit-g001.pdf',
            'tipe_mime' => 'application/pdf',
        ]);

        $dokumen = DokumenIzin::query()
            ->where('nama_asli', 'surat-sakit-g001.pdf')
            ->firstOrFail();

        Storage::disk('public')->assertExists($dokumen->path);
        $this->assertStringStartsWith('%PDF-1.4', Storage::disk('public')->get($dokumen->path));
    }

    public function test_pengajuan_izin_mobile_menolak_dokumen_txt(): void
    {
        $guru = $this->buatGuru();
        $token = $this->masuk($guru);

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/mobile/pengajuan-izin', [
            'jenis' => 'sakit',
            'tanggal_mulai' => today()->addDay()->toDateString(),
            'tanggal_selesai' => today()->addDay()->toDateString(),
            'alasan' => 'Demam.',
            'dokumen' => UploadedFile::fake()->createWithContent('surat-sakit.txt', 'Surat sakit demo'),
        ])->assertStatus(422)
            ->assertJsonValidationErrors('dokumen');
    }

    public function test_pengajuan_izin_mobile_menerima_dokumen_pdf(): void
    {
        Storage::fake('public');

        $guru = $this->buatGuru();
        $token = $this->masuk($guru);

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/mobile/pengajuan-izin', [
            'jenis' => 'sakit',
            'tanggal_mulai' => today()->addDay()->toDateString(),
            'tanggal_selesai' => today()->addDay()->toDateString(),
            'alasan' => 'Demam.',
            'dokumen' => UploadedFile::fake()->create('surat-sakit.pdf', 100, 'application/pdf'),
        ])->assertCreated()
            ->assertJsonPath('pengajuan_izin.jenis', 'sakit');

        $this->assertDatabaseHas('dokumen_izin', [
            'nama_asli' => 'surat-sakit.pdf',
            'tipe_mime' => 'application/pdf',
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
