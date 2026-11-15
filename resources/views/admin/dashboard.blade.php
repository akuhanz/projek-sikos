@extends('layouts.admin')

@section('title', 'Ringkasan')

@section('content')
    @php
        $nama           = \Illuminate\Support\Str::before(Auth::user()->nama_lengkap, ' ');
        $menunggu       = $hitungKos['menunggu'] ?? 0;
        $tayang         = $hitungKos['terverifikasi'] ?? 0;
        $ditolak        = $hitungKos['ditolak'] ?? 0;
        $penggunaAktif  = ($hitungPengguna['pencari_kos'] ?? 0) + ($hitungPengguna['pemilik_kos'] ?? 0);
        $pengajuanAktif = ($hitungPengajuan['menunggu'] ?? 0) + ($hitungPengajuan['diterima'] ?? 0);

        $warnaPengajuan = [
            'menunggu'   => 'bg-amber-100 text-amber-800',
            'diterima'   => 'bg-emerald-100 text-emerald-800',
            'ditolak'    => 'bg-red-100 text-red-800',
            'dibatalkan' => 'bg-neutral-200 text-neutral-800',
            'selesai'    => 'bg-neutral-200 text-neutral-800',
        ];

        $labelRole = ['pencari_kos' => 'Pencari kos', 'pemilik_kos' => 'Pemilik kos', 'admin' => 'Admin'];
    @endphp

    <div class="mx-auto max-w-5xl">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Halo, {{ $nama }}</h1>
        <p class="mt-1 text-sm text-neutral-600">Ringkasan seluruh data di Sikos.</p>

        {{-- Angka ringkas --}}
        <dl class="mt-6 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-neutral-200 bg-neutral-200 lg:grid-cols-4">
            @foreach ([
                [$menunggu,       'Menunggu verifikasi', route('admin.kos.index', ['status' => 'menunggu'])],
                [$tayang,         'Kos tayang',          route('admin.kos.index', ['status' => 'terverifikasi'])],
                [$penggunaAktif,  'Pengguna',            route('admin.pengguna.index')],
                [$pengajuanAktif, 'Pengajuan aktif',     route('admin.pengajuan.index')],
            ] as [$angka, $label, $url])
                <a href="{{ $url }}" class="flex flex-col-reverse bg-white p-5 hover:bg-neutral-50">
                    <dt class="mt-1 text-sm text-neutral-600">{{ $label }}</dt>
                    <dd class="text-3xl font-bold tracking-tight">{{ $angka }}</dd>
                </a>
            @endforeach
        </dl>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_320px]">

            <div class="space-y-6">
                {{-- Antrean verifikasi --}}
                <section class="rounded-2xl border border-neutral-200 bg-white p-5" aria-labelledby="judul-antrean">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="judul-antrean" class="text-base font-semibold">Antrean verifikasi</h2>
                        @if ($menunggu > 5)
                            <a href="{{ route('admin.kos.index', ['status' => 'menunggu']) }}" class="text-[13px] font-semibold underline underline-offset-4">Lihat semua ({{ $menunggu }})</a>
                        @endif
                    </div>

                    @if ($antrean->count())
                        <ul class="mt-3 divide-y divide-neutral-200">
                            @foreach ($antrean as $k)
                                <li class="flex items-center gap-4 py-3">
                                    <div class="h-14 w-20 shrink-0 overflow-hidden rounded-lg bg-neutral-100">
                                        @if ($k->foto_url)
                                            <img src="{{ $k->foto_url }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold">{{ $k->nama_kos }}</p>
                                        <p class="truncate text-[13px] text-neutral-600">
                                            {{ $k->pemilik?->nama_lengkap ?? '-' }} &middot; diajukan {{ $k->created_at->locale('id')->diffForHumans() }}
                                        </p>
                                    </div>

                                    <a href="{{ route('admin.kos.show', $k) }}"
                                       class="shrink-0 rounded-full bg-neutral-900 px-4 py-2 text-[13px] font-semibold text-white hover:bg-black">Tinjau</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-neutral-600">Tidak ada kos yang menunggu verifikasi.</p>
                    @endif
                </section>

                {{-- Pengajuan terbaru --}}
                <section class="rounded-2xl border border-neutral-200 bg-white p-5" aria-labelledby="judul-pengajuan">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="judul-pengajuan" class="text-base font-semibold">Pengajuan terbaru</h2>
                        <a href="{{ route('admin.pengajuan.index') }}" class="text-[13px] font-semibold underline underline-offset-4">Lihat semua</a>
                    </div>

                    @if ($pengajuanTerbaru->count())
                        <ul class="mt-3 divide-y divide-neutral-200">
                            @foreach ($pengajuanTerbaru as $p)
                                <li class="flex items-center justify-between gap-3 py-3">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium">{{ $p->pencari?->nama_lengkap ?? '-' }}</p>
                                        <p class="truncate text-[13px] text-neutral-600">{{ $p->kos?->nama_kos ?? '-' }} &middot; {{ $p->created_at->locale('id')->diffForHumans() }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $warnaPengajuan[$p->status] ?? 'bg-neutral-200 text-neutral-800' }}">
                                        {{ \App\Models\PengajuanSewa::STATUS[$p->status] ?? $p->status }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-neutral-600">Belum ada pengajuan sewa.</p>
                    @endif
                </section>
            </div>

            <div class="space-y-6">
                {{-- Rincian kos --}}
                <section class="rounded-2xl border border-neutral-200 bg-white p-5" aria-labelledby="judul-rincian">
                    <h2 id="judul-rincian" class="text-base font-semibold">Rincian kos</h2>
                    <dl class="mt-3 space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-neutral-600">Terverifikasi</dt><dd class="font-semibold">{{ $tayang }}</dd></div>
                        <div class="flex justify-between"><dt class="text-neutral-600">Menunggu</dt><dd class="font-semibold">{{ $menunggu }}</dd></div>
                        <div class="flex justify-between"><dt class="text-neutral-600">Ditolak</dt><dd class="font-semibold">{{ $ditolak }}</dd></div>
                        <div class="flex justify-between border-t border-neutral-200 pt-2"><dt class="text-neutral-600">Pemilik kos</dt><dd class="font-semibold">{{ $hitungPengguna['pemilik_kos'] ?? 0 }}</dd></div>
                        <div class="flex justify-between"><dt class="text-neutral-600">Pencari kos</dt><dd class="font-semibold">{{ $hitungPengguna['pencari_kos'] ?? 0 }}</dd></div>
                    </dl>
                </section>

                {{-- Pengguna baru --}}
                <section class="rounded-2xl border border-neutral-200 bg-white p-5" aria-labelledby="judul-baru">
                    <div class="flex items-center justify-between gap-3">
                        <h2 id="judul-baru" class="text-base font-semibold">Pengguna baru</h2>
                        <a href="{{ route('admin.pengguna.index') }}" class="text-[13px] font-semibold underline underline-offset-4">Semua</a>
                    </div>

                    @if ($penggunaBaru->count())
                        <ul class="mt-3 space-y-3">
                            @foreach ($penggunaBaru as $u)
                                <li class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-neutral-900 text-xs font-semibold text-white">
                                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($u->nama_lengkap, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium">{{ $u->nama_lengkap }}</p>
                                        <p class="text-xs text-neutral-600">{{ $labelRole[$u->role] ?? $u->role }} &middot; {{ $u->created_at->locale('id')->diffForHumans() }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-neutral-600">Belum ada pengguna.</p>
                    @endif
                </section>
            </div>
        </div>
    </div>
@endsection