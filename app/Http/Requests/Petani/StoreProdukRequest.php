<?php

namespace App\Http\Requests\Petani;

use Illuminate\Foundation\Http\FormRequest;

class StoreProdukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses petani sudah dijaga middleware di rute
    }

    public function rules(): array
    {
        return [
            'nama'            => ['required', 'string', 'max:100'],
            'kategori_id'     => ['required', 'exists:kategori,id'],
            'grade'           => ['nullable', 'in:A,B,C'],
            'harga_per_kg'    => ['required', 'integer', 'min:1'],
            'stok_kg'         => ['required', 'numeric', 'min:0.01', 'max:99999999'],
            'min_order_kg'    => ['required', 'numeric', 'min:0.01', 'lte:stok_kg'],
            'deskripsi'       => ['nullable', 'string', 'max:1000'],
            'tanggal_panen'   => ['nullable', 'date'],
            'tersedia_sampai' => ['required', 'date', 'after_or_equal:today'],
            'foto'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'required'                       => ':Attribute wajib diisi.',
            'min_order_kg.lte'               => 'Minimal pesan tidak boleh melebihi stok.',
            'tersedia_sampai.after_or_equal' => 'Tanggal tersedia tidak boleh sebelum hari ini.',
            'foto.image'                     => 'Berkas harus berupa gambar.',
            'foto.mimes'                     => 'Pilih foto JPG, PNG, WebP, atau GIF.',
            'foto.max'                       => 'Ukuran foto maksimal 5 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama'            => 'nama produk',
            'kategori_id'     => 'kategori',
            'harga_per_kg'    => 'harga per kg',
            'stok_kg'         => 'stok',
            'min_order_kg'    => 'minimal pesan',
            'deskripsi'       => 'deskripsi',
            'tanggal_panen'   => 'tanggal panen',
            'tersedia_sampai' => 'tanggal tersedia',
            'foto'            => 'foto',
        ];
    }
}