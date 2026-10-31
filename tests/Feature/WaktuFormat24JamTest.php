<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\LokasiSekolah;
use App\Models\MataPelajaran;
use App\Models\Pengguna;
use Database\Seeders\PresensiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaktuFormat24JamTest extends TestCase
{
    use RefreshDatabase;

    private Pengguna $admin;
    private Pengguna $guruPengguna;
    private Guru $guru;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PresensiDemoSeeder::class);

        $this->admin = Pengguna::where('peran', 'admin')->first();
        $this->guru = Guru::first();
        $this->guruPengguna = $this->guru->pengguna;
    }

    public function test_form_jadwal_pelajaran_menggunakan_timepicker_24_jam_tanpa_native_time(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.jadwal-pelajaran.create'));

        $response->assertStatus(200);
        $response->assertSee('timepicker-24');
        $response->assertSee('pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$"', false);
        $response->assertSee('Format 24 jam');
        $response->assertDontSee('<input type="time" name="jam_mulai"', false);
        $response->assertDontSee('<input type="time" name="jam_selesai"', false);
    }

    public function test_halaman_sesi_presensi_menggunakan_timepicker_24_jam(): void
    {
        $response = $this->actingAs($this->guruPengguna)->get(route('admin.sesi-presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('timepicker-24');
        $response->assertSee('Jam Mulai (24 Jam)');
        $response->assertSee('Jam Selesai (24 Jam)');
        $response->assertDontSee('<input type="time" name="jam_mulai"', false);
        $response->assertDontSee('<input type="time" name="jam_selesai"', false);
    }

    public function test_validasi_jadwal_pelajaran_menerima_format_24_jam_dan_menolak_format_am_pm(): void
    {
        $kelas = Kelas::first();
        $mapel = MataPelajaran::first();
        $lokasi = LokasiSekolah::first();

        // 1. Format 24 Jam Valid (13:30 - 15:00)
        $validResponse = $this->actingAs($this->admin)->post(route('admin.jadwal-pelajaran.store'), [
            'id_kelas' => $kelas->id,
            'id_mata_pelajaran' => $mapel->id,
            'id_guru' => $this->guru->id,
            'id_lokasi_sekolah' => $lokasi->id,
            'hari' => 'senin',
            'jam_mulai' => '13:30',
            'jam_selesai' => '15:00',
            'toleransi_terlambat_menit' => 15,
            'ruangan' => 'Lab 1',
            'aktif' => '1',
        ]);
        $validResponse->assertRedirect(route('admin.jadwal-pelajaran.index'));
        $this->assertDatabaseHas('jadwal_pelajaran', [
            'jam_mulai' => '13:30',
            'jam_selesai' => '15:00',
        ]);

        // 2. Format 12 Jam AM/PM Ditolak
        $invalidResponse = $this->actingAs($this->admin)->post(route('admin.jadwal-pelajaran.store'), [
            'id_kelas' => $kelas->id,
            'id_mata_pelajaran' => $mapel->id,
            'id_guru' => $this->guru->id,
            'id_lokasi_sekolah' => $lokasi->id,
            'hari' => 'selasa',
            'jam_mulai' => '01:30 PM',
            'jam_selesai' => '03:00 PM',
            'toleransi_terlambat_menit' => 15,
        ]);
        $invalidResponse->assertSessionHasErrors(['jam_mulai', 'jam_selesai']);
    }

    public function test_validasi_sesi_presensi_guru_menerima_format_24_jam_dan_menolak_am_pm(): void
    {
        // Bersihkan sesi presensi demo agar tidak terkena constraint duplikat aktif
        \App\Models\SesiPresensi::query()->delete();

        $jadwal = JadwalPelajaran::where('id_guru', $this->guru->id)->first();

        // 1. Format 24 Jam Valid
        $validResponse = $this->actingAs($this->guruPengguna)->post(route('admin.sesi-presensi.store'), [
            'id_jadwal_pelajaran' => $jadwal->id,
            'tanggal_mulai' => today()->toDateString(),
            'jam_mulai' => '14:00',
            'tanggal_selesai' => today()->toDateString(),
            'jam_selesai' => '16:00',
        ]);
        $validResponse->assertSessionHasNoErrors();
        $validResponse->assertRedirect();

        // 2. Format AM/PM Ditolak
        $invalidResponse = $this->actingAs($this->guruPengguna)->post(route('admin.sesi-presensi.store'), [
            'id_jadwal_pelajaran' => $jadwal->id,
            'tanggal_mulai' => today()->toDateString(),
            'jam_mulai' => '02:00 PM',
            'tanggal_selesai' => today()->toDateString(),
            'jam_selesai' => '04:00 PM',
        ]);
        $invalidResponse->assertSessionHasErrors(['jam_mulai', 'jam_selesai']);
    }

    public function test_halaman_show_sesi_presensi_memiliki_timepicker_24_jam_pada_modal_perpanjang(): void
    {
        \App\Models\SesiPresensi::query()->delete();
        $jadwal = JadwalPelajaran::where('id_guru', $this->guru->id)->first();

        $this->actingAs($this->guruPengguna)->post(route('admin.sesi-presensi.store'), [
            'id_jadwal_pelajaran' => $jadwal->id,
            'tanggal_mulai' => today()->toDateString(),
            'jam_mulai' => '07:30',
            'tanggal_selesai' => today()->toDateString(),
            'jam_selesai' => '09:30',
        ]);

        $sesi = \App\Models\SesiPresensi::first();
        $response = $this->actingAs($this->guruPengguna)->get(route('admin.sesi-presensi.show', $sesi));

        $response->assertStatus(200);
        $response->assertSee('timepicker-24');
        $response->assertSee('Jam Selesai Baru (24 Jam)');
        $response->assertDontSee('<input type="time" name="jam_selesai_baru"', false);
    }

    public function test_perpanjang_sesi_menerima_format_24_jam_dan_menolak_am_pm(): void
    {
        \Illuminate\Support\Carbon::setTestNow(today()->setTime(8, 0, 0));

        \App\Models\SesiPresensi::query()->delete();
        $jadwal = JadwalPelajaran::where('id_guru', $this->guru->id)->first();

        $this->actingAs($this->guruPengguna)->post(route('admin.sesi-presensi.store'), [
            'id_jadwal_pelajaran' => $jadwal->id,
            'tanggal_mulai' => today()->toDateString(),
            'jam_mulai' => '07:30',
            'tanggal_selesai' => today()->toDateString(),
            'jam_selesai' => '09:30',
        ]);

        $sesi = \App\Models\SesiPresensi::first();

        // 1. Format 24 Jam Valid (contoh: 11:30 > now 08:00)
        $validResponse = $this->actingAs($this->guruPengguna)->post(route('admin.sesi-presensi.perpanjang', $sesi), [
            'jam_selesai_baru' => '11:30',
        ]);
        $validResponse->assertSessionHasNoErrors();
        $validResponse->assertRedirect();
        $sesi->refresh();
        $this->assertSame('11:30', $sesi->waktu_selesai->format('H:i'));

        // 2. Format AM/PM Ditolak
        $invalidResponse = $this->actingAs($this->guruPengguna)->post(route('admin.sesi-presensi.perpanjang', $sesi), [
            'jam_selesai_baru' => '11:30 AM',
        ]);
        $invalidResponse->assertSessionHasErrors(['jam_selesai_baru']);

        \Illuminate\Support\Carbon::setTestNow();
    }
}
