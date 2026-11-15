@extends('layouts.app')

@php
    $menuNavbar = [
        ['Tentang', '#tentang'],
        ['Cara kerja', '#cara-kerja'],
        ['Kos terbaru', '#kos-terbaru'],
    ];
    if (! $pencari) {
        $menuNavbar[] = ['Pemilik kos', '#pemilik'];
    }
@endphp

@section('navbar-kelas', 'group border-transparent bg-white/90 backdrop-blur transition data-[stuck=true]:border-neutral-200')

@section('navbar-tengah')
            {{-- Tengah: menu, berganti dengan search bar saat halaman discroll --}}
            <div class="grid min-w-0 flex-1 justify-items-center">
                <nav id="nav-links"
                     class="col-start-1 row-start-1 hidden items-center gap-8 text-sm font-medium transition duration-300 md:flex
                            group-data-[stuck=true]:pointer-events-none group-data-[stuck=true]:-translate-y-2 group-data-[stuck=true]:opacity-0">
                    <a href="#tentang" class="text-neutral-700 hover:text-black">Tentang</a>
                    <a href="#cara-kerja" class="text-neutral-700 hover:text-black">Cara Kerja</a>
                    <a href="#kos-terbaru" class="text-neutral-700 hover:text-black">Kos Terbaru</a>
                    @unless ($pencari)
                        <a href="#pemilik" class="text-neutral-700 hover:text-black">Pemilik Kos</a>
                    @endunless
                </nav>

                <form id="nav-search" action="{{ route('kos.index') }}" method="GET"
                      class="pointer-events-none col-start-1 row-start-1 flex w-full max-w-md translate-y-2 items-center gap-2 rounded-full border border-neutral-300 bg-white py-1 pl-4 pr-1 opacity-0 transition duration-300 focus-within:border-neutral-900
                             group-data-[stuck=true]:pointer-events-auto group-data-[stuck=true]:translate-y-0 group-data-[stuck=true]:opacity-100">
                    <input type="text" name="q" data-q placeholder="Cari kos..." aria-label="Cari kos"
                        class="min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-neutral-500">
                    <button type="submit" aria-label="Cari"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-neutral-900 text-white hover:bg-black">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </button>
                </form>
            </div>
@endsection

@section('content')
    {{-- HERO --}}
    <section class="mx-auto max-w-7xl px-4 pb-16 pt-14 sm:pt-20 lg:pt-24">
        <div class="mx-auto max-w-3xl text-center">
           <span class="inline-flex items-center gap-2 rounded-full border border-neutral-200 px-4 py-1.5 text-xs font-medium text-neutral-700">
                <span class="h-1.5 w-1.5 rounded-full bg-neutral-900"></span>
                @if ($pencari)
                    Halo, {{ \Illuminate\Support\Str::before(Auth::user()->nama_lengkap, ' ') }}
                @else
                    {{ $totalKos }} kos terverifikasi
                @endif
            </span>

            <h1 class="mt-6 text-4xl font-bold tracking-tight sm:text-5xl lg:text-6xl">
                Temukan kos yang pas, tanpa keliling kota.
            </h1>

            <p class="mx-auto mt-5 max-w-2xl text-lg leading-8 text-neutral-600">
                Lihat lokasi kos langsung di peta, bandingkan tipe dan harga, lalu hubungi pemilik secara langsung.
            </p>
        </div>

        <form id="hero-search" action="{{ route('kos.index') }}" method="GET"
              class="mx-auto mt-10 flex max-w-3xl flex-col gap-2 rounded-2xl border border-neutral-300 bg-white p-2 shadow-xl shadow-neutral-900/5 focus-within:border-neutral-900 sm:flex-row sm:items-center sm:rounded-full">
            <label for="q-hero" class="sr-only">Cari kos</label>
            <input id="q-hero" type="text" name="q" data-q placeholder="Cari nama kos atau alamat, contoh: Seturan"
                class="min-w-0 flex-1 bg-transparent px-5 py-3 text-base outline-none placeholder:text-neutral-500">

            <div class="hidden h-8 w-px bg-neutral-200 sm:block"></div>

            <select name="jenis" aria-label="Tipe kos"
                class="rounded-full bg-transparent px-4 py-3 text-sm font-medium outline-none">
                <option value="">Semua tipe</option>
                <option value="putra">Putra</option>
                <option value="putri">Putri</option>
                <option value="campur">Campur</option>
            </select>

            <button type="submit" class="rounded-full bg-neutral-900 px-8 py-3 text-sm font-semibold text-white hover:bg-black">
                Cari
            </button>
        </form>

        <div class="mt-5 flex flex-wrap items-center justify-center gap-2 text-sm">
            <span class="text-neutral-600">Pilih cepat:</span>
            @foreach (['putra' => 'Kos Putra', 'putri' => 'Kos Putri', 'campur' => 'Kos Campur'] as $val => $teks)
                <a href="{{ route('kos.index', ['jenis' => $val]) }}"
                   class="rounded-full border border-neutral-300 px-4 py-1.5 font-medium hover:border-neutral-900 hover:bg-neutral-900 hover:text-white">
                    {{ $teks }}
                </a>
            @endforeach
        </div>
    </section>

    {{-- FAVORIT KAMU (hanya pencari yang punya favorit) --}}
    @if ($pencari && $favoritTerbaru->count())
        <section id="favorit-kamu" class="scroll-mt-24 border-t border-neutral-200 py-16">
            <div class="mx-auto max-w-7xl px-4">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight">Favorit kamu</h2>
                        <p class="mt-2 text-neutral-600">Kos yang terakhir kamu simpan.</p>
                    </div>
                    <a href="{{ route('favorites.index') }}"
                    class="inline-flex items-center gap-2 rounded-full border border-neutral-300 px-6 py-3 text-sm font-semibold hover:border-neutral-900 hover:bg-neutral-900 hover:text-white">
                        Lihat semua favorit <span>&rarr;</span>
                    </a>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($favoritTerbaru as $f)
                        <div class="relative">
                            <a href="{{ route('kos.show', $f) }}"
                            class="group block overflow-hidden rounded-2xl border border-neutral-200 bg-white transition hover:border-neutral-900 hover:shadow-lg">
                                <div class="relative h-44 overflow-hidden bg-neutral-100">
                                    @if ($f->foto_url)
                                        <img src="{{ $f->foto_url }}" alt="{{ $f->nama_kos }}" loading="lazy"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                    @endif
                                    <span class="absolute left-3 top-3 rounded-full bg-neutral-900/85 px-3 py-1 text-xs font-medium text-white backdrop-blur">
                                        {{ ucfirst($f->jenis_kos) }}
                                    </span>
                                </div>

                                <div class="p-4">
                                    <h3 class="truncate text-base font-semibold">{{ $f->nama_kos }}</h3>
                                    <p class="mt-1.5 line-clamp-2 text-sm text-neutral-600">{{ $f->alamat }}</p>
                                    @if ($f->harga)
                                        <p class="mt-3 text-base font-bold">
                                            Rp {{ number_format($f->harga, 0, ',', '.') }}<span class="text-sm font-normal text-neutral-600"> /bulan</span>
                                        </p>
                                    @endif
                                </div>
                            </a>
                            <div class="absolute right-3 top-3"><x-tombol-favorit :kos="$f" /></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- KOS TERBARU (slider) --}}
    <section id="kos-terbaru" class="scroll-mt-24 border-t border-neutral-200 py-16">
        <div class="mx-auto max-w-7xl px-4">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight">Kos terbaru</h2>
                    <p class="mt-2 text-neutral-600">Geser ke kanan untuk melihat lebih banyak.</p>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('kos.index') }}"
                       class="inline-flex items-center gap-2 rounded-full bg-neutral-900 px-6 py-3 text-sm font-semibold text-white hover:bg-black">
                        Lihat semua kos <span>&rarr;</span>
                    </a>

                    @if ($kosTerbaru->count() > 1)
                        <div class="hidden gap-2 sm:flex">
                            <button type="button" data-geser="-1" aria-label="Geser ke kiri"
                                class="flex h-11 w-11 items-center justify-center rounded-full border border-neutral-300 hover:border-neutral-900 hover:bg-neutral-900 hover:text-white">&larr;</button>
                            <button type="button" data-geser="1" aria-label="Geser ke kanan"
                                class="flex h-11 w-11 items-center justify-center rounded-full border border-neutral-300 hover:border-neutral-900 hover:bg-neutral-900 hover:text-white">&rarr;</button>
                        </div>
                    @endif
                </div>
            </div>

            @if ($kosTerbaru->count())
                <div id="slider"
                     class="-mx-4 mt-8 flex snap-x snap-mandatory scroll-pl-4 gap-4 overflow-x-auto scroll-smooth px-4 pb-4 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">

                    @foreach ($kosTerbaru as $k)
                    <div class="relative w-[260px] shrink-0 snap-start sm:w-[280px]">
                        <a href="{{ route('kos.show', $k) }}"
                           class="group block w-[260px] shrink-0 snap-start overflow-hidden rounded-2xl border border-neutral-200 bg-white transition hover:border-neutral-900 hover:shadow-lg sm:w-[280px]">

                            <div class="relative h-44 overflow-hidden bg-neutral-100">
                                @if ($k->foto_url)
                                    <img src="{{ $k->foto_url }}" alt="{{ $k->nama_kos }}" loading="lazy"
                                         class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                                @else
                                    <div class="flex h-full items-center justify-center text-neutral-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                        </svg>
                                    </div>
                                @endif

                                <span class="absolute left-3 top-3 rounded-full bg-neutral-900/85 px-3 py-1 text-xs font-medium text-white backdrop-blur">
                                    {{ ucfirst($k->jenis_kos) }}
                                </span>
                            </div>

                            <div class="p-4">
                                <h3 class="truncate text-base font-semibold">{{ $k->nama_kos }}</h3>

                                <p class="mt-1.5 flex items-start gap-1.5 text-sm text-neutral-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    <span class="line-clamp-2">{{ $k->alamat }}</span>
                                </p>

                                @if ($k->harga)
                                    <p class="mt-3 text-base font-bold">
                                        Rp {{ number_format($k->harga, 0, ',', '.') }}<span class="text-sm font-normal text-neutral-600"> /bulan</span>
                                    </p>
                                @else
                                    <p class="mt-3 text-sm text-neutral-600">Harga belum dicantumkan</p>
                                @endif
                            </div>
                        </a>
                    </div>
                    @endforeach

                    {{-- Kartu penutup slider --}}
                    <a href="{{ route('kos.index') }}"
                       class="flex w-[200px] shrink-0 snap-start flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-neutral-400 p-6 text-center text-sm font-semibold hover:border-neutral-900 hover:bg-neutral-50">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-neutral-900 text-lg text-white">&rarr;</span>
                        Lihat semua kos
                    </a>
                </div>
            @else
                <p class="mt-8 rounded-2xl border border-neutral-200 p-10 text-center text-neutral-600">
                    Belum ada kos yang terverifikasi.
                </p>
            @endif
        </div>
    </section>

    {{-- TENTANG --}}
    <section id="tentang" class="scroll-mt-24 border-t border-neutral-200 py-20">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 lg:grid-cols-2 lg:gap-16">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-neutral-500">Tentang Sikos</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">
                    Cari kos jadi lebih jelas dan tidak membuang waktu.
                </h2>
                <p class="mt-5 text-base leading-7 text-neutral-600">
                    NamaKos mempertemukan pencari kos dan pemilik kos dalam satu tempat. Semua kos ditampilkan lengkap dengan lokasi di peta, foto, fasilitas, dan harga, sehingga kamu bisa membandingkan pilihan sebelum datang survei.
                </p>
                <p class="mt-4 text-base leading-7 text-neutral-600">
                    Setiap kos diperiksa admin lebih dulu sebelum tampil, dan kamu bisa menghubungi pemilik langsung tanpa perantara.
                </p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    ['Lokasi di peta', 'Lihat posisi kos dan area sekitarnya secara langsung.', 'M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z'],
                    ['Pencarian cepat', 'Cari berdasarkan nama, alamat, dan tipe kos dalam sekali klik.', 'm21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z'],
                    ['Kos terverifikasi', 'Setiap kos diperiksa admin sebelum ditampilkan.', 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
                    ['Langsung ke pemilik', 'Hubungi pemilik kos lewat WhatsApp tanpa perantara.', 'M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z'],
                ] as [$judul, $isi, $ikon])
                    <div class="rounded-2xl border border-neutral-200 p-5">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-neutral-900 text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold">{{ $judul }}</h3>
                        <p class="mt-1.5 text-sm leading-6 text-neutral-600">{{ $isi }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CARA KERJA --}}
    <section id="cara-kerja" class="scroll-mt-24 bg-neutral-950 py-20 text-white">
        <div class="mx-auto max-w-7xl px-4">
            <p class="text-sm font-semibold uppercase tracking-widest text-neutral-400">Cara kerja</p>
            <h2 class="mt-3 max-w-2xl text-3xl font-bold tracking-tight sm:text-4xl">Tiga langkah menuju kos yang cocok</h2>

            <div class="mt-12 grid gap-6 md:grid-cols-3">
                @foreach ([
                    ['01', 'Cari kos', 'Ketik nama atau alamat, lalu pilih tipe kos yang kamu butuhkan.'],
                    ['02', 'Bandingkan', 'Lihat foto, fasilitas, harga, dan lokasi di peta untuk menyaring pilihan.'],
                    ['03', 'Hubungi pemilik', 'Tanyakan ketersediaan kamar dan atur jadwal survei lewat WhatsApp.'],
                ] as [$no, $judul, $isi])
                    <div class="rounded-2xl border border-white/15 p-6">
                        <span class="text-5xl font-bold text-white/30">{{ $no }}</span>
                        <h3 class="mt-6 text-xl font-semibold">{{ $judul }}</h3>
                        <p class="mt-2 text-base leading-7 text-neutral-300">{{ $isi }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- UNTUK PEMILIK KOS --}}
    @unless ($pencari)
        <section id="pemilik" class="scroll-mt-24 py-20">
            <div class="mx-auto max-w-7xl px-4">
                <div class="flex flex-col items-start justify-between gap-6 rounded-3xl border border-neutral-200 p-8 sm:p-12 md:flex-row md:items-center">
                    <div class="max-w-xl">
                        <p class="text-sm font-semibold uppercase tracking-widest text-neutral-500">Untuk pemilik kos</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight">Punya kos? Pasarkan kamarmu di sini.</h2>
                        <p class="mt-3 text-base leading-7 text-neutral-600">
                            Daftarkan kosmu, tampilkan foto dan lokasinya di peta, dan jangkau lebih banyak calon penghuni.
                        </p>
                    </div>

                    @if ($dashboardUrl)
                        <a href="{{ $dashboardUrl }}" class="shrink-0 rounded-full bg-neutral-900 px-7 py-3.5 text-sm font-semibold text-white hover:bg-black">Buka dashboard</a>
                    @else
                        <a href="{{ route('register') }}" class="shrink-0 rounded-full bg-neutral-900 px-7 py-3.5 text-sm font-semibold text-white hover:bg-black">Daftar sebagai pemilik kos</a>
                    @endif
                </div>
            </div>
        </section>
    @endunless

    {{-- FOOTER --}}
    <footer class="border-t border-neutral-200 py-8">
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-3 px-4 text-sm text-neutral-600 sm:flex-row">
            <span>&copy; {{ date('Y') }} NamaKos. Seluruh hak cipta dilindungi.</span>
            <div class="flex gap-6">
                <a href="{{ route('kos.index') }}" class="hover:text-black">Semua kos</a>
                @unless ($dashboardUrl)
                    <a href="{{ route('login') }}" class="hover:text-black">Login</a>
                    <a href="{{ route('register') }}" class="hover:text-black">Daftar</a>
                @endunless
            </div>
        </div>
    </footer>
@endsection

@push('scripts')
    <script>
        const navbar    = document.getElementById('navbar');
        const navLinks  = document.getElementById('nav-links');
        const navSearch = document.getElementById('nav-search');
        const heroForm  = document.getElementById('hero-search');

        // Elemen yang sedang tersembunyi tidak boleh bisa difokus lewat Tab
        function setStuck(stuck) {
            navbar.dataset.stuck = stuck ? 'true' : 'false';
            navSearch.inert = !stuck;
            navLinks.inert = stuck;
        }
        setStuck(false);

        // Search bar di hero keluar dari layar (naik ke bawah navbar): pindah ke navbar
        new IntersectionObserver(([entry]) => {
            setStuck(!entry.isIntersecting && entry.boundingClientRect.top < 0);
        }, { rootMargin: '-64px 0px 0px 0px', threshold: 0 }).observe(heroForm);

        // Isi kolom pencarian di hero dan navbar selalu sama
        const qInputs = document.querySelectorAll('[data-q]');
        qInputs.forEach(input => {
            input.addEventListener('input', () => {
                qInputs.forEach(other => { if (other !== input) other.value = input.value; });
            });
        });

        // Tombol geser slider
        const slider = document.getElementById('slider');
        document.querySelectorAll('[data-geser]').forEach(btn => {
            btn.addEventListener('click', () => {
                slider?.scrollBy({ left: Number(btn.dataset.geser) * 300 });
            });
        });

        // Gulir halus hanya saat link menu diklik
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const target = document.querySelector(a.getAttribute('href'));
                if (!target) return;

                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
@endpush