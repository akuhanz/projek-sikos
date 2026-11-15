@extends('layouts.app')

@section('title', 'Semua kos')
@section('body-class', 'group bg-white text-neutral-900 antialiased')
@section('navbar-kelas', 'border-neutral-200 bg-white transition group-data-[stuck=true]:border-transparent')

@section('navbar-tengah')
    <div class="flex min-w-0 justify-center">
        <div class="flex w-full max-w-xl items-center gap-2 rounded-full border border-neutral-300 bg-white py-1 pl-4 pr-1 focus-within:border-neutral-900">
            <input form="form-filter" type="text" name="q" value="{{ $q }}"
                   placeholder="Cari nama kos atau alamat" aria-label="Cari kos"
                   class="min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-neutral-500">
            <button form="form-filter" type="submit" aria-label="Cari"
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-neutral-900 text-white hover:bg-black">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </button>
        </div>
    </div>
@endsection

@section('content')


    <form id="form-filter" method="GET" action="{{ route('kos.index') }}"></form>

    <x-breadcrumb :items="[['Beranda', route('home')], ['Semua kos']]" />

    {{-- JUDUL --}}
    <section class="mx-auto max-w-7xl px-4 pb-6 pt-10">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Semua kos</h1>
        <p class="mt-2 text-neutral-600">Cari dan bandingkan kos yang sudah terverifikasi.</p>
    </section>

    {{-- Penanda: saat ini lewat di bawah navbar, filter dianggap menempel --}}
    <div id="sentinel" class="h-px"></div>

    {{-- FILTER (menempel ke navbar saat discroll) --}}
    <div id="filter-bar"
         class="sticky top-16 z-40 border-b border-transparent bg-white transition group-data-[stuck=true]:border-neutral-200 group-data-[stuck=true]:shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-3">
            <div class="flex items-center gap-4 overflow-x-auto px-0.5 py-1 [scrollbar-none] [&::-webkit-scrollbar]:hidden">

                {{-- Tipe kos --}}
                <div role="radiogroup" aria-label="Tipe kos" class="flex shrink-0 items-center gap-2">
                    @foreach (['' => 'Semua', 'putra' => 'Putra', 'putri' => 'Putri', 'campur' => 'Campur'] as $val => $teks)
                        <label class="cursor-pointer">
                            <input type="radio" name="jenis" value="{{ $val }}" form="form-filter" data-auto
                                   class="peer sr-only" {{ ($jenis ?? '') === $val ? 'checked' : '' }}>
                            <span class="block rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium transition hover:border-neutral-900 peer-checked:border-neutral-900 peer-checked:bg-neutral-900 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-neutral-900 peer-focus-visible:ring-offset-2">
                                {{ $teks }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="h-6 w-px shrink-0 bg-neutral-200"></div>

                {{-- Rentang harga --}}
                <div class="flex shrink-0 items-center gap-2">
                    <span class="text-sm font-medium text-neutral-700">Harga</span>

                    <div class="flex items-center gap-2 rounded-full border border-neutral-300 px-4 focus-within:border-neutral-900">
                        <span class="text-sm text-neutral-500">Rp</span>
                        <input type="number" name="harga_min" form="form-filter" value="{{ $min }}" min="0" inputmode="numeric"
                               placeholder="Min" aria-label="Harga minimal per bulan"
                               class="w-24 bg-transparent py-2 text-sm outline-none placeholder:text-neutral-500 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                        <span class="text-neutral-400">&ndash;</span>
                        <input type="number" name="harga_maks" form="form-filter" value="{{ $maks }}" min="0" inputmode="numeric"
                               placeholder="Maks" aria-label="Harga maksimal per bulan"
                               class="w-24 bg-transparent py-2 text-sm outline-none placeholder:text-neutral-500 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                    </div>

                    <button type="submit" form="form-filter"
                            class="rounded-full bg-neutral-900 px-5 py-2 text-sm font-semibold text-white hover:bg-black">
                        Terapkan
                    </button>
                </div>

                @if ($filterAktif)
                    <a href="{{ route('kos.index') }}" class="shrink-0 text-sm font-medium underline hover:text-neutral-600">Hapus filter</a>
                @endif
            </div>
        </div>
    </div>

    {{-- DAFTAR KOS --}}
    <section id="daftar-kos" class="mx-auto max-w-7xl scroll-mt-36 px-4 py-8">
        <p class="text-sm text-neutral-600">
            @if ($daftarKos->total() > 0)
                Menampilkan {{ $daftarKos->firstItem() }}&ndash;{{ $daftarKos->lastItem() }} dari {{ $daftarKos->total() }} kos
            @else
                0 kos ditemukan
            @endif
        </p>

        @if ($daftarKos->count())
            <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($daftarKos as $k)
                <div class="relative">
                    <a href="{{ route('kos.show', array_filter(['kos' => $k, 'jenis' => $jenis])) }}"
                       class="group/kartu block overflow-hidden rounded-2xl border border-neutral-200 bg-white transition hover:border-neutral-900 hover:shadow-lg">

                        <div class="relative h-44 overflow-hidden bg-neutral-100">
                            @if ($k->foto_url)
                                <img src="{{ $k->foto_url }}" alt="{{ $k->nama_kos }}" loading="lazy"
                                     class="h-full w-full object-cover transition duration-500 group-hover/kartu:scale-105">
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
                            <h3 class="truncate text-base font-semibold" title="{{ $k->nama_kos }}">{{ $k->nama_kos }}</h3>

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
            </div>

            <div class="mt-10">
                {{ $daftarKos->links('pagination.kos') }}
            </div>
        @else
            <p class="mt-5 rounded-2xl border border-neutral-200 p-10 text-center text-neutral-600">
                Belum ada kos yang cocok dengan pencarianmu. Coba ubah atau hapus filter.
            </p>
        @endif
    </section>

    <footer class="border-t border-neutral-200 py-8">
        <div class="mx-auto max-w-7xl px-4 text-center text-sm text-neutral-600">
            &copy; {{ date('Y') }} NamaKos. Seluruh hak cipta dilindungi.
        </div>
    </footer>

@endsection
@push('scripts')
    <script>
        const NAV_H = 64;
        const navbar = document.getElementById('navbar');
        const filterBar = document.getElementById('filter-bar');
        const sentinel = document.getElementById('sentinel');
        const form = document.getElementById('form-filter');

        // Filter dianggap "menempel" saat penanda sudah naik melewati navbar
        new IntersectionObserver(([e]) => {
            const stuck = !e.isIntersecting && e.boundingClientRect.top < NAV_H;
            document.body.dataset.stuck = stuck ? 'true' : 'false';
        }, { rootMargin: `-${NAV_H}px 0px 0px 0px`, threshold: 0 }).observe(sentinel);

        // Pilihan tipe kos langsung diterapkan
        document.querySelectorAll('[data-auto]').forEach(el => {
            el.addEventListener('change', () => form.requestSubmit());
        });

        // Kolom kosong tidak ikut dikirim, supaya URL tetap bersih
        form.addEventListener('submit', () => {
            [...form.elements].forEach(el => {
                if (el.name && el.type !== 'submit' && el.value === '') el.disabled = true;
            });
        });
        window.addEventListener('pageshow', () => {
            [...form.elements].forEach(el => { el.disabled = false; });
        });

        // Pagination tanpa reload: hanya bagian daftar kos yang diganti
        const daftar = document.getElementById('daftar-kos');

        async function muatHalaman(url, { push = true, gulir = true } = {}) {
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) throw new Error('Gagal memuat');

                const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                const baru = doc.getElementById('daftar-kos');
                if (!baru) throw new Error('Bagian daftar tidak ditemukan');

                daftar.innerHTML = baru.innerHTML;

                if (push) history.pushState({}, '', url);
                if (gulir) {
                    const offset = navbar.offsetHeight + filterBar.offsetHeight + 16;
                    window.scrollTo(0, daftar.getBoundingClientRect().top + window.scrollY - offset);
                }
            } catch (err) {
                window.location.href = url;
            }
        }

        daftar.addEventListener('click', e => {
            const a = e.target.closest('nav[aria-label="Navigasi halaman"] a');
            if (!a) return;

            e.preventDefault();
            muatHalaman(a.href);
        });

        window.addEventListener('popstate', () => muatHalaman(location.href, { push: false, gulir: false }));
    </script>
@endpush