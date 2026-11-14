<?php

namespace Database\Seeders;

use App\Models\{User, Kategori, Produk, FotoProduk, Alamat, KeranjangItem, Pesanan, VerifikasiAkun};
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== AKUN (password semua: "password") =====
        User::create([
            'name' => 'Admin TaniLoka', 'email' => 'admin@taniloka.test',
            'peran' => 'admin', 'password' => 'password',
            'status_verifikasi' => 'terverifikasi', 'terverifikasi_pada' => now(),
        ]);

        $made = User::create([
            'name' => 'Made Sutama', 'email' => 'made@taniloka.test', 'no_wa' => '081200000001',
            'peran' => 'petani', 'password' => 'password',
            'status_verifikasi' => 'terverifikasi', 'terverifikasi_pada' => now(),
        ]);
        $made->profilPetani()->create([
            'nama_usaha_tani' => 'Kebun Sutama', 'kabupaten' => 'Tabanan',
            'kecamatan' => 'Baturiti', 'desa' => 'Bedugul',
            'deskripsi' => 'Sayur dataran tinggi segar dari Bedugul.',
            'nama_bank' => 'BRI', 'no_rekening' => '1234567890', 'atas_nama_rekening' => 'Made Sutama',
        ]);

        $ketut = User::create([
            'name' => 'Ketut Arsana', 'email' => 'ketut@taniloka.test', 'no_wa' => '081200000002',
            'peran' => 'petani', 'password' => 'password',
            'status_verifikasi' => 'terverifikasi', 'terverifikasi_pada' => now(),
        ]);
        $ketut->profilPetani()->create([
            'nama_usaha_tani' => 'Tani Arsana', 'kabupaten' => 'Bangli',
            'kecamatan' => 'Kintamani', 'desa' => 'Songan',
            'deskripsi' => 'Cabai, bawang, dan jeruk Kintamani.',
            'nama_bank' => 'BPD Bali', 'no_rekening' => '0987654321', 'atas_nama_rekening' => 'Ketut Arsana',
        ]);

        // Petani belum terverifikasi, untuk menguji pembatasan
        $wayan = User::create([
            'name' => 'Wayan Baru', 'email' => 'wayan@taniloka.test', 'no_wa' => '081200000003',
            'peran' => 'petani', 'password' => 'password',
            'status_verifikasi' => 'belum_diajukan',
        ]);
        $wayan->profilPetani()->create(['kabupaten' => 'Gianyar']);

        $resto = User::create([
            'name' => 'Putu Restu', 'email' => 'resto@taniloka.test', 'no_wa' => '081300000001',
            'peran' => 'pengusaha', 'password' => 'password',
            'status_verifikasi' => 'terverifikasi', 'terverifikasi_pada' => now(),
        ]);
        $resto->profilPengusaha()->create(['nama_usaha' => 'Warung Restu', 'jenis_usaha' => 'restoran']);
        $alamatResto = Alamat::create([
            'user_id' => $resto->id, 'label' => 'Warung', 'nama_penerima' => 'Putu Restu',
            'no_hp' => '081300000001', 'alamat_lengkap' => 'Jl. Raya Ubud No. 10',
            'kabupaten' => 'Gianyar', 'kecamatan' => 'Ubud', 'kode_pos' => '80571', 'utama' => true,
        ]);

        // Pengusaha belum terverifikasi, untuk menguji pembatasan checkout
        $kafe = User::create([
            'name' => 'Nyoman Kafe', 'email' => 'kafe@taniloka.test', 'no_wa' => '081300000002',
            'peran' => 'pengusaha', 'password' => 'password',
            'status_verifikasi' => 'belum_diajukan',
        ]);
        $kafe->profilPengusaha()->create(['nama_usaha' => 'Kopi Nyoman', 'jenis_usaha' => 'kafe']);

        // ===== KATEGORI =====
        $kategori = collect(['Sayuran', 'Buah', 'Bumbu', 'Umbi', 'Beras & Biji'])
            ->mapWithKeys(fn ($nama) => [$nama => Kategori::create(['nama' => $nama, 'slug' => Str::slug($nama)])]);

        // ===== PRODUK =====
        $daftarProduk = [
            [$made,  'Sayuran', 'Kol Bedugul',        8000,  200, 5,  'A'],
            [$made,  'Sayuran', 'Wortel Bedugul',     12000, 150, 5,  'A'],
            [$made,  'Sayuran', 'Selada Keriting',    18000, 60,  2,  'A'],
            [$made,  'Buah',    'Stroberi Bedugul',   45000, 40,  1,  'A'],
            [$made,  'Umbi',    'Kentang Granola',    14000, 300, 10, 'B'],
            [$ketut, 'Bumbu',   'Cabai Rawit Merah',  55000, 80,  2,  'A'],
            [$ketut, 'Bumbu',   'Bawang Merah',       38000, 120, 5,  'A'],
            [$ketut, 'Bumbu',   'Bawang Putih',       32000, 100, 5,  'B'],
            [$ketut, 'Buah',    'Jeruk Kintamani',    15000, 250, 10, 'A'],
            [$ketut, 'Sayuran', 'Tomat',              10000, 180, 5,  'B'],
        ];

        $produk = [];
        foreach ($daftarProduk as [$petani, $kat, $nama, $harga, $stok, $min, $grade]) {
            $p = Produk::create([
                'petani_id' => $petani->id,
                'kategori_id' => $kategori[$kat]->id,
                'nama' => $nama,
                'slug' => Str::slug($nama) . '-' . Str::lower(Str::random(4)),
                'deskripsi' => "$nama segar langsung dari petani.",
                'harga_per_kg' => $harga,
                'stok_kg' => $stok,
                'min_order_kg' => $min,
                'grade' => $grade,
                'tanggal_panen' => now()->subDays(rand(1, 5)),
            ]);
            FotoProduk::create([
                'produk_id' => $p->id,
                'path' => 'https://placehold.co/600x400?text=' . urlencode($nama),
            ]);
            $produk[$nama] = $p;
        }

        // ===== KERANJANG (untuk tes checkout Ayudia) =====
        KeranjangItem::create(['user_id' => $resto->id, 'produk_id' => $produk['Kol Bedugul']->id, 'jumlah_kg' => 10]);
        KeranjangItem::create(['user_id' => $resto->id, 'produk_id' => $produk['Cabai Rawit Merah']->id, 'jumlah_kg' => 3]);

        // ===== CONTOH PESANAN (untuk tes Pesanan Masuk Gede) =====
        $this->buatPesanan($resto, $made, $alamatResto, [[$produk['Wortel Bedugul'], 10]], 'menunggu_konfirmasi');
        $this->buatPesanan($resto, $ketut, $alamatResto, [[$produk['Bawang Merah'], 5], [$produk['Tomat'], 5]], 'selesai');

        foreach (['wayan@taniloka.test' => ['ktp', 'foto_lahan'], 'kafe@taniloka.test' => ['nib', 'foto_tempat_usaha']] as $email => $jenisList) {
            $u = User::where('email', $email)->first();
            $u->update(['status_verifikasi' => 'menunggu']);
            $v = VerifikasiAkun::create(['user_id' => $u->id, 'status' => 'menunggu']);
            foreach ($jenisList as $jenis) {
                $v->bukti()->create(['jenis' => $jenis, 'path' => 'verifikasi/contoh.jpg']);
            }
        }
    }

    private function buatPesanan(User $pengusaha, User $petani, Alamat $alamat, array $items, string $status): void
    {
        $subtotal = collect($items)->sum(fn ($i) => $i[0]->harga_per_kg * $i[1]);
        $ongkir = 15000;
        $layanan = (int) round($subtotal * 0.02);

        $pesanan = Pesanan::create([
            'kode' => Pesanan::buatKode(),
            'pengusaha_id' => $pengusaha->id,
            'petani_id' => $petani->id,
            'nama_penerima' => $alamat->nama_penerima,
            'no_hp_penerima' => $alamat->no_hp,
            'alamat_pengiriman' => "{$alamat->alamat_lengkap}, {$alamat->kecamatan}, {$alamat->kabupaten}",
            'subtotal' => $subtotal,
            'ongkir' => $ongkir,
            'biaya_layanan' => $layanan,
            'total' => $subtotal + $ongkir + $layanan,
            'status' => $status,
        ]);

        foreach ($items as [$p, $kg]) {
            $pesanan->item()->create([
                'produk_id' => $p->id, 'nama_produk' => $p->nama,
                'harga_per_kg' => $p->harga_per_kg, 'jumlah_kg' => $kg,
                'subtotal' => $p->harga_per_kg * $kg,
            ]);
        }

        $alur = ['menunggu_pembayaran', 'menunggu_konfirmasi', 'diproses', 'dikirim', 'selesai'];
        foreach (array_slice($alur, 0, array_search($status, $alur) + 1) as $s) {
            $pesanan->riwayat()->create(['status' => $s]);
        }

        $pesanan->pembayaran()->create([
            'metode' => 'transfer_bank', 'jumlah' => $pesanan->total,
            'status' => 'lunas', 'dibayar_pada' => now(),
        ]);
    }

    
}