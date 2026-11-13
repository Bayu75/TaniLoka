<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekVerifikasi
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->terverifikasi()) {
            return back()->with('peringatan', 'Akunmu belum terverifikasi. Ajukan verifikasi di halaman Akun.');
        }

        return $next($request);
    }
}