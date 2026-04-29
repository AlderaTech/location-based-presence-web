<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PastikanPenggunaAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('admin.masuk');
        }

        if (! Auth::user()->adalahAdmin() || ! Auth::user()->aktif()) {
            abort(403, 'Akun ini tidak memiliki akses dashboard admin.');
        }

        return $next($request);
    }
}
