<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pencairan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->unique()->constrained('pesanan')->cascadeOnDelete();
            $table->foreignId('petani_id')->constrained('users')->restrictOnDelete();

            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('ongkir')->default(0);
            $table->decimal('komisi_persen', 5, 2);
            $table->unsignedInteger('komisi');
            $table->unsignedInteger('bagian_petani');

            $table->enum('status', ['tertahan', 'siap_dicairkan', 'sudah_dicairkan', 'dibatalkan'])->default('tertahan');
            $table->timestamp('siap_pada')->nullable();
            $table->timestamp('dicairkan_pada')->nullable();
            $table->foreignId('dicairkan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->string('catatan')->nullable();
            $table->timestamps();

            $table->index(['petani_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pencairan');
    }
};