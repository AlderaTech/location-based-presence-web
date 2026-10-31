<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Murid;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MuridController extends Controller
{
    public function index(Request $request): View
    {
        $murid = Murid::query()
            ->with(['pengguna.perangkat', 'kelas'])
            ->when($request->query('q'), function ($query, string $q): void {
                $query->where('nis', 'like', '%'.$q.'%')
                    ->orWhere('nisn', 'like', '%'.$q.'%')
                    ->orWhereHas('pengguna', fn ($p) => $p->where('nama', 'like', '%'.$q.'%')->orWhere('email', 'like', '%'.$q.'%'));
            })
            ->when($request->query('id_kelas'), fn ($q, $kelasId) => $q->where('id_kelas', $kelasId))
            ->orderBy('id_kelas')
            ->orderBy('nis')
            ->paginate(15)
            ->withQueryString();

        $daftarKelas = Kelas::query()->where('aktif', true)->orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('admin.murid.index', compact('murid', 'daftarKelas'));
    }

    public function create(): View
    {
        return view('admin.murid.form', [
            'murid' => new Murid(['aktif' => true, 'jenis_kelamin' => 'L']),
            'pengguna' => new Pengguna(['peran' => 'murid', 'status' => 'aktif']),
            'daftarKelas' => Kelas::query()->where('aktif', true)->orderBy('tingkat')->orderBy('nama_kelas')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateInput($request);

        DB::transaction(function () use ($data) {
            $pengguna = Pengguna::create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'kata_sandi' => $data['kata_sandi'],
                'peran' => 'murid',
                'nomor_pegawai' => $data['nis'],
                'telepon' => $data['telepon'] ?? null,
                'status' => $data['aktif'] ? 'aktif' : 'nonaktif',
            ]);

            $pengguna->murid()->create([
                'id_kelas' => $data['id_kelas'],
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'telepon_wali' => $data['telepon_wali'] ?? null,
                'alamat' => $data['alamat'] ?? null,
                'aktif' => $data['aktif'],
            ]);
        });

        return redirect()->route('admin.murid.index')->with('status', 'Data murid berhasil ditambahkan.');
    }

    public function edit(Murid $murid): View
    {
        return view('admin.murid.form', [
            'murid' => $murid->load('pengguna'),
            'pengguna' => $murid->pengguna,
            'daftarKelas' => Kelas::query()->where('aktif', true)->orderBy('tingkat')->orderBy('nama_kelas')->get(),
        ]);
    }

    public function update(Request $request, Murid $murid): RedirectResponse
    {
        $data = $this->validateInput($request, $murid);

        DB::transaction(function () use ($murid, $data) {
            $payloadPengguna = [
                'nama' => $data['nama'],
                'email' => $data['email'],
                'nomor_pegawai' => $data['nis'],
                'telepon' => $data['telepon'] ?? null,
                'status' => $data['aktif'] ? 'aktif' : 'nonaktif',
            ];

            if (! empty($data['kata_sandi'])) {
                $payloadPengguna['kata_sandi'] = $data['kata_sandi'];
            }

            $murid->pengguna->update($payloadPengguna);

            $murid->update([
                'id_kelas' => $data['id_kelas'],
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'telepon_wali' => $data['telepon_wali'] ?? null,
                'alamat' => $data['alamat'] ?? null,
                'aktif' => $data['aktif'],
            ]);
        });

        return redirect()->route('admin.murid.index')->with('status', 'Data murid berhasil diperbarui.');
    }

    public function destroy(Murid $murid): RedirectResponse
    {
        DB::transaction(function () use ($murid) {
            $pengguna = $murid->pengguna;
            $murid->delete();
            $pengguna?->delete();
        });

        return redirect()->route('admin.murid.index')->with('status', 'Data murid berhasil dihapus.');
    }

    public function resetPerangkat(Murid $murid): RedirectResponse
    {
        $murid->pengguna->perangkat()->update(['aktif' => false]);
        $murid->pengguna->tokenApi()->delete();

        return back()->with('status', 'Ikatan perangkat murid '.$murid->pengguna->nama.' berhasil di-reset.');
    }

    private function validateInput(Request $request, ?Murid $murid = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('pengguna', 'email')->ignore($murid?->id_pengguna)],
            'kata_sandi' => [$murid ? 'nullable' : 'required', 'string', 'min:6'],
            'id_kelas' => ['required', 'exists:kelas,id'],
            'nis' => ['required', 'string', 'max:50', Rule::unique('murid', 'nis')->ignore($murid?->id)],
            'nisn' => ['nullable', 'string', 'max:50', Rule::unique('murid', 'nisn')->ignore($murid?->id)],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'telepon_wali' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string', 'max:1000'],
            'aktif' => ['required', 'boolean'],
        ]);
    }
}
