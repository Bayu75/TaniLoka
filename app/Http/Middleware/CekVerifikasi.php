<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekVerifikasi
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->terverifikasi()) {
            $pesan = 'Akunmu belum terverifikasi. Ajukan verifikasi di halaman Akun.';

            if ($user?->peran === 'pengusaha') {
                return redirect()
                    ->route('pengusaha.keranjang')
                    ->with('error', $pesan)
                    ->with('peringatan', $pesan);
            }

            return back()->with('error', $pesan)->with('peringatan', $pesan);
        }

        return $next($request);
    }
}