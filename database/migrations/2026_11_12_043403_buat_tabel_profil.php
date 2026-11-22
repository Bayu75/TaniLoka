<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_petani', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nama_usaha_tani')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('desa')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('nama_bank')->nullable();
            $table->string('no_rekening', 30)->nullable();
            $table->string('atas_nama_rekening')->nullable();
            $table->timestamps();
        });

        Schema::create('profil_pengusaha', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nama_usaha')->nullable();
            $table->enum('jenis_usaha', ['restoran', 'kafe', 'hotel', 'katering', 'warung', 'lainnya'])->nullable();
            $table->timestamps();
        });

        Schema::create('alamat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 50);
            $table->string('nama_penerima');
            $table->string('no_hp', 20);
            $table->text('alamat_lengkap');
            $table->string('kabupaten');
            $table->string('kecamatan');
            $table->string('kode_pos', 10)->nullable();
            $table->boolean('utama')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alamat');
        Schema::dropIfExists('profil_pengusaha');
        Schema::dropIfExists('profil_petani');
    }
};