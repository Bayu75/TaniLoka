<?php

namespace App\Http\Controllers\Petani;

use App\Models\Pesan;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\RiwayatStatusPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends PetaniController
{
    /**
     * Pendapatan petani dihitung dari kolom ini pada pesanan berstatus "selesai".
     * subtotal = nilai produk (tanpa ongkir dan biaya layanan). Ganti ke 'total' kalau tim memutuskan lain.
     */
    private const KOLOM_PENDAPATAN = 'subtotal';

    public function index(Request $request): View
    {
        $user = $this->petani($request);

        $produkAktif = $user->produk()->aktif()->count();      // scope Produk::aktif(): aktif & stok > 0
        $totalStok = (float) $user->produk()->sum('stok_kg');

        $pesananBaru = $user->pesananMasuk()->where('status', 'menunggu_konfirmasi')->count();
        $pesananDiproses = $user->pesananMasuk()->where('status', 'diproses')->count();

        // Waktu selesai = riwayat status "selesai" terakhir; kalau tidak ada, pakai updated_at pesanan.
        $waktuSelesai = RiwayatStatusPesanan::query()
            ->select('pesanan_id', DB::raw('MAX(created_at) as waktu'))
            ->where('status', 'selesai')
            ->groupBy('pesanan_id');

        $selesai = fn () => $user->pesananMasuk()
            ->leftJoinSub($waktuSelesai, 'rs', 'rs.pesanan_id', '=', 'pesanan.id')
            ->where('pesanan.status', 'selesai');

        $awalBulan = Carbon::now(self::ZONA_WAKTU)->startOfMonth()->timezone(config('app.timezone'));
        $pendapatanTotal = (int) $selesai()->sum('pesanan.'.self::KOLOM_PENDAPATAN);
        $pendapatanBulanIni = (int) $selesai()
            ->whereRaw('COALESCE(rs.waktu, pesanan.updated_at) >= ?', [$awalBulan->format('Y-m-d H:i:s')])
            ->sum('pesanan.'.self::KOLOM_PENDAPATAN);

        $pesanBelumDibaca = Pesan::whereHas('percakapan', fn ($q) => $q->where('petani_id', $user->id))
            ->where('pengirim_id', '!=', $user->id)
            ->whereNull('dibaca_pada')
            ->count();

        $kelas = [
            'menunggu_konfirmasi' => 'bg-yellow-100 text-yellow-800',
            'diproses' => 'bg-blue-100 text-blue-800',
            'dikirim' => 'bg-indigo-100 text-indigo-800',
            'selesai' => 'bg-emerald-100 text-emerald-800',
            'dibatalkan' => 'bg-red-100 text-red-800',
            'ditolak' => 'bg-red-100 text-red-800',
        ];

        $pesananTerbaru = $user->pesananMasuk()
            ->with(['pengusaha.profilPengusaha', 'item'])
            ->where('status', '!=', 'menunggu_pembayaran')
            ->latest()->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (Pesanan $p) => [
                'id' => $p->id,
                'nomor' => $p->kode,
                'pembeli' => $p->pengusaha?->profilPengusaha?->nama_usaha ?: ($p->pengusaha?->name ?: 'Pembeli'),
                'produk' => $p->item
                    ->map(fn (PesananItem $i) => $i->nama_produk.' '.rtrim(rtrim(number_format((float) $i->jumlah_kg, 2, ',', '.'), '0'), ',').' kg')
                    ->implode(', '),
                'total' => (int) $p->total,
                'status' => PesananController::LABEL_STATUS[$p->status] ?? $p->status,
                'kelas' => $kelas[$p->status] ?? 'bg-gray-100 text-gray-800',
            ])->all();

        return view('petani.dashboard', [
            'namaPetani' => $user->name,
            'statistik' => [
                'produkAktif' => $produkAktif,
                'totalStok' => $totalStok,
                'pesananBaru' => $pesananBaru,
                'pesananDiproses' => $pesananDiproses,
                'pesanBelumDibaca' => $pesanBelumDibaca,
                'pendapatanBulanIni' => $pendapatanBulanIni,
                'pendapatanTotal' => $pendapatanTotal,
            ],
            'pesananTerbaru' => $pesananTerbaru,
        ]);
    }
}
