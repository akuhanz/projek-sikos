@extends('layouts.admin')

@section('title', 'Kos')

@section('content')
    @php
        $inputKelas = 'rounded-lg border border-neutral-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900';

        $tab = [
            ['menunggu',      'Menunggu',      $hitung['menunggu'] ?? 0],
            ['terverifikasi', 'Terverifikasi', $hitung['terverifikasi'] ?? 0],
            ['ditolak',       'Ditolak',       $hitung['ditolak'] ?? 0],
            ['semua',         'Semua',         $hitung->sum()],
        ];

        $warna = [
            'terverifikasi' => ['Terverifikasi', 'bg-emerald-100 text-emerald-800'],
            'menunggu'      => ['Menunggu verifikasi', 'bg-amber-100 text-amber-800'],
            'ditolak'       => ['Ditolak', 'bg-red-100 text-red-800'],
        ];

        // Parameter yang ikut terbawa saat pindah tab
        $bawa = array_filter(['q' => $q, 'jenis' => $jenis, 'pemilik' => $idPemilik]);
    @endphp

    <div class="mx-auto max-w-5xl">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Kos</h1>
        <p class="mt-1 text-sm text-neutral-600">Periksa kos yang masuk, lalu setujui atau tolak. Kos tampil di situs setelah disetujui.</p>

        @if ($idPemilik)
            <p class="mt-4 inline-flex items-center gap-2 rounded-full border border-neutral-300 bg-white px-4 py-1.5 text-sm">
                Pemilik: <span class="font-semibold">{{ $namaPemilik ?? '#' . $idPemilik }}</span>
                <a href="{{ route('admin.kos.index', array_filter(['status' => $status])) }}"
                   class="text-neutral-600 hover:text-black" aria-label="Hapus filter pemilik">&times;</a>
            </p>
        @endif

        <nav class="mt-6 flex gap-6 overflow-x-auto border-b border-neutral-300 text-sm" aria-label="Filter status">
            @foreach ($tab as [$nilai, $teks, $jumlah])
                <a href="{{ route('admin.kos.index', $bawa + ['status' => $nilai]) }}"
                   @if ($status === $nilai) aria-current="page" @endif
                   class="-mb-px shrink-0 border-b-2 pb-3 font-medium {{ $status === $nilai ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-600 hover:text-neutral-900' }}">
                    {{ $teks }} <span class="ml-1 text-xs text-neutral-600">{{ $jumlah }}</span>
                </a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('admin.kos.index') }}" class="mt-5 flex flex-wrap gap-2">
            <input type="hidden" name="status" value="{{ $status }}">
            @if ($idPemilik)
                <input type="hidden" name="pemilik" value="{{ $idPemilik }}">
            @endif

            <label for="q" class="sr-only">Cari kos</label>
            <input id="q" type="text" name="q" value="{{ $q }}" placeholder="Cari nama kos, alamat, atau pemilik"
                   class="{{ $inputKelas }} min-w-0 flex-1 sm:max-w-sm">

            <label for="jenis" class="sr-only">Tipe kos</label>
            <select id="jenis" name="jenis" class="{{ $inputKelas }}">
                <option value="">Semua tipe</option>
                @foreach (['putra' => 'Putra', 'putri' => 'Putri', 'campur' => 'Campur'] as $v => $t)
                    <option value="{{ $v }}" {{ $jenis === $v ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>

            <button type="submit" class="rounded-full bg-neutral-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-black">Cari</button>

            @if ($q !== '' || $jenis)
                <a href="{{ route('admin.kos.index', array_filter(['status' => $status, 'pemilik' => $idPemilik])) }}"
                   class="self-center text-[13px] font-semibold underline underline-offset-4">Hapus filter</a>
            @endif
        </form>

        @if ($daftar->count())
            <div class="mt-6 space-y-3">
                @foreach ($daftar as $k)
                    @php $s = $warna[$k->status_verifikasi] ?? [ucfirst($k->status_verifikasi), 'bg-neutral-200 text-neutral-800']; @endphp

                    <article class="flex flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-4 sm:flex-row sm:items-center">
                        <div class="h-32 w-full shrink-0 overflow-hidden rounded-xl bg-neutral-100 sm:h-24 sm:w-36">
                            @if ($k->foto_url)
                                <img src="{{ $k->foto_url }}" alt="{{ $k->nama_kos }}" loading="lazy" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center text-xs text-neutral-500">Belum ada foto</div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="truncate text-base font-semibold">{{ $k->nama_kos }}</h2>
                                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $s[1] }}">{{ $s[0] }}</span>
                            </div>
                            <p class="mt-1 line-clamp-1 text-[13px] text-neutral-600">{{ $k->alamat }}</p>
                            <p class="mt-1 text-xs text-neutral-600">
                                Pemilik: {{ $k->pemilik?->nama_lengkap ?? '-' }}
                                &middot; Kos {{ ucfirst($k->jenis_kos) }}
                                &middot; {{ $k->harga ? 'Rp ' . number_format($k->harga, 0, ',', '.') : 'Harga belum diisi' }}
                            </p>
                            <p class="mt-1 text-xs text-neutral-600">Diajukan {{ $k->created_at->locale('id')->diffForHumans() }}</p>
                        </div>

                        <a href="{{ route('admin.kos.show', $k) }}"
                           class="rounded-full {{ $k->status_verifikasi === 'menunggu' ? 'bg-neutral-900 text-white hover:bg-black' : 'border border-neutral-300 hover:border-neutral-900' }} px-6 py-2.5 text-center text-[13px] font-semibold">
                            {{ $k->status_verifikasi === 'menunggu' ? 'Tinjau' : 'Lihat' }}
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $daftar->links('pagination.kos') }}
            </div>
        @else
            <div class="mt-8 rounded-2xl border border-dashed border-neutral-300 bg-white p-10 text-center">
                <p class="font-semibold">Tidak ada kos di sini</p>
                <p class="mt-1 text-sm text-neutral-600">Coba ganti tab status atau hapus filter pencarian.</p>
            </div>
        @endif
    </div>
@endsection