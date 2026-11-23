<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUS_LAMA = ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'gagal'];
    private const STATUS_BARU = ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'gagal', 'perlu_refund', 'dikembalikan'];
    private const METODE_LAMA = ['transfer_bank', 'gopay', 'ovo', 'cod'];

    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('midtrans_order_id', 50)->nullable()->unique()->after('metode');
            $table->string('snap_token')->nullable()->after('midtrans_order_id');
            $table->timestamp('snap_kedaluwarsa')->nullable()->after('snap_token');
            $table->string('transaction_id', 64)->nullable()->after('snap_kedaluwarsa');
            $table->string('status_midtrans', 30)->nullable()->after('transaction_id');
            $table->json('payload')->nullable()->after('status_midtrans');
        });

        $this->ubahMetode(false);
        $this->ubahStatus(self::STATUS_BARU);
    }

    public function down(): void
    {
        DB::table('pembayaran')->whereIn('status', ['perlu_refund', 'dikembalikan'])->update(['status' => 'gagal']);
        $this->ubahStatus(self::STATUS_LAMA);

        DB::table('pembayaran')
            ->where(fn ($q) => $q->whereNull('metode')->orWhereNotIn('metode', self::METODE_LAMA))
            ->update(['metode' => 'transfer_bank']);
        $this->ubahMetode(true);

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropUnique(['midtrans_order_id']);
            $table->dropColumn([
                'midtrans_order_id', 'snap_token', 'snap_kedaluwarsa',
                'transaction_id', 'status_midtrans', 'payload',
            ]);
        });
    }

    /** $kembaliKeEnum=false: jadi string nullable. true: balik ke enum lama. */
    private function ubahMetode(bool $kembaliKeEnum): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            if ($kembaliKeEnum) {
                $daftar = implode(', ', array_map(fn ($v) => "'{$v}'", self::METODE_LAMA));
                DB::statement("ALTER TABLE `pembayaran` MODIFY `metode` ENUM({$daftar}) NOT NULL");
            } else {
                DB::statement('ALTER TABLE `pembayaran` MODIFY `metode` VARCHAR(40) NULL');
            }

            return;
        }

        if ($driver === 'sqlite') { // khusus phpunit
            Schema::table('pembayaran', function (Blueprint $table) use ($kembaliKeEnum) {
                if ($kembaliKeEnum) {
                    $table->enum('metode', self::METODE_LAMA)->default('transfer_bank')->change();
                } else {
                    $table->string('metode', 40)->nullable()->change();
                }
            });
        }
    }

    private function ubahStatus(array $nilai): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $daftar = implode(', ', array_map(fn ($v) => "'{$v}'", $nilai));
            DB::statement("ALTER TABLE `pembayaran` MODIFY `status` ENUM({$daftar}) NOT NULL DEFAULT 'belum_bayar'");

            return;
        }

        if ($driver === 'sqlite') { // khusus phpunit
            Schema::table('pembayaran', function (Blueprint $table) use ($nilai) {
                $table->enum('status', $nilai)->default('belum_bayar')->change();
            });
        }
    }
};