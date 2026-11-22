<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index biasa dibuat dulu, karena foreign key pengusaha_id butuh index.
        Schema::table('percakapan', function (Blueprint $table) {
            $table->index('pengusaha_id');
            $table->dropUnique(['pengusaha_id', 'petani_id']);
        });
    }

    public function down(): void
    {
        // Unique dibuat dulu, baru index biasa dihapus (kalau terbalik: error 1553).
        Schema::table('percakapan', function (Blueprint $table) {
            $table->unique(['pengusaha_id', 'petani_id']);
            $table->dropIndex(['pengusaha_id']);
        });
    }
};