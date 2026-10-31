<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('admin.masuk');
        }

        $user = Auth::user();

        if ((! $user->adalahAdmin() && ! $user->adalahGuru()) || ! $user->aktif()) {
            abort(403, 'Akun ini tidak memiliki akses dashboard web.');
        }

        return $next($request);
    }
}
