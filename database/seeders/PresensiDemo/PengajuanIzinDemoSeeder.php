<?php

namespace Database\Seeders\PresensiDemo;

use App\Models\Guru;
use App\Models\PengajuanIzin;
use App\Models\Pengguna;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Database\Seeders\PresensiDemo\Concerns\MembuatDataDemo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class PengajuanIzinDemoSeeder extends Seeder
{
    use MembuatDataDemo;

    /**
     * @param  array<int, Guru>  $guru
     */
    public function seed(array $guru, Pengguna $peninjau): void
    {
        Storage::disk('public')->makeDirectory('dokumen-izin/demo');

        $guruAktif = collect($guru)->filter(fn (Guru $guruItem) => $guruItem->aktif)->values();
        $awalBulan = CarbonImmutable::now()->startOfMonth();

        $items = [
            [
                'guru' => $guruAktif->get(0),
                'jenis' => 'sakit',
                'tanggal_mulai' => $awalBulan->addDays(7),
                'tanggal_selesai' => $awalBulan->addDays(7),
                'alasan' => 'Demam dan perlu istirahat satu hari.',
                'status' => 'disetujui',
                'dokumen' => 'surat-sakit-g001.pdf',
            ],
            [
                'guru' => $guruAktif->get(1),
                'jenis' => 'izin',
                'tanggal_mulai' => $awalBulan->addDays(9),
                'tanggal_selesai' => $awalBulan->addDays(9),
                'alasan' => 'Mengurus administrasi keluarga.',
                'status' => 'ditolak',
                'alasan_penolakan' => 'Dokumen pendukung belum lengkap.',
                'dokumen' => 'izin-g002.txt',
            ],
            [
                'guru' => $guruAktif->get(3),
                'jenis' => 'cuti',
                'tanggal_mulai' => $awalBulan->addDays(14),
                'tanggal_selesai' => $awalBulan->addDays(15),
                'alasan' => 'Cuti keluarga yang sudah dijadwalkan.',
                'status' => 'disetujui',
                'dokumen' => 'cuti-g004.txt',
            ],
            [
                'guru' => $guruAktif->get(4),
                'jenis' => 'izin',
                'tanggal_mulai' => CarbonImmutable::now()->addDays(2),
                'tanggal_selesai' => CarbonImmutable::now()->addDays(2),
                'alasan' => 'Menghadiri undangan pelatihan MGMP.',
                'status' => 'menunggu',
                'dokumen' => 'izin-pelatihan-g005.txt',
            ],
            [
                'guru' => $guruAktif->get(5),
                'jenis' => 'sakit',
                'tanggal_mulai' => CarbonImmutable::now()->addDays(4),
                'tanggal_selesai' => CarbonImmutable::now()->addDays(4),
                'alasan' => 'Kontrol kesehatan lanjutan.',
                'status' => 'menunggu',
                'dokumen' => null,
            ],
        ];

        foreach ($items as $item) {
            /** @var Guru|null $guruItem */
            $guruItem = $item['guru'];
            if (! $guruItem) {
                continue;
            }

            $pengajuan = PengajuanIzin::query()
                ->where('id_guru', $guruItem->id)
                ->where('jenis', $item['jenis'])
                ->whereDate('tanggal_mulai', $item['tanggal_mulai']->toDateString())
                ->first() ?: new PengajuanIzin([
                    'id_guru' => $guruItem->id,
                    'jenis' => $item['jenis'],
                    'tanggal_mulai' => $item['tanggal_mulai']->toDateString(),
                ]);

            $pengajuan->fill([
                'id_pengguna' => $guruItem->id_pengguna,
                'tanggal_selesai' => $item['tanggal_selesai']->toDateString(),
                'alasan' => $item['alasan'],
                'status' => $item['status'],
                'id_peninjau' => $item['status'] === 'menunggu' ? null : $peninjau->id,
                'ditinjau_pada' => $item['status'] === 'menunggu' ? null : now()->subDays(2),
                'alasan_penolakan' => $item['alasan_penolakan'] ?? null,
            ])->save();

            if ($item['dokumen']) {
                $this->seedDokumenIzin($pengajuan, $item['dokumen']);
            }

            if ($item['status'] === 'disetujui') {
                foreach (CarbonPeriod::create($item['tanggal_mulai'], $item['tanggal_selesai']) as $tanggal) {
                    if (CarbonImmutable::parse($tanggal)->isWeekend()) {
                        continue;
                    }

                    $presensi = $this->presensi($guruItem, null, CarbonImmutable::parse($tanggal), [
                        'id_jadwal_kerja' => $guruItem->id_jadwal_kerja_bawaan,
                        'status' => $item['jenis'],
                        'catatan' => $item['alasan'],
                        'id_pengoreksi' => $peninjau->id,
                        'alasan_koreksi' => 'Persetujuan pengajuan demo #'.$pengajuan->id,
                    ]);

                    $this->audit($presensi, $guruItem->pengguna, $peninjau, 'seed_pengajuan_disetujui', [
                        'id_pengajuan_izin' => $pengajuan->id,
                        'jenis' => $item['jenis'],
                    ]);
                }
            }
        }
    }

    private function seedDokumenIzin(PengajuanIzin $pengajuan, string $namaFile): void
    {
        $path = 'dokumen-izin/demo/'.$namaFile;
        $isi = implode(PHP_EOL, [
            'Dokumen pendukung demo',
            'Nomor pengajuan: '.$pengajuan->id,
            'Guru: '.$pengajuan->pengguna->nama,
            'Jenis: '.$pengajuan->jenis,
            'Tanggal: '.$pengajuan->tanggal_mulai->toDateString().' s/d '.$pengajuan->tanggal_selesai->toDateString(),
        ]);
        $pdf = str_ends_with($namaFile, '.pdf');
        $konten = $pdf ? $this->pdfSederhana($isi) : $isi;

        Storage::disk('public')->put($path, $konten);

        $pengajuan->dokumen()->updateOrCreate(
            ['path' => $path],
            [
                'disk' => 'public',
                'nama_asli' => $namaFile,
                'tipe_mime' => $pdf ? 'application/pdf' : 'text/plain',
                'ukuran' => strlen($konten),
            ],
        );
    }

    private function pdfSederhana(string $teks): string
    {
        $stream = "BT\n/F1 12 Tf\n72 760 Td\n";
        foreach (explode(PHP_EOL, $teks) as $index => $baris) {
            if ($index > 0) {
                $stream .= "0 -18 Td\n";
            }
            $stream .= '('.strtr($baris, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']).") Tj\n";
        }
        $stream .= 'ET';

        $objects = [
            '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj',
            '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj',
            '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj',
            '4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj',
            '5 0 obj << /Length '.strlen($stream)." >> stream\n".$stream."\nendstream endobj",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= str_pad((string) $offset, 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }
        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xref."\n%%EOF";

        return $pdf;
    }
}
