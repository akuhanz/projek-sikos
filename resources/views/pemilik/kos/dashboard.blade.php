@extends('layouts.pemilik')

@section('title', 'Ringkasan')

@section('content')
    @php
        $inputKelas = 'rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900';
        $warnaTitik = ['merah' => 'bg-red-500', 'kuning' => 'bg-amber-500', 'biasa' => 'bg-neutral-900'];
        $nama       = \Illuminate\Support\Str::before(Auth::user()->nama_lengkap, ' ');
    @endphp

    <div class="mx-auto max-w-5xl">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Halo, {{ $nama }}</h1>
        <p class="mt-1 text-sm text-neutral-600">Ini kondisi kosmu hari ini.</p>

        {{-- Angka ringkas --}}
        <dl class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-200 lg:grid-cols-4">
            @foreach ([
                [$ringkasan['total'], 'Kos terdaftar'],
                [$ringkasan['tayang'], 'Tayang di situs'],
                [$ringkasan['tersedia'] . ' / ' . $ringkasan['kamar'], 'Kamar tersedia'],
                [$ringkasan['favorit'], 'Kali disimpan pencari'],
            ] as [$angka, $label])
                <div class="flex flex-col-reverse bg-white p-5">
                    <dt class="mt-1 text-sm text-neutral-600">{{ $label }}</dt>
                    <dd class="text-3xl font-bold tracking-tight">{{ $angka }}</dd>
                </div>
            @endforeach
        </dl>

        @if ($ringkasan['total'] === 0)
            {{-- Belum punya kos --}}
            <div class="mt-6 rounded-2xl border border-dashed border-neutral-300 bg-white p-10 text-center">
                <p class="font-semibold">Kamu belum punya kos terdaftar</p>
                <p class="mt-1 text-sm text-neutral-600">Tambahkan kos pertamamu. Kos tampil di situs setelah diverifikasi admin.</p>
                <a href="{{ route('pemilik.kos.create') }}"
                   class="mt-5 inline-block rounded-full bg-neutral-900 px-6 py-3 text-sm font-semibold text-white hover:bg-black">
                    Tambah kos pertama
                </a>
            </div>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">

            {{-- Perlu perhatian --}}
            <section class="rounded-2xl border border-neutral-200 bg-white p-5" aria-labelledby="judul-perhatian">
                <h2 id="judul-perhatian" class="text-base font-semibold">Perlu perhatian</h2>

                @if ($butuhTelepon || count($perhatian))
                    <ul class="mt-2 divide-y divide-neutral-200">
                        @if ($butuhTelepon)
                            <li class="py-3">
                                <div class="flex gap-3">
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-red-500"></span>
                                    <p class="text-sm">Nomor teleponmu belum diisi. Tanpa nomor ini, tombol WhatsApp tidak muncul di halaman kosmu.</p>
                                </div>

                                <form action="{{ route('pemilik.telepon.update') }}" method="POST" class="mt-3 flex flex-wrap items-start gap-2 pl-5">
                                    @csrf
                                    @method('PATCH')
                                    <div>
                                        <label for="no_telp" class="sr-only">Nomor telepon</label>
                                        <input type="tel" id="no_telp" name="no_telp" value="{{ old('no_telp') }}"
                                               placeholder="08xxxxxxxxxx" inputmode="tel" autocomplete="tel" required
                                               class="{{ $inputKelas }} w-52">
                                        @error('no_telp')
                                            <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <button type="submit"
                                            class="rounded-full bg-neutral-900 px-5 py-2 text-[13px] font-semibold text-white hover:bg-black">
                                        Simpan nomor
                                    </button>
                                </form>
                            </li>
                        @endif

                        @foreach ($perhatian as $p)
                            <li class="flex items-start justify-between gap-4 py-3">
                                <div class="flex gap-3">
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $warnaTitik[$p['nada']] }}"></span>
                                    <p class="text-sm">{{ $p['teks'] }}</p>
                                </div>

                                @isset($p['url'])
                                    <a href="{{ $p['url'] }}"
                                       class="shrink-0 text-[13px] font-semibold underline underline-offset-4 hover:text-black">
                                        {{ $p['aksi'] }}
                                    </a>
                                @endisset
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-2 text-sm text-neutral-600">Semua aman. Tidak ada yang perlu kamu tindaklanjuti sekarang.</p>
                @endif
            </section>

            {{-- Ketersediaan kamar --}}
            @if ($ketersediaan->count())
                <section class="rounded-2xl border border-neutral-200 bg-white p-5" aria-labelledby="judul-kamar">
                    <h2 id="judul-kamar" class="text-base font-semibold">Ketersediaan kamar</h2>

                    <ul class="mt-4 space-y-4">
                        @foreach ($ketersediaan as $k)
                            @php
                                $tersedia = (int) ($k->kamar_tersedia ?? 0);
                                $persen   = min(100, round($tersedia / $k->jumlah_kamar * 100));
                            @endphp
                            <li>
                                <div class="flex items-baseline justify-between gap-3">
                                    <p class="truncate text-sm font-medium">{{ $k->nama_kos }}</p>
                                    <p class="shrink-0 text-xs text-neutral-600">{{ $tersedia }} dari {{ $k->jumlah_kamar }}</p>
                                </div>
                                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-neutral-200">
                                    <div class="h-full rounded-full bg-neutral-900" style="width: {{ $persen }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('pemilik.kos.index') }}"
                       class="mt-5 inline-block text-[13px] font-semibold underline underline-offset-4 hover:text-black">
                        Atur ketersediaan
                    </a>
                </section>
            @endif
        </div>
    </div>
@endsection