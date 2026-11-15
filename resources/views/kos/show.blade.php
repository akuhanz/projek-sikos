@extends('layouts.app')

@section('title', $kos->nama_kos)
@section('navbar-z', 'z-[1200]')
@section('navbar-kelas', 'border-neutral-200 bg-white/90 backdrop-blur')

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@section('content')
    @php
        $terverifikasi = $kos->status_verifikasi === 'terverifikasi';
        $tersedia = $kos->kamar_tersedia === null ? null : (int) $kos->kamar_tersedia;

        $spesifikasi = collect([
            ['Jenis kos', 'Kos ' . ucfirst($kos->jenis_kos)],
            ['Ukuran kamar', $kos->ukuran_kamar],
            ['Jumlah kamar', $kos->jumlah_kamar ? $kos->jumlah_kamar . ' kamar' : null],
            ['Ketersediaan', $tersedia === null ? null : ($tersedia === 0 ? 'Penuh' : $tersedia . ' kamar kosong')],
            ['Listrik', \App\Models\Kos::LISTRIK[$kos->listrik] ?? null],
        ])->filter(fn ($baris) => filled($baris[1]));

        $kelompokFasilitas = [
            'Fasilitas kamar'       => $kos->fasilitasDi(\App\Models\Kos::FASILITAS_KAMAR),
            'Fasilitas kamar mandi' => $kos->fasilitasDi(\App\Models\Kos::FASILITAS_KAMAR_MANDI),
            'Fasilitas umum'        => $kos->fasilitasDi(\App\Models\Kos::FASILITAS_UMUM),
        ];
    @endphp

    {{-- BAR TAB (muncul saat halaman digulir) --}}
    <div id="tabbar" data-tampil="false" aria-hidden="true" inert
        class="pointer-events-none fixed inset-x-0 top-16 z-[1100] -translate-y-2 border-b border-neutral-200 bg-white opacity-0 shadow-sm transition duration-200
                data-[tampil=true]:pointer-events-auto data-[tampil=true]:translate-y-0 data-[tampil=true]:opacity-100">
        <div class="mx-auto max-w-7xl px-4">
            <nav id="tabbar-nav" aria-label="Bagian halaman"
                class="relative -mx-4 flex gap-1 overflow-x-auto px-4 [scrollbar-none] [&::-webkit-scrollbar]:hidden">
                @foreach ([
                    ['foto',        'Foto Luar'],
                    ['spesifikasi', 'Spesifikasi'],
                    ['foto-detail', 'Foto Detail'],
                    ['fasilitas',   'Fasilitas'],
                    ['peraturan',   'Peraturan'],
                    ['lokasi',      'Lokasi'],
                ] as [$id, $label])
                    <a href="#{{ $id }}" data-tab="{{ $id }}" data-aktif="false"
                    class="shrink-0 whitespace-nowrap border-b-2 border-transparent px-4 py-3.5 text-sm font-medium text-neutral-600 transition hover:text-neutral-900
                            data-[aktif=true]:border-neutral-900 data-[aktif=true]:text-neutral-900">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    @php
        $jejak = [
            ['Beranda', route('home')],
            ['Semua kos', route('kos.index')],
        ];

        // Tingkat tipe hanya muncul kalau pengunjung datang dari daftar yang difilter
        if ($dariJenis) {
            $jejak[] = [ucfirst($dariJenis), route('kos.index', ['jenis' => $dariJenis])];
        }

        $jejak[] = [$kos->nama_kos];
    @endphp

    <x-breadcrumb :items="$jejak" />

    <main class="mx-auto max-w-7xl px-4 py-8">

        @unless ($terverifikasi)
            <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                Status kos ini: {{ ucfirst($kos->status_verifikasi) }}. Kos belum tampil di halaman publik dan hanya terlihat oleh pemilik dan admin.
            </div>
        @endunless

        {{-- GALERI TAMPILAN LUAR --}}
        <section id="foto" aria-label="Tampilan luar kos">
            @if ($fotoLuar->count())
                <button type="button" data-lb="luar" aria-label="Perbesar foto"
                        class="group relative block w-full overflow-hidden rounded-2xl bg-neutral-100">
                    <img id="foto-utama" src="{{ $fotoLuar->first() }}" alt="Tampilan luar {{ $kos->nama_kos }}"
                         class="aspect-[4/3] max-h-[560px] w-full object-cover sm:aspect-[16/9]">
                    <span class="absolute bottom-3 left-3 rounded-full bg-black/70 px-3 py-1 text-xs font-medium text-white">Tampilan luar</span>
                    <span class="absolute bottom-3 right-3 rounded-full bg-white px-3 py-1 text-xs font-semibold text-neutral-900 opacity-0 transition group-hover:opacity-100">Perbesar</span>
                </button>

                @if ($fotoLuar->count() > 1)
                    <div class="mt-3 grid grid-cols-4 gap-3 px-1">
                        @foreach ($fotoLuar as $i => $url)
                            <button type="button" data-thumb="{{ $i }}" aria-label="Lihat foto {{ $i + 1 }}"
                                class="overflow-hidden rounded-xl ring-2 ring-offset-2 {{ $i === 0 ? 'ring-neutral-900' : 'ring-transparent' }}">
                                <img src="{{ $url }}" alt="" loading="lazy" class="aspect-[4/3] w-full object-cover transition hover:opacity-90">
                            </button>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="flex aspect-[16/9] max-h-[420px] w-full items-center justify-center rounded-2xl bg-neutral-100 text-neutral-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </div>
            @endif
        </section>

        {{-- JUDUL + HARGA --}}
        <section class="mt-8 grid gap-6 lg:grid-cols-[1fr_380px] lg:items-start">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-neutral-900 px-3.5 py-1.5 text-xs font-semibold text-white">Kos {{ ucfirst($kos->jenis_kos) }}</span>
                    @if ($terverifikasi)
                        <span class="rounded-full border border-neutral-900 px-3.5 py-1.5 text-xs font-semibold">Terverifikasi</span>
                    @endif
                </div>

                <div class="mt-4 flex items-start justify-between gap-4">
                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">{{ $kos->nama_kos }}</h1>
                    @if ($terverifikasi)
                        <div class="shrink-0"><x-tombol-favorit :kos="$kos" ukuran="besar" /></div>
                    @endif
                </div>

                <p class="mt-3 flex items-start gap-2 text-base text-neutral-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-1 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>
                    {{ $kos->alamat }}
                </p>
            </div>

            <div class="rounded-2xl border border-neutral-300 p-5">
                @if ($kos->harga)
                    <p class="text-3xl font-bold">Rp {{ number_format($kos->harga, 0, ',', '.') }}</p>
                    <p class="mt-1 text-sm text-neutral-600">per bulan</p>
                @else
                    <p class="text-lg font-semibold">Harga belum dicantumkan</p>
                    <p class="mt-1 text-sm text-neutral-600">Hubungi pemilik untuk menanyakan harga.</p>
                @endif

                @if ($wa)
                    <a href="{{ route('kos.hubungi', $kos) }}"
                    @auth target="_blank" rel="noopener" @endauth
                    class="mt-5 flex items-center justify-center rounded-full bg-neutral-900 px-5 py-3 text-sm font-semibold text-white hover:bg-black">
                        Hubungi via WhatsApp
                    </a>

                    @guest
                        <p class="mt-2 text-center text-xs text-neutral-600">Login dulu untuk menghubungi pemilik.</p>
                    @endguest
                @else
                    <p class="mt-5 rounded-lg bg-neutral-100 px-4 py-3 text-sm text-neutral-700">Pemilik belum mencantumkan nomor telepon.</p>
                @endif

                <div class="mt-5 flex items-center gap-3 border-t border-neutral-200 pt-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-neutral-900 text-sm font-semibold text-white">
                        {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($kos->pemilik?->nama_lengkap ?? 'P', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-neutral-600">Pemilik kos</p>
                        <p class="truncate text-sm font-semibold">{{ $kos->pemilik?->nama_lengkap ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- SPESIFIKASI KAMAR --}}
        <section id="spesifikasi" class="mt-12 scroll-mt-24 border-t border-neutral-200 pt-10">
            <h2 class="text-2xl font-bold tracking-tight">Spesifikasi kamar</h2>

            @if ($spesifikasi->count())
                <dl class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($spesifikasi as [$nama, $isi])
                        <div class="rounded-2xl border border-neutral-200 p-4">
                            <dt class="text-sm text-neutral-600">{{ $nama }}</dt>
                            <dd class="mt-1.5 text-base font-semibold">{{ $isi }}</dd>
                        </div>
                    @endforeach
                </dl>
            @else
                <p class="mt-4 text-base text-neutral-600">Pemilik belum mencantumkan spesifikasi kamar.</p>
            @endif

            @if ($kos->deskripsi)
                <h3 class="mt-10 text-lg font-semibold">Tentang kos</h3>
                <p class="mt-3 max-w-3xl whitespace-pre-line text-base leading-7 text-neutral-700">{{ $kos->deskripsi }}</p>
            @endif
        </section>

        {{-- FOTO DETAIL DALAM KOS --}}
        <section id="foto-detail" class="mt-12 scroll-mt-24 border-t border-neutral-200 pt-10">
            <h2 class="text-2xl font-bold tracking-tight">Foto detail dalam kos</h2>

            @if ($fotoDalam->count())
                <div class="mt-6 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($fotoDalam as $i => $f)
                        <button type="button" data-lb="dalam" data-i="{{ $i }}" class="group block text-left" aria-label="Perbesar foto {{ $f['ket'] ?? ($i + 1) }}">
                            <span class="block overflow-hidden rounded-xl bg-neutral-100">
                                <img src="{{ $f['url'] }}" alt="{{ $f['ket'] ?? '' }}" loading="lazy"
                                     class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-105">
                            </span>
                            <span class="mt-2 block text-sm font-medium">{{ $f['ket'] ?: 'Foto ' . ($i + 1) }}</span>
                        </button>
                    @endforeach
                </div>
            @else
                <p class="mt-4 text-base text-neutral-600">Pemilik belum menambahkan foto detail.</p>
            @endif
        </section>

        {{-- FASILITAS --}}
        <section id="fasilitas" class="mt-12 scroll-mt-24 border-t border-neutral-200 pt-10">
            <h2 class="text-2xl font-bold tracking-tight">Fasilitas</h2>

            <div class="mt-6 grid gap-4 md:grid-cols-3">
                @foreach ($kelompokFasilitas as $judul => $daftar)
                    <div class="rounded-2xl border border-neutral-200 p-5">
                        <h3 class="text-base font-semibold">{{ $judul }}</h3>

                        @if (count($daftar))
                            <ul class="mt-4 space-y-3">
                                @foreach ($daftar as $teks)
                                    <li class="flex items-center gap-3 text-[15px]">
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-neutral-900 text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        </span>
                                        {{ $teks }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-4 text-sm text-neutral-600">Belum dicantumkan.</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>

        {{-- PERATURAN --}}
        <section id="peraturan" class="mt-12 scroll-mt-24 border-t border-neutral-200 pt-10">
            <h2 class="text-2xl font-bold tracking-tight">Peraturan kos</h2>

            @if ($peraturan->count())
                <ol class="mt-6 grid max-w-4xl gap-3 sm:grid-cols-2">
                    @foreach ($peraturan as $i => $aturan)
                        <li class="flex items-start gap-3 rounded-xl border border-neutral-200 p-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-neutral-900 text-xs font-semibold text-white">{{ $i + 1 }}</span>
                            <span class="pt-0.5 text-[15px] leading-6">{{ $aturan }}</span>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="mt-4 text-base text-neutral-600">Pemilik belum mencantumkan peraturan kos.</p>
            @endif
        </section>

       {{-- LOKASI --}}
        <section id="lokasi" class="mt-12 scroll-mt-24 border-t border-neutral-200 pt-10">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight">Lokasi</h2>
                    <p class="mt-2 flex items-start gap-2 text-neutral-600">{{ $kos->alamat }}</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="https://www.openstreetmap.org/?mlat={{ $kos->latitude }}&mlon={{ $kos->longitude }}#map=17/{{ $kos->latitude }}/{{ $kos->longitude }}"
                    target="_blank" rel="noopener"
                    class="rounded-full border border-neutral-300 px-5 py-2 text-sm font-semibold hover:border-neutral-900">
                        Buka di OpenStreetMap
                    </a>
                    <a href="https://www.google.com/maps/dir/?api=1&destination={{ $kos->latitude }},{{ $kos->longitude }}"
                    target="_blank" rel="noopener"
                    class="rounded-full bg-neutral-900 px-5 py-2 text-sm font-semibold text-white hover:bg-black">
                        Petunjuk arah
                    </a>
                </div>
            </div>

            <div class="isolate mt-6 overflow-hidden rounded-2xl ring-1 ring-neutral-300">
                <div id="map-detail" class="h-[360px] w-full lg:h-[460px]"></div>
            </div>
        </section>

        {{-- KOS SERUPA --}}
        @if ($serupa->count())
            <section class="mt-14 border-t border-neutral-200 pt-10">
                <div class="flex items-end justify-between gap-3">
                    <h2 class="text-2xl font-bold tracking-tight">Kos serupa</h2>
                    <a href="{{ route('kos.index', ['jenis' => $kos->jenis_kos]) }}" class="text-sm font-semibold underline hover:text-neutral-600">Lihat semua &rarr;</a>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($serupa as $s)
                    <div class="relative">
                        <a href="{{ route('kos.show', $s) }}"
                           class="group block overflow-hidden rounded-2xl border border-neutral-200 bg-white transition hover:border-neutral-900 hover:shadow-lg">
                            <div class="h-40 overflow-hidden bg-neutral-100">
                                @if ($s->foto_url)
                                    <img src="{{ $s->foto_url }}" alt="{{ $s->nama_kos }}" loading="lazy"
                                         class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @endif
                            </div>
                            <div class="p-4">
                                <h3 class="truncate text-base font-semibold">{{ $s->nama_kos }}</h3>
                                <p class="mt-1 line-clamp-1 text-sm text-neutral-600">{{ $s->alamat }}</p>
                                @if ($s->harga)
                                    <p class="mt-2 text-base font-bold">Rp {{ number_format($s->harga, 0, ',', '.') }}<span class="text-sm font-normal text-neutral-600"> /bulan</span></p>
                                @endif
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </section>
        @endif
    </main>

    <footer class="mt-16 border-t border-neutral-200 py-8">
        <div class="mx-auto max-w-7xl px-4 text-center text-sm text-neutral-600">
            &copy; {{ date('Y') }} NamaKos. Seluruh hak cipta dilindungi.
        </div>
    </footer>


    {{-- LIGHTBOX --}}
    <dialog id="lightbox" class="m-auto w-[min(92vw,1000px)] overflow-hidden rounded-2xl bg-black p-0 text-white backdrop:bg-black/80">
        <div class="relative">
            <img id="lb-img" src="" alt="" class="max-h-[78vh] w-full object-contain">

            <button type="button" id="lb-tutup" aria-label="Tutup"
                    class="absolute right-3 top-3 flex h-10 w-10 items-center justify-center rounded-full bg-white text-xl text-black hover:bg-neutral-200">&times;</button>
            <button type="button" id="lb-prev" aria-label="Foto sebelumnya"
                    class="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white text-black hover:bg-neutral-200">&larr;</button>
            <button type="button" id="lb-next" aria-label="Foto berikutnya"
                    class="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white text-black hover:bg-neutral-200">&rarr;</button>
        </div>
        <p id="lb-ket" class="px-5 py-3 text-sm"></p>
    </dialog>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const fotoLuar  = @json($fotoLuar->map(fn ($u) => ['url' => $u, 'ket' => 'Tampilan luar'])->values());
        const fotoDalam = @json($fotoDalam);

        /* ---------- GALERI TAMPILAN LUAR ---------- */
        let idxLuar = 0;
        const fotoUtama = document.getElementById('foto-utama');
        const thumbs = document.querySelectorAll('[data-thumb]');

        thumbs.forEach(btn => {
            btn.addEventListener('click', () => {
                idxLuar = Number(btn.dataset.thumb);
                fotoUtama.src = fotoLuar[idxLuar].url;

                thumbs.forEach(b => {
                    const aktif = b === btn;
                    b.classList.toggle('ring-neutral-900', aktif);
                    b.classList.toggle('ring-transparent', !aktif);
                });
            });
        });

        /* ---------- LIGHTBOX ---------- */
        const lb = document.getElementById('lightbox');
        const lbImg = document.getElementById('lb-img');
        const lbKet = document.getElementById('lb-ket');
        const lbPrev = document.getElementById('lb-prev');
        const lbNext = document.getElementById('lb-next');
        let lbList = [];
        let lbIdx = 0;

        function lbTampil() {
            const f = lbList[lbIdx];
            lbImg.src = f.url;
            lbImg.alt = f.ket || '';
            lbKet.textContent = (f.ket ? f.ket + ' · ' : '') + (lbIdx + 1) + ' / ' + lbList.length;
            lbPrev.hidden = lbNext.hidden = lbList.length < 2;
        }

        function lbBuka(list, i) {
            if (!list.length) return;
            lbList = list;
            lbIdx = i;
            lbTampil();
            lb.showModal();
        }

        function lbGeser(arah) {
            lbIdx = (lbIdx + arah + lbList.length) % lbList.length;
            lbTampil();
        }

        document.querySelectorAll('[data-lb]').forEach(el => {
            el.addEventListener('click', () => {
                if (el.dataset.lb === 'luar') lbBuka(fotoLuar, idxLuar);
                else lbBuka(fotoDalam, Number(el.dataset.i));
            });
        });

        document.getElementById('lb-tutup').addEventListener('click', () => lb.close());
        lbPrev.addEventListener('click', () => lbGeser(-1));
        lbNext.addEventListener('click', () => lbGeser(1));
        lb.addEventListener('click', e => { if (e.target === lb) lb.close(); });
        document.addEventListener('keydown', e => {
            if (!lb.open) return;
            if (e.key === 'ArrowLeft') lbGeser(-1);
            if (e.key === 'ArrowRight') lbGeser(1);
        });

        /* ---------- PETA ---------- */
        const lat = @json((float) $kos->latitude);
        const lng = @json((float) $kos->longitude);
        const namaKos = @json($kos->nama_kos);

        const map = L.map('map-detail', { scrollWheelZoom: false }).setView([lat, lng], 16);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        map.on('click', () => map.scrollWheelZoom.enable());
        map.on('mouseout', () => map.scrollWheelZoom.disable());

        // Nama kos dimasukkan sebagai teks biasa, bukan HTML
        const isiPopup = document.createElement('strong');
        isiPopup.textContent = namaKos;

        const pinKos = L.divIcon({
            className: '',
            iconSize: [34, 44],
            iconAnchor: [17, 44],
            popupAnchor: [0, -40],
            html: `<svg width="34" height="44" viewBox="0 0 34 44" xmlns="http://www.w3.org/2000/svg" style="filter:drop-shadow(0 3px 4px rgba(0,0,0,.35))">
                    <path d="M17 0C7.6 0 0 7.4 0 16.6 0 29 17 44 17 44s17-15 17-27.4C34 7.4 26.4 0 17 0z" fill="#171717"/>
                    <circle cx="17" cy="16.5" r="6.5" fill="#fff"/>
                </svg>`
        });

        L.marker([lat, lng], { icon: pinKos }).addTo(map).bindPopup(isiPopup).openPopup();

    (() => {
        const TARGET = 130;   // posisi judul bagian setelah tab diklik
        const SPY = 140;      // batas bagian dianggap sedang dibaca

        const tabbar = document.getElementById('tabbar');
        const nav = document.getElementById('tabbar-nav');

        const daftar = [...tabbar.querySelectorAll('[data-tab]')]
            .map(tab => ({ tab, el: document.getElementById(tab.dataset.tab) }))
            .filter(x => x.el);

        if (!daftar.length) return;

        let tampil = null;
        let aktifSekarang = null;
        let menunggu = false;

        function atur() {
            menunggu = false;

            // Bar muncul begitu halaman mulai digulir
            const baru = window.scrollY > 10;
            if (baru !== tampil) {
                tampil = baru;
                tabbar.dataset.tampil = baru ? 'true' : 'false';
                tabbar.inert = !baru; // tab tersembunyi tidak bisa difokus lewat keyboard
                tabbar.setAttribute('aria-hidden', baru ? 'false' : 'true');
            }

            // Tab aktif: bagian terakhir yang bagian atasnya sudah lewat batas
            let aktif = daftar[0];
            for (const x of daftar) {
                if (x.el.getBoundingClientRect().top <= SPY) aktif = x;
            }
            if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) {
                aktif = daftar[daftar.length - 1];
            }

            if (aktif !== aktifSekarang) {
                aktifSekarang = aktif;
                daftar.forEach(x => { x.tab.dataset.aktif = x === aktif ? 'true' : 'false'; });

                // Di layar kecil, geser bar supaya tab aktif tetap terlihat
                nav.scrollTo({
                    left: aktif.tab.offsetLeft - (nav.clientWidth - aktif.tab.offsetWidth) / 2,
                    behavior: 'smooth'
                });
            }
        }

        const jadwal = () => {
            if (menunggu) return;
            menunggu = true;
            requestAnimationFrame(atur);
        };

        window.addEventListener('scroll', jadwal, { passive: true });
        window.addEventListener('resize', jadwal);
        atur();

        // Klik tab: gulir halus, dengan jarak supaya judul tidak tertutup navbar dan bar
        tabbar.addEventListener('click', e => {
            const a = e.target.closest('[data-tab]');
            if (!a) return;

            const el = document.getElementById(a.dataset.tab);
            if (!el) return;

            e.preventDefault();

            let tujuan = el.getBoundingClientRect().top + window.scrollY - TARGET;

            // Galeri ada di dekat puncak halaman. Sisakan sedikit gulir supaya bar tidak ikut hilang.
            if (a.dataset.tab === 'foto') tujuan = Math.max(tujuan, 24);

            window.scrollTo({ top: tujuan, behavior: 'smooth' });
        });
    })();
    </script>
@endpush