<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LokasiSekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LokasiSekolahController extends Controller
{
    public function index(): View
    {
        return view('admin.lokasi-sekolah.index', [
            'lokasiSekolah' => LokasiSekolah::query()->latest('dibuat_pada')->paginate(15),
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
