<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KelasController extends Controller
{
    public function index(Request $request): View
    {
        $kelas = Kelas::query()
            ->withCount('murid')
            ->when($request->query('q'), fn ($q, string $search) => $q->where('nama_kelas', 'like', '%'.$search.'%')->orWhere('jurusan', 'like', '%'.$search.'%'))
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->paginate(15)
            ->withQueryString();

        return view('admin.kelas.index', compact('kelas'));
    }

    public function create(): View
    {
        return view('admin.kelas.form', [
            'kelas' => new Kelas([
                'tingkat' => 10,
                'tahun_ajaran' => '2026/2027',
                'aktif' => true,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateInput($request);
        Kelas::create($data);

        return redirect()->route('admin.kelas.index')->with('status', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kela): View
    {
        return view('admin.kelas.form', ['kelas' => $kela]);
    }

    public function update(Request $request, Kelas $kela): RedirectResponse
    {
        $data = $this->validateInput($request, $kela);
        $kela->update($data);

        return redirect()->route('admin.kelas.index')->with('status', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela): RedirectResponse
    {
        $kela->delete();

        return redirect()->route('admin.kelas.index')->with('status', 'Kelas berhasil dihapus.');
    }

    private function validateInput(Request $request, ?Kelas $kelas = null): array
    {
        return $request->validate([
            'nama_kelas' => ['required', 'string', 'max:50', Rule::unique('kelas', 'nama_kelas')->ignore($kelas?->id)],
            'tingkat' => ['required', 'integer', 'min:1', 'max:13'],
            'jurusan' => ['nullable', 'string', 'max:50'],
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'aktif' => ['required', 'boolean'],
        ]);
    }
}
