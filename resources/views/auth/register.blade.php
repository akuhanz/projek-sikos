@extends('layouts.auth')

@section('title', 'Daftar')
@section('panel-judul', 'Satu akun, sesuai peranmu.')
@section('panel-teks', 'Pencari menyimpan kos dan menghubungi pemilik. Pemilik memasang kos dan mengelola kamarnya.')

@section('content')
    @php
        $dasar  = 'w-full rounded-xl border bg-white px-4 py-3 text-sm outline-none placeholder:text-neutral-400 focus:ring-1';
        $normal = 'border-neutral-300 focus:border-neutral-900 focus:ring-neutral-900';
        $galat  = 'border-red-500 focus:border-red-500 focus:ring-red-500';
        $kolom  = fn (string $f) => $dasar . ' ' . ($errors->has($f) ? $galat : $normal);

        $kartuRole = 'group/rol flex cursor-pointer items-start gap-3 rounded-xl border border-neutral-300 p-4 transition has-[:checked]:border-neutral-900 has-[:checked]:bg-neutral-50 has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-neutral-900 has-[:focus-visible]:ring-offset-2';
        $roleDipilih = old('role', 'pencari_kos');
    @endphp

    <h1 class="text-3xl font-bold tracking-tight">Buat akun Sikos</h1>
    <p class="mt-2 text-sm text-neutral-600">Pilih peranmu, lalu isi data di bawah.</p>

    <form action="{{ route('register.post') }}" method="POST" class="mt-8 space-y-5">
        @csrf

        {{-- Peran --}}
        <fieldset>
            <legend class="mb-2 text-sm font-medium">Daftar sebagai</legend>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                    'pencari_kos' => ['Pencari kos', 'Cari, simpan, dan hubungi pemilik.'],
                    'pemilik_kos' => ['Pemilik kos', 'Pasang kos dan kelola kamarmu.'],
                ] as $nilai => [$judul, $ket])
                    <label class="{{ $kartuRole }}">
                        <input type="radio" name="role" value="{{ $nilai }}" class="sr-only" {{ $roleDipilih === $nilai ? 'checked' : '' }}>
                        <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-neutral-400 group-has-[:checked]/rol:border-neutral-900 group-has-[:checked]/rol:bg-neutral-900">
                            <span class="h-1.5 w-1.5 rounded-full bg-white opacity-0 group-has-[:checked]/rol:opacity-100"></span>
                        </span>
                        <span>
                            <span class="block text-sm font-semibold">{{ $judul }}</span>
                            <span class="mt-0.5 block text-xs text-neutral-600">{{ $ket }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('role')
                <p class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </fieldset>

        <div>
            <label for="nama_lengkap" class="mb-2 block text-sm font-medium">Nama lengkap</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}"
                   placeholder="Nama sesuai identitas" autocomplete="name" required
                   class="{{ $kolom('nama_lengkap') }}">
            @error('nama_lengkap')
                <p class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="username" class="mb-2 block text-sm font-medium">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                       placeholder="namakamu" autocomplete="username" required
                       class="{{ $kolom('username') }}">
                @error('username')
                    <p class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="no_telp" class="mb-2 block text-sm font-medium">No. telepon</label>
                <input type="tel" id="no_telp" name="no_telp" value="{{ old('no_telp') }}"
                       placeholder="08xxxxxxxxxx" inputmode="tel" autocomplete="tel"
                       class="{{ $kolom('no_telp') }}">
                @error('no_telp')
                    <p class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <p class="-mt-2 text-xs text-neutral-600">Pemilik kos: nomor ini dipakai pencari untuk menghubungi lewat WhatsApp.</p>

        <div>
            <label for="email" class="mb-2 block text-sm font-medium">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   placeholder="nama@email.com" autocomplete="email" required
                   class="{{ $kolom('email') }}">
            @error('email')
                <p class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-2 block text-sm font-medium">Password</label>
            <x-input-password id="password" placeholder="Minimal 8 karakter" autocomplete="new-password"
                              :galat="$errors->has('password')" />
            @error('password')
                <p class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-2 block text-sm font-medium">Ulangi password</label>
            <x-input-password id="password_confirmation" placeholder="Ketik ulang password" autocomplete="new-password" />
        </div>

        <button type="submit"
                class="w-full rounded-full bg-neutral-900 py-3 text-sm font-semibold text-white hover:bg-black focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-neutral-900">
            Daftar
        </button>
    </form>

    <p class="mt-8 text-center text-sm text-neutral-600">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="font-semibold text-neutral-900 underline underline-offset-4 hover:text-black">Login</a>
    </p>
@endsection