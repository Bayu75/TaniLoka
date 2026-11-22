<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->decimal('harga_flash_sale', 12, 2)->nullable()->after('harga_per_kg');
            $table->date('flash_sale_sampai')->nullable()->after('harga_flash_sale');
        });
    }

    public function down(): void
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn(['harga_flash_sale', 'flash_sale_sampai']);
        });
    }
};