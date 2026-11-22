<?php

namespace Database\Seeders;

use App\Models\Alamat;
use App\Models\User;
use App\Models\VerifikasiAkun;
use Illuminate\Database\Seeder;

class AkunSeeder extends Seeder
{
    public function run(): void
    {
        $this->buatPengguna([
            'name' => 'Admin TaniLoka',
            'email' => 'admin@taniloka.test',
            'peran' => 'admin',
            'status_verifikasi' => 'terverifikasi',
            'terverifikasi_pada' => now(),
        ]);

        $made = $this->buatPengguna([
            'name' => 'Made Sutama',
            'email' => 'made@taniloka.test',
            'no_wa' => '081200000001',
            'peran' => 'petani',
            'status_verifikasi' => 'terverifikasi',
            'terverifikasi_pada' => now(),
        ]);
        $made->profilPetani()->updateOrCreate([], [
            'nama_usaha_tani' => 'Kebun Sutama',
            'deskripsi' => 'Sayur dataran tinggi segar dari Bedugul.',
            'nama_bank' => 'BRI',
            'no_rekening' => '1234567890',
            'atas_nama_rekening' => 'Made Sutama',
        ]);
        $this->buatAlamat($made, [
            'label' => 'Kebun',
            'nama_penerima' => 'Made Sutama',
            'no_hp' => '081200000001',
            'alamat_lengkap' => 'Desa Bedugul, Baturiti',
            'kabupaten' => 'Tabanan',
            'kecamatan' => 'Baturiti',
            'kode_pos' => '82191',
            'latitude' => -8.2764000,
            'longitude' => 115.1668000,
            'catatan' => 'Dekat Pura Ulun Danu Beratan.',
            'utama' => true,
        ]);

        $ketut = $this->buatPengguna([
            'name' => 'Ketut Arsana',
            'email' => 'ketut@taniloka.test',
            'no_wa' => '081200000002',
            'peran' => 'petani',
            'status_verifikasi' => 'terverifikasi',
            'terverifikasi_pada' => now(),
        ]);
        $ketut->profilPetani()->updateOrCreate([], [
            'nama_usaha_tani' => 'Tani Arsana',
            'deskripsi' => 'Cabai, bawang, dan jeruk Kintamani.',
            'nama_bank' => 'BPD Bali',
            'no_rekening' => '0987654321',
            'atas_nama_rekening' => 'Ketut Arsana',
        ]);
        $this->buatAlamat($ketut, [
            'label' => 'Kebun',
            'nama_penerima' => 'Ketut Arsana',
            'no_hp' => '081200000002',
            'alamat_lengkap' => 'Desa Songan, Kintamani',
            'kabupaten' => 'Bangli',
            'kecamatan' => 'Kintamani',
            'kode_pos' => '80652',
            'latitude' => -8.2428000,
            'longitude' => 115.4056000,
            'catatan' => 'Akses motor, jalan desa berkelok.',
            'utama' => true,
        ]);

        // Profil kosong: untuk tes form verifikasi petani.
        $wayan = $this->buatPengguna([
            'name' => 'Wayan Baru',
            'email' => 'wayan@taniloka.test',
            'no_wa' => '081200000003',
            'peran' => 'petani',
            'status_verifikasi' => 'menunggu',
            'terverifikasi_pada' => null,
        ]);
        $wayan->profilPetani()->updateOrCreate([], []);
        $this->ajukanVerifikasi($wayan, ['ktp', 'foto_lahan']);

        $resto = $this->buatPengguna([
            'name' => 'Putu Restu',
            'email' => 'resto@taniloka.test',
            'no_wa' => '081300000001',
            'peran' => 'pengusaha',
            'status_verifikasi' => 'terverifikasi',
            'terverifikasi_pada' => now(),
        ]);
        $resto->profilPengusaha()->updateOrCreate([], [
            'nama_usaha' => 'Warung Restu',
            'jenis_usaha' => 'restoran',
            'deskripsi' => 'Warung makan khas Bali di Ubud.',
        ]);
        $this->buatAlamat($resto, [
            'label' => 'Warung',
            'nama_penerima' => 'Putu Restu',
            'no_hp' => '081300000001',
            'alamat_lengkap' => 'Jl. Raya Ubud No. 10',
            'kabupaten' => 'Gianyar',
            'kecamatan' => 'Ubud',
            'kode_pos' => '80571',
            'latitude' => -8.5069000,
            'longitude' => 115.2624000,
            'catatan' => 'Masuk gang di sebelah toko oleh-oleh.',
            'utama' => true,
        ]);
        $this->buatAlamat($resto, [
            'label' => 'Gudang',
            'nama_penerima' => 'Putu Restu',
            'no_hp' => '081300000001',
            'alamat_lengkap' => 'Jl. Cok Rai Pudak No. 8, Peliatan',
            'kabupaten' => 'Gianyar',
            'kecamatan' => 'Ubud',
            'kode_pos' => '80571',
            'latitude' => -8.5182000,
            'longitude' => 115.2751000,
            'utama' => false,
        ]);

        // Tanpa alamat: untuk tes pembatasan checkout pengusaha yang datanya belum lengkap.
        $kafe = $this->buatPengguna([
            'name' => 'Nyoman Kafe',
            'email' => 'kafe@taniloka.test',
            'no_wa' => '081300000002',
            'peran' => 'pengusaha',
            'status_verifikasi' => 'menunggu',
            'terverifikasi_pada' => null,
        ]);
        $kafe->profilPengusaha()->updateOrCreate([], [
            'nama_usaha' => 'Kopi Nyoman',
            'jenis_usaha' => 'kafe',
        ]);
        $this->ajukanVerifikasi($kafe, ['nib', 'foto_tempat_usaha']);
    }

    private function buatPengguna(array $data): User
    {
        return User::updateOrCreate(
            ['email' => $data['email']],
            array_merge([
                'password' => 'password',
                'email_verified_at' => now(),
            ], $data),
        );
    }

    private function buatAlamat(User $user, array $data): Alamat
    {
        return Alamat::updateOrCreate(
            ['user_id' => $user->id, 'label' => $data['label']],
            $data,
        );
    }

    private function ajukanVerifikasi(User $user, array $jenisList): void
    {
        $verifikasi = VerifikasiAkun::updateOrCreate(
            ['user_id' => $user->id],
            ['status' => 'menunggu'],
        );

        foreach ($jenisList as $jenis) {
            $verifikasi->bukti()->updateOrCreate(
                ['jenis' => $jenis],
                ['path' => 'verifikasi/contoh.jpg'],
            );
        }
    }
}