
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('percakapan', function (Blueprint $table) {
            $table->index('pengusaha_id');
            $table->dropUnique(['pengusaha_id', 'petani_id']);
        });
    }

    public function down(): void
    {
        Schema::table('percakapan', function (Blueprint $table) {
            $table->dropIndex(['pengusaha_id']);
            $table->unique(['pengusaha_id', 'petani_id']);
        });
    }
};
