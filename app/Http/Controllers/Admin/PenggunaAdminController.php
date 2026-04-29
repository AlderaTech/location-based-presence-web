<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PenggunaAdminController extends Controller
{
    public function index(): View
    {
        return view('admin.pengguna-admin.index', [
            'penggunaAdmin' => Pengguna::query()->whereIn('peran', ['admin', 'tata_usaha', 'kepala_sekolah'])->latest('dibuat_pada')->paginate(15),
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
