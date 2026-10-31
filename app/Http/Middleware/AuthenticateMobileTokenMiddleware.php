<?php

namespace App\Http\Middleware;

use App\Models\TokenApi;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMobileTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tokenPolos = $request->bearerToken();

        if (! $tokenPolos) {
            return response()->json(['pesan' => 'Token autentikasi tidak ditemukan.'], 401);
        }

        $token = TokenApi::query()
            ->with(['pengguna.murid.kelas', 'pengguna.guru'])
            ->where('hash_token', hash('sha256', $tokenPolos))
            ->first();

        if (! $token || ($token->kedaluwarsa_pada && $token->kedaluwarsa_pada->isPast())) {
            return response()->json(['pesan' => 'Token autentikasi tidak valid.'], 401);
        }

        if (! $token->pengguna || ! $token->pengguna->aktif()) {
            return response()->json(['pesan' => 'Akun tidak aktif.'], 403);
        }

        $token->forceFill(['terakhir_dipakai_pada' => now()])->save();
        $request->setUserResolver(fn () => $token->pengguna);
        $request->attributes->set('token_api', $token);
        Auth::setUser($token->pengguna);

        return $next($request);
    }
}
