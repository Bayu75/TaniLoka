@extends('layouts.guest')
@section('content')
    <div class="p-10">
        @if (session('sukses')) <p class="mb-4 rounded-xl bg-green-muted p-3 text-primary">{{ session('sukses') }}</p> @endif
        <p>Halo, {{ session('nama') }} ({{ session('peran') }})</p>
        <form method="POST" action="{{ route('keluar') }}">@csrf <button class="mt-4 underline">Keluar</button></form>
    </div>
@endsection