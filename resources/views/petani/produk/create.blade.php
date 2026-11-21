@extends('layouts.petani')

@section('content')
<div class="mx-auto max-w-4xl space-y-5 px-4 pb-6">
  <x-page-header judul="Tambah Produk Baru" :kembali="route('petani.produk.index')" lebar="max-w-4xl" />

  <form method="POST" action="{{ route('petani.produk.store') }}" enctype="multipart/form-data"
        class="space-y-5" onsubmit="this.querySelector('[type=submit]').disabled = true">
    @csrf
    @include('petani.produk._form', ['tombol' => 'Simpan Produk'])
  </form>
</div>
@endsection