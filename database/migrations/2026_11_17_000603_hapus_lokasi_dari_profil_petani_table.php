<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profil_petani', function (Blueprint $table) {
            $table->dropColumn(['kabupaten', 'kecamatan', 'desa']);
        });
    }

    public function down(): void
    {
        Schema::table('profil_petani', function (Blueprint $table) {
            $table->string('kabupaten')->nullable()->after('nama_usaha_tani');
            $table->string('kecamatan')->nullable()->after('kabupaten');
            $table->string('desa')->nullable()->after('kecamatan');
        });
    }
};