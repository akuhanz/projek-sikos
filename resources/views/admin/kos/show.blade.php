@extends('layouts.admin')

@section('title', $kos->nama_kos)

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    @php
        $warna = [
            'terverifikasi' => ['Terverifikasi', 'bg-emerald-100 text-emerald-800'],
            'menunggu'      => ['Menunggu verifikasi', 'bg-amber-100 text-amber-800'],
            'ditolak'       => ['Ditolak', 'bg-red-100 text-red-800'],
        ];
        $s = $warna[$kos->status_verifikasi] ?? [ucfirst($kos->status_verifikasi), 'bg-neutral-200 text-neutral-800'];

        $inputKelas = 'w-full rounded-lg border border-neutral-300 bg-white px-3 py-2 text-sm outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900';

        $fotoLuar  = $kos->galeri->where('kategori', 'luar');
        $fotoDalam = $kos->galeri->where('kategori', 'dalam');

        $peraturan = collect(preg_split('/\R/u', (string) $kos->peraturan) ?: [])
            ->map(fn ($b) => trim($b))->filter()->values();

        $spesifikasi = collect([
            ['Tipe', 'Kos ' . ucfirst($kos->jenis_kos)],
            ['Harga', $kos->harga ? 'Rp ' . number_format($kos->harga, 0, ',', '.') . ' per bulan' : null],
            ['Ukuran kamar', $kos->ukuran_kamar],
            ['Jumlah kamar', $kos->jumlah_kamar],
            ['Kamar kosong', $kos->kamar_tersedia],
            ['Listrik', \App\Models\Kos::LISTRIK[$kos->listrik] ?? null],
        ]);

        $kelompokFasilitas = [
            'Fasilitas kamar'       => $kos->fasilitasDi(\App\Models\Kos::FASILITAS_KAMAR),
            'Fasilitas kamar mandi' => $kos->fasilitasDi(\App\Models\Kos::FASILITAS_KAMAR_MANDI),
            'Fasilitas umum'        => $kos->fasilitasDi(\App\Models\Kos::FASILITAS_UMUM),
        ];
    @endphp

    <div class="mx-auto max-w-6xl">
        <a href="{{ route('admin.kos.index', ['status' => $kos->status_verifikasi]) }}"
           class="text-[13px] font-semibold underline underline-offset-4">&larr; Kembali ke daftar kos</a>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $kos->nama_kos }}</h1>
            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $s[1] }}">{{ $s[0] }}</span>
        </div>
        <p class="mt-1 text-sm text-neutral-600">{{ $kos->alamat }}</p>

        <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_340px]">

            {{-- DATA KOS --}}
            <div class="min-w-0 space-y-6">

                {{-- Foto --}}
                <section class="rounded-2xl border border-neutral-200 bg-white p-5" aria-labelledby="judul-foto">
                    <h2 id="judul-foto" class="text-base font-semibold">Foto</h2>

                    @if ($kos->foto_url || $fotoLuar->count() || $fotoDalam->count())
                        @if ($kos->foto_url)
                            <p class="mt-3 text-xs font-medium text-neutral-600">Foto utama</p>
                            <a href="{{ $kos->foto_url }}" target="_blank" rel="noopener" class="mt-1 block overflow-hidden rounded-xl bg-neutral-100">
                                <img src="{{ $kos->foto_url }}" alt="Foto utama {{ $kos->nama_kos }}" class="aspect-[16/9] w-full object-cover">
                            </a>
                        @endif

                        @if ($fotoLuar->count())
                            <p class="mt-4 text-xs font-medium text-neutral-600">Foto luar tambahan</p>
                            <div class="mt-1 grid grid-cols-3 gap-3">
                                @foreach ($fotoLuar as $f)
                                    <a href="{{ $f->url }}" target="_blank" rel="noopener" class="overflow-hidden rounded-lg bg-neutral-100">
                                        <img src="{{ $f->url }}" alt="" loading="lazy" class="aspect-[4/3] w-full object-cover">
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if ($fotoDalam->count())
                            <p class="mt-4 text-xs font-medium text-neutral-600">Foto detail dalam kos</p>
                            <div class="mt-1 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                @foreach ($fotoDalam as $f)
                                    <a href="{{ $f->url }}" target="_blank" rel="noopener" class="block">
                                        <span class="block overflow-hidden rounded-lg bg-neutral-100">
                                            <img src="{{ $f->url }}" alt="{{ $f->keterangan }}" loading="lazy" class="aspect-[4/3] w-full object-cover">
                                        </span>
                                        <span class="mt-1 block truncate text-xs text-neutral-600">{{ $f->keterangan ?: 'Tanpa keterangan' }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <p class="mt-3 text-sm text-neutral-600">Belum ada foto.</p>
                    @endif
                </section>

                {{-- Spesifikasi --}}
                <section class="rounded-2xl border border-neutral-200 bg-white p-5" aria-labelledby="judul-spek">
                    <h2 id="judul-spek" class="text-base font-semibold">Spesifikasi</h2>
                    <dl class="mt-3 grid grid-cols-2 gap-4 sm:grid-cols-3">
                        @foreach ($spesifikasi as [$label, $isi])
                            <div>
                                <dt class="text-xs text-neutral-600">{{ $label }}</dt>
                                <dd class="mt-0.5 text-sm font-medium">{{ filled($isi) ? $isi : '-' }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    @if ($kos->deskripsi)
                        <p class="mt-4 text-xs font-medium text-neutral-600">Deskripsi</p>
                        <p class="mt-1 whitespace-pre-line break-words text-sm leading-6">{{ $kos->deskripsi }}</p>
                    @endif
                </section>

                {{-- Fasilitas + peraturan --}}
                <section class="rounded-2xl border border-neutral-200 bg-white p-5" aria-labelledby="judul-fasilitas">
                    <h2 id="judul-fasilitas" class="text-base font-semibold">Fasilitas dan peraturan</h2>

                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        @foreach ($kelompokFasilitas as $judul => $daftar)
                            <div>
                                <p class="text-xs font-medium text-neutral-600">{{ $judul }}</p>
                                @if (count($daftar))
                                    <ul class="mt-1 space-y-1 text-sm">
                                        @foreach ($daftar as $teks)
                                            <li>&bull; {{ $teks }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="mt-1 text-sm text-neutral-600">-</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-5 text-xs font-medium text-neutral-600">Peraturan</p>
                    @if ($peraturan->count())
                        <ol class="mt-1 list-inside list-decimal space-y-1 text-sm">
                            @foreach ($peraturan as $aturan)
                                <li>{{ $aturan }}</li>
                            @endforeach
                        </ol>
                    @else
                        <p class="mt-1 text-sm text-neutral-600">Belum dicantumkan.</p>
                    @endif
                </section>

                {{-- Lokasi --}}
                <section class="rounded-2xl border border-neutral-200 bg-white p-5" aria-labelledby="judul-lokasi">
                    <h2 id="judul-lokasi" class="text-base font-semibold">Lokasi</h2>
                    <p class="mt-1 text-sm text-neutral-600">{{ $kos->alamat }}</p>

                    <div class="isolate mt-3 overflow-hidden rounded-xl ring-1 ring-neutral-300">
                        <div id="map-admin" class="h-64 w-full"></div>
                    </div>
                </section>
            </div>

            {{-- PANEL VERIFIKASI --}}
            <aside class="space-y-4 lg:sticky lg:top-6 lg:self-start" aria-label="Verifikasi">

                <section class="rounded-2xl border border-neutral-200 bg-white p-5">
                    <h2 class="text-base font-semibold">Verifikasi</h2>

                    <p class="mt-2 text-sm text-neutral-700">
                        Status: <span class="font-semibold">{{ $s[0] }}</span>
                        @if ($kos->diverifikasi_pada)
                            <span class="block text-xs text-neutral-600">Terakhir diputuskan {{ $kos->diverifikasi_pada->locale('id')->diffForHumans() }}</span>
                        @endif
                    </p>

                    @if ($kos->status_verifikasi === 'ditolak' && $kos->catatan_verifikasi)
                        <p class="mt-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-[13px] text-red-900">
                            <span class="font-semibold">Alasan penolakan:</span> {{ $kos->catatan_verifikasi }}
                        </p>
                    @endif

                    <form action="{{ route('admin.kos.verifikasi', $kos) }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="catatan_verifikasi" class="mb-1.5 block text-[13px] font-medium">
                                Alasan penolakan <span class="font-normal text-neutral-600">(wajib kalau menolak)</span>
                            </label>
                            <textarea id="catatan_verifikasi" name="catatan_verifikasi" rows="3" maxlength="500"
                                      placeholder="Contoh: Foto kamar buram, mohon unggah ulang"
                                      class="{{ $inputKelas }}">{{ old('catatan_verifikasi') }}</textarea>
                            @error('catatan_verifikasi')
                                <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <button type="submit" name="aksi" value="setujui" @disabled($kos->status_verifikasi === 'terverifikasi')
                                    class="rounded-full bg-neutral-900 px-6 py-2.5 text-[13px] font-semibold text-white hover:bg-black disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-neutral-900">
                                Setujui
                            </button>
                            <button type="submit" name="aksi" value="tolak"
                                    class="rounded-full border border-neutral-300 px-6 py-2.5 text-[13px] font-semibold hover:border-neutral-900">
                                {{ $kos->status_verifikasi === 'terverifikasi' ? 'Cabut dari situs' : 'Tolak' }}
                            </button>
                        </div>
                    </form>
                </section>

                <section class="rounded-2xl border border-neutral-200 bg-white p-5">
                    <h2 class="text-base font-semibold">Pemilik</h2>

                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-neutral-900 text-sm font-semibold text-white">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($kos->pemilik?->nama_lengkap ?? 'P', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">{{ $kos->pemilik?->nama_lengkap ?? '-' }}</p>
                            <p class="truncate text-xs text-neutral-600">{{ $kos->pemilik?->email }}</p>
                            <p class="text-xs text-neutral-600">{{ $kos->pemilik?->no_telp ?: 'Nomor telepon belum diisi' }}</p>
                        </div>
                    </div>

                    <dl class="mt-4 space-y-2 border-t border-neutral-200 pt-4 text-sm">
                        <div class="flex justify-between"><dt class="text-neutral-600">Pengajuan aktif</dt><dd class="font-semibold">{{ $pengajuanAktif }}</dd></div>
                        <div class="flex justify-between"><dt class="text-neutral-600">Disimpan sebagai favorit</dt><dd class="font-semibold">{{ $totalFavorit }}</dd></div>
                    </dl>

                    @if ($kos->pemilik)
                        <a href="{{ route('admin.kos.index', ['pemilik' => $kos->pemilik->id_user]) }}"
                           class="mt-4 inline-block text-[13px] font-semibold underline underline-offset-4">Semua kos pemilik ini</a>
                    @endif
                </section>

                <section class="rounded-2xl border border-neutral-200 bg-white p-5">
                    <a href="{{ route('kos.show', $kos) }}" target="_blank" rel="noopener"
                       class="block rounded-full border border-neutral-300 px-5 py-2.5 text-center text-[13px] font-semibold hover:border-neutral-900">
                        Lihat di situs
                    </a>

                    <form action="{{ route('admin.kos.destroy', $kos) }}" method="POST" class="mt-3"
                          onsubmit="return confirm('Hapus kos ini permanen? Foto, pengajuan, dan favorit terkait ikut terhapus. Tindakan ini tidak bisa dibatalkan.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="w-full rounded-full border border-red-300 px-5 py-2.5 text-[13px] font-semibold text-red-700 hover:bg-red-50">
                            Hapus kos
                        </button>
                    </form>

                    @if ($pengajuanAktif > 0)
                        <p class="mt-2 text-xs text-neutral-600">Ada {{ $pengajuanAktif }} pengajuan aktif yang ikut terhapus kalau kos ini dihapus.</p>
                    @endif
                </section>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const lat = @json((float) $kos->latitude);
    const lng = @json((float) $kos->longitude);

    const map = L.map('map-admin', { scrollWheelZoom: false }).setView([lat, lng], 16);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    L.marker([lat, lng]).addTo(map);

    map.on('click', () => map.scrollWheelZoom.enable());
    map.on('mouseout', () => map.scrollWheelZoom.disable());
</script>
@endpush