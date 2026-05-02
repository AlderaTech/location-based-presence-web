<?php

namespace Tests\Feature;

use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use App\Models\Presensi;
use Database\Seeders\PresensiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AksesKepalaSekolahTest extends TestCase
{
    use RefreshDatabase;

    public function test_kepala_sekolah_hanya_mengakses_laporan_dan_analitik(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $kepalaSekolah = Pengguna::query()->where('email', 'kepsek@presensi.test')->firstOrFail();

        $halamanBoleh = [
            '/admin',
            '/admin/presensi',
            '/admin/pengajuan-izin',
            '/admin/laporan',
            '/admin/laporan/ekspor/pdf',
        ];

        foreach ($halamanBoleh as $url) {
            $this->actingAs($kepalaSekolah)->get($url)->assertOk();
        }

        $halamanDibatasi = [
            '/admin/guru',
            '/admin/lokasi-sekolah',
            '/admin/jadwal-kerja',
            '/admin/hari-libur',
            '/admin/pengguna-admin',
        ];

        foreach ($halamanDibatasi as $url) {
            $this->actingAs($kepalaSekolah)->get($url)->assertForbidden();
        }
    }

    public function test_kepala_sekolah_tidak_bisa_melakukan_aksi_mutasi_operasional(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $kepalaSekolah = Pengguna::query()->where('email', 'kepsek@presensi.test')->firstOrFail();
        $presensi = Presensi::query()->firstOrFail();
        $pengajuan = PengajuanIzin::query()->where('status', 'menunggu')->firstOrFail();

        $this->actingAs($kepalaSekolah)
            ->get('/admin/presensi/'.$presensi->id.'/edit')
            ->assertForbidden();

        $this->actingAs($kepalaSekolah)
            ->put('/admin/presensi/'.$presensi->id, [
                'status' => 'hadir',
                'alasan_koreksi' => 'Tidak boleh mengoreksi.',
            ])
            ->assertForbidden();

        $this->actingAs($kepalaSekolah)
            ->post('/admin/pengajuan-izin/'.$pengajuan->id.'/setujui')
            ->assertForbidden();

        $this->actingAs($kepalaSekolah)
            ->post('/admin/pengajuan-izin/'.$pengajuan->id.'/tolak', [
                'alasan_penolakan' => 'Tidak boleh menolak.',
            ])
            ->assertForbidden();
    }
}
