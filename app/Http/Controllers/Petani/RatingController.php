<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Ulasan;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function index()
    {
        $petaniId = Auth::id();

        $dasar = Ulasan::where('petani_id', $petaniId);

        $jumlah = (clone $dasar)->count();
        $rataRata = $jumlah ? round((float) (clone $dasar)->avg('rating'), 1) : 0;

        // Sebaran bintang 5 sampai 1
        $hitung = (clone $dasar)
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $sebaran = [];
        foreach ([5, 4, 3, 2, 1] as $b) {
            $total = (int) ($hitung[$b] ?? 0);
            $sebaran[$b] = [
                'total'  => $total,
                'persen' => $jumlah ? round($total / $jumlah * 100) : 0,
            ];
        }

        $ulasan = (clone $dasar)
            ->with(['pengusaha', 'pesanan'])
            ->latest()
            ->paginate(10);

        return view('petani.rating', compact('jumlah', 'rataRata', 'sebaran', 'ulasan'));
    }
}