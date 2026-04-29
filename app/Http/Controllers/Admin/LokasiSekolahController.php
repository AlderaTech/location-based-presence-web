<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LokasiSekolah;
use App\Support\SortirAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LokasiSekolahController extends Controller
{
    public function index(Request $request): View
    {
        $query = LokasiSekolah::query()
            ->when($request->query('q'), fn ($query, string $q) => $query->where('nama', 'like', '%'.$q.'%'))
            ->when($request->has('aktif') && $request->query('aktif') !== '', fn ($query) => $query->where('aktif', $request->boolean('aktif')))
            ->when($request->query('radius_min'), fn ($query, $radius) => $query->where('radius_meter', '>=', $radius))
            ->when($request->query('radius_max'), fn ($query, $radius) => $query->where('radius_meter', '<=', $radius));

        SortirAdmin::terapkan($query, $request, [
            'nama' => 'lokasi_sekolah.nama',
            'radius' => 'lokasi_sekolah.radius_meter',
            'status' => 'lokasi_sekolah.aktif',
            'dibuat_pada' => 'lokasi_sekolah.dibuat_pada',
        ], 'dibuat_pada', 'desc');

        return view('admin.lokasi-sekolah.index', [
            'lokasiSekolah' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.lokasi-sekolah.form', ['lokasi' => new LokasiSekolah(['aktif' => true, 'radius_meter' => 100])]);
    }

    public function store(Request $request): RedirectResponse
    {
        LokasiSekolah::create($this->data($request));

        return redirect()->route('admin.lokasi-sekolah.index')->with('status', 'Lokasi sekolah berhasil ditambahkan.');
    }

    public function edit(LokasiSekolah $lokasiSekolah): View
    {
        return view('admin.lokasi-sekolah.form', ['lokasi' => $lokasiSekolah]);
    }

    public function update(Request $request, LokasiSekolah $lokasiSekolah): RedirectResponse
    {
        $lokasiSekolah->update($this->data($request));

        return redirect()->route('admin.lokasi-sekolah.index')->with('status', 'Lokasi sekolah berhasil diperbarui.');
    }

    public function destroy(LokasiSekolah $lokasiSekolah): RedirectResponse
    {
        $lokasiSekolah->update(['aktif' => false]);

        return back()->with('status', 'Lokasi sekolah dinonaktifkan.');
    }

    private function data(Request $request): array
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:191'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:10', 'max:10000'],
        ]);
        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }
}
