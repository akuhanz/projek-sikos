@extends('layouts.auth')

@section('title', 'Login')
@section('panel-judul', 'Masuk, lalu temukan kamar yang pas.')
@section('panel-teks', 'Kos favoritmu tersimpan di akunmu, siap dibandingkan kapan saja.')

@section('content')
    @php
        $dasar = 'w-full rounded-xl border bg-white px-4 py-3 text-sm outline-none placeholder:text-neutral-400 focus:ring-1';
        $normal = 'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900';
        $galat  = 'border-red-500 focus:border-red-500 focus:ring-red-500';
    @endphp

    <h1 class="text-3xl font-bold tracking-tight">Selamat datang kembali</h1>
    <p class="mt-2 text-sm text-neutral-600">Login untuk menyimpan kos favorit dan menghubungi pemilik.</p>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-neutral-300 bg-neutral-50 px-4 py-3 text-sm font-medium">
            {{ session('status') }}
        </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="mb-2 block text-sm font-medium">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="nama@email.com" autocomplete="email" required autofocus
                   class="{{ $dasar }} {{ $errors->has('email') ? $galat : $normal }}">
            @error('email')
                <p class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-medium">Password</label>
            <x-input-password id="password" placeholder="Masukkan password" autocomplete="current-password"
                              :galat="$errors->has('email')" />
        </div>

        <label class="flex items-center gap-2 text-sm text-neutral-700">
            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-neutral-300 accent-neutral-900">
            Ingat saya di perangkat ini
        </label>

        <button type="submit"
                class="w-full rounded-full bg-neutral-900 py-3 text-sm font-semibold text-white hover:bg-black focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-900">
            Login
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-neutral-600">
        Belum punya akun?
        <a href="{{ route('register') }}" class="font-semibold text-neutral-900 underline underline-offset-4 hover:text-black">Daftar</a>
    </p>
@endsection