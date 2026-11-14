<?php

namespace App\Http\Controllers\Petani;

use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\RiwayatStatusPesanan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Pesanan Masuk: daftar, detail, terima, tolak, tandai dikirim.
 *
 * Status yang boleh diubah petani:
 *   menunggu_konfirmasi -> diproses  (terima)
 *   menunggu_konfirmasi -> ditolak   (tolak, alasan opsional)
 *   diproses            -> dikirim   (kirim)
 * Pesanan "menunggu_pembayaran" hanya bisa dilihat, tidak bisa diproses.
 */
class PesananController extends PetaniController
{
    /** Status database -> id tab di tampilan (hash #menunggu dsb. sudah dipakai dashboard). */
    public const STATUS_KE_TAB = [
        'menunggu_pembayaran' => 'pembayaran',
        'menunggu_konfirmasi' => 'menunggu',
        'diproses' => 'diproses',
        'dikirim' => 'dikirim',
        'selesai' => 'selesai',
        'dibatalkan' => 'dibatalkan',
        'ditolak' => 'ditolak',
    ];

    public const LABEL_STATUS = [
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
        'diproses' => 'Diproses',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        'ditolak' => 'Ditolak',
    ];

    public function index(Request $request): View
    {
        $user = $this->petani($request);

        $request->validate([
            'status' => ['nullable', 'in:'.implode(',', array_merge(array_keys(self::STATUS_KE_TAB), array_values(self::STATUS_KE_TAB), ['semua']))],
        ], $this->pesanValidasi(), ['status' => 'status']);

        $query = $this->queryPesanan($user);

        // Filter server-side (opsional): /petani/pesanan?status=diproses atau ?status=menunggu_konfirmasi
        $status = $request->query('status');
        if ($status && $status !== 'semua') {
            $query->where('status', array_search($status, self::STATUS_KE_TAB, true) ?: $status);
        }

        return view('petani.pesanan', [
            'orders' => $this->formatPesanan($query->get()),
            'detailAwal' => null,
            'tabAwal' => $status ? (self::STATUS_KE_TAB[$status] ?? $status) : null,
        ]);
    }

    public function show(Request $request, int $pesanan): View
    {
        $user = $this->petani($request);
        $data = $this->pastikanMilik(Pesanan::find($pesanan), $user);

        return view('petani.pesanan', [
            'orders' => $this->formatPesanan($this->queryPesanan($user)->get()),
            'detailAwal' => $data->id,
            'tabAwal' => null,
        ]);
    }

    public function terima(Request $request, int $pesanan): RedirectResponse
    {
        return $this->ubahStatus($request, $pesanan, 'menunggu_konfirmasi', 'diproses',
            'Pesanan diterima petani.', 'Pesanan diterima dan sedang diproses.');
    }

    public function tolak(Request $request, int $pesanan): RedirectResponse
    {
        $data = $request->validate(
            ['alasan' => ['nullable', 'string', 'max:255']],
            $this->pesanValidasi(),
            ['alasan' => 'alasan penolakan'],
        );
        $alasan = trim((string) ($data['alasan'] ?? ''));

        return $this->ubahStatus($request, $pesanan, 'menunggu_konfirmasi', 'ditolak',
            $alasan !== '' ? 'Ditolak petani: '.$alasan : 'Pesanan ditolak petani.', 'Pesanan ditolak.');
    }

    public function kirim(Request $request, int $pesanan): RedirectResponse
    {
        return $this->ubahStatus($request, $pesanan, 'diproses', 'dikirim',
            'Pesanan dikirim petani.', 'Pesanan ditandai dikirim.');
    }

    private function ubahStatus(Request $request, int $id, string $dari, string $ke, string $keterangan, string $pesanSukses): RedirectResponse
    {
        $user = $this->petani($request);

        $statusSaatIni = DB::transaction(function () use ($id, $user, $dari, $ke, $keterangan) {
            $pesanan = Pesanan::whereKey($id)->lockForUpdate()->first();
            $this->pastikanMilik($pesanan, $user);

            if ($pesanan->status !== $dari) {
                return $pesanan->status; // transisi tidak diizinkan
            }

            // Sama seperti Pesanan::ubahStatus(), tapi created_at riwayat diisi dari Laravel (lihat CATATAN.md:
            // model RiwayatStatusPesanan memakai $timestamps = false sehingga created_at bergantung jam server MySQL).
            $pesanan->update(['status' => $ke]);
            $pesanan->riwayat()->create([
                'status' => $ke,
                'keterangan' => mb_substr($keterangan, 0, 255),
                'diubah_oleh' => $user->id,
                'created_at' => now(),
            ]);

            return null; // berhasil
        });

        $keDetail = redirect()->route('petani.pesanan.show', $id);

        if ($statusSaatIni === null) {
            return $keDetail->with('sukses', $pesanSukses);
        }

        if ($statusSaatIni === 'menunggu_pembayaran') {
            return $keDetail->with('gagal', 'Pesanan ini belum dibayar pembeli, jadi belum bisa diproses.');
        }

        $label = self::LABEL_STATUS[$statusSaatIni] ?? $statusSaatIni;

        return $keDetail->with('gagal', "Aksi tidak bisa dilakukan karena status pesanan saat ini \"{$label}\".");
    }

    private function queryPesanan(User $user)
    {
        return $user->pesananMasuk()
            ->with(['pengusaha.profilPengusaha', 'item.produk.fotoUtama', 'riwayat', 'pembayaran'])
            ->latest()
            ->latest('id');
    }

    /** Ubah pesanan ke bentuk yang dipakai Alpine di petani/pesanan.blade.php. */
    private function formatPesanan(Collection $daftar): array
    {
        $metode = ['transfer_bank' => 'Transfer Bank', 'qris' => 'QRIS', 'ewallet' => 'E-Wallet'];
        $statusBayar = ['belum_bayar' => 'belum dibayar', 'menunggu_verifikasi' => 'menunggu verifikasi', 'lunas' => 'lunas', 'gagal' => 'gagal'];

        return $daftar->map(function (Pesanan $o) use ($metode, $statusBayar) {
            $bayar = $o->pembayaran;

            return [
                'id' => $o->id,
                'nomor' => $o->kode,
                'pembeli' => $o->pengusaha?->profilPengusaha?->nama_usaha ?: ($o->pengusaha?->name ?: 'Pembeli'),
                'tanggal' => $this->waktuWita($o->created_at, 'j M Y'),
                'status' => self::STATUS_KE_TAB[$o->status] ?? $o->status,
                'statusDb' => $o->status,
                'penerima' => $o->nama_penerima,
                'telepon' => $o->no_hp_penerima,
                'alamat' => $o->alamat_pengiriman,
                'catatan' => $o->catatan,
                'pembayaran' => $bayar
                    ? ($metode[$bayar->metode] ?? $bayar->metode).' · '.($statusBayar[$bayar->status] ?? $bayar->status)
                    : null,
                'produk' => $o->item->map(fn (PesananItem $i) => [
                    'nama' => $i->nama_produk,
                    'jumlah' => (float) $i->jumlah_kg,
                    'harga' => (int) $i->harga_per_kg,
                    'subtotal' => (int) $i->subtotal,
                    'gambar' => $i->produk?->fotoUtama?->url,
                ])->values()->all(),
                'subtotal' => (int) $o->subtotal,
                'ongkir' => (int) $o->ongkir,
                'biayaLayanan' => (int) $o->biaya_layanan,
                'total' => (int) $o->total,
                'riwayat' => $o->riwayat->map(fn (RiwayatStatusPesanan $r) => [
                    'status' => self::STATUS_KE_TAB[$r->status] ?? $r->status,
                    'keterangan' => $r->keterangan,
                    'waktu' => $this->waktuWita($r->created_at).' WITA',
                ])->values()->all(),
            ];
        })->values()->all();
    }
}
