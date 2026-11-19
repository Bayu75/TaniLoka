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
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        // Pertahankan seluruh nilai agar data pembayaran lama
        // tidak hilang ketika migration di-rollback.
        DB::statement("
            ALTER TABLE pembayaran
            MODIFY metode ENUM(
                'transfer_bank',
                'gopay',
                'ovo',
                'cod'
            ) NOT NULL
        ");
    }
};
