<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pesan', function (Blueprint $table) {
            $table->enum('tipe', ['teks', 'penawaran'])->default('teks')->after('isi');
            $table->decimal('harga_penawaran', 12, 2)->nullable()->after('tipe');
            $table->unsignedInteger('jumlah_penawaran')->nullable()->after('harga_penawaran');
            $table->decimal('total_penawaran', 14, 2)->nullable()->after('jumlah_penawaran');
            $table->enum('status_penawaran', ['menunggu', 'disepakati', 'ditolak'])->nullable()->after('total_penawaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesan', function (Blueprint $table) {
            $table->dropColumn([
                'tipe',
                'harga_penawaran',
                'jumlah_penawaran',
                'total_penawaran',
                'status_penawaran',
            ]);
        });
    }
};
