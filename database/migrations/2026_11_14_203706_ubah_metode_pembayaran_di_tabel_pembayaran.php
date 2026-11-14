<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE pembayaran
            MODIFY metode ENUM(
                'transfer_bank',
                'gopay',
                'ovo',
                'cod'
            ) NOT NULL DEFAULT 'transfer_bank'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE pembayaran
            MODIFY metode ENUM(
                'transfer_bank',
                'qris',
                'ewallet'
            ) NOT NULL DEFAULT 'transfer_bank'
        ");
    }
};
