@extends('layouts.pemilik')

@section('title', 'Pengajuan sewa')

@section('content')
    @php
        $inputKelas = 'w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900';

        $tab = [
            ['menunggu', 'Menunggu', $hitung['menunggu'] ?? 0],
            ['diterima', 'Diterima', $hitung['diterima'] ?? 0],
            ['ditolak',  'Ditolak',  $hitung['ditolak'] ?? 0],
            ['selesai',  'Selesai',  $hitung['selesai'] ?? 0],
            ['semua',    'Semua',    $hitung->sum()],
        ];

        $warna = [
            'menunggu'   => 'bg-amber-100 text-amber-800',
            'diterima'   => 'bg-emerald-100 text-emerald-800',
            'ditolak'    => 'bg-red-100 text-red-800',
            'selesai'    => 'bg-neutral-200 text-neutral-800',
            'dibatalkan' => 'bg-neutral-200 text-neutral-800',
        ];

        $adaTelepon = filled(Auth::user()->no_telp);
    @endphp

    <div class="mx-auto max-w-5xl">
        <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Pengajuan sewa</h1>
        <p class="mt-1 text-sm text-neutral-600">Terima atau tolak pengajuan dari pencari kos. Nomor WhatsApp-mu hanya dibagikan ke pencari yang kamu terima.</p>

        @if (! $adaTelepon)
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                Nomor teleponmu belum diisi, jadi kamu belum bisa menerima pengajuan.
                <a href="{{ route('pemilik.dashboard') }}" class="font-semibold underline underline-offset-2">Isi di Ringkasan</a>
            </div>
        @endif

        <nav class="mt-6 flex gap-6 overflow-x-auto border-b border-neutral-300 text-sm" aria-label="Filter status">
            @foreach ($tab as [$nilai, $teks, $jumlah])
                <a href="{{ route('pemilik.pengajuan.index', ['status' => $nilai]) }}"
                   @if ($filter === $nilai) aria-current="page" @endif
                   class="-mb-px shrink-0 border-b-2 pb-3 font-medium {{ $filter === $nilai ? 'border-neutral-900 text-neutral-900' : 'border-transparent text-neutral-600 hover:text-neutral-900' }}">
                    {{ $teks }} <span class="ml-1 text-xs text-neutral-600">{{ $jumlah }}</span>
                </a>
            @endforeach
        </nav>

        @if ($daftar->count())
            <div class="mt-6 space-y-4">
                @foreach ($daftar as $p)
                    @php $nama = $p->pencari?->nama_lengkap ?? 'Pencari'; @endphp

                    <article class="rounded-2xl border border-neutral-200 bg-white p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-neutral-900 text-sm font-semibold text-white">
                                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($nama, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold">{{ $nama }}</p>
                                    <p class="truncate text-[13px] text-neutral-600">mengajukan sewa di {{ $p->kos?->nama_kos }}</p>
                                </div>
                            </div>

                            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $warna[$p->status] }}">
                                {{ \App\Models\PengajuanSewa::STATUS[$p->status] }}
                            </span>
                        </div>

                        <dl class="mt-4 grid grid-cols-3 gap-3 rounded-xl bg-neutral-50 p-4 text-sm">
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

                        <p class="mt-4 text-xs text-neutral-600">Diajukan {{ $p->created_at->locale('id')->diffForHumans() }}</p>

                        @if ($p->status === 'menunggu')
                            <form action="{{ route('pemilik.pengajuan.respons', $p) }}" method="POST"
                                  class="mt-4 space-y-3 border-t border-neutral-200 pt-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label for="catatan-{{ $p->id_pengajuan }}" class="mb-1.5 block text-[13px] font-medium">
                                        Pesan untuk pencari <span class="font-normal text-neutral-600">(opsional)</span>
                                    </label>
                                    <textarea id="catatan-{{ $p->id_pengajuan }}" name="catatan_pemilik" rows="2" maxlength="300"
                                              placeholder="Contoh: Silakan survei hari Sabtu pukul 10.00"
                                              class="{{ $inputKelas }}"></textarea>
                                </div>

                                @if ($adaTelepon)
                                    <p class="text-xs text-neutral-600">Menerima akan membagikan nomor WhatsApp-mu ({{ Auth::user()->no_telp }}) ke pencari ini.</p>
                                @endif

                                <div class="flex flex-wrap gap-2">
                                    <button type="submit" name="aksi" value="terima"
                                            class="rounded-full bg-neutral-900 px-6 py-2.5 text-[13px] font-semibold text-white hover:bg-black">
                                        Terima
                                    </button>
                                    <button type="submit" name="aksi" value="tolak"
                                            onclick="return confirm('Tolak pengajuan ini?')"
                                            class="rounded-full border border-neutral-300 px-6 py-2.5 text-[13px] font-semibold hover:border-neutral-900">
                                        Tolak
                                    </button>
                                </div>
                            </form>

                        @elseif ($p->status === 'diterima')
                            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-neutral-200 pt-4">
                                <div class="min-w-0 text-sm">
                                    <p class="text-neutral-600">
                                        Kamu menerima pengajuan ini {{ $p->direspons_pada?->locale('id')->diffForHumans() }}.
                                    </p>
                                    @if ($p->catatan_pemilik)
                                        <p class="mt-1 whitespace-pre-line break-words">Pesanmu: {{ $p->catatan_pemilik }}</p>
                                    @endif
                                </div>

                                <form action="{{ route('pemilik.pengajuan.akhiri', $p) }}" method="POST"
                                    onsubmit="return confirm('Tandai penghuni ini sudah keluar? Dia akan bisa mengajukan ke kos lain.')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit"
                                            class="rounded-full border border-neutral-300 px-5 py-2.5 text-[13px] font-semibold hover:border-neutral-900">
                                        Tandai penghuni sudah keluar
                                    </button>
                                </form>
                            </div>

                        @elseif ($p->status === 'selesai')
                            <p class="mt-4 border-t border-neutral-200 pt-4 text-sm text-neutral-600">
                                Sewa berakhir {{ $p->diakhiri_pada?->locale('id')->diffForHumans() }},
                                diakhiri oleh {{ $p->diakhiri_oleh === 'pemilik' ? 'kamu' : 'penghuni' }}.
                            </p>

                        @elseif ($p->status === 'dibatalkan')
                            <p class="mt-4 border-t border-neutral-200 pt-4 text-sm text-neutral-600">Pengajuan ini dibatalkan oleh pencari.</p>

                        @else
                            <div class="mt-4 border-t border-neutral-200 pt-4 text-sm">
                                <p class="text-neutral-600">
                                    Kamu {{ $p->status === 'diterima' ? 'menerima' : 'menolak' }} pengajuan ini
                                    {{ $p->direspons_pada?->locale('id')->diffForHumans() }}.
                                </p>
                                @if ($p->catatan_pemilik)
                                    <p class="mt-1 whitespace-pre-line break-words">Pesanmu: {{ $p->catatan_pemilik }}</p>
                                @endif
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $daftar->links('pagination.kos') }}
            </div>
        @else
            <div class="mt-8 rounded-2xl border border-dashed border-neutral-300 bg-white p-10 text-center">
                <p class="font-semibold">Tidak ada pengajuan di sini</p>
                <p class="mt-1 text-sm text-neutral-600">Pengajuan dari pencari akan muncul setelah kosmu tayang di situs.</p>
            </div>
        @endif
    </div>
@endsection