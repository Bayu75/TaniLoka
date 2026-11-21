@extends('layouts.petani')

@section('content')
<div class="mx-auto max-w-4xl space-y-5 px-4 pb-6">
  <x-page-header judul="Ubah Produk" :kembali="route('petani.produk.index')" lebar="max-w-4xl" />

  <form method="POST" action="{{ route('petani.produk.update', $produk) }}" enctype="multipart/form-data"
        class="space-y-5" onsubmit="this.querySelector('[type=submit]').disabled = true">
    @csrf
    @method('PUT')
    @include('petani.produk._form', ['tombol' => 'Simpan Perubahan'])
  </form>
</div>
@endsection