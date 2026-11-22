<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_akun', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['menunggu', 'perlu_perbaikan', 'terverifikasi', 'ditolak'])->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditinjau_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('bukti_verifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verifikasi_id')->constrained('verifikasi_akun')->cascadeOnDelete();
            $table->enum('jenis', [
                // petani
                'ktp', 'selfie_ktp', 'foto_lahan', 'surat_kelompok_tani',
                // pengusaha
                'nib', 'foto_tempat_usaha', 'izin_usaha',
                'lainnya',
            ]);
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_verifikasi');
        Schema::dropIfExists('verifikasi_akun');
    }
};