@extends('layouts.app')

@section('title', 'Kos favorit')

@section('content')
    @php
        $jejak = [['Beranda', route('home')]];

        if ($dari === 'kos') {
            $jejak[] = ['Semua kos', route('kos.index')];
        }

        $jejak[] = ['Favorit'];
    @endphp

    <x-breadcrumb :items="$jejak" />


    <main class="mx-auto max-w-7xl px-4 pb-16 pt-10">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Kos favorit</h1>
        <p class="mt-2 text-neutral-600">Kos yang kamu simpan untuk dibandingkan nanti.</p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-neutral-300 bg-neutral-50 px-4 py-3 text-sm font-medium">
                {{ session('status') }}
            </div>
        @endif

        @if ($favorit->count())
            <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($favorit as $k)
                    <div class="relative" data-kartu>
                        <a href="{{ route('kos.show', $k) }}"
                           class="group block overflow-hidden rounded-2xl border border-neutral-200 bg-white transition hover:border-neutral-900 hover:shadow-lg">

                            <div class="relative h-44 overflow-hidden bg-neutral-100">
                                @if ($k->foto_url)
                                    <img src="{{ $k->foto_url }}" alt="{{ $k->nama_kos }}" loading="lazy"
                                         class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @endif
                                <span class="absolute left-3 top-3 rounded-full bg-neutral-900/85 px-3 py-1 text-xs font-medium text-white backdrop-blur">
                                    {{ ucfirst($k->jenis_kos) }}
                                </span>
                            </div>

                            <div class="p-4">
                                <h3 class="truncate text-base font-semibold" title="{{ $k->nama_kos }}">{{ $k->nama_kos }}</h3>
                                <p class="mt-1.5 line-clamp-2 text-sm text-neutral-600">{{ $k->alamat }}</p>

                                @if ($k->harga)
                                    <p class="mt-3 text-base font-bold">
                                        Rp {{ number_format($k->harga, 0, ',', '.') }}<span class="text-sm font-normal text-neutral-600"> /bulan</span>
                                    </p>
                                @else
                                    <p class="mt-3 text-sm text-neutral-600">Harga belum dicantumkan</p>
                                @endif
                            </div>
                        </a>
                        <div class="absolute right-3 top-3"><x-tombol-favorit :kos="$k" hapus-kartu /></div>
                    </div>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $favorit->links('pagination.kos') }}
            </div>
        @else
            <div class="mt-10 rounded-2xl border border-dashed border-neutral-300 px-6 py-16 text-center">
                <p class="text-lg font-semibold">Belum ada kos favorit</p>
                <p class="mt-1 text-sm text-neutral-600">Klik ikon hati pada kos yang kamu suka untuk menyimpannya di sini.</p>
                <a href="{{ route('kos.index') }}"
                   class="mt-6 inline-block rounded-full bg-neutral-900 px-6 py-3 text-sm font-semibold text-white hover:bg-black">
                    Cari kos
                </a>
            </div>
        @endif
    </main>
@endsection