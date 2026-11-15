@extends('layouts.pemilik')

@section('title', 'Tambah Kos')

@section('content')
    @include('pemilik.kos._form', [
        'kos'    => null,
        'action' => route('pemilik.kos.store'),
        'method' => 'POST',
    ])
@endsection