<?php

namespace App\Services\Petani;

use App\Models\Pesanan;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Aturan alur status Pesanan Masuk (milik Gede, feat/petani-backend).
 *
 * Alur:
 *   menunggu_konfirmasi --(petani terima)--> menunggu_pembayaran
 *   menunggu_konfirmasi --(petani tolak + alasan)--> ditolak
 *   menunggu_pembayaran --(pembeli upload bukti bayar, otomatis)--> diproses
 *   diproses --(petani: Siap Dikirim)--> dikirim          (kurir, mis.)
 *   diproses --(petani: Siap Diambil)--> siap_diambil     (Kendaraan Sendiri)
 *   dikirim / siap_diambil --(petani)--> terkirim
 *   terkirim --(pengusaha konfirmasi)--> selesai
 *   menunggu_konfirmasi / menunggu_pembayaran / diproses --(lewat tenggat)--> dibatalkan
 *
 * Tenggat:
 *   1) 24 jam TOTAL sejak pesanan dibuat sampai bukti bayar diunggah
 *      (konfirmasi petani + pembayaran pembeli digabung).
 *   2) Di-reset saat masuk "diproses": 24 jam untuk petani menandai
 *      Dikirim / Siap Diambil.
 *   Setelah dikirim tidak ada tenggat.
 *
 * Tidak ada "menunggu verifikasi pembayaran".
 */
class AlurPesanan
{
    /** Nama kolom metode pengiriman (menunggu konfirmasi Ayudia). Ganti di sini saja kalau berubah. */
    public const KOLOM_METODE = 'metode_pengiriman';

    /** Nilai metode yang berarti pembeli mengambil sendiri, langkah ke-5 jadi "Siap Diambil". */
       /**
     * Nilai metode yang berarti pembeli mengambil sendiri, langkah ke-5 jadi "Siap Diambil".
     * 'ambil' = nilai yang disimpan checkout. 'kendaraan' (diantar petani) SENGAJA tidak masuk.
     */
    public const METODE_AMBIL_SENDIRI = ['ambil', 'ambil sendiri', 'ambil_sendiri', 'Kendaraan Sendiri', 'kendaraan_sendiri'];

    public const JAM_TENGGAT_AWAL = 24;
    public const JAM_TENGGAT_PROSES = 24;

    public const KETERANGAN_BATAL_OTOMATIS = 'Dibatalkan otomatis: lewat tenggat';

    /** Semua perpindahan status yang sah: dari => [ke...]. */
    public const TRANSISI = [
        // 'diproses' langsung dari konfirmasi hanya dipakai pesanan COD (bayar saat barang diterima).
        'menunggu_konfirmasi' => ['menunggu_pembayaran', 'diproses', 'ditolak', 'dibatalkan'],
        'menunggu_pembayaran' => ['diproses', 'dibatalkan'],
        'diproses' => ['dikirim', 'siap_diambil', 'dibatalkan'],
        'dikirim' => ['terkirim'],
        'siap_diambil' => ['terkirim'],
        'terkirim' => ['selesai'],
    ];

    /** Status yang masih berjalan tenggatnya. */
    public const STATUS_BERTENGGAT = ['menunggu_konfirmasi', 'menunggu_pembayaran', 'diproses'];

    // ------------------------------------------------------------------
    // Metode pengiriman
    // ------------------------------------------------------------------

    public static function metodePengiriman(Pesanan $pesanan): ?string
    {
        $nilai = $pesanan->getAttribute(self::KOLOM_METODE);
        $nilai = is_string($nilai) ? trim($nilai) : null;

        return $nilai !== '' ? $nilai : null;
    }

    public static function ambilSendiri(Pesanan $pesanan): bool
    {
        $metode = self::metodePengiriman($pesanan);

        return $metode !== null && in_array(mb_strtolower($metode), array_map('mb_strtolower', self::METODE_AMBIL_SENDIRI), true);
    }

    /** Status tujuan saat petani menekan "Siap Dikirim"/"Siap Diambil". */
    public static function statusSiap(Pesanan $pesanan): string
    {
        return self::ambilSendiri($pesanan) ? 'siap_diambil' : 'dikirim';
    }

    // ------------------------------------------------------------------
    // Tenggat
    // ------------------------------------------------------------------

    /** Waktu masuk ke status tertentu menurut riwayat (entri terakhir), null kalau tidak ada. */
    public static function waktuMasuk(Pesanan $pesanan, string $status): ?Carbon
    {
        $riwayat = $pesanan->relationLoaded('riwayat') ? $pesanan->riwayat : $pesanan->riwayat()->get();
        $entri = $riwayat->where('status', $status)->sortBy('created_at')->last();

        return $entri?->created_at ? Carbon::parse($entri->created_at) : null;
    }

    /** Batas waktu pesanan saat ini, atau null kalau status tidak bertenggat. */
    public static function tenggat(Pesanan $pesanan): ?Carbon
    {
        if (in_array($pesanan->status, ['menunggu_konfirmasi', 'menunggu_pembayaran'], true)) {
            $mulai = self::waktuMasuk($pesanan, 'menunggu_konfirmasi') ?? $pesanan->created_at;
            // Kalau riwayat menunggu_konfirmasi tercatat lebih lambat dari pembuatan, tetap hitung dari pembuatan.
            if ($pesanan->created_at && $mulai && $pesanan->created_at->lt($mulai)) {
                $mulai = $pesanan->created_at;
            }

            return $mulai ? Carbon::parse($mulai)->addHours(self::JAM_TENGGAT_AWAL) : null;
        }

        if ($pesanan->status === 'diproses') {
            $mulai = self::waktuMasuk($pesanan, 'diproses') ?? $pesanan->created_at;

            return $mulai ? Carbon::parse($mulai)->addHours(self::JAM_TENGGAT_PROSES) : null;
        }

        return null;
    }

    public static function kedaluwarsa(Pesanan $pesanan, ?Carbon $sekarang = null): bool
    {
        $batas = self::tenggat($pesanan);

        return $batas !== null && ($sekarang ?? now())->gte($batas);
    }

    /** Label chip tenggat per status. */
    public static function labelTenggat(string $status): ?string
    {
        return match ($status) {
            'menunggu_konfirmasi' => 'Batas konfirmasi',
            'menunggu_pembayaran' => 'Batas pembayaran',
            'diproses' => 'Tenggat siap kirim',
            default => null,
        };
    }

    /** "20 jam 55 menit" dari sekarang ke batas. */
    public static function sisaWaktu(?Carbon $batas, ?Carbon $sekarang = null): ?string
    {
        if (! $batas) {
            return null;
        }

        $detik = $batas->getTimestamp() - ($sekarang ?? now())->getTimestamp();
        if ($detik <= 0) {
            return 'Lewat tenggat';
        }

        $menit = intdiv($detik + 59, 60); // dibulatkan ke atas supaya tidak tampil "0 menit" sebelum habis
        $jam = intdiv($menit, 60);
        $menit %= 60;

        return $jam > 0 ? "{$jam} jam {$menit} menit" : "{$menit} menit";
    }

    // ------------------------------------------------------------------
    // Perubahan status
    // ------------------------------------------------------------------

    public static function bolehPindah(string $dari, string $ke): bool
    {
        return in_array($ke, self::TRANSISI[$dari] ?? [], true);
    }

    /**
     * Pindahkan status dalam transaksi + kunci baris, lalu catat riwayat.
     *
     * @param  string[]|null  $dariYangDiizinkan  batasi status asal (mis. aksi petani tertentu)
     * @return array{ok: bool, status: string, alasan: ?string}
     *         alasan: null | 'transisi' | 'kedaluwarsa' | 'milik'
     */
    public static function pindah(int $pesananId, string $ke, ?string $keterangan, ?int $olehId, ?array $dariYangDiizinkan = null, ?int $petaniId = null): array
    {
        return DB::transaction(function () use ($pesananId, $ke, $keterangan, $olehId, $dariYangDiizinkan, $petaniId) {
            $pesanan = Pesanan::query()->whereKey($pesananId)->lockForUpdate()->firstOrFail();

            if ($petaniId !== null && (int) $pesanan->petani_id !== $petaniId) {
                return ['ok' => false, 'status' => $pesanan->status, 'alasan' => 'milik'];
            }

            $dari = $pesanan->status;

            // Tenggat dicek dulu: aksi setelah lewat tenggat tidak boleh "menyelamatkan" pesanan.
            if ($ke !== 'dibatalkan' && self::kedaluwarsa($pesanan)) {
                self::catat($pesanan, 'dibatalkan', self::KETERANGAN_BATAL_OTOMATIS, null);

                return ['ok' => false, 'status' => 'dibatalkan', 'alasan' => 'kedaluwarsa'];
            }

            if (($dariYangDiizinkan !== null && ! in_array($dari, $dariYangDiizinkan, true)) || ! self::bolehPindah($dari, $ke)) {
                return ['ok' => false, 'status' => $dari, 'alasan' => 'transisi'];
            }

            self::catat($pesanan, $ke, $keterangan, $olehId);

            return ['ok' => true, 'status' => $ke, 'alasan' => null];
        });
    }

    private static function catat(Pesanan $pesanan, string $ke, ?string $keterangan, ?int $olehId): void
    {
        $pesanan->update(['status' => $ke]);
        $pesanan->riwayat()->create([
            'status' => $ke,
            'keterangan' => $keterangan !== null ? mb_substr($keterangan, 0, 255) : null,
            'diubah_oleh' => $olehId,
            // Diisi dari Laravel supaya zona/jam konsisten (model memakai $timestamps = false).
            'created_at' => now(),
        ]);
        $pesanan->unsetRelation('riwayat');
    }

    /**
     * UNTUK AYUDIA (checkout/pembayaran pengusaha):
     * panggil setelah bukti transfer tersimpan di tabel pembayaran.
     * Status langsung menunggu_pembayaran -> diproses (tanpa verifikasi),
     * dan tenggat petani di-reset 24 jam dari sekarang.
     *
     *   $hasil = \App\Services\Petani\AlurPesanan::buktiBayarDiunggah($pesanan->id, $request->user()->id);
     *   if (! $hasil['ok']) { ... $hasil['alasan'] === 'kedaluwarsa' berarti sudah dibatalkan otomatis ... }
     */
    public static function buktiBayarDiunggah(int $pesananId, ?int $olehId = null): array
    {
        return self::pindah($pesananId, 'diproses', 'Bukti pembayaran dikirim pembeli', $olehId, ['menunggu_pembayaran']);
    }

    /** UNTUK SISI PENGUSAHA: konfirmasi pesanan diterima, terkirim -> selesai. */
    public static function konfirmasiSelesai(int $pesananId, ?int $olehId = null): array
    {
        return self::pindah($pesananId, 'selesai', 'Pesanan dikonfirmasi pembeli', $olehId, ['terkirim']);
    }

    /**
     * Batalkan otomatis pesanan yang lewat tenggat.
     * Dipanggil saat halaman Pesanan Masuk dibuka (cek "malas", per petani)
     * dan oleh perintah artisan pesanan:batalkan-kedaluwarsa (semua petani).
     *
     * @return int jumlah pesanan yang dibatalkan
     */
    public static function batalkanKedaluwarsa(?int $petaniId = null): int
    {
        $sekarang = now();
        $jumlah = 0;

        Pesanan::query()
            ->with('riwayat')
            ->whereIn('status', self::STATUS_BERTENGGAT)
            ->when($petaniId !== null, fn ($q) => $q->where('petani_id', $petaniId))
            // Kandidat kasar: dibuat > 24 jam lalu (tenggat apa pun paling cepat 24 jam sejak dibuat).
            ->where('created_at', '<=', $sekarang->copy()->subHours(min(self::JAM_TENGGAT_AWAL, self::JAM_TENGGAT_PROSES)))
            ->orderBy('id')
            ->chunkById(200, function (Collection $daftar) use (&$jumlah, $sekarang) {
                foreach ($daftar as $pesanan) {
                    if (! self::kedaluwarsa($pesanan, $sekarang)) {
                        continue;
                    }

                    $hasil = DB::transaction(function () use ($pesanan) {
                        $terkunci = Pesanan::query()->with('riwayat')->whereKey($pesanan->id)->lockForUpdate()->first();
                        if (! $terkunci || ! in_array($terkunci->status, self::STATUS_BERTENGGAT, true) || ! self::kedaluwarsa($terkunci)) {
                            return false;
                        }
                        self::catat($terkunci, 'dibatalkan', self::KETERANGAN_BATAL_OTOMATIS, null);

                        return true;
                    });

                    $jumlah += $hasil ? 1 : 0;
                }
            });

        return $jumlah;
    }
}
