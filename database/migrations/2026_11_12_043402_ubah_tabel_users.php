<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('peran', ['petani', 'pengusaha', 'admin'])->default('pengusaha')->after('email');
            $table->string('no_wa', 20)->nullable()->unique()->after('peran');
            $table->string('foto')->nullable()->after('no_wa');
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['peran', 'no_wa', 'foto']);
        });
    }
};