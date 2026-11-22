<?php

namespace App\Console\Commands;

use App\Services\Petani\AlurPesanan;
use Illuminate\Console\Command;

/**
 * Membatalkan otomatis pesanan yang lewat tenggat (milik Gede, feat/petani-backend).
 * Jadwal (routes/console.php, konfirmasi ketua dulu):
 *   Schedule::command('pesanan:batalkan-kedaluwarsa')->everyMinute()->withoutOverlapping();
 * Tanpa jadwal pun halaman Pesanan Masuk petani tetap membatalkan pesanan
 * yang kedaluwarsa saat dibuka.
 */
class BatalkanPesananKedaluwarsa extends Command
{
    protected $signature = 'pesanan:batalkan-kedaluwarsa';

    protected $description = 'Batalkan otomatis pesanan yang lewat tenggat konfirmasi/pembayaran (24 jam) atau tenggat siap kirim (24 jam)';

    public function handle(): int
    {
        $jumlah = AlurPesanan::batalkanKedaluwarsa();
        $this->info("Pesanan dibatalkan otomatis: {$jumlah}");

        return self::SUCCESS;
    }
}
