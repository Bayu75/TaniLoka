<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('keranjang_item', function (Blueprint $table) {
            $table->decimal('harga_per_kg', 12, 2)
                ->nullable()
                ->after('jumlah_kg');
        });
    }

    public function down(): void
    {
        Schema::table('keranjang_item', function (Blueprint $table) {
            $table->dropColumn('harga_per_kg');
        });
    }
};
