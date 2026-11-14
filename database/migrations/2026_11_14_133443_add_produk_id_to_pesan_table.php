
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesan', function (Blueprint $table) {
            $table->foreignId('produk_id')
                ->nullable()
                ->after('percakapan_id')
                ->constrained('produk')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pesan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('produk_id');
        });
    }
};
