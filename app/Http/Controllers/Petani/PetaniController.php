<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Dasar semua controller bagian petani:
 * cek peran, cek kepemilikan data, format waktu WITA, dan pesan validasi berbahasa Indonesia.
 */
abstract class PetaniController extends Controller
{
    /** Zona waktu tampilan untuk pengguna di Bali. */
    protected const ZONA_WAKTU = 'Asia/Makassar';

    /**
     * Petani yang sedang login. Lapisan kedua setelah middleware peran:petani,
     * jadi akun non-petani tetap ditolak walau route lupa diberi middleware.
     */
    protected function petani(Request $request): User
    {
        $user = $request->user();
        abort_if(! $user, 401);
        abort_unless($user->isPetani(), 403, 'Halaman ini khusus untuk akun petani.');

        return $user;
    }

    /** Respons standar saat petani belum terverifikasi mencoba mengubah data produk. */
    protected function tolakBelumTerverifikasi(Request $request): JsonResponse|RedirectResponse
    {
        $pesan = 'Akun Anda belum terverifikasi. Produk baru bisa ditambah atau diubah setelah admin menyetujui verifikasi akun.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $pesan], 403);
        }

        return back()->with('gagal', $pesan);
    }

    /** 404 kalau data tidak ada, 403 kalau milik akun lain. */
    protected function pastikanMilik(?object $data, User $user, string $kolom = 'petani_id'): object
    {
        abort_if(! $data, 404);
        abort_unless((int) $data->{$kolom} === (int) $user->id, 403, 'Anda tidak berhak mengakses data ini.');

        return $data;
    }

    /** URL publik untuk path di disk "public" (butuh php artisan storage:link). URL luar dipakai apa adanya. */
    protected function urlFile(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return $this->pathLokal($path) ? asset('storage/'.ltrim($path, '/')) : $path;
    }

    protected function pathLokal(?string $path): bool
    {
        return $path && ! preg_match('#^(https?:)?//#i', $path) && ! str_starts_with($path, 'data:');
    }

    protected function waktuWita($waktu, string $format = 'j M Y, H:i'): string
    {
        if (! $waktu) {
            return '';
        }

        return Carbon::parse($waktu)
            ->timezone(self::ZONA_WAKTU)
            ->locale('id')
            ->translatedFormat($format);
    }

    /** Pesan validasi berbahasa Indonesia, dipakai semua form petani. */
    protected function pesanValidasi(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'string' => ':Attribute harus berupa teks.',
            'integer' => ':Attribute harus berupa bilangan bulat.',
            'numeric' => ':Attribute harus berupa angka.',
            'boolean' => ':Attribute harus bernilai ya atau tidak.',
            'date' => ':Attribute harus berupa tanggal yang valid.',
            'image' => ':Attribute harus berupa gambar.',
            'mimes' => ':Attribute harus berformat :values.',
            'exists' => ':Attribute yang dipilih tidak ditemukan.',
            'in' => ':Attribute yang dipilih tidak valid.',
            'unique' => ':Attribute sudah dipakai akun lain.',
            'regex' => 'Format :attribute tidak valid.',
            'uploaded' => ':Attribute gagal diunggah. Pastikan ukurannya tidak melebihi batas.',
            'max' => [
                'string' => ':Attribute maksimal :max karakter.',
                'numeric' => ':Attribute maksimal :max.',
                'file' => ':Attribute maksimal :max KB.',
                'array' => ':Attribute maksimal :max item.',
            ],
            'min' => [
                'string' => ':Attribute minimal :min karakter.',
                'numeric' => ':Attribute minimal :min.',
                'file' => ':Attribute minimal :min KB.',
                'array' => ':Attribute minimal :min item.',
            ],
            'gt' => [
                'numeric' => ':Attribute harus lebih dari :value.',
                'string' => ':Attribute harus lebih dari :value karakter.',
                'file' => ':Attribute harus lebih dari :value KB.',
                'array' => ':Attribute harus lebih dari :value item.',
            ],
            'lte' => [
                'numeric' => ':Attribute tidak boleh melebihi :value.',
                'string' => ':Attribute tidak boleh melebihi :value karakter.',
                'file' => ':Attribute tidak boleh melebihi :value KB.',
                'array' => ':Attribute tidak boleh melebihi :value item.',
            ],
        ];
    }
}
