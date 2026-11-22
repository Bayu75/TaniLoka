<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->foreignId('pengusaha_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('petani_id')->constrained('users')->restrictOnDelete();

            $table->string('nama_penerima');
            $table->string('no_hp_penerima', 20);
            $table->text('alamat_pengiriman');
            $table->text('catatan')->nullable();

            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('ongkir')->default(0);
            $table->unsignedInteger('biaya_layanan')->default(0);
            $table->unsignedInteger('total');

            $table->enum('status', [
                'menunggu_pembayaran', 'menunggu_konfirmasi', 'diproses',
                'dikirim', 'selesai', 'dibatalkan', 'ditolak',
            ])->default('menunggu_pembayaran');
            $table->timestamps();

            $table->index(['pengusaha_id', 'status']);
            $table->index(['petani_id', 'status']);
        });

        Schema::create('pesanan_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->cascadeOnDelete();
            $table->foreignId('produk_id')->nullable()->constrained('produk')->nullOnDelete();
            $table->string('nama_produk');
            $table->unsignedInteger('harga_per_kg');
            $table->decimal('jumlah_kg', 8, 2);
            $table->unsignedInteger('subtotal');
            $table->timestamps();
        });

        Schema::create('riwayat_status_pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan')->cascadeOnDelete();
            $table->string('status', 30);
            $table->string('keterangan')->nullable();
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status_pesanan');
        Schema::dropIfExists('pesanan_item');
        Schema::dropIfExists('pesanan');
    }
};