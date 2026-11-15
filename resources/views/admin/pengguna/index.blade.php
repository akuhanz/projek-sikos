@extends('layouts.admin')

@section('title', 'Pengguna')

@section('content')
    @php
        $inputKelas = 'rounded-lg border border-neutral-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900';

        $tab = [
            [null,          'Semua',       $hitung->sum()],
            ['pencari_kos', 'Pencari kos', $hitung['pencari_kos'] ?? 0],
            ['pemilik_kos', 'Pemilik kos', $hitung['pemilik_kos'] ?? 0],
            ['admin',       'Admin',       $hitung['admin'] ?? 0],
        ];

        $labelRole = ['pencari_kos' => 'Pencari kos', 'pemilik_kos' => 'Pemilik kos', 'admin' => 'Admin'];
        $warnaRole = [
            'pencari_kos' => 'bg-neutral-200 text-neutral-800',
            'pemilik_kos' => 'bg-sky-100 text-sky-800',
            'admin'       => 'bg-neutral-900 text-white',
        ];
    @endphp

    <div class="mx-auto max-w-5xl">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Pengguna</h1>
        <p class="mt-1 text-sm text-neutral-600">Semua akun yang terdaftar di Sikos.</p>

        <nav class="mt-6 flex gap-6 overflow-x-auto border-b border-neutral-300 text-sm" aria-label="Filter role">
            @foreach ($tab as [$nilai, $teks, $jumlah])
                <a href="{{ route('admin.pengguna.index', array_filter(['role' => $nilai, 'q' => $q])) }}"
                   @if ($role === $nilai) aria-current="page" @endif
                   class="-mb-px shrink-0 border-b-2 pb-3 font-medium {{ $role === $nilai ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-600 hover:text-neutral-900' }}">
                    {{ $teks }} <span class="ml-1 text-xs text-neutral-600">{{ $jumlah }}</span>
                </a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('admin.pengguna.index') }}" class="mt-5 flex flex-wrap gap-2">
            @if ($role)
                <input type="hidden" name="role" value="{{ $role }}">
            @endif

            <label for="q" class="sr-only">Cari pengguna</label>
            <input id="q" type="text" name="q" value="{{ $q }}" placeholder="Cari nama, username, atau email"
                   class="{{ $inputKelas }} min-w-0 flex-1 sm:max-w-sm">

            <button type="submit" class="rounded-full bg-neutral-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-black">Cari</button>

            @if ($q !== '')
                <a href="{{ route('admin.pengguna.index', array_filter(['role' => $role])) }}"
                   class="self-center text-[13px] font-semibold underline underline-offset-4">Hapus filter</a>
            @endif
        </form>

        @if ($daftar->count())
            <div class="mt-6 space-y-3">
                @foreach ($daftar as $u)
                    <article class="flex flex-col gap-4 rounded-2xl border border-neutral-200 bg-white p-4 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 flex-1 items-center gap-4">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-neutral-900 text-sm font-semibold text-white">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($u->nama_lengkap, 0, 1)) }}
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="truncate text-base font-semibold">{{ $u->nama_lengkap }}</h2>
                                    <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $warnaRole[$u->role] ?? 'bg-neutral-200 text-neutral-800' }}">
                                        {{ $labelRole[$u->role] ?? $u->role }}
                                    </span>
                                </div>
                                <p class="truncate text-[13px] text-neutral-600">{{ $u->email }} &middot; &#64;{{ $u->username }}</p>
                                <p class="text-xs text-neutral-600">
                                    {{ $u->no_telp ?: 'Telepon belum diisi' }}
                                    &middot; bergabung {{ $u->created_at->locale('id')->translatedFormat('j M Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                            @if ($u->role === 'pemilik_kos')
                                <a href="{{ route('admin.kos.index', ['pemilik' => $u->id_user]) }}"
                                   class="text-[13px] font-semibold underline underline-offset-4">{{ $u->kos_count }} kos</a>
                            @elseif ($u->role === 'pencari_kos')
                                <a href="{{ route('admin.pengajuan.index', ['pencari' => $u->id_user]) }}"
                                   class="text-[13px] font-semibold underline underline-offset-4">{{ $u->pengajuan_count }} pengajuan</a>
                            @endif

                            @if ($u->role !== 'admin')
                                <form action="{{ route('admin.pengguna.destroy', $u) }}" method="POST"
                                      onsubmit="return confirm('Hapus akun ini beserta semua datanya (kos, pengajuan, favorit)? Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-full border border-red-300 px-4 py-2 text-[13px] font-semibold text-red-700 hover:bg-red-50">
                                        Hapus
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $daftar->links('pagination.kos') }}
            </div>
        @else
            <div class="mt-8 rounded-2xl border border-dashed border-neutral-300 bg-white p-10 text-center">
                <p class="font-semibold">Tidak ada pengguna di sini</p>
                <p class="mt-1 text-sm text-neutral-600">Coba ganti tab role atau hapus filter pencarian.</p>
            </div>
        @endif
    </div>
@endsection