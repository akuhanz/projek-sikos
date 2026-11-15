@extends('layouts.app')

@section('title', 'Pengajuan sewa')

@section('content')
    @php
        $tab = [
            [null,         'Semua',      $hitung->sum()],
            ['menunggu',   'Menunggu',   $hitung['menunggu'] ?? 0],
            ['diterima',   'Diterima',   $hitung['diterima'] ?? 0],
            ['ditolak',    'Ditolak',    $hitung['ditolak'] ?? 0],
            ['dibatalkan', 'Dibatalkan', $hitung['dibatalkan'] ?? 0],
            ['selesai',    'Selesai',    $hitung['selesai'] ?? 0],
        ];

        $warna = [
            'menunggu'   => 'bg-amber-100 text-amber-800',
            'diterima'   => 'bg-emerald-100 text-emerald-800',
            'ditolak'    => 'bg-red-100 text-red-800',
            'dibatalkan' => 'bg-neutral-200 text-neutral-800',
            'selesai'    => 'bg-neutral-200 text-neutral-800',
        ];
    @endphp

    <x-breadcrumb lebar="max-w-4xl" :items="[['Beranda', route('home')], ['Pengajuan sewa']]" />

    <main class="mx-auto max-w-4xl px-4 pb-16 pt-8">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Pengajuan sewa</h1>
        <p class="mt-2 text-neutral-600">
            Kamu bisa punya satu pengajuan aktif dalam satu waktu. Kontak pemilik tampil setelah pengajuan diterima.
        </p>

        @if (session('status'))
            <div class="mt-6 rounded-xl border border-neutral-300 bg-neutral-50 px-4 py-3 text-sm font-medium" role="status">{{ session('status') }}</div>
        @endif

        @if (session('galat'))
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800" role="alert">{{ session('galat') }}</div>
        @endif

        <nav class="mt-6 flex gap-6 overflow-x-auto border-b border-neutral-300 text-sm" aria-label="Filter status">
            @foreach ($tab as [$nilai, $teks, $jumlah])
                <a href="{{ route('pengajuan.index', array_filter(['status' => $nilai])) }}"
                   @if ($filter === $nilai) aria-current="page" @endif
                   class="-mb-px shrink-0 border-b-2 pb-3 font-medium {{ $filter === $nilai ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-600 hover:text-neutral-900' }}">
                    {{ $teks }} <span class="ml-1 text-xs text-neutral-600">{{ $jumlah }}</span>
                </a>
            @endforeach
        </nav>

        @if ($daftar->count())
            <div class="mt-6 space-y-4">
                @foreach ($daftar as $p)
                    @php
                        $k      = $p->kos;
                        $linkWa = $p->linkWa();
                    @endphp

                    <article class="rounded-2xl border border-neutral-200 bg-white p-4 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row">
                            <a href="{{ route('kos.show', $k) }}" class="block h-32 w-full shrink-0 overflow-hidden rounded-xl bg-neutral-100 sm:h-24 sm:w-36">
                                @if ($k->foto_url)
                                    <img src="{{ $k->foto_url }}" alt="{{ $k->nama_kos }}" loading="lazy" class="h-full w-full object-cover">
                                @endif
                            </a>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="truncate text-base font-semibold">
                                        <a href="{{ route('kos.show', $k) }}" class="hover:underline">{{ $k->nama_kos }}</a>
                                    </h2>
                                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $warna[$p->status] }}">
                                        {{ \App\Models\PengajuanSewa::STATUS[$p->status] }}
                                    </span>
                                </div>
                                <p class="mt-1 line-clamp-1 text-[13px] text-neutral-600">{{ $k->alamat }}</p>

                                <dl class="mt-3 grid grid-cols-3 gap-3 text-sm">
                                    <div>
                                        <dt class="text-xs text-neutral-600">Mulai</dt>
                                        <dd class="font-medium">{{ $p->tanggalMasukIndo() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-neutral-600">Lama sewa</dt>
                                        <dd class="font-medium">{{ $p->labelDurasi() }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs text-neutral-600">Penghuni</dt>
                                        <dd class="font-medium">{{ $p->jumlah_penghuni }} orang</dd>
                                    </div>
                                </dl>

                                <p class="mt-3 text-xs text-neutral-600">Diajukan {{ $p->created_at->locale('id')->diffForHumans() }}</p>
                            </div>
                        </div>

                        @if ($p->status === 'menunggu')
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-neutral-200 pt-4">
                                <p class="text-xs text-neutral-600">Selama pengajuan ini menunggu, kamu belum bisa mengajukan ke kos lain.</p>

                                <form action="{{ route('pengajuan.batal', $p) }}" method="POST"
                                      onsubmit="return confirm('Batalkan pengajuan sewa ini?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded-full border border-neutral-300 px-5 py-2 text-[13px] font-semibold hover:border-neutral-900">
                                        Batalkan pengajuan
                                    </button>
                                </form>
                            </div>

                        @elseif ($p->status === 'diterima')
                            <div class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 p-4">
                                <p class="text-sm font-semibold text-emerald-900">Diterima oleh {{ $k->pemilik?->nama_lengkap ?? 'pemilik' }}</p>

                                @if ($p->catatan_pemilik)
                                    <p class="mt-2 whitespace-pre-line break-words text-sm text-emerald-950">{{ $p->catatan_pemilik }}</p>
                                @endif

                                @if ($linkWa)
                                    <div class="mt-3 flex flex-wrap items-center gap-3">
                                        <a href="{{ $linkWa }}" target="_blank" rel="noopener"
                                           class="rounded-full bg-neutral-900 px-6 py-2.5 text-[13px] font-semibold text-white hover:bg-black">
                                            Hubungi via WhatsApp
                                        </a>
                                        <span class="text-sm font-medium text-emerald-950">+{{ $p->nomorPemilik() }}</span>
                                    </div>
                                    <p class="mt-2 text-xs text-emerald-900">Bahas jadwal survei, pembayaran, dan serah terima kamar langsung dengan pemilik.</p>
                                @else
                                    <p class="mt-3 text-[13px] text-emerald-950">Pemilik belum mencantumkan nomor telepon. Coba lagi nanti.</p>
                                @endif

                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-emerald-200 pt-3">
                                    <p class="text-xs text-emerald-900">Selama sewa ini aktif, kamu belum bisa mengajukan ke kos lain.</p>

                                    <form action="{{ route('pengajuan.akhiri', $p) }}" method="POST"
                                          onsubmit="return confirm('Akhiri sewa di kos ini? Setelah itu kamu bisa mengajukan ke kos lain.')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-[13px] font-semibold text-emerald-900 underline underline-offset-2 hover:text-emerald-950">
                                            Saya sudah tidak ngekos di sini
                                        </button>
                                    </form>
                                </div>
                            </div>

                        @elseif ($p->status === 'selesai')
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-neutral-50 p-4">
                                <p class="text-sm text-neutral-800">
                                    Sewa berakhir {{ $p->diakhiri_pada?->locale('id')->diffForHumans() }},
                                    diakhiri oleh {{ $p->diakhiri_oleh === 'pemilik' ? 'pemilik' : 'kamu' }}.
                                </p>
                                <a href="{{ route('kos.show', $k) }}#ajukan" class="text-[13px] font-semibold underline underline-offset-4">Ajukan lagi</a>
                            </div>

                        @elseif ($p->status === 'ditolak')
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-neutral-50 p-4">
                                <p class="text-sm text-neutral-800">
                                    Pemilik menolak pengajuan ini.
                                    @if ($p->catatan_pemilik) Alasan: {{ $p->catatan_pemilik }} @endif
                                </p>
                                <a href="{{ route('kos.show', $k) }}#ajukan" class="text-[13px] font-semibold underline underline-offset-4">Ajukan lagi</a>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $daftar->links('pagination.kos') }}
            </div>
        @else
            <div class="mt-8 rounded-2xl border border-dashed border-neutral-300 px-6 py-16 text-center">
                <p class="text-lg font-semibold">Belum ada pengajuan</p>
                <p class="mt-1 text-sm text-neutral-600">Pilih kos yang kamu suka, lalu klik &ldquo;Ajukan Sewa&rdquo; di halaman detailnya.</p>
                <a href="{{ route('kos.index') }}"
                   class="mt-6 inline-block rounded-full bg-neutral-900 px-6 py-3 text-sm font-semibold text-white hover:bg-black">
                    Cari kos
                </a>
            </div>
        @endif
    </main>
@endsection