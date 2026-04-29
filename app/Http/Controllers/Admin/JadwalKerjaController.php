<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JadwalKerjaController extends Controller
{
    public function index(): View
    {
        return view('admin.jadwal-kerja.index', ['jadwalKerja' => JadwalKerja::query()->latest('dibuat_pada')->paginate(15)]);
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
