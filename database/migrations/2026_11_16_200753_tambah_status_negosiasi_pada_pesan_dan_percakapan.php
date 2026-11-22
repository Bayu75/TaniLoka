<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pesan', 'status_penawaran')) {
            if ($this->mysql()) { // MODIFY tidak dikenal sqlite
                DB::statement("ALTER TABLE pesan MODIFY status_penawaran
                    ENUM('menunggu', 'ditawar', 'disepakati', 'ditolak') NULL DEFAULT NULL");
            }
        } else {
            Schema::table('pesan', function (Blueprint $table) {
                $table->enum('status_penawaran', ['menunggu', 'ditawar', 'disepakati', 'ditolak'])->nullable();
            });
        }

        if (! Schema::hasColumn('percakapan', 'status')) {
            Schema::table('percakapan', function (Blueprint $table) {
                $table->enum('status', ['terbuka', 'tertutup'])->default('terbuka')->after('petani_id');
            });
        }
    }

    public function down(): void
    {
        if ($this->mysql() && Schema::hasColumn('pesan', 'status_penawaran')) {
            DB::table('pesan')->where('status_penawaran', 'ditawar')->update(['status_penawaran' => 'menunggu']);
            DB::statement("ALTER TABLE pesan MODIFY status_penawaran
                ENUM('menunggu', 'disepakati', 'ditolak') NULL DEFAULT NULL");
        }

        if (Schema::hasColumn('percakapan', 'status')) {
            Schema::table('percakapan', fn (Blueprint $table) => $table->dropColumn('status'));
        }
    }

    private function mysql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }
};