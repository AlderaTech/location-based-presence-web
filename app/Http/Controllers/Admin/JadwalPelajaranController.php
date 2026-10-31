<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\LokasiSekolah;
use App\Models\MataPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalPelajaranController extends Controller
{
    public function index(Request $request): View
    {
        $jadwal = JadwalPelajaran::query()
            ->with(['mataPelajaran', 'kelas', 'guru.pengguna', 'lokasiSekolah'])
            ->when($request->query('hari'), fn ($q, $h) => $q->where('hari', $h))
            ->when($request->query('id_kelas'), fn ($q, $k) => $q->where('id_kelas', $k))
            ->when($request->query('id_mata_pelajaran'), fn ($q, $m) => $q->where('id_mata_pelajaran', $m))
            ->when($request->query('id_guru'), fn ($q, $g) => $q->where('id_guru', $g))
            ->orderByRaw("CASE hari WHEN 'senin' THEN 1 WHEN 'selasa' THEN 2 WHEN 'rabu' THEN 3 WHEN 'kamis' THEN 4 WHEN 'jumat' THEN 5 WHEN 'sabtu' THEN 6 WHEN 'minggu' THEN 7 ELSE 8 END")
            ->orderBy('jam_mulai')
            ->paginate(15)
            ->withQueryString();

        $daftarKelas = Kelas::query()->where('aktif', true)->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $daftarMapel = MataPelajaran::query()->where('aktif', true)->orderBy('nama_mapel')->get();
        $daftarGuru = Guru::query()->with('pengguna')->where('aktif', true)->get();

        return view('admin.jadwal-pelajaran.index', compact('jadwal', 'daftarKelas', 'daftarMapel', 'daftarGuru'));
    }

    public function create(): View
    {
        return view('admin.jadwal-pelajaran.form', [
            'jadwal' => new JadwalPelajaran([
                'hari' => 'senin',
                'toleransi_terlambat_menit' => 15,
                'aktif' => true,
            ]),
            'daftarKelas' => Kelas::query()->where('aktif', true)->orderBy('tingkat')->orderBy('nama_kelas')->get(),
            'daftarMapel' => MataPelajaran::query()->where('aktif', true)->orderBy('nama_mapel')->get(),
            'daftarGuru' => Guru::query()->with('pengguna')->where('aktif', true)->get(),
            'daftarLokasi' => LokasiSekolah::query()->where('aktif', true)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateInput($request);
        JadwalPelajaran::create($data);

        return redirect()->route('admin.jadwal-pelajaran.index')->with('status', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function edit(JadwalPelajaran $jadwalPelajaran): View
    {
        return view('admin.jadwal-pelajaran.form', [
            'jadwal' => $jadwalPelajaran,
            'daftarKelas' => Kelas::query()->where('aktif', true)->orderBy('tingkat')->orderBy('nama_kelas')->get(),
            'daftarMapel' => MataPelajaran::query()->where('aktif', true)->orderBy('nama_mapel')->get(),
            'daftarGuru' => Guru::query()->with('pengguna')->where('aktif', true)->get(),
            'daftarLokasi' => LokasiSekolah::query()->where('aktif', true)->get(),
        ]);
    }

    public function update(Request $request, JadwalPelajaran $jadwalPelajaran): RedirectResponse
    {
        $data = $this->validateInput($request);
        $jadwalPelajaran->update($data);

        return redirect()->route('admin.jadwal-pelajaran.index')->with('status', 'Jadwal pelajaran berhasil diperbarui.');
    }

    public function destroy(JadwalPelajaran $jadwalPelajaran): RedirectResponse
    {
        $jadwalPelajaran->delete();

        return redirect()->route('admin.jadwal-pelajaran.index')->with('status', 'Jadwal pelajaran berhasil dihapus.');
    }

    private function validateInput(Request $request): array
    {
        return $request->validate([
            'id_mata_pelajaran' => ['required', 'exists:mata_pelajaran,id'],
            'id_kelas' => ['required', 'exists:kelas,id'],
            'id_guru' => ['required', 'exists:guru,id'],
            'id_lokasi_sekolah' => ['nullable', 'exists:lokasi_sekolah,id'],
            'hari' => ['required', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'toleransi_terlambat_menit' => ['required', 'integer', 'min:0', 'max:120'],
            'ruangan' => ['nullable', 'string', 'max:50'],
            'aktif' => ['required', 'boolean'],
        ]);
    }
}
