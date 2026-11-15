@extends('layouts.admin')

@section('title', 'Pengajuan')

@section('content')
    @php
        $inputKelas = 'rounded-lg border border-neutral-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900';

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

        $bawa = array_filter(['q' => $q, 'pencari' => $idPencari]);
    @endphp

    <div class="mx-auto max-w-5xl">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Pengajuan sewa</h1>
        <p class="mt-1 text-sm text-neutral-600">Pantau semua pengajuan. Pencari dan pemilik yang mengelola statusnya sendiri.</p>

        @if ($idPencari)
            <p class="mt-4 inline-flex items-center gap-2 rounded-full border border-neutral-300 bg-white px-4 py-1.5 text-sm">
                Pencari: <span class="font-semibold">{{ $namaPencari ?? '#' . $idPencari }}</span>
                <a href="{{ route('admin.pengajuan.index', array_filter(['status' => $status])) }}"
                   class="text-neutral-600 hover:text-black" aria-label="Hapus filter pencari">&times;</a>
            </p>
        @endif

        <nav class="mt-6 flex gap-6 overflow-x-auto border-b border-neutral-300 text-sm" aria-label="Filter status">
            @foreach ($tab as [$nilai, $teks, $jumlah])
                <a href="{{ route('admin.pengajuan.index', $bawa + array_filter(['status' => $nilai])) }}"
                   @if ($status === $nilai) aria-current="page" @endif
                   class="-mb-px shrink-0 border-b-2 pb-3 font-medium {{ $status === $nilai ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-600 hover:text-neutral-900' }}">
                    {{ $teks }} <span class="ml-1 text-xs text-neutral-600">{{ $jumlah }}</span>
                </a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('admin.pengajuan.index') }}" class="mt-5 flex flex-wrap gap-2">
            @if ($status)
                <input type="hidden" name="status" value="{{ $status }}">
            @endif
            @if ($idPencari)
                <input type="hidden" name="pencari" value="{{ $idPencari }}">
            @endif

            <label for="q" class="sr-only">Cari pengajuan</label>
            <input id="q" type="text" name="q" value="{{ $q }}" placeholder="Cari nama pencari atau nama kos"
                   class="{{ $inputKelas }} min-w-0 flex-1 sm:max-w-sm">

            <button type="submit" class="rounded-full bg-neutral-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-black">Cari</button>

            @if ($q !== '')
                <a href="{{ route('admin.pengajuan.index', array_filter(['status' => $status, 'pencari' => $idPencari])) }}"
                   class="self-center text-[13px] font-semibold underline underline-offset-4">Hapus filter</a>
            @endif
        </form>

        @if ($daftar->count())
            <div class="mt-6 space-y-3">
                @foreach ($daftar as $p)
                    <article class="rounded-2xl border border-neutral-200 bg-white p-4 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold">{{ $p->pencari?->nama_lengkap ?? 'Akun terhapus' }}</p>
                                <p class="truncate text-[13px] text-neutral-600">
                                    mengajukan sewa di
                                    @if ($p->kos)
                                        <a href="{{ route('admin.kos.show', $p->kos) }}" class="font-medium underline underline-offset-2">{{ $p->kos->nama_kos }}</a>
                                    @else
                                        kos terhapus
                                    @endif
                                    &middot; pemilik {{ $p->kos?->pemilik?->nama_lengkap ?? '-' }}
                                </p>
                            </div>

                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $warna[$p->status] ?? 'bg-neutral-200 text-neutral-800' }}">
                                {{ \App\Models\PengajuanSewa::STATUS[$p->status] ?? $p->status }}
                            </span>
                        </div>

                        <dl class="mt-3 grid grid-cols-3 gap-3 text-sm">
                            <div>
                                <dt class="text-xs text-neutral-600">Rencana masuk</dt>
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

                        <p class="mt-3 text-xs text-neutral-600">
                            Diajukan {{ $p->created_at->locale('id')->diffForHumans() }}
                            @if ($p->direspons_pada)
                                &middot; direspons {{ $p->direspons_pada->locale('id')->diffForHumans() }}
                            @endif
                            @if ($p->diakhiri_pada)
                                &middot; berakhir {{ $p->diakhiri_pada->locale('id')->diffForHumans() }} (oleh {{ $p->diakhiri_oleh }})
                            @endif
                        </p>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $daftar->links('pagination.kos') }}
            </div>
        @else
            <div class="mt-8 rounded-2xl border border-dashed border-neutral-300 bg-white p-10 text-center">
                <p class="font-semibold">Tidak ada pengajuan di sini</p>
                <p class="mt-1 text-sm text-neutral-600">Coba ganti tab status atau hapus filter pencarian.</p>
            </div>
        @endif
    </div>
@endsection