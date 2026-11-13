<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use App\Models\KeranjangItem;
use App\Models\Produk;
use Illuminate\Http\Request;

class KeranjangController extends Controller
{
    public function index()
    {
        $items = auth()->user()->keranjang()
            ->with(['produk.fotoUtama', 'produk.petani.profilPetani'])
            ->get();

        // Dikelompokkan per petani, sama seperti checkout Ayudia nanti
        $perPetani = $items->groupBy(fn ($item) => $item->produk->petani_id);
        $total = $items->sum(fn ($item) => $item->subtotal);
        $bisaCheckout = auth()->user()->terverifikasi() && $items->isNotEmpty();

        return view('pengusaha.keranjang', compact('items', 'perPetani', 'total', 'bisaCheckout'));
    }

    public function tambah(Request $request, Produk $produk)
    {
        abort_unless($produk->aktif, 404);

        $data = $request->validate([
            'jumlah_kg' => ['required', 'numeric', 'min:' . $produk->min_order_kg, 'max:' . $produk->stok_kg],
        ], [
            'jumlah_kg.min' => "Minimal pembelian {$produk->min_order_kg} kg.",
            'jumlah_kg.max' => "Stok tersedia hanya {$produk->stok_kg} kg.",
        ]);

        $item = KeranjangItem::firstOrNew([
            'user_id' => auth()->id(),
            'produk_id' => $produk->id,
        ]);

        $jumlahBaru = ($item->exists ? $item->jumlah_kg : 0) + $data['jumlah_kg'];

        if ($jumlahBaru > $produk->stok_kg) {
            return back()->withErrors(['jumlah_kg' => "Total di keranjang melebihi stok ({$produk->stok_kg} kg)."]);
        }

        $item->jumlah_kg = $jumlahBaru;
        $item->save();

        if ($request->input('aksi') === 'beli') {
            return redirect()->route('pengusaha.keranjang');
        }

        return back()->with('sukses', 'Produk ditambahkan ke keranjang.');
    }

    public function ubah(Request $request, KeranjangItem $item)
    {
        abort_unless($item->user_id == auth()->id(), 403);

        $produk = $item->produk;

        $data = $request->validate([
            'jumlah_kg' => ['required', 'numeric', 'min:' . $produk->min_order_kg, 'max:' . $produk->stok_kg],
        ], [
            'jumlah_kg.min' => "Minimal pembelian {$produk->min_order_kg} kg.",
            'jumlah_kg.max' => "Stok tersedia hanya {$produk->stok_kg} kg.",
        ]);

        $item->update(['jumlah_kg' => $data['jumlah_kg']]);

        return back()->with('sukses', 'Jumlah diperbarui.');
    }

    public function hapus(KeranjangItem $item)
    {
        abort_unless($item->user_id == auth()->id(), 403);

        $item->delete();

        return back()->with('sukses', 'Produk dihapus dari keranjang.');
    }
}