<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MataPelajaranController extends Controller
{
    public function index(Request $request): View
    {
        $mataPelajaran = MataPelajaran::query()
            ->when($request->query('q'), fn ($q, string $search) => $q->where('nama_mapel', 'like', '%'.$search.'%')->orWhere('kode_mapel', 'like', '%'.$search.'%'))
            ->orderBy('nama_mapel')
            ->paginate(15)
            ->withQueryString();

        return view('admin.mata-pelajaran.index', compact('mataPelajaran'));
    }

    public function create(): View
    {
        return view('admin.mata-pelajaran.form', [
            'mataPelajaran' => new MataPelajaran(['aktif' => true]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateInput($request);
        MataPelajaran::create($data);

        return redirect()->route('admin.mata-pelajaran.index')->with('status', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(MataPelajaran $mataPelajaran): View
    {
        return view('admin.mata-pelajaran.form', compact('mataPelajaran'));
    }

    public function update(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $data = $this->validateInput($request, $mataPelajaran);
        $mataPelajaran->update($data);

        return redirect()->route('admin.mata-pelajaran.index')->with('status', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran): RedirectResponse
    {
        $mataPelajaran->delete();

        return redirect()->route('admin.mata-pelajaran.index')->with('status', 'Mata pelajaran berhasil dihapus.');
    }

    private function validateInput(Request $request, ?MataPelajaran $mataPelajaran = null): array
    {
        return $request->validate([
            'kode_mapel' => ['required', 'string', 'max:20', Rule::unique('mata_pelajaran', 'kode_mapel')->ignore($mataPelajaran?->id)],
            'nama_mapel' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'aktif' => ['required', 'boolean'],
        ]);
    }
}
