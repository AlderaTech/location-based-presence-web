<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Support\SortirAdmin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenggunaAdminController extends Controller
{
    public function index(Request $request): View
    {
        $query = Pengguna::query()
            ->whereIn('peran', ['admin', 'tata_usaha', 'kepala_sekolah'])
            ->when($request->query('q'), function ($query, string $q): void {
                $query->where(function ($query) use ($q): void {
                    $query->where('nama', 'like', '%'.$q.'%')
                        ->orWhere('email', 'like', '%'.$q.'%')
                        ->orWhere('telepon', 'like', '%'.$q.'%');
                });
            })
            ->when($request->query('peran'), fn ($query, string $peran) => $query->where('peran', $peran))
            ->when($request->query('status'), fn ($query, string $status) => $query->where('status', $status));

        SortirAdmin::terapkan($query, $request, [
            'nama' => 'pengguna.nama',
            'email' => 'pengguna.email',
            'peran' => 'pengguna.peran',
            'status' => 'pengguna.status',
            'dibuat_pada' => 'pengguna.dibuat_pada',
        ], 'dibuat_pada', 'desc');

        return view('admin.pengguna-admin.index', [
            'penggunaAdmin' => $query->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pengguna-admin.form', ['pengguna' => new Pengguna(['peran' => 'tata_usaha', 'status' => 'aktif'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->data($request);
        $data['kata_sandi'] = Hash::make($data['kata_sandi']);
        Pengguna::create($data);

        return redirect()->route('admin.pengguna-admin.index')->with('status', 'Akun admin berhasil ditambahkan.');
    }

    public function edit(Pengguna $penggunaAdmin): View
    {
        return view('admin.pengguna-admin.form', ['pengguna' => $penggunaAdmin]);
    }

    public function update(Request $request, Pengguna $penggunaAdmin): RedirectResponse
    {
        $data = $this->data($request, $penggunaAdmin);
        if (! empty($data['kata_sandi'])) {
            $data['kata_sandi'] = Hash::make($data['kata_sandi']);
        } else {
            unset($data['kata_sandi']);
        }
        $penggunaAdmin->update($data);

        return redirect()->route('admin.pengguna-admin.index')->with('status', 'Akun admin berhasil diperbarui.');
    }

    public function destroy(Pengguna $penggunaAdmin): RedirectResponse
    {
        if (auth()->id() === $penggunaAdmin->id) {
            return back()->withErrors(['pengguna' => 'Akun sendiri tidak bisa dinonaktifkan dari halaman ini.']);
        }

        $penggunaAdmin->update(['status' => 'nonaktif']);

        return back()->with('status', 'Akun admin dinonaktifkan.');
    }

    private function data(Request $request, ?Pengguna $pengguna = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('pengguna', 'email')->ignore($pengguna?->id)],
            'kata_sandi' => [$pengguna ? 'nullable' : 'required', 'string', 'min:6'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'peran' => ['required', 'in:admin,tata_usaha,kepala_sekolah'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);
    }
}
