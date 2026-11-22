<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Kolom penawaran yang dipakai ChatController & PesanController tapi belum pernah dibuat.
// Tiap kolom dicek dulu, jadi aman di database yang kolomnya sudah ditambah manual.
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pesan', 'tipe')) {
            Schema::table('pesan', function (Blueprint $table) {
                $table->enum('tipe', ['teks', 'penawaran'])->default('teks')->after('pengirim_id');
            });
        }

        if (! Schema::hasColumn('pesan', 'produk_id')) {
            Schema::table('pesan', function (Blueprint $table) {
                $table->foreignId('produk_id')->nullable()->after('tipe')
                    ->constrained('produk')->nullOnDelete();
            });
        }

        foreach ([
            'harga_normal'     => fn (Blueprint $t) => $t->decimal('harga_normal', 12, 2)->nullable()->after('produk_id'),
            'harga_penawaran'  => fn (Blueprint $t) => $t->decimal('harga_penawaran', 12, 2)->nullable()->after('harga_normal'),
            'jumlah_penawaran' => fn (Blueprint $t) => $t->unsignedInteger('jumlah_penawaran')->nullable()->after('harga_penawaran'),
            'total_penawaran'  => fn (Blueprint $t) => $t->decimal('total_penawaran', 14, 2)->nullable()->after('jumlah_penawaran'),
        ] as $nama => $buat) {
            if (! Schema::hasColumn('pesan', $nama)) {
                Schema::table('pesan', fn (Blueprint $table) => $buat($table));
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pesan', 'produk_id')) {
            Schema::table('pesan', fn (Blueprint $table) => $table->dropConstrainedForeignId('produk_id'));
        }

        $kolom = array_values(array_filter(
            ['tipe', 'harga_normal', 'harga_penawaran', 'jumlah_penawaran', 'total_penawaran'],
            fn ($k) => Schema::hasColumn('pesan', $k)
        ));

        if ($kolom) {
            Schema::table('pesan', fn (Blueprint $table) => $table->dropColumn($kolom));
        }
    }
};