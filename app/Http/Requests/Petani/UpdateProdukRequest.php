<?php

namespace App\Http\Requests\Petani;

class UpdateProdukRequest extends StoreProdukRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'tersedia_sampai' => ['nullable', 'date'], // saat ubah boleh kosong atau tanggal lampau
            'aktif'           => ['nullable', 'boolean'],
        ]);
    }
}