<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->unique()->constrained('pesanan')->cascadeOnDelete();
            $table->enum('metode', ['transfer_bank', 'qris', 'ewallet'])->default('transfer_bank');
            $table->unsignedInteger('jumlah');
            $table->string('bukti_path')->nullable();
            $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'gagal'])->default('belum_bayar');
            $table->timestamp('dibayar_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('percakapan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengusaha_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('petani_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pesanan_id')->nullable()->constrained('pesanan')->nullOnDelete();
            $table->timestamp('pesan_terakhir_pada')->nullable();
            $table->timestamps();

            $table->unique(['pengusaha_id', 'petani_id']);
        });

        Schema::create('pesan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('percakapan_id')->constrained('percakapan')->cascadeOnDelete();
            $table->foreignId('pengirim_id')->constrained('users')->cascadeOnDelete();
            $table->text('isi');
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('ulasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->unique()->constrained('pesanan')->cascadeOnDelete();
            $table->foreignId('pengusaha_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('petani_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('komentar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ulasan');
        Schema::dropIfExists('pesan');
        Schema::dropIfExists('percakapan');
        Schema::dropIfExists('pembayaran');
    }
};