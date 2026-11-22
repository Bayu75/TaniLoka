<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use App\Models\KeranjangItem;
use App\Models\Produk;
use Illuminate\Http\Request;
use App\Models\Pesan;

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
        if ($produk->tersedia_sampai && $produk->tersedia_sampai->copy()->endOfDay()->isPast()) {
            return back()->withErrors(['jumlah_kg' => 'Masa tersedia produk ini sudah berakhir.']);
        }
        $item = KeranjangItem::firstOrNew([
            'user_id' => auth()->id(),
            'produk_id' => $produk->id,
        ]);
        // Dari chat: harga dan jumlah diambil dari penawaran yang sudah disepakati, bukan dari form
        if ($request->filled('penawaran_id')) {
            $penawaran = Pesan::query()
                ->whereKey($request->integer('penawaran_id'))
                ->where('tipe', 'penawaran')
                ->where('status_penawaran', 'disepakati')
                ->where('produk_id', $produk->id)
                ->whereHas('percakapan', fn ($q) => $q->where('pengusaha_id', auth()->id()))
                ->first();
            if (! $penawaran) {
                return back()->withErrors(['jumlah_kg' => 'Penawaran tidak ditemukan atau belum disepakati.']);
            }
            if ($penawaran->jumlah_penawaran > $produk->stok_kg) {
                return back()->withErrors(['jumlah_kg' => "Stok tersedia hanya {$produk->stok_kg} kg."]);
            }
            $item->jumlah_kg = $penawaran->jumlah_penawaran;
            $item->harga_per_kg = $penawaran->harga_penawaran;
            $item->save();
            return redirect()->route('pengusaha.keranjang')->with('sukses', 'Produk hasil nego masuk ke keranjang.');
        }
        // Beli biasa dari detail produk
        $data = $request->validate([
            'jumlah_kg' => ['required', 'numeric', 'min:' . $produk->min_order_kg, 'max:' . $produk->stok_kg],
        ], [
            'jumlah_kg.min' => "Minimal pembelian {$produk->min_order_kg} kg.",
            'jumlah_kg.max' => "Stok tersedia hanya {$produk->stok_kg} kg.",
        ]);
        $jumlahBaru = ($item->exists ? $item->jumlah_kg : 0) + $data['jumlah_kg'];
        if ($jumlahBaru > $produk->stok_kg) {
            return back()->withErrors(['jumlah_kg' => "Total di keranjang melebihi stok ({$produk->stok_kg} kg)."]);
        }
        $item->jumlah_kg = $jumlahBaru;
        // Tambah lewat beli biasa = harga normal lagi, supaya harga nego nggak dipakai untuk jumlah tambahan
        $item->harga_per_kg = null;
        $item->save();
        if ($request->input('aksi') === 'beli') {
            return redirect()->route('pengusaha.keranjang');
        }
        return back()->with('sukses', 'Produk ditambahkan ke keranjang.');
    }

    public function ubah(Request $request, KeranjangItem $item)
    {
        abort_unless($item->user_id == auth()->id(), 403);
        // Jumlah hasil nego dikunci, kalau diubah harga nego bisa dipakai untuk jumlah lain
        if (! is_null($item->harga_per_kg)) {
            return back()->with('error', 'Jumlah hasil nego tidak bisa diubah. Hapus item ini lalu nego ulang kalau perlu.');
        }
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
