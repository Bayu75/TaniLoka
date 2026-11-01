<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CekPeran
{
    public function handle(Request $request, Closure $next, string $peran)
    {
        if (! session('peran')) {
            return redirect()->route('masuk');
        }

        if (session('peran') !== $peran) {
            $tujuan = session('peran') === 'petani' ? 'petani.dashboard' : 'pengusaha.beranda';
            return redirect()->route($tujuan);
        }

        return $next($request);
    }
}