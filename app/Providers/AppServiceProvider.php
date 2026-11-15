<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Pesan;
use App\Models\KeranjangItem;
use App\Models\Pesanan;
use App\Models\Percakapan;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.navbar', function ($view) {
            $pesanBelumDibaca = 0;
            $jumlahKeranjang = 0;
            $pesananBerlangsung = 0;

            if (Auth::check() && Auth::user()->peran === 'pengusaha') {
                $userId = Auth::id();

                // Pesan masuk dari petani yang belum dibaca
                $pesanBelumDibaca = Pesan::whereHas('percakapan', function ($query) use ($userId) {
                    $query->where('pengusaha_id', $userId);
                })
                ->where('pengirim_id', '!=', $userId)
                ->whereNull('dibaca_pada')
                ->count();

                // Jumlah produk berbeda di keranjang
                $jumlahKeranjang = KeranjangItem::where('user_id', $userId)
                    ->count();

                // Pesanan yang masih berlangsung
                $pesananBerlangsung = Pesanan::where('pengusaha_id', $userId)
                    ->whereNotIn('status', ['selesai', 'dibatalkan'])
                    ->count();
            }

            $view->with(compact(
                'pesanBelumDibaca',
                'jumlahKeranjang',
                'pesananBerlangsung'
            ));
        });
    }
}
