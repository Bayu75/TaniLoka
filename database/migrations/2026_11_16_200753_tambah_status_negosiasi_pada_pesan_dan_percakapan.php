
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Menambahkan status ditawar pada penawaran
        DB::statement("
            ALTER TABLE pesan
            MODIFY status_penawaran
            ENUM('menunggu', 'ditawar', 'disepakati', 'ditolak')
            NULL DEFAULT NULL
        ");

        // Menambahkan status percakapan
        Schema::table('percakapan', function (Blueprint $table) {
            $table->enum('status', ['terbuka', 'tertutup'])
                ->default('terbuka')
                ->after('petani_id');
        });
    }

    public function down(): void
    {
        // Mengembalikan status penawaran agar sesuai enum sebelumnya
        DB::table('pesan')
            ->where('status_penawaran', 'ditawar')
            ->update(['status_penawaran' => 'menunggu']);

        DB::statement("
            ALTER TABLE pesan
            MODIFY status_penawaran
            ENUM('menunggu', 'disepakati', 'ditolak')
            NULL DEFAULT NULL
        ");

        Schema::table('percakapan', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
