<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use App\Support\AdminSortHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalKerjaController extends Controller
{
    public function index(Request $request): View
    {
        $query = JadwalKerja::query()
            ->when($request->query('q'), fn ($query, string $q) => $query->where('nama', 'like', '%'.$q.'%'))
            ->when($request->has('aktif') && $request->query('aktif') !== '', fn ($query) => $query->where('aktif', $request->boolean('aktif')));

        AdminSortHelper::terapkan($query, $request, [
            'nama' => 'jadwal_kerja.nama',
            'jam_masuk' => 'jadwal_kerja.jam_masuk',
            'jam_pulang' => 'jadwal_kerja.jam_pulang',
            'toleransi' => 'jadwal_kerja.toleransi_terlambat_menit',
            'status' => 'jadwal_kerja.aktif',
            'dibuat_pada' => 'jadwal_kerja.dibuat_pada',
        ], 'dibuat_pada', 'desc');

        return view('admin.jadwal-kerja.index', ['jadwalKerja' => $query->paginate(15)->withQueryString()]);
    }

    public function create(): View
    {
        return view('admin.jadwal-kerja.form', ['jadwal' => new JadwalKerja(['aktif' => true, 'toleransi_terlambat_menit' => 10])]);
    }

    public function store(Request $request): RedirectResponse
    {
        JadwalKerja::create($this->data($request));

        return redirect()->route('admin.jadwal-kerja.index')->with('status', 'Jadwal kerja berhasil ditambahkan.');
    }

    public function edit(JadwalKerja $jadwalKerja): View
    {
        return view('admin.jadwal-kerja.form', ['jadwal' => $jadwalKerja]);
    }

    public function update(Request $request, JadwalKerja $jadwalKerja): RedirectResponse
    {
        $jadwalKerja->update($this->data($request));

        return redirect()->route('admin.jadwal-kerja.index')->with('status', 'Jadwal kerja berhasil diperbarui.');
    }

    public function destroy(JadwalKerja $jadwalKerja): RedirectResponse
    {
        $jadwalKerja->update(['aktif' => false]);

        return back()->with('status', 'Jadwal kerja dinonaktifkan.');
    }

    private function data(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:191'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i'],
            'toleransi_terlambat_menit' => ['required', 'integer', 'min:0', 'max:240'],
        ]);
        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }
}
