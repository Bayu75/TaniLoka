<?php

namespace App\Http\Controllers\Petani;

use App\Models\Percakapan;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\RiwayatStatusPesanan;
use App\Models\User;
use App\Services\Petani\AlurPesanan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Pesanan Masuk petani: daftar, detail, dan aksi status.
 *
 * Aksi petani (aturan lengkap di App\Services\Petani\AlurPesanan):
 *   terima    : menunggu_konfirmasi -> menunggu_pembayaran
 *   tolak     : menunggu_konfirmasi -> ditolak (alasan WAJIB)
 *   kirim     : diproses -> dikirim (kurir) / siap_diambil (Kendaraan Sendiri)
 *   terkirim  : dikirim / siap_diambil -> terkirim
 *   hubungi   : buka chat dengan pembeli di halaman Pesan
 * Tidak ada status "menunggu verifikasi pembayaran".
 */
class PesananController extends PetaniController
{
    /** Tab di daftar: id => [nama, status database]. Hash #menunggu, #diproses, #selesai dipakai dashboard. */
    public const TAB = [
        'semua' => ['nama' => 'Semua', 'status' => null],
        'menunggu' => ['nama' => 'Menunggu', 'status' => ['menunggu_konfirmasi', 'menunggu_pembayaran']],
        'diproses' => ['nama' => 'Diproses', 'status' => ['diproses']],
        'dikirim' => ['nama' => 'Dikirim', 'status' => ['dikirim', 'siap_diambil', 'terkirim']],
        'selesai' => ['nama' => 'Selesai', 'status' => ['selesai']],
        'dibatalkan' => ['nama' => 'Dibatalkan', 'status' => ['dibatalkan', 'ditolak']],
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

    private const METODE_BAYAR = ['transfer_bank' => 'Transfer Bank', 'qris' => 'QRIS', 'ewallet' => 'E-Wallet'];

    public function index(Request $request): View
    {
        $user = $this->petani($request);

        // Cek tenggat "malas": pesanan petani ini yang lewat tenggat langsung dibatalkan.
        AlurPesanan::batalkanKedaluwarsa($user->id);

        // ?status= lama (nama tab atau status database) tetap diterima untuk tab awal.
        $status = (string) $request->query('status', '');
        $tabAwal = $this->tabDariQuery($status);

        $daftar = $this->queryPesanan($user)->get();

        $orders = $daftar->map(fn (Pesanan $o) => $this->ringkas($o))->values()->all();
        $jumlah = [];
        foreach (self::TAB as $id => $tab) {
            $jumlah[$id] = $tab['status'] === null
                ? count($orders)
                : count(array_filter($orders, fn ($o) => in_array($o['statusDb'], $tab['status'], true)));
        }

        return view('petani.pesanan', [
            'orders' => $orders,
            'tabs' => collect(self::TAB)->map(fn ($t, $id) => ['id' => $id, 'nama' => $t['nama'], 'jumlah' => $jumlah[$id]])->values()->all(),
            'tabAwal' => $tabAwal,
        ]);
    }

    public function show(Request $request, int $pesanan): View
    {
        $user = $this->petani($request);
        $this->pastikanMilik(Pesanan::query()->find($pesanan), $user);

        AlurPesanan::batalkanKedaluwarsa($user->id);

        $model = Pesanan::query()
            ->with(['pengusaha.profilPengusaha', 'item.produk.fotoUtama', 'riwayat', 'pembayaran', 'ulasan'])
            ->findOrFail($pesanan);

        return view('petani.pesanan-detail', ['o' => $this->detail($model)]);
    }

    public function terima(Request $request, int $pesanan): RedirectResponse
    {
        return $this->aksi($request, $pesanan, 'menunggu_pembayaran', ['menunggu_konfirmasi'],
            'Pesanan diterima penjual', 'Pesanan diterima. Menunggu pembeli membayar.');
    }

    public function tolak(Request $request, int $pesanan): RedirectResponse
    {
        $user = $this->petani($request);
        $this->pastikanMilik(Pesanan::query()->find($pesanan), $user);

        $data = $request->validate(
            ['alasan' => ['required', 'string', 'min:3', 'max:200']],
            $this->pesanValidasi() + ['alasan.required' => 'Alasan penolakan wajib diisi.'],
            ['alasan' => 'alasan penolakan'],
        );

        $alasan = trim(preg_replace('/\s+/u', ' ', $data['alasan']) ?? '');
        if (mb_strlen($alasan) < 3) {
            return back()->withErrors(['alasan' => 'Alasan penolakan wajib diisi.'])->withInput();
        }

        return $this->aksi($request, $pesanan, 'ditolak', ['menunggu_konfirmasi'],
            'Ditolak penjual: '.$alasan, 'Pesanan ditolak.');
    }

    /** "Siap Dikirim" (kurir) atau "Siap Diambil" (Kendaraan Sendiri). Nama route lama petani.pesanan.kirim tetap. */
    public function kirim(Request $request, int $pesanan): RedirectResponse
    {
        $user = $this->petani($request);
        $model = $this->pastikanMilik(Pesanan::query()->find($pesanan), $user);

        $ke = AlurPesanan::statusSiap($model);

        return $ke === 'siap_diambil'
            ? $this->aksi($request, $pesanan, $ke, ['diproses'], 'Pesanan siap diambil pembeli', 'Pesanan ditandai siap diambil.')
            : $this->aksi($request, $pesanan, $ke, ['diproses'], 'Pesanan dikirim penjual', 'Pesanan ditandai dikirim.');
    }

    /** "Tandai Terkirim" (kurir) atau "Tandai Sudah Diambil" (Kendaraan Sendiri). */
    public function terkirim(Request $request, int $pesanan): RedirectResponse
    {
        $user = $this->petani($request);
        $model = $this->pastikanMilik(Pesanan::query()->find($pesanan), $user);

        return $model->status === 'siap_diambil'
            ? $this->aksi($request, $pesanan, 'terkirim', ['siap_diambil'], 'Pesanan sudah diambil pembeli', 'Pesanan ditandai sudah diambil.')
            : $this->aksi($request, $pesanan, 'terkirim', ['dikirim'], 'Pesanan sampai di pembeli', 'Pesanan ditandai terkirim.');
    }

    /** Buka (atau buat) percakapan dengan pembeli pesanan ini, lalu ke halaman Pesan petani. */
    public function hubungi(Request $request, int $pesanan): RedirectResponse
    {
        $user = $this->petani($request);
        $model = $this->pastikanMilik(Pesanan::query()->find($pesanan), $user);

        $percakapan = Percakapan::query()->firstOrCreate(
            ['pengusaha_id' => $model->pengusaha_id, 'petani_id' => $user->id],
            ['pesanan_id' => $model->id],
        );

        if (! $percakapan->pesanan_id) {
            $percakapan->update(['pesanan_id' => $model->id]);
        }

        return redirect()->route('petani.pesan.show', $percakapan->id);
    }

    // ------------------------------------------------------------------

    private function aksi(Request $request, int $id, string $ke, array $dari, string $keterangan, string $pesanSukses): RedirectResponse
    {
        $user = $this->petani($request);
        $this->pastikanMilik(Pesanan::query()->find($id), $user);

        $hasil = AlurPesanan::pindah($id, $ke, $keterangan, $user->id, $dari, $user->id);
        $keDetail = redirect()->route('petani.pesanan.show', $id);

        if ($hasil['ok']) {
            return $keDetail->with('sukses', $pesanSukses);
        }

        if ($hasil['alasan'] === 'kedaluwarsa') {
            return $keDetail->with('gagal', 'Pesanan sudah lewat tenggat sehingga dibatalkan otomatis.');
        }

        if ($hasil['status'] === 'menunggu_pembayaran') {
            return $keDetail->with('gagal', 'Pesanan ini belum dibayar pembeli, jadi belum bisa diproses.');
        }

        $label = self::LABEL_STATUS[$hasil['status']] ?? $hasil['status'];

        return $keDetail->with('gagal', "Aksi tidak bisa dilakukan karena status pesanan saat ini \"{$label}\".");
    }

    private function tabDariQuery(string $status): string
    {
        if ($status === '' || isset(self::TAB[$status])) {
            return $status ?: 'semua';
        }
        $status = ['pembayaran' => 'menunggu_pembayaran', 'ditolak' => 'ditolak'][$status] ?? $status;
        foreach (self::TAB as $id => $tab) {
            if ($tab['status'] && in_array($status, $tab['status'], true)) {
                return $id;
            }
        }

        return 'semua';
    }

    private function tabStatus(string $status): string
    {
        foreach (self::TAB as $id => $tab) {
            if ($tab['status'] && in_array($status, $tab['status'], true)) {
                return $id;
            }
        }

        return 'semua';
    }

    private function queryPesanan(User $user)
    {
        return $user->pesananMasuk()
            ->with(['pengusaha.profilPengusaha', 'item.produk.fotoUtama', 'riwayat'])
            ->latest()
            ->latest('id');
    }

    private function namaPembeli(Pesanan $o): string
    {
        $usaha = trim((string) ($o->pengusaha?->profilPengusaha?->nama_usaha ?? ''));

        return $usaha !== '' ? $usaha : (trim((string) $o->pengusaha?->name) ?: 'Pembeli');
    }

    private function kg($jumlah): string
    {
        return rtrim(rtrim(number_format((float) $jumlah, 2, ',', '.'), '0'), ',').' kg';
    }

    private function judulStatus(Pesanan $o): string
    {
        return self::LABEL_STATUS[$o->status] ?? ucfirst(str_replace('_', ' ', $o->status));
    }

    /** Teks "Perlu tindakan: ..." untuk kartu, null kalau petani tidak perlu berbuat apa-apa. */
    private function perluTindakan(Pesanan $o): ?string
    {
        return match ($o->status) {
            'menunggu_konfirmasi' => 'Terima atau tolak pesanan',
            'diproses' => AlurPesanan::ambilSendiri($o) ? 'Tandai siap diambil' : 'Tandai siap dikirim',
            'dikirim' => 'Tandai terkirim',
            'siap_diambil' => 'Tandai sudah diambil',
            default => null,
        };
    }

    private function alasanBatal(Pesanan $o): ?string
    {
        if (! in_array($o->status, ['dibatalkan', 'ditolak'], true)) {
            return null;
        }
        $entri = $o->riwayat->where('status', $o->status)->sortBy('created_at')->last();

        return $entri?->keterangan ?: ($o->status === 'ditolak' ? 'Ditolak penjual' : 'Pesanan dibatalkan');
    }

    private function chipTenggat(Pesanan $o): ?array
    {
        $label = AlurPesanan::labelTenggat($o->status);
        $batas = AlurPesanan::tenggat($o);
        if (! $label || ! $batas) {
            return null;
        }

        return [
            'label' => $label,
            'sisa' => AlurPesanan::sisaWaktu($batas),
            'batasIso' => $batas->toIso8601String(),
            'batasWita' => $this->waktuWita($batas, 'j M Y, H.i').' WITA',
        ];
    }

    /** Data kartu daftar. */
    private function ringkas(Pesanan $o): array
    {
        $item = $o->item->first();

        return [
            'id' => $o->id,
            'nomor' => $o->kode,
            'pembeli' => $this->namaPembeli($o),
            'tanggal' => $this->waktuWita($o->created_at, 'j M Y'),
            'statusDb' => $o->status,
            'tab' => $this->tabStatus($o->status),
            'label' => $this->judulStatus($o),
            'produk' => $item?->nama_produk ?? '-',
            'jumlah' => $item ? $this->kg($item->jumlah_kg) : '',
            'produkLain' => max(0, $o->item->count() - 1),
            'gambar' => $item?->produk?->fotoUtama?->url,
            'total' => (int) $o->total,
            'tenggat' => $this->chipTenggat($o),
            'tindakan' => $this->perluTindakan($o),
            'alasanBatal' => $this->alasanBatal($o),
            'url' => route('petani.pesanan.show', $o->id),
        ];
    }

    /** Data halaman detail. */
    private function detail(Pesanan $o): array
    {
        $ambilSendiri = AlurPesanan::ambilSendiri($o);
        $bayar = $o->pembayaran;

        $produk = $o->item->map(fn (PesananItem $i) => [
            'nama' => $i->nama_produk,
            'jumlah' => $this->kg($i->jumlah_kg),
            'harga' => (int) $i->harga_per_kg,
            'subtotal' => (int) $i->subtotal,
            'gambar' => $i->produk?->fotoUtama?->url,
        ])->values()->all();

        return $this->ringkas($o) + [
            'dipesan' => $this->waktuWita($o->created_at, 'j M Y'),
            'ambilSendiri' => $ambilSendiri,
            'langkah' => $this->langkah($o, $ambilSendiri),
            'items' => $produk,
            'subtotalProduk' => (int) $o->item->sum('subtotal'),
            'ongkir' => (int) $o->ongkir,
            'biayaLayanan' => (int) $o->biaya_layanan,
            'metodePengiriman' => AlurPesanan::metodePengiriman($o),
            'penerima' => $o->nama_penerima,
            'telepon' => $o->no_hp_penerima,
            'alamat' => $o->alamat_pengiriman,
            'catatan' => $o->catatan,
            'metodeBayar' => $bayar ? (self::METODE_BAYAR[$bayar->metode] ?? $bayar->metode) : null,
            'buktiBayar' => (bool) ($bayar?->bukti_path),
            'ulasan' => $o->ulasan ? ['rating' => (int) $o->ulasan->rating, 'komentar' => $o->ulasan->komentar] : null,
        ];
    }

    /**
     * Perjalanan Pesanan: 6 langkah. Langkah sebelum status saat ini = selesai (centang hijau),
     * langkah status saat ini = aktif (lingkaran kuning), sisanya = belum.
     * Untuk ditolak/dibatalkan: langkah yang sudah dilewati tetap hijau, lalu satu baris merah.
     */
    private function langkah(Pesanan $o, bool $ambilSendiri): array
    {
        $daftar = [
            ['status' => ['menunggu_konfirmasi'], 'nama' => 'Menunggu Konfirmasi', 'bawaan' => 'Pesanan dibuat pembeli'],
            ['status' => ['menunggu_pembayaran'], 'nama' => 'Menunggu Pembayaran', 'bawaan' => 'Pesanan diterima penjual'],
            ['status' => ['diproses'], 'nama' => 'Diproses', 'bawaan' => 'Bukti pembayaran dikirim pembeli'],
            ['status' => ['dikirim', 'siap_diambil'], 'nama' => $ambilSendiri ? 'Siap Diambil' : 'Dikirim', 'bawaan' => $ambilSendiri ? 'Pesanan siap diambil pembeli' : 'Pesanan dikirim penjual'],
            ['status' => ['terkirim'], 'nama' => 'Terkirim', 'bawaan' => $ambilSendiri ? 'Pesanan sudah diambil pembeli' : 'Pesanan sampai di pembeli'],
            ['status' => ['selesai'], 'nama' => 'Selesai', 'bawaan' => 'Pesanan dikonfirmasi pembeli'],
        ];

        $batal = in_array($o->status, ['dibatalkan', 'ditolak'], true);

        // Indeks langkah saat ini.
        $saatIni = null;
        foreach ($daftar as $i => $l) {
            if (in_array($o->status, $l['status'], true)) {
                $saatIni = $i;
            }
        }
        if ($batal) {
            // Langkah terakhir yang tercatat sebelum batal = langkah tempat pesanan berhenti.
            $saatIni = 0;
            foreach ($daftar as $i => $l) {
                if ($o->riwayat->whereIn('status', $l['status'])->isNotEmpty()) {
                    $saatIni = $i;
                }
            }
        }

        $hasil = [];
        foreach ($daftar as $i => $l) {
            $entri = $o->riwayat->whereIn('status', $l['status'])->sortBy('created_at')->last();
            $waktu = $entri?->created_at ?? ($i === 0 ? $o->created_at : null);
            $keadaan = match (true) {
                $o->status === 'selesai' => 'selesai',
                $batal => $i <= $saatIni ? 'selesai' : 'belum',
                $i < $saatIni => 'selesai',
                $i === $saatIni => 'aktif',
                default => 'belum',
            };

            $hasil[] = [
                'nomor' => $i + 1,
                'nama' => $l['nama'],
                'keadaan' => $keadaan,
                'keterangan' => $keadaan === 'belum' ? 'Belum' : $this->baris($waktu, $entri?->keterangan ?: $l['bawaan']),
            ];
        }

        if ($batal) {
            $entri = $o->riwayat->where('status', $o->status)->sortBy('created_at')->last();
            // Langkah berikutnya tidak relevan lagi.
            $hasil = array_values(array_filter($hasil, fn ($l) => $l['keadaan'] === 'selesai'));
            $hasil[] = [
                'nomor' => count($hasil) + 1,
                'nama' => self::LABEL_STATUS[$o->status],
                'keadaan' => 'batal',
                'keterangan' => $this->baris($entri?->created_at ?? $o->updated_at, $this->alasanBatal($o)),
            ];
        }

        return $hasil;
    }

    private function baris($waktu, ?string $keterangan): string
    {
        $jam = $waktu ? $this->waktuWita(Carbon::parse($waktu), 'j M, H.i') : 'Waktu tidak tercatat';

        return $keterangan ? $jam.' · '.$keterangan : $jam;
    }
}
