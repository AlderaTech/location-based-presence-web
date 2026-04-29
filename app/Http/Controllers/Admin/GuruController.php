<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\JadwalKerja;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuruController extends Controller
{
    public function index(Request $request): View
    {
        $guru = Guru::query()
            ->with(['pengguna', 'jadwalKerjaBawaan'])
            ->when($request->query('q'), function ($query, string $q): void {
                $query->whereHas('pengguna', fn ($pengguna) => $pengguna
                    ->where('nama', 'like', '%'.$q.'%')
                    ->orWhere('email', 'like', '%'.$q.'%')
                    ->orWhere('nomor_pegawai', 'like', '%'.$q.'%'));
            })
            ->latest('dibuat_pada')
            ->paginate(15)
            ->withQueryString();

        return view('admin.guru.index', [
            'guru' => $guru,
            'jadwalKerja' => JadwalKerja::query()->where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.guru.form', [
            'guruItem' => new Guru(),
            'pengguna' => new Pengguna(['peran' => 'guru', 'status' => 'aktif']),
            'jadwalKerja' => JadwalKerja::query()->orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        DB::transaction(function () use ($data): void {
            $pengguna = Pengguna::create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'kata_sandi' => Hash::make($data['kata_sandi']),
                'peran' => 'guru',
                'nomor_pegawai' => $data['nomor_pegawai'] ?? null,
                'telepon' => $data['telepon'] ?? null,
                'status' => $data['status'],
            ]);

            Guru::create([
                'pengguna_id' => $pengguna->id,
                'jadwal_kerja_bawaan_id' => $data['jadwal_kerja_bawaan_id'] ?? null,
                'nip' => $data['nip'] ?? null,
                'mata_pelajaran' => $data['mata_pelajaran'] ?? null,
                'jabatan' => $data['jabatan'] ?? null,
                'alamat' => $data['alamat'] ?? null,
                'jadwal_kerja' => $data['jadwal_kerja'] ?? null,
                'aktif' => $data['status'] === 'aktif',
            ]);
        });

        return redirect()->route('admin.guru.index')->with('status', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru): View
    {
        return view('admin.guru.form', [
            'guruItem' => $guru->load('pengguna'),
            'pengguna' => $guru->pengguna,
            'jadwalKerja' => JadwalKerja::query()->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Guru $guru): RedirectResponse
    {
        $data = $this->validateData($request, $guru);

        DB::transaction(function () use ($data, $guru): void {
            $penggunaData = [
                'nama' => $data['nama'],
                'email' => $data['email'],
                'nomor_pegawai' => $data['nomor_pegawai'] ?? null,
                'telepon' => $data['telepon'] ?? null,
                'status' => $data['status'],
            ];

            if (! empty($data['kata_sandi'])) {
                $penggunaData['kata_sandi'] = Hash::make($data['kata_sandi']);
            }

            $guru->pengguna->update($penggunaData);
            $guru->update([
                'jadwal_kerja_bawaan_id' => $data['jadwal_kerja_bawaan_id'] ?? null,
                'nip' => $data['nip'] ?? null,
                'mata_pelajaran' => $data['mata_pelajaran'] ?? null,
                'jabatan' => $data['jabatan'] ?? null,
                'alamat' => $data['alamat'] ?? null,
                'jadwal_kerja' => $data['jadwal_kerja'] ?? null,
                'aktif' => $data['status'] === 'aktif',
            ]);
        });

        return redirect()->route('admin.guru.index')->with('status', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru): RedirectResponse
    {
        $guru->update(['aktif' => false]);
        $guru->pengguna->update(['status' => 'nonaktif']);

        return back()->with('status', 'Guru dinonaktifkan agar riwayat presensi tetap tersimpan.');
    }

    public function resetPerangkat(Guru $guru): RedirectResponse
    {
        $guru->pengguna->perangkat()->update(['aktif' => false]);
        $guru->pengguna->tokenApi()->delete();

        return back()->with('status', 'Perangkat guru berhasil direset.');
    }

    private function validateData(Request $request, ?Guru $guru = null): array
    {
        $penggunaId = $guru?->pengguna_id;

        return $request->validate([
            'nama' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('pengguna', 'email')->ignore($penggunaId)],
            'kata_sandi' => [$guru ? 'nullable' : 'required', 'string', 'min:6'],
            'nomor_pegawai' => ['nullable', 'string', 'max:100', Rule::unique('pengguna', 'nomor_pegawai')->ignore($penggunaId)],
            'telepon' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'jadwal_kerja_bawaan_id' => ['nullable', 'exists:jadwal_kerja,id'],
            'nip' => ['nullable', 'string', 'max:100', Rule::unique('guru', 'nip')->ignore($guru?->id)],
            'mata_pelajaran' => ['nullable', 'string', 'max:191'],
            'jabatan' => ['nullable', 'string', 'max:191'],
            'alamat' => ['nullable', 'string', 'max:2000'],
            'jadwal_kerja' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
