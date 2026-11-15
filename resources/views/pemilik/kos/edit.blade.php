@extends('layouts.pemilik')

@section('title', 'Edit Kos')

@section('content')
    @include('pemilik.kos._form', [
        'kos'    => $kos,
        'action' => route('pemilik.kos.update', $kos),
        'method' => 'PUT',
    ])
@endsection