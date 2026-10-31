<?php

namespace Tests\Feature;

use App\Models\DokumenIzin;
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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class PresensiMobileTest extends TestCase
{
    use RefreshDatabase;

    public function test_murid_masuk_mengikat_perangkat_pertama(): void
    {
        $data = $this->buatMurid();
        $murid = $data['murid'];

        $response = $this->postJson('/api/mobile/autentikasi/masuk', [
            'email' => $murid->pengguna->email,
            'kata_sandi' => 'password',
            'id_perangkat' => 'perangkat-1',
            'nama_perangkat' => 'Android Demo',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'tipe_token', 'pengguna' => ['murid']]);

        $this->assertDatabaseHas('perangkat_pengguna', [
            'id_pengguna' => $murid->id_pengguna,
            'id_perangkat' => 'perangkat-1',
            'aktif' => true,
        ]);
    }

    public function test_murid_bisa_masuk_menggunakan_nis(): void
    {
        $data = $this->buatMurid();
        $murid = $data['murid'];

        $response = $this->postJson('/api/mobile/autentikasi/masuk', [
            'email' => $murid->nis,
            'kata_sandi' => 'password',
            'id_perangkat' => 'perangkat-1',
            'nama_perangkat' => 'Android Demo',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'tipe_token', 'pengguna' => ['murid']]);
    }

    public function test_masuk_dari_perangkat_berbeda_ditolak(): void
    {
        $data = $this->buatMurid();
        $murid = $data['murid'];

        $murid->pengguna->perangkat()->create([
            'id_perangkat' => 'perangkat-1',
            'nama_perangkat' => 'Android Demo',
            'platform' => 'android',
            'aktif' => true,
            'pertama_terlihat_pada' => now(),
            'terakhir_terlihat_pada' => now(),
        ]);

        $this->postJson('/api/mobile/autentikasi/masuk', [
            'email' => $murid->pengguna->email,
            'kata_sandi' => 'password',
            'id_perangkat' => 'perangkat-2',
        ])->assertStatus(423);
    }

    public function test_murid_bisa_presensi_dengan_scan_qr_dan_di_dalam_geofence(): void
    {
        $data = $this->buatMurid();
        $murid = $data['murid'];
        $jadwal = $data['jadwal'];
        $token = $this->masuk($murid);

        $sesi = SesiPresensi::create([
            'id_jadwal_pelajaran' => $jadwal->id,
            'id_guru' => $jadwal->id_guru,
            'tanggal' => today(),
            'kode_qr' => 'QR-TEST-'.Str::upper(Str::random(12)),
            'waktu_buka' => now()->subMinutes(5),
            'waktu_tutup' => now()->addMinutes(45),
            'status' => 'aktif',
        ]);

        $this->postJson('/api/mobile/presensi/scan', [
            'kode_qr' => $sesi->kode_qr,
            'latitude' => -6.2,
            'longitude' => 106.816666,
            'akurasi' => 8,
            'id_perangkat' => 'perangkat-1',
            'lokasi_palsu' => false,
        ], ['Authorization' => 'Bearer '.$token])
            ->assertOk()
            ->assertJsonPath('presensi.tanggal', today()->toDateString());

        $this->assertTrue(Presensi::query()
            ->where('id_murid', $murid->id)
            ->where('id_jadwal_pelajaran', $jadwal->id)
            ->whereDate('tanggal_presensi', today())
            ->exists());
    }

    public function test_lokasi_palsu_diblokir_dan_dicatat_di_log(): void
    {
        $data = $this->buatMurid();
        $murid = $data['murid'];
        $jadwal = $data['jadwal'];
        $token = $this->masuk($murid);

        $sesi = SesiPresensi::create([
            'id_jadwal_pelajaran' => $jadwal->id,
            'id_guru' => $jadwal->id_guru,
            'tanggal' => today(),
            'kode_qr' => 'QR-FAKE-'.Str::upper(Str::random(12)),
            'waktu_buka' => now()->subMinutes(5),
            'waktu_tutup' => now()->addMinutes(45),
            'status' => 'aktif',
        ]);

        $this->postJson('/api/mobile/presensi/scan', [
            'kode_qr' => $sesi->kode_qr,
            'latitude' => -6.2,
            'longitude' => 106.816666,
            'akurasi' => 8,
            'id_perangkat' => 'perangkat-1',
            'lokasi_palsu' => true,
        ], ['Authorization' => 'Bearer '.$token])
            ->assertStatus(422)
            ->assertJsonValidationErrors('lokasi_palsu');

        $this->assertDatabaseMissing('presensi', ['id_murid' => $murid->id]);
        $this->assertDatabaseHas('log_audit_presensi', [
            'id_pengguna' => $murid->id_pengguna,
            'aksi' => 'lokasi_palsu_diblokir',
        ]);
    }

    public function test_seeder_membuat_surat_sakit_demo_berformat_pdf(): void
    {
        Storage::fake('public');

        $this->seed(PresensiDemoSeeder::class);

        $this->assertDatabaseHas('dokumen_izin', [
            'nama_asli' => 'surat-dokter-klinik-dian.pdf',
            'tipe_mime' => 'application/pdf',
        ]);

        $dokumen = DokumenIzin::query()
            ->where('nama_asli', 'surat-dokter-klinik-dian.pdf')
            ->firstOrFail();

        Storage::disk('public')->assertExists($dokumen->path);
        $this->assertStringStartsWith('%PDF-1.4', Storage::disk('public')->get($dokumen->path));
    }

    public function test_pengajuan_izin_mobile_menolak_dokumen_txt(): void
    {
        $data = $this->buatMurid();
        $murid = $data['murid'];
        $jadwal = $data['jadwal'];
        $token = $this->masuk($murid);

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/mobile/pengajuan-izin', [
            'id_jadwal_pelajaran' => $jadwal->id,
            'jenis' => 'sakit',
            'tanggal_mulai' => today()->toDateString(),
            'tanggal_selesai' => today()->toDateString(),
            'alasan' => 'Demam tinggi.',
            'dokumen' => UploadedFile::fake()->createWithContent('surat-sakit.txt', 'Surat sakit demo'),
        ])->assertStatus(422)
            ->assertJsonValidationErrors('dokumen');
    }

    public function test_pengajuan_izin_mobile_menerima_dokumen_pdf(): void
    {
        Storage::fake('public');

        $data = $this->buatMurid();
        $murid = $data['murid'];
        $jadwal = $data['jadwal'];
        $token = $this->masuk($murid);

        $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->post('/api/mobile/pengajuan-izin', [
            'id_jadwal_pelajaran' => $jadwal->id,
            'jenis' => 'sakit',
            'tanggal_mulai' => today()->toDateString(),
            'tanggal_selesai' => today()->toDateString(),
            'alasan' => 'Demam tinggi.',
            'dokumen' => UploadedFile::fake()->create('surat-sakit.pdf', 100, 'application/pdf'),
        ])->assertCreated()
            ->assertJsonPath('pengajuan_izin.jenis', 'sakit');

        $this->assertDatabaseHas('dokumen_izin', [
            'nama_asli' => 'surat-sakit.pdf',
            'tipe_mime' => 'application/pdf',
        ]);
    }

    private function buatMurid(): array
    {
        $kelas = Kelas::create([
            'nama_kelas' => 'X-MIPA-1',
            'tingkat' => 'X',
            'jurusan' => 'MIPA',
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => 'MAT-01',
            'nama_mapel' => 'Matematika Wajib',
            'deskripsi' => 'Pelajaran matematika',
        ]);

        $lokasi = LokasiSekolah::create([
            'nama' => 'Sekolah',
            'latitude' => -6.2,
            'longitude' => 106.816666,
            'radius_meter' => 100,
            'aktif' => true,
        ]);

        $penggunaGuru = Pengguna::create([
            'nama' => 'Guru Pengampu Test',
            'email' => 'guru.pengampu@example.test',
            'kata_sandi' => Hash::make('password'),
            'peran' => 'guru',
            'status' => 'aktif',
        ]);

        $guru = Guru::create([
            'id_pengguna' => $penggunaGuru->id,
            'nip' => 'G-TEST-001',
            'aktif' => true,
        ]);

        $hariMap = [
            0 => 'minggu',
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ];
        $hariIni = $hariMap[now()->dayOfWeek] ?? 'senin';

        $jadwal = JadwalPelajaran::create([
            'id_mata_pelajaran' => $mapel->id,
            'id_kelas' => $kelas->id,
            'id_guru' => $guru->id,
            'id_lokasi_sekolah' => $lokasi->id,
            'hari' => $hariIni,
            'jam_mulai' => now()->subMinutes(10)->format('H:i:s'),
            'jam_selesai' => now()->addMinutes(60)->format('H:i:s'),
            'toleransi_terlambat_menit' => 15,
            'ruangan' => 'R-101',
            'aktif' => true,
        ]);

        $penggunaMurid = Pengguna::create([
            'nama' => 'Murid Test',
            'email' => 'murid.test@example.test',
            'kata_sandi' => Hash::make('password'),
            'peran' => 'murid',
            'status' => 'aktif',
        ]);

        $murid = Murid::create([
            'id_pengguna' => $penggunaMurid->id,
            'id_kelas' => $kelas->id,
            'nis' => '20261001',
            'nisn' => '0098765432',
            'jenis_kelamin' => 'L',
        ]);

        return [
            'murid' => $murid,
            'jadwal' => $jadwal,
            'guru' => $guru,
            'lokasi' => $lokasi,
        ];
    }

    private function masuk(Murid $murid): string
    {
        return $this->postJson('/api/mobile/autentikasi/masuk', [
            'email' => $murid->pengguna->email,
            'kata_sandi' => 'password',
            'id_perangkat' => 'perangkat-1',
        ])->json('token');
    }
}
