<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->string('slug', 60)->unique();
            $table->string('ikon')->nullable();
            $table->timestamps();
        });

        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petani_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('harga_per_kg');
            $table->decimal('stok_kg', 10, 2)->default(0);
            $table->decimal('min_order_kg', 8, 2)->default(1);
            $table->enum('grade', ['A', 'B', 'C'])->nullable();
            $table->date('tanggal_panen')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kategori_id', 'aktif']);
        });

        Schema::create('foto_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('keranjang_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->decimal('jumlah_kg', 8, 2);
            $table->timestamps();

            $table->unique(['user_id', 'produk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keranjang_item');
        Schema::dropIfExists('foto_produk');
        Schema::dropIfExists('produk');
        Schema::dropIfExists('kategori');
    }
};