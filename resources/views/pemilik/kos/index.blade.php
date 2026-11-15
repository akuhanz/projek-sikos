@extends('layouts.pemilik')

@section('title', 'Kos Saya')

@section('content')
    @php
        $inputKelas = 'rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900';

        $tab = [
            [null, 'Semua', $hitung->sum()],
            ['terverifikasi', 'Tayang', $hitung['terverifikasi'] ?? 0],
            ['menunggu', 'Menunggu', $hitung['menunggu'] ?? 0],
            ['ditolak', 'Ditolak', $hitung['ditolak'] ?? 0],
        ];
    @endphp

    <div class="mx-auto max-w-5xl">

        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Kos saya</h1>
                <p class="mt-1 text-sm text-neutral-600">Kelola data dan ketersediaan kamar tiap kos.</p>
            </div>

            <a href="{{ route('pemilik.kos.create') }}"
               class="rounded-full bg-neutral-900 px-6 py-3 text-sm font-semibold text-white hover:bg-black">
                Tambah kos
            </a>
        </div>

        {{-- Filter status --}}
        <nav class="mt-6 flex gap-6 overflow-x-auto border-b border-neutral-300 text-sm" aria-label="Filter status">
            @foreach ($tab as [$nilai, $teks, $jumlah])
                <a href="{{ route('pemilik.kos.index', array_filter(['status' => $nilai])) }}"
                   @if ($filter === $nilai) aria-current="page" @endif
                   class="-mb-px shrink-0 border-b-2 pb-3 font-medium {{ $filter === $nilai ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-500 hover:text-neutral-900' }}">
                    {{ $teks }} <span class="ml-1 text-xs text-neutral-500">{{ $jumlah }}</span>
                </a>
            @endforeach
        </nav>

        @if ($daftarKos->count())
            <div class="mt-6 space-y-3">
                @foreach ($daftarKos as $k)
                    @php
                        $status = [
                            'terverifikasi' => ['Terverifikasi', 'bg-emerald-100 text-emerald-800'],
                            'menunggu'      => ['Menunggu verifikasi', 'bg-amber-100 text-amber-800'],
                            'ditolak'       => ['Ditolak', 'bg-red-100 text-red-800'],
                        ][$k->status_verifikasi] ?? [ucfirst($k->status_verifikasi), 'bg-neutral-200 text-neutral-800'];
                    @endphp

                    <article id="kos-{{ $k->id_kos }}" class="scroll-mt-24 flex flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-4 sm:flex-row sm:items-center">
                        <div class="h-32 w-full shrink-0 overflow-hidden rounded-xl bg-neutral-100 sm:h-24 sm:w-36">
                            @if ($k->foto_url)
                                <img src="{{ $k->foto_url }}" alt="{{ $k->nama_kos }}" loading="lazy" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center text-xs text-neutral-400">Belum ada foto</div>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="truncate text-base font-semibold">{{ $k->nama_kos }}</h2>
                                <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $status[1] }}">{{ $status[0] }}</span>
                            </div>
                            <p class="mt-1 line-clamp-1 text-[13px] text-neutral-600">{{ $k->alamat }}</p>
                            
                            @if ($k->status_verifikasi === 'ditolak' && $k->catatan_verifikasi)
                                <p class="mt-1 text-[13px] font-medium text-red-800">Alasan ditolak: {{ $k->catatan_verifikasi }}</p>
                            @endif
                            <p class="mt-1 text-xs text-neutral-600">
                                Kos {{ ucfirst($k->jenis_kos) }}
                                &middot; {{ $k->harga ? 'Rp ' . number_format($k->harga, 0, ',', '.') . ' per bulan' : 'Harga belum diisi' }}
                                &middot; {{ $k->total_favorit }} pencari menyimpan
                            </p>

                            <form action="{{ route('pemilik.kos.kamar', $k) }}" method="POST" class="mt-3 flex flex-wrap items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <label for="kamar-{{ $k->id_kos }}" class="text-xs text-neutral-600">Kamar tersedia</label>
                                <input type="number" id="kamar-{{ $k->id_kos }}" name="kamar_tersedia"
                                       value="{{ $k->kamar_tersedia }}" min="0"
                                       @if ($k->jumlah_kamar) max="{{ $k->jumlah_kamar }}" @endif
                                       class="{{ $inputKelas }} w-20 py-1.5">
                                @if ($k->jumlah_kamar)
                                    <span class="text-xs text-neutral-600">dari {{ $k->jumlah_kamar }}</span>
                                @endif
                                <button type="submit"
                                        class="rounded-full border border-neutral-300 px-4 py-1.5 text-xs font-semibold hover:border-neutral-900">
                                    Simpan
                                </button>
                            </form>
                        </div>

                        <div class="flex gap-2 sm:flex-col">
                            <a href="{{ route('pemilik.kos.edit', $k) }}"
                               class="flex-1 rounded-full bg-neutral-900 px-5 py-2.5 text-center text-[13px] font-semibold text-white hover:bg-black">
                                Edit
                            </a>
                            <a href="{{ route('kos.show', $k) }}" target="_blank" rel="noopener"
                               class="flex-1 rounded-full border border-neutral-300 px-5 py-2.5 text-center text-[13px] font-semibold hover:border-neutral-900">
                                Lihat di situs
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $daftarKos->links('pagination.kos') }}
            </div>
        @else
            <div class="mt-6 rounded-2xl border border-dashed border-neutral-300 bg-white p-10 text-center">
                @if ($filter)
                    <p class="text-sm text-neutral-600">Tidak ada kos dengan status ini.</p>
                    <a href="{{ route('pemilik.kos.index') }}" class="mt-3 inline-block text-[13px] font-semibold underline underline-offset-4 hover:text-black">Lihat semua kos</a>
                @else
                    <p class="text-sm text-neutral-600">Kamu belum punya kos terdaftar.</p>
                    <a href="{{ route('pemilik.kos.create') }}"
                       class="mt-4 inline-block rounded-full bg-neutral-900 px-6 py-3 text-sm font-semibold text-white hover:bg-black">
                        Tambah kos pertama
                    </a>
                @endif
            </div>
        @endif
    </div>
@endsection