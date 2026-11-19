<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE pesanan
            MODIFY status ENUM(
                'menunggu_pembayaran',
                'menunggu_konfirmasi',
                'diproses',
                'dikirim',
                'siap_diambil',
                'terkirim',
                'selesai',
                'dibatalkan',
                'ditolak'
            ) NOT NULL DEFAULT 'menunggu_pembayaran'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE pesanan
            MODIFY status ENUM(
                'menunggu_pembayaran',
                'menunggu_konfirmasi',
                'diproses',
                'dikirim',
                'selesai',
                'dibatalkan',
                'ditolak'
            ) NOT NULL DEFAULT 'menunggu_pembayaran'
        ");
    }
};
