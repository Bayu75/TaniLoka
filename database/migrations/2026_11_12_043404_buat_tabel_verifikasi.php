<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_petani', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petani_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['menunggu', 'perlu_perbaikan', 'terverifikasi', 'ditolak'])->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditinjau_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('bukti_verifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verifikasi_id')->constrained('verifikasi_petani')->cascadeOnDelete();
            $table->enum('jenis', ['ktp', 'selfie_ktp', 'foto_lahan', 'surat_kelompok_tani', 'lainnya']);
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_verifikasi');
        Schema::dropIfExists('verifikasi_petani');
    }
};