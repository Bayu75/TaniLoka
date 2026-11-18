<?php

namespace App\Http\Controllers\Petani;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FlashSaleController extends PetaniController
{
    public function index(Request $request): View
    {
        $user = $this->petani($request);
        $hariIni = now()->startOfDay();

        $produk = $user->produk()
            ->with('fotoUtama')
            ->where('aktif', true)
            ->whereNotNull('tersedia_sampai')
            ->whereDate('tersedia_sampai', '>=', $hariIni)
            ->whereDate('tersedia_sampai', '<=', $hariIni->copy()->addDays(2))
            ->orderBy('tersedia_sampai')
            ->get();

        return view('petani.flash-sale', compact('produk'));
    }

    public function store(Request $request, int $produk): RedirectResponse
    {
        $user = $this->petani($request);
        $produk = $user->produk()->findOrFail($produk);

        $data = $request->validate([
            'harga_flash_sale' => ['required', 'integer', 'min:1', 'lt:'.(int) $produk->harga_per_kg],
        ], [
            'harga_flash_sale.lt' => 'Harga flash sale harus lebih murah dari harga normal.',
        ]);

        $produk->update([
            'harga_flash_sale' => $data['harga_flash_sale'],
            'flash_sale_sampai' => $produk->tersedia_sampai,
        ]);

        return back()->with('sukses', 'Flash sale untuk '.$produk->nama.' sudah aktif.');
    }

    public function destroy(Request $request, int $produk): RedirectResponse
    {
        $user = $this->petani($request);
        $produk = $user->produk()->findOrFail($produk);

        $produk->update(['harga_flash_sale' => null, 'flash_sale_sampai' => null]);

        return back()->with('sukses', 'Flash sale untuk '.$produk->nama.' dibatalkan.');
    }
}