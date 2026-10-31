<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use App\Models\PerangkatPengguna;
use App\Models\TokenApi;
use App\Support\PresensiFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function masuk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string'],
            'kata_sandi' => ['required', 'string'],
            'id_perangkat' => ['required', 'string', 'max:191'],
            'nama_perangkat' => ['nullable', 'string', 'max:191'],
            'platform' => ['nullable', 'string', 'max:50'],
        ]);

        $inputIdentitas = trim($data['email']);
        $pengguna = Pengguna::query()
            ->with(['murid.kelas', 'perangkat'])
            ->where(function ($query) use ($inputIdentitas) {
                $query->where('email', $inputIdentitas)
                    ->orWhere('nomor_pegawai', $inputIdentitas)
                    ->orWhereHas('murid', fn ($m) => $m->where('nis', $inputIdentitas));
            })
            ->first();

        if (! $pengguna || ! Hash::check($data['kata_sandi'], $pengguna->kata_sandi)) {
            throw ValidationException::withMessages(['email' => 'Email/NIS atau kata sandi salah.']);
        }

        if (! $pengguna->adalahMurid() || ! $pengguna->murid || ! $pengguna->murid->aktif) {
            throw ValidationException::withMessages(['email' => 'Akun ini bukan akun murid aktif.']);
        }

        if (! $pengguna->aktif()) {
            throw ValidationException::withMessages(['email' => 'Akun murid tidak aktif. Hubungi admin.']);
        }

        $perangkatTerikat = $pengguna->perangkat()->where('aktif', true)->first();

        if ($perangkatTerikat && $perangkatTerikat->id_perangkat !== $data['id_perangkat']) {
            return response()->json([
                'pesan' => 'Akun sudah terikat pada perangkat lain. Hubungi admin untuk reset perangkat.',
            ], 423);
        }

        PerangkatPengguna::query()->updateOrCreate(
            ['id_pengguna' => $pengguna->id, 'id_perangkat' => $data['id_perangkat']],
            [
                'nama_perangkat' => $data['nama_perangkat'] ?? null,
                'platform' => $data['platform'] ?? 'android',
                'pertama_terlihat_pada' => $perangkatTerikat?->pertama_terlihat_pada ?? now(),
                'terakhir_terlihat_pada' => now(),
                'aktif' => true,
            ],
        );

        $tokenPolos = Str::random(80);
        TokenApi::create([
            'id_pengguna' => $pengguna->id,
            'nama' => 'mobile_murid',
            'hash_token' => hash('sha256', $tokenPolos),
            'id_perangkat' => $data['id_perangkat'],
            'kedaluwarsa_pada' => now()->addDays(30),
        ]);

        return response()->json([
            'token' => $tokenPolos,
            'tipe_token' => 'Bearer',
            'kedaluwarsa_pada' => now()->addDays(30)->toIso8601String(),
            'pengguna' => PresensiFormatter::pengguna($pengguna->fresh('murid.kelas')),
        ]);
    }

    public function aktivasi(Request $request): JsonResponse
    {
        return $this->masuk($request);
    }

    public function resetKataSandi(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        Password::sendResetLink($data);

        return response()->json([
            'pesan' => 'Jika email terdaftar, instruksi reset kata sandi akan dikirim atau dicatat pada log mail.',
        ]);
    }

    public function keluar(Request $request): JsonResponse
    {
        $token = $request->attributes->get('token_api');
        $token?->delete();

        return response()->json(['pesan' => 'Berhasil keluar.']);
    }
}
