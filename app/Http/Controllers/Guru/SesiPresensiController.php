<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\Presensi;
use App\Models\SesiPresensi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SesiPresensiController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $isGuru = $user->adalahGuru();
        $guruId = $user->guru?->id;

        // Jadwal mengajar
        $jadwalQuery = JadwalPelajaran::query()
            ->with(['mataPelajaran', 'kelas', 'lokasiSekolah', 'guru.pengguna'])
            ->where('aktif', true);

        if ($isGuru && $guruId) {
            $jadwalQuery->where('id_guru', $guruId);
        }

        $hariIniStr = [
            0 => 'minggu',
            1 => 'senin',
            2 => 'selasa',
            3 => 'rabu',
            4 => 'kamis',
            5 => 'jumat',
            6 => 'sabtu',
        ][(int) now()->dayOfWeek] ?? 'senin';

        $jadwalHariIni = (clone $jadwalQuery)
            ->where('hari', $hariIniStr)
            ->orderBy('jam_mulai')
            ->get();

        $semuaJadwal = $jadwalQuery
            ->orderByRaw("CASE hari WHEN 'senin' THEN 1 WHEN 'selasa' THEN 2 WHEN 'rabu' THEN 3 WHEN 'kamis' THEN 4 WHEN 'jumat' THEN 5 WHEN 'sabtu' THEN 6 WHEN 'minggu' THEN 7 ELSE 8 END")
            ->orderBy('jam_mulai')
            ->get();

        // Sesi yang dibuka hari ini
        $sesiQuery = SesiPresensi::query()
            ->with(['jadwalPelajaran.mataPelajaran', 'jadwalPelajaran.kelas', 'mataPelajaran', 'guru.pengguna'])
            ->whereDate('tanggal', today())
            ->latest('waktu_buka');

        if ($isGuru && $guruId) {
            $sesiQuery->where('id_guru', $guruId);
        }

        $sesiHariIni = $sesiQuery->get();

        // Otomatis sinkronkan status kedaluwarsa jika waktu sudah terlewati
        foreach ($sesiHariIni as $s) {
            if ($s->status === 'aktif' && $s->sudahKedaluwarsa()) {
                $s->update(['status' => 'kedaluwarsa']);
            }
        }

        return view('guru.sesi-presensi.index', compact('jadwalHariIni', 'semuaJadwal', 'sesiHariIni', 'hariIniStr'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'id_jadwal_pelajaran' => ['required', 'exists:jadwal_pelajaran,id'],
            'tanggal_mulai' => ['nullable', 'date'],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'tanggal_selesai' => ['nullable', 'date'],
            'jam_selesai' => ['nullable', 'date_format:H:i'],
            'durasi_menit' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();
        $jadwal = JadwalPelajaran::query()->with(['guru', 'mataPelajaran'])->findOrFail($data['id_jadwal_pelajaran']);

        if ($user->adalahGuru() && $jadwal->id_guru !== $user->guru?->id) {
            abort(403, 'Anda hanya dapat membuka sesi presensi untuk jadwal yang Anda ampu.');
        }

        // Tentukan tanggal & jam mulai
        $tanggalMulai = $data['tanggal_mulai'] ?? today()->toDateString();
        $jamMulai = $data['jam_mulai'] ?? now()->format('H:i');
        $waktuMulai = Carbon::parse("$tanggalMulai $jamMulai");

        // Tentukan tanggal & jam selesai
        if (! empty($data['durasi_menit'])) {
            $waktuSelesai = (clone $waktuMulai)->addMinutes((int) $data['durasi_menit']);
        } elseif (! empty($data['jam_selesai'])) {
            $tanggalSelesai = $data['tanggal_selesai'] ?? $tanggalMulai;
            $waktuSelesai = Carbon::parse("$tanggalSelesai {$data['jam_selesai']}");
        } else {
            // Default: jam selesai dari jadwal pelajaran pada tanggal mulai, atau +60 menit jika sudah lewat
            $jamSelesaiJadwal = substr($jadwal->jam_selesai, 0, 5);
            $waktuSelesai = Carbon::parse("$tanggalMulai $jamSelesaiJadwal");
            if ($waktuSelesai->lte($waktuMulai)) {
                $waktuSelesai = (clone $waktuMulai)->addMinutes(60);
            }
        }

        if ($waktuSelesai->lte($waktuMulai)) {
            return back()->withErrors(['jam_selesai' => 'Waktu selesai harus lebih besar daripada waktu mulai.'])->withInput();
        }

        // Cek jika sudah ada sesi aktif yang belum kedaluwarsa untuk jadwal ini
        $sesiAktif = SesiPresensi::query()
            ->where('id_jadwal_pelajaran', $jadwal->id)
            ->where('status', 'aktif')
            ->where(function ($q) {
                $q->whereNull('waktu_selesai')->orWhere('waktu_selesai', '>', now());
            })
            ->first();

        if ($sesiAktif) {
            return redirect()->route('admin.sesi-presensi.show', $sesiAktif)
                ->with('status', 'Sesi presensi untuk kelas ini sudah aktif.');
        }

        // Format kode QR spesifik mata pelajaran: SESI-{KODEMAPEL}-{YMD}-{RANDOM}
        $kodeUnik = strtoupper(Str::random(10));
        $kodeMapelClean = preg_replace('/[^A-Z0-9]/', '', strtoupper($jadwal->mataPelajaran?->kode_mapel ?? 'MPL'));
        if (empty($kodeMapelClean)) {
            $kodeMapelClean = 'MPL';
        }
        $kodeQr = 'SESI-'.$kodeMapelClean.'-'.now()->format('ymd').'-'.$kodeUnik;

        $sesi = SesiPresensi::create([
            'id_jadwal_pelajaran' => $jadwal->id,
            'id_mata_pelajaran' => $jadwal->id_mata_pelajaran,
            'id_guru' => $jadwal->id_guru,
            'tanggal' => $tanggalMulai,
            'kode_qr' => $kodeQr,
            'kode_unik' => $kodeUnik,
            'waktu_buka' => now(),
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'status' => 'aktif',
            'catatan' => $data['catatan'] ?? null,
        ]);

        $sesi->generateDynamicPayload();
        $sesi->save();

        return redirect()->route('admin.sesi-presensi.show', $sesi)
            ->with('status', 'Sesi presensi '.$jadwal->mataPelajaran?->nama_mapel.' berhasil dibuka.');
    }

    public function show(SesiPresensi $sesiPresensi): View
    {
        $user = request()->user();
        if ($user && $user->adalahGuru() && $sesiPresensi->id_guru !== $user->guru?->id) {
            abort(403, 'Anda tidak memiliki hak akses terhadap sesi presensi ini.');
        }

        $sesiPresensi->load([
            'jadwalPelajaran.mataPelajaran',
            'jadwalPelajaran.kelas.murid.pengguna',
            'jadwalPelajaran.lokasiSekolah',
            'mataPelajaran',
            'guru.pengguna',
        ]);

        // Cek apakah waktu sudah kedaluwarsa
        if ($sesiPresensi->status === 'aktif' && $sesiPresensi->sudahKedaluwarsa()) {
            $sesiPresensi->update(['status' => 'kedaluwarsa']);
        }

        if (empty($sesiPresensi->payload_qr)) {
            $sesiPresensi->generateDynamicPayload();
            $sesiPresensi->save();
        }

        // Generate QR code SVG dari dynamic payload
        $qrSvg = QrCode::size(300)
            ->margin(1)
            ->generate($sesiPresensi->payload_qr ?: $sesiPresensi->kode_qr);

        $jadwal = $sesiPresensi->jadwalPelajaran;
        $daftarMurid = $jadwal->kelas?->murid()->with('pengguna')->where('aktif', true)->orderBy('nis')->get() ?? collect();

        $presensiList = Presensi::query()
            ->where('id_jadwal_pelajaran', $jadwal->id)
            ->whereDate('tanggal_presensi', $sesiPresensi->tanggal)
            ->get()
            ->keyBy('id_murid');

        $rekap = [
            'total' => $daftarMurid->count(),
            'hadir' => $presensiList->where('status', 'hadir')->count(),
            'terlambat' => $presensiList->where('status', 'terlambat')->count(),
            'izin' => $presensiList->whereIn('status', ['izin', 'sakit'])->count(),
            'belum' => 0,
        ];
        $rekap['belum'] = max(0, $rekap['total'] - ($rekap['hadir'] + $rekap['terlambat'] + $rekap['izin']));

        return view('guru.sesi-presensi.show', [
            'sesi' => $sesiPresensi,
            'qrSvg' => $qrSvg,
            'daftarMurid' => $daftarMurid,
            'presensiList' => $presensiList,
            'rekap' => $rekap,
            'sisaDetik' => $sesiPresensi->sisaDetik(),
        ]);
    }

    public function perpanjang(Request $request, SesiPresensi $sesiPresensi): RedirectResponse
    {
        $user = $request->user();
        if ($user->adalahGuru() && $sesiPresensi->id_guru !== $user->guru?->id) {
            abort(403, 'Anda tidak memiliki hak untuk mengubah sesi ini.');
        }

        $data = $request->validate([
            'menit_tambahan' => ['nullable', 'integer', 'min:1', 'max:240'],
            'jam_selesai_baru' => ['nullable', 'date_format:H:i'],
        ]);

        $waktuSelesaiLama = $sesiPresensi->waktu_selesai ?: now();
        $basisWaktu = $waktuSelesaiLama->isPast() ? now() : $waktuSelesaiLama;

        if (! empty($data['menit_tambahan'])) {
            $waktuSelesaiBaru = (clone $basisWaktu)->addMinutes((int) $data['menit_tambahan']);
        } elseif (! empty($data['jam_selesai_baru'])) {
            $tanggal = $sesiPresensi->waktu_selesai ? $sesiPresensi->waktu_selesai->toDateString() : today()->toDateString();
            $waktuSelesaiBaru = Carbon::parse("$tanggal {$data['jam_selesai_baru']}");
            if ($waktuSelesaiBaru->lte(now())) {
                return back()->withErrors(['jam_selesai_baru' => 'Jam selesai baru harus lebih besar daripada waktu saat ini.'])->withInput();
            }
        } else {
            $waktuSelesaiBaru = (clone $basisWaktu)->addMinutes(15);
        }

        $newKodeUnik = strtoupper(Str::random(10));
        $kodeMapel = $sesiPresensi->mataPelajaran?->kode_mapel ?: $sesiPresensi->jadwalPelajaran?->mataPelajaran?->kode_mapel ?: 'MAPEL';
        $kodeMapelClean = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $kodeMapel));
        $newKodeQr = 'SESI-'.$kodeMapelClean.'-'.now()->format('ymd').'-'.$newKodeUnik;

        $sesiPresensi->update([
            'waktu_selesai' => $waktuSelesaiBaru,
            'kode_unik' => $newKodeUnik,
            'kode_qr' => $newKodeQr,
            'status' => 'aktif',
            'waktu_tutup' => null,
        ]);

        $sesiPresensi->generateDynamicPayload();
        $sesiPresensi->save();

        return back()->with('status', 'Waktu hidup sesi presensi berhasil diperpanjang hingga pukul '.$waktuSelesaiBaru->format('H:i').' WIB.');
    }

    public function tutup(SesiPresensi $sesiPresensi): RedirectResponse
    {
        $user = request()->user();
        if ($user && $user->adalahGuru() && $sesiPresensi->id_guru !== $user->guru?->id) {
            abort(403, 'Anda tidak memiliki hak untuk menutup sesi ini.');
        }

        $sesiPresensi->update([
            'status' => 'ditutup',
            'waktu_tutup' => now(),
        ]);

        return back()->with('status', 'Sesi presensi telah ditutup.');
    }

    public function live(SesiPresensi $sesiPresensi): JsonResponse
    {
        $user = request()->user();
        if ($user && $user->adalahGuru() && $sesiPresensi->id_guru !== $user->guru?->id) {
            abort(403, 'Anda tidak memiliki hak untuk melihat sesi ini.');
        }

        if ($sesiPresensi->status === 'aktif' && $sesiPresensi->sudahKedaluwarsa()) {
            $sesiPresensi->update(['status' => 'kedaluwarsa']);
        }

        $sesiPresensi->load(['jadwalPelajaran.kelas.murid.pengguna', 'jadwalPelajaran.mataPelajaran']);
        $jadwal = $sesiPresensi->jadwalPelajaran;

        $daftarMurid = $jadwal->kelas?->murid()->with('pengguna')->where('aktif', true)->orderBy('nis')->get() ?? collect();
        $presensiList = Presensi::query()
            ->where('id_jadwal_pelajaran', $jadwal->id)
            ->whereDate('tanggal_presensi', $sesiPresensi->tanggal)
            ->get()
            ->keyBy('id_murid');

        $muridData = $daftarMurid->map(function ($m) use ($presensiList) {
            $p = $presensiList->get($m->id);

            return [
                'id_murid' => $m->id,
                'nis' => $m->nis,
                'nama' => $m->pengguna?->nama,
                'status' => $p ? $p->status : 'belum_hadir',
                'waktu' => $p?->waktu_presensi ? $p->waktu_presensi->format('H:i:s') : null,
                'jarak_meter' => $p?->jarak_meter,
            ];
        });

        $hadir = $presensiList->where('status', 'hadir')->count();
        $terlambat = $presensiList->where('status', 'terlambat')->count();
        $izin = $presensiList->whereIn('status', ['izin', 'sakit'])->count();
        $total = $daftarMurid->count();
        $belum = max(0, $total - ($hadir + $terlambat + $izin));

        $qrSvg = QrCode::size(300)
            ->margin(1)
            ->generate($sesiPresensi->payload_qr ?: $sesiPresensi->kode_qr);

        return response()->json([
            'status_sesi' => $sesiPresensi->status,
            'is_expired' => $sesiPresensi->sudahKedaluwarsa(),
            'sisa_detik' => $sesiPresensi->sisaDetik(),
            'waktu_selesai' => $sesiPresensi->waktu_selesai?->format('H:i'),
            'kode_unik' => $sesiPresensi->kode_unik,
            'qr_svg' => (string) $qrSvg,
            'rekap' => [
                'total' => $total,
                'hadir' => $hadir,
                'terlambat' => $terlambat,
                'izin' => $izin,
                'belum' => $belum,
            ],
            'murid' => $muridData,
        ]);
    }
}
