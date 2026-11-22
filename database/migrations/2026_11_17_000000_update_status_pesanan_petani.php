<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Milik Gede (feat/petani-backend), disetujui Bayu 17 Nov 2026.
 *
 * 1. Menambah nilai status pesanan "terkirim" dan "siap_diambil".
 *    Nilai lama TIDAK dihapus, migration 2026_11_12_043406_buat_tabel_pesanan tidak diubah.
 * 2. Menambah kolom metode_pengiriman (nullable) setelah alamat_pengiriman.
 *    Nama kolom masih menunggu konfirmasi Ayudia (checkout). Kalau namanya
 *    berubah, cukup ganti di sini + konstanta KOLOM_METODE di
 *    app/Services/Petani/AlurPesanan.php.
 */
return new class extends Migration
{
    private const STATUS_LAMA = [
        'menunggu_pembayaran', 'menunggu_konfirmasi', 'diproses',
        'dikirim', 'selesai', 'dibatalkan', 'ditolak',
    ];

    private const STATUS_BARU = [
        'menunggu_pembayaran', 'menunggu_konfirmasi', 'diproses',
        'dikirim', 'siap_diambil', 'terkirim', 'selesai', 'dibatalkan', 'ditolak',
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('pesanan', 'metode_pengiriman')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->string('metode_pengiriman', 50)->nullable()->after('alamat_pengiriman');
            });
        }

        $this->ubahEnumStatus(self::STATUS_BARU);
    }

    public function down(): void
    {
        // Pesanan dengan status baru dikembalikan ke status lama terdekat dulu,
        // supaya ALTER TABLE tidak gagal karena nilai enum hilang.
        DB::table('pesanan')->whereIn('status', ['siap_diambil', 'terkirim'])->update(['status' => 'dikirim']);

        $this->ubahEnumStatus(self::STATUS_LAMA);

        if (Schema::hasColumn('pesanan', 'metode_pengiriman')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->dropColumn('metode_pengiriman');
            });
        }
    }

    private function ubahEnumStatus(array $nilai): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            // MySQL tidak bisa menambah nilai enum lewat Schema builder biasa dengan aman,
            // jadi pakai ALTER TABLE ... MODIFY. Default tetap sama seperti migration asli.
            $daftar = implode(', ', array_map(fn ($v) => "'".$v."'", $nilai));
            DB::statement("ALTER TABLE `pesanan` MODIFY `status` ENUM({$daftar}) NOT NULL DEFAULT 'menunggu_pembayaran'");

            return;
        }

        if ($driver === 'sqlite') {
            // Hanya untuk pengujian (phpunit memakai sqlite). Laravel membangun ulang tabel
            // supaya CHECK constraint enum ikut berubah.
            Schema::table('pesanan', function (Blueprint $table) use ($nilai) {
                $table->enum('status', $nilai)->default('menunggu_pembayaran')->change();
            });
        }

        // Driver lain (pgsql, dll.) tidak dipakai tim, sengaja dilewati.
    }
};
