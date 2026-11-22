<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const METODE_LAMA = ['transfer_bank', 'qris', 'ewallet'];
    private const METODE_BARU = ['transfer_bank', 'gopay', 'ovo', 'cod'];

    public function up(): void
    {
        $this->ubahEnum(self::METODE_BARU, null);
    }

    public function down(): void
    {
        // Lebarkan enum dulu, pindahkan data, baru kembalikan ke enum lama.
        $this->ubahEnum(array_values(array_unique([...self::METODE_LAMA, ...self::METODE_BARU])), 'transfer_bank');

        DB::table('pembayaran')->whereIn('metode', ['gopay', 'ovo'])->update(['metode' => 'ewallet']);
        DB::table('pembayaran')->where('metode', 'cod')->update(['metode' => 'transfer_bank']);

        $this->ubahEnum(self::METODE_LAMA, 'transfer_bank');
    }

    private function ubahEnum(array $nilai, ?string $default): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $daftar = implode(', ', array_map(fn ($v) => "'{$v}'", $nilai));
            $default = $default ? " DEFAULT '{$default}'" : '';
            DB::statement("ALTER TABLE `pembayaran` MODIFY `metode` ENUM({$daftar}) NOT NULL{$default}");
            return;
        }

        if ($driver === 'sqlite') { // khusus phpunit
            Schema::table('pembayaran', function (Blueprint $table) use ($nilai, $default) {
                $kolom = $table->enum('metode', $nilai);
                if ($default) {
                    $kolom->default($default);
                }
                $kolom->change();
            });
        }
    }
};