<?php

namespace Tests\Feature;

use App\Models\Pengguna;
use Database\Seeders\PresensiDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class LaporanExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_ekspor_laporan_pdf_dan_xlsx_memiliki_format_rapi(): void
    {
        $this->seed(PresensiDemoSeeder::class);

        $admin = Pengguna::query()->where('email', 'admin@presensi.test')->firstOrFail();

        $pdf = $this->actingAs($admin)
            ->get('/admin/laporan/ekspor/pdf?status=hadir&sort=tanggal_presensi&direction=asc');

        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF-1.4', $pdf->baseResponse->getContent());
        $this->assertStringContainsString('/MediaBox [0 0 842 595]', $pdf->baseResponse->getContent());
        $this->assertStringNotContainsString('...', $pdf->baseResponse->getContent());
        $this->assertStringContainsString('Pulang', $pdf->baseResponse->getContent());
        $this->assertStringContainsString('Koreksi demo karena', $pdf->baseResponse->getContent());
        $this->assertStringContainsString('membuka', $pdf->baseResponse->getContent());
        $this->assertStringContainsString('GPS', $pdf->baseResponse->getContent());

        $xlsx = $this->actingAs($admin)
            ->get('/admin/laporan/ekspor/xlsx?status=hadir&sort=nama&direction=asc');

        $xlsx->assertOk();
        $xlsx->assertDownload();

        $path = $xlsx->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($path);
        $sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($path);

        $this->assertIsString($sheet);
        $this->assertStringContainsString('<autoFilter', $sheet);
        $this->assertStringContainsString('state="frozen"', $sheet);
        $this->assertStringContainsString('Rekap Presensi Guru', $sheet);
        $this->assertStringContainsString('NIP atau Nomor Pegawai', $sheet);
        $this->assertStringContainsString('Pulang', $sheet);
        $this->assertStringContainsString('Koreksi demo karena aplikasi guru sempat tidak bisa membuka GPS.', $sheet);
        $this->assertStringNotContainsString('...', $sheet);
    }
}
