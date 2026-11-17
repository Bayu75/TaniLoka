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

class PesananController extends PetaniController
{
    /**
     * Status database -> tab tampilan Petani.
     */
    public const STATUS_KE_TAB = [
        'menunggu_konfirmasi' => 'menunggu',
        'menunggu_pembayaran' => 'pembayaran',
        'diproses' => 'diproses',
        'dikirim' => 'dikirim',
        'siap_diambil' => 'siap_diambil',
        'terkirim' => 'terkirim',
        'selesai' => 'selesai',
        'dibatalkan' => 'dibatalkan',
        'ditolak' => 'ditolak',
    ];

    public const LABEL_STATUS = [
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'diproses' => 'Diproses',
        'dikirim' => 'Dikirim',
        'siap_diambil' => 'Siap Diambil',
        'terkirim' => 'Terkirim',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        'ditolak' => 'Ditolak',
    ];

    /**
     * Daftar pesanan yang masuk ke Petani.
     */
    public function index(Request $request): View
    {
        $user = $this->petani($request);

        $request->validate([
            'status' => [
                'nullable',
                'in:' . implode(',', array_merge(
                    array_keys(self::STATUS_KE_TAB),
                    array_values(self::STATUS_KE_TAB),
                    ['semua']
                )),
            ],
        ], $this->pesanValidasi(), ['status' => 'status']);

        $query = $this->queryPesanan($user);

        $status = $request->query('status');

        if ($status && $status !== 'semua') {
            $statusDatabase = array_search(
                $status,
                self::STATUS_KE_TAB,
                true
            ) ?: $status;

            $query->where('status', $statusDatabase);
        }

        return view('petani.pesanan', [
            'orders' => $this->formatPesanan($query->get()),
            'detailAwal' => null,
            'tabAwal' => $status
                ? (self::STATUS_KE_TAB[$status] ?? $status)
                : null,
        ]);
    }

    /**
     * Detail pesanan milik Petani.
     */
    public function show(Request $request, int $pesanan): View
    {
        $user = $this->petani($request);

        $data = $this->pastikanMilik(
            Pesanan::find($pesanan),
            $user
        );

        return view('petani.pesanan', [
            'orders' => $this->formatPesanan(
                $this->queryPesanan($user)->get()
            ),
            'detailAwal' => $data->id,
            'tabAwal' => null,
        ]);
    }

    /**
     * Petani menerima pesanan.
     *
     * menunggu_konfirmasi -> menunggu_pembayaran
     */
    public function terima(
        Request $request,
        int $pesanan
    ): RedirectResponse {
        return $this->ubahStatus(
            $request,
            $pesanan,
            'menunggu_konfirmasi',
            'menunggu_pembayaran',
            'Pesanan diterima petani. Menunggu pembayaran pembeli.',
            'Pesanan berhasil diterima. Menunggu pembayaran pembeli.'
        );
    }

    /**
     * Petani menolak pesanan.
     *
     * menunggu_konfirmasi -> ditolak
     */
    public function tolak(
        Request $request,
        int $pesanan
    ): RedirectResponse {
        $data = $request->validate(
            [
                'alasan' => ['nullable', 'string', 'max:255'],
            ],
            $this->pesanValidasi(),
            ['alasan' => 'alasan penolakan']
        );

        $alasan = trim((string) ($data['alasan'] ?? ''));

        return $this->ubahStatus(
            $request,
            $pesanan,
            'menunggu_konfirmasi',
            'ditolak',
            $alasan !== ''
                ? 'Ditolak petani: ' . $alasan
                : 'Pesanan ditolak petani.',
            'Pesanan berhasil ditolak.'
        );
    }

    /**
     * Petani menyiapkan pesanan untuk dikirim atau diambil.
     *
     * diproses -> dikirim / siap_diambil
     */
    public function kirim(
        Request $request,
        int $pesanan
    ): RedirectResponse {
        $user = $this->petani($request);

        $data = Pesanan::find($pesanan);

        $this->pastikanMilik($data, $user);

        // Pesanan dengan metode ambil sendiri menjadi siap_diambil.
        $metodePengiriman = strtolower(
            (string) $data->metode_pengiriman
        );

        $statusTujuan = in_array(
            $metodePengiriman,
            ['ambil sendiri', 'ambil', 'kendaraan sendiri'],
            true
        ) ? 'siap_diambil' : 'dikirim';

        $keterangan = $statusTujuan === 'siap_diambil'
            ? 'Pesanan sudah siap diambil pembeli.'
            : 'Pesanan sudah diserahkan untuk dikirim.';

        $pesanSukses = $statusTujuan === 'siap_diambil'
            ? 'Pesanan ditandai siap diambil.'
            : 'Pesanan ditandai dikirim.';

        return $this->ubahStatus(
            $request,
            $pesanan,
            'diproses',
            $statusTujuan,
            $keterangan,
            $pesanSukses
        );
    }

    public function konfirmasiPembayaran(
        Request $request,
        int $id
    ): RedirectResponse {
        $user = $this->petani($request);

        $berhasil = DB::transaction(function () use ($id, $user) {
            $pesanan = Pesanan::whereKey($id)
                ->lockForUpdate()
                ->first();

            $this->pastikanMilik($pesanan, $user);

            if ($pesanan->status !== 'menunggu_pembayaran') {
                return false;
            }

            $pembayaran = $pesanan->pembayaran()
                ->lockForUpdate()
                ->first();

            if (!$pembayaran || $pembayaran->status !== 'menunggu_verifikasi') {
                return false;
            }

            $pembayaran->update([
                'status' => 'lunas',
            ]);

            $pesanan->update([
                'status' => 'diproses',
            ]);

            $pesanan->riwayat()->create([
                'status' => 'diproses',
                'keterangan' => 'Pembayaran telah dikonfirmasi petani.',
                'diubah_oleh' => $user->id,
                'created_at' => now(),
            ]);

            return true;
        });

        if (!$berhasil) {
            return redirect()->route('petani.pesanan.show', $id)
                ->with('gagal', 'Pembayaran tidak dapat dikonfirmasi. Periksa status pesanan dan bukti pembayaran.');
        }

        return redirect()->route('petani.pesanan.show', $id)
            ->with('sukses', 'Pembayaran berhasil dikonfirmasi. Pesanan siap diproses.');
    }


    public function tolakBukti(
        Request $request,
        int $id
    ): RedirectResponse {
        $user = $this->petani($request);

        $fileLama = DB::transaction(function () use ($id, $user) {
            $pesanan = Pesanan::whereKey($id)
                ->lockForUpdate()
                ->first();

            $this->pastikanMilik($pesanan, $user);

            if ($pesanan->status !== 'menunggu_pembayaran') {
                return null;
            }

            $pembayaran = $pesanan->pembayaran()
                ->lockForUpdate()
                ->first();

            if (!$pembayaran || $pembayaran->status !== 'menunggu_verifikasi') {
                return null;
            }

            $path = $pembayaran->bukti_path;

            $pembayaran->update([
                'bukti_path' => null,
                'status' => 'belum_bayar',
                'dibayar_pada' => null,
            ]);

            return $path;
        });

        if ($fileLama === null) {
            return redirect()->route('petani.pesanan.show', $id)
                ->with('gagal', 'Bukti pembayaran tidak dapat ditolak. Periksa status pembayaran.');
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($fileLama);

        return redirect()->route('petani.pesanan.show', $id)
            ->with('sukses', 'Bukti pembayaran ditolak. Pembeli dapat mengunggah ulang bukti pembayaran.');
    }


    /**
     * Proses perubahan status dan pencatatan riwayat.
     */
    private function ubahStatus(
        Request $request,
        int $id,
        string $dari,
        string $ke,
        string $keterangan,
        string $pesanSukses
    ): RedirectResponse {
        $user = $this->petani($request);

        $statusSaatIni = DB::transaction(function () use (
            $id,
            $user,
            $dari,
            $ke,
            $keterangan
        ) {
            $pesanan = Pesanan::whereKey($id)
                ->lockForUpdate()
                ->first();

            $this->pastikanMilik($pesanan, $user);

            if ($pesanan->status !== $dari) {
                return $pesanan->status;
            }

            $pesanan->update([
                'status' => $ke,
            ]);

            $pesanan->riwayat()->create([
                'status' => $ke,
                'keterangan' => mb_substr($keterangan, 0, 255),
                'diubah_oleh' => $user->id,
                'created_at' => now(),
            ]);

            return null;
        });

        $keDetail = redirect()->route(
            'petani.pesanan.show',
            $id
        );

        if ($statusSaatIni === null) {
            return $keDetail->with('sukses', $pesanSukses);
        }

        if ($statusSaatIni === 'menunggu_pembayaran') {
            return $keDetail->with(
                'gagal',
                'Pesanan belum dibayar pembeli sehingga belum bisa diproses.'
            );
        }

        $label = self::LABEL_STATUS[$statusSaatIni]
            ?? $statusSaatIni;

        return $keDetail->with(
            'gagal',
            "Aksi tidak bisa dilakukan karena status pesanan saat ini \"{$label}\"."
        );
    }

    /**
     * Mengambil pesanan berdasarkan Petani yang sedang login.
     */
    private function queryPesanan(User $user)
    {
        return $user->pesananMasuk()
            ->with([
                'pengusaha.profilPengusaha',
                'item.produk.fotoUtama',
                'riwayat',
                'pembayaran',
            ])
            ->latest()
            ->latest('id');
    }

    /**
     * Mengubah data pesanan ke format yang digunakan Alpine.js.
     */
    private function formatPesanan(Collection $daftar): array
    {
        $metode = [
            'transfer_bank' => 'Transfer Bank',
            'gopay' => 'GoPay',
            'ovo' => 'OVO',
            'cod' => 'COD',
        ];

        $statusBayar = [
            'belum_bayar' => 'belum dibayar',
            'menunggu_verifikasi' => 'menunggu verifikasi',
            'lunas' => 'lunas',
            'gagal' => 'gagal',
        ];

        return $daftar->map(function (Pesanan $o) use (
            $metode,
            $statusBayar
        ) {
            $bayar = $o->pembayaran;

            return [
                'id' => $o->id,
                'nomor' => $o->kode,
                'pembeli' => $o->pengusaha?->profilPengusaha?->nama_usaha
                    ?: ($o->pengusaha?->name ?: 'Pembeli'),

                'tanggal' => $this->waktuWita(
                    $o->created_at,
                    'j M Y'
                ),

                'status' => self::STATUS_KE_TAB[$o->status]
                    ?? $o->status,

                'statusDb' => $o->status,
                'penerima' => $o->nama_penerima,
                'telepon' => $o->no_hp_penerima,
                'alamat' => $o->alamat_pengiriman,
                'catatan' => $o->catatan,

                'metodePengiriman' => $o->metode_pengiriman,

                'pembayaran' => $bayar
                    ? ($metode[$bayar->metode] ?? $bayar->metode)
                        . ' · '
                        . ($statusBayar[$bayar->status] ?? $bayar->status)
                    : null,

                'bukti_path' => $bayar?->bukti_path,
                'status_pembayaran' => $bayar?->status,

                'produk' => $o->item->map(
                    fn (PesananItem $i) => [
                        'nama' => $i->nama_produk,
                        'jumlah' => (float) $i->jumlah_kg,
                        'harga' => (int) $i->harga_per_kg,
                        'subtotal' => (int) $i->subtotal,
                        'gambar' => $i->produk?->fotoUtama?->url,
                    ]
                )->values()->all(),

                'subtotal' => (int) $o->subtotal,
                'ongkir' => (int) $o->ongkir,
                'biayaLayanan' => (int) $o->biaya_layanan,
                'total' => (int) $o->total,

                'riwayat' => $o->riwayat->map(
                    fn (RiwayatStatusPesanan $r) => [
                        'status' => self::STATUS_KE_TAB[$r->status]
                            ?? $r->status,
                        'keterangan' => $r->keterangan,
                        'waktu' => $this->waktuWita(
                            $r->created_at
                        ) . ' WITA',
                    ]
                )->values()->all(),
            ];
        })->values()->all();
    }
}
