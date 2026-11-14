<?php

namespace App\Http\Controllers\Pengusaha;

use App\Http\Controllers\Controller;
use App\Models\Percakapan;
use App\Models\Pesan;
use App\Models\User;
use App\Models\Produk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    // Menampilkan daftar percakapan atau membuka chat dari katalog
    public function index(Request $request)
    {
        // Jika pembeli datang dari tombol Tawar Harga di katalog
        if ($request->filled('produk')) {
            $produk = Produk::with('petani')
                ->where('slug', $request->produk)
                ->firstOrFail();

            $percakapan = Percakapan::firstOrCreate(
                [
                    'pengusaha_id' => Auth::id(),
                    'petani_id' => $produk->petani_id,
                ],
                [
                    'pesan_terakhir_pada' => null,
                ]
            );

            return redirect()->route('pengusaha.pesan.detail', [
                'id' => $percakapan->id,
                'tawar' => 1,
                'produk' => $produk->slug,
            ]);
        }

        $dataPercakapan = Percakapan::with([
            'petani',
            'pesan.produk.fotoUtama',
            'pesanan.item',
        ])
        ->where('pengusaha_id', Auth::id())
        ->latest('pesan_terakhir_pada')
        ->get();

        $daftarPercakapan = $dataPercakapan->map(function ($item) {
            $pesanTerakhir = $item->pesan->last();

            $penawaranTerakhir = $item->pesan
                ->where('tipe', 'penawaran')
                ->last();

            $produk = $penawaranTerakhir?->produk
                ?? $pesanTerakhir?->produk;

            $namaProduk = $produk?->nama
                ?? $item->pesanan?->item->first()?->nama_produk
                ?? '-';

            $pesanPreview = $pesanTerakhir?->isi ?? 'Belum ada pesan';

            if ($pesanTerakhir?->tipe === 'penawaran') {
                $pesanPreview = 'Penawaran: Rp ' .
                    number_format($pesanTerakhir->harga_penawaran, 0, ',', '.') .
                    '/kg × ' . $pesanTerakhir->jumlah_penawaran . ' kg';
            }

            return [
                'id' => $item->id,
                'nama' => $item->petani?->name ?? 'Petani',
                'produk' => $namaProduk,
                'gambar' => $produk?->fotoUtama?->path
                    ? asset('storage/' . $produk->fotoUtama->path)
                    : asset('images/products/bayam.png'),
                'pesan' => $pesanPreview,
                'waktu' => $pesanTerakhir?->created_at?->format('H:i') ?? '',
                'status' => $penawaranTerakhir?->status_penawaran ?? 'terbuka',
            ];
        });

        return view('pengusaha.pesan', compact('daftarPercakapan'));
    }

    // Menampilkan detail percakapan
    public function show($id)
    {
        $percakapan = Percakapan::with([
            'petani',
            'pesan.pengirim',
            'pesan.produk',
            'pesanan.item',
        ])
        ->where('pengusaha_id', Auth::id())
        ->where('id', $id)
        ->firstOrFail();

        $namaPetani = $percakapan->petani?->name ?? 'Petani';

        $penawaranAktif = $percakapan->pesan
            ->where('tipe', 'penawaran')
            ->where('status_penawaran', 'menunggu')
            ->last();

        $produk = null;

        if (request()->filled('produk')) {
            $produk = Produk::where('slug', request('produk'))
                ->where('petani_id', $percakapan->petani_id)
                ->first();
        }

        $daftarPesan = $percakapan->pesan->map(function ($pesan) {
            return [
                'id' => $pesan->id,
                'tipe' => $pesan->tipe ?? 'teks',
                'arah' => $pesan->pengirim_id == Auth::id() ? 'keluar' : 'masuk',
                'pengirim' => $pesan->pengirim?->name ?? 'Pengguna',
                'isi' => $pesan->isi,
                'harga' => $pesan->harga_penawaran,
                'jumlah' => $pesan->jumlah_penawaran,
                'total' => $pesan->total_penawaran,
                'harga_normal' => $pesan->produk?->harga_per_kg ?? 0,
                'status' => $pesan->status_penawaran,
                'waktu' => $pesan->created_at?->format('H:i'),
            ];
        })->values();

        return view('pengusaha.chat', compact(
            'percakapan',
            'namaPetani',
            'daftarPesan',
            'produk',
            'penawaranAktif'
        ));
    }

    // Mengirim pesan dalam percakapan
    public function kirim(Request $request, $id)
    {
        $data = $request->validate([
            'isi' => 'required|string|max:5000',
        ]);

        $percakapan = Percakapan::where('id', $id)
            ->where('pengusaha_id', Auth::id())
            ->firstOrFail();

        $pesan = $percakapan->pesan()->create([
            'pengirim_id' => Auth::id(),
            'isi' => $data['isi'],
        ]);

        $percakapan->update([
            'pesan_terakhir_pada' => now(),
        ]);

        // Mengembalikan data pesan jika dikirim melalui AJAX
        if ($request->expectsJson()) {
            $pesan->load('pengirim');

            return response()->json([
                'success' => true,
                'pesan' => [
                    'id' => $pesan->id,
                    'tipe' => $pesan->tipe ?? 'teks',
                    'arah' => 'keluar',
                    'pengirim' => $pesan->pengirim?->name ?? 'Pengguna',
                    'isi' => $pesan->isi,
                    'harga' => $pesan->harga_penawaran,
                    'jumlah' => $pesan->jumlah_penawaran,
                    'total' => $pesan->total_penawaran,
                    'status' => $pesan->status_penawaran,
                    'waktu' => $pesan->created_at?->format('H:i'),
                ],
            ]);
        }

        return redirect()->route('pengusaha.pesan.detail', [
            'id' => $id,
            'tawar' => 1,
            'produk' => $request->input('produk'),
        ]);
    }

    // Membuka atau membuat percakapan dengan petani
    public function buka($petaniId)
    {
        $percakapan = Percakapan::firstOrCreate(
            [
                'pengusaha_id' => Auth::id(),
                'petani_id' => $petaniId,
            ],
            [
                'pesan_terakhir_pada' => null,
            ]
        );

        return redirect()->route('pengusaha.pesan', [
            'percakapan' => $percakapan->id,
        ]);
    }

    // Mengirim penawaran harga
    public function kirimPenawaran(Request $request, $id)
    {
        $percakapan = Percakapan::where('id', $id)
            ->where('pengusaha_id', Auth::id())
            ->firstOrFail();

        $data = $request->validate([
            'produk' => 'required|string',
            'harga_penawaran' => 'required|numeric|min:1',
            'jumlah_penawaran' => 'required|integer|min:1',
            'isi' => 'nullable|string|max:5000',
        ]);

        $produk = Produk::where('slug', $data['produk'])
            ->where('petani_id', $percakapan->petani_id)
            ->firstOrFail();

        $request->validate([
            'jumlah_penawaran' => 'required|integer|min:' . $produk->min_order_kg,
        ]);

        $percakapan->pesan()->create([
            'pengirim_id' => Auth::id(),
            'produk_id' => $produk->id,
            'tipe' => 'penawaran',
            'harga_penawaran' => $data['harga_penawaran'],
            'jumlah_penawaran' => $data['jumlah_penawaran'],
            'total_penawaran' => $data['harga_penawaran'] * $data['jumlah_penawaran'],
            'status_penawaran' => 'menunggu',
            'isi' => $data['isi'] ?? '',
        ]);

        $percakapan->update([
            'pesan_terakhir_pada' => now(),
        ]);

        return redirect()->route('pengusaha.pesan.detail', [
            'id' => $id,
            'produk' => $produk->slug,
        ]);
    }
}
