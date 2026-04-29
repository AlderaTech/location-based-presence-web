<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HariLibur;
use App\Support\SortirAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HariLiburController extends Controller
{
    public function index(Request $request): View
    {
        $query = HariLibur::query()
            ->when($request->query('q'), fn ($query, string $q) => $query->where('nama', 'like', '%'.$q.'%'))
            ->when($request->query('jenis'), fn ($query, string $jenis) => $query->where('jenis', $jenis))
            ->when($request->query('tanggal_mulai'), fn ($query, $tanggal) => $query->whereDate('tanggal', '>=', $tanggal))
            ->when($request->query('tanggal_selesai'), fn ($query, $tanggal) => $query->whereDate('tanggal', '<=', $tanggal));

        SortirAdmin::terapkan($query, $request, [
            'tanggal' => 'hari_libur.tanggal',
            'nama' => 'hari_libur.nama',
            'jenis' => 'hari_libur.jenis',
        ], 'tanggal', 'desc');

        return view('admin.hari-libur.index', [
            'hariLibur' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.hari-libur.form', ['hariLibur' => new HariLibur(['jenis' => 'sekolah'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        HariLibur::create($this->data($request));

        return redirect()->route('admin.hari-libur.index')->with('status', 'Hari libur berhasil ditambahkan.');
    }

    public function edit(HariLibur $hariLibur): View
    {
        return view('admin.hari-libur.form', ['hariLibur' => $hariLibur]);
    }

    public function update(Request $request, HariLibur $hariLibur): RedirectResponse
    {
        $hariLibur->update($this->data($request, $hariLibur));

        return redirect()->route('admin.hari-libur.index')->with('status', 'Hari libur berhasil diperbarui.');
    }

    public function destroy(HariLibur $hariLibur): RedirectResponse
    {
        $hariLibur->delete();

        return back()->with('status', 'Hari libur berhasil dihapus.');
    }

    private function data(Request $request, ?HariLibur $hariLibur = null): array
    {
        return $request->validate([
            'tanggal' => ['required', 'date', Rule::unique('hari_libur', 'tanggal')->ignore($hariLibur?->id)],
            'nama' => ['required', 'string', 'max:191'],
            'jenis' => ['required', 'in:nasional,sekolah'],
        ]);
    }
}
