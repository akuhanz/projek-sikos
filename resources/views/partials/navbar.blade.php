@php
    $nama = 'Sikos'; // ganti nama brand di sini saja
    $user = auth()->user();
    $role = $user?->role;
    $adaTengah = $__env->hasSection('navbar-tengah');
    $menuNavbar = $menuNavbar ?? []; // tautan anchor tambahan untuk menu HP (diisi landing)

    $dari = request()->routeIs('kos.*') ? ['dari' => 'kos'] : [];

    // [teks, url, sedang aktif?]
    $tautan = [['Semua kos', route('kos.index'), request()->routeIs('kos.index')]];

    if ($role === 'pencari_kos') {
        $tautan[] = ['Favorit', route('favorites.index', $dari), request()->routeIs('favorites.index')];
    } elseif ($role === 'pemilik_kos') {
        $tautan[] = ['Kos saya', route('pemilik.kos.index'), request()->routeIs('pemilik.kos.index')];
        $tautan[] = ['Tambah kos', route('pemilik.kos.create'), request()->routeIs('pemilik.kos.create')];
    } elseif ($role === 'admin') {
        $tautan[] = ['Dashboard', url('/admin/dashboard'), request()->is('admin/*')];
    }

    // Di desktop, "Semua kos" disembunyikan kalau bagian tengah sudah berisi search bar atau menu
    $tautanDesktop = $adaTengah ? array_slice($tautan, 1) : $tautan;

    // Daftar tautan dropdown HP
    $tautanHp = collect($menuNavbar)->map(fn ($m) => [$m[0], $m[1]])->all();
    if (! request()->routeIs('home')) {
        $tautanHp[] = ['Beranda', route('home')];
    }
    foreach ($tautan as [$t, $u]) {
        $tautanHp[] = [$t, $u];
    }
@endphp

<header id="navbar" data-stuck="false"
        class="sticky top-0 @yield('navbar-z', 'z-50') border-b @yield('navbar-kelas', 'border-neutral-200 bg-white')">
    <div class="mx-auto flex h-16 @yield('navbar-lebar', 'max-w-7xl') items-center justify-between gap-3 px-4 md:gap-6">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-neutral-900 text-sm font-bold text-white">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($nama, 0, 1)) }}</div>
            <span class="hidden text-lg font-bold sm:inline">{{ $nama }}</span>
        </a>

        {{-- Tengah (diisi halaman lewat @section('navbar-tengah')) --}}
        <div class="min-w-0 flex-1">@yield('navbar-tengah')</div>

        {{-- Kanan --}}
        <div class="flex shrink-0 items-center gap-2 md:gap-5">

            {{-- Desktop --}}
            <nav class="hidden items-center gap-5 text-sm md:flex">
                @foreach ($tautanDesktop as [$teks, $url, $aktif])
                    <a href="{{ $url }}"
                       class="{{ $aktif ? 'font-semibold text-black' : 'font-medium text-neutral-700 hover:text-black' }}">{{ $teks }}</a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                @if ($user)
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-full border border-neutral-300 px-4 py-2 text-sm font-medium hover:border-neutral-900">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-2 text-sm font-medium text-neutral-700 hover:text-black">Login</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-neutral-900 px-5 py-2 text-sm font-semibold text-white hover:bg-black">Daftar</a>
                @endif
            </div>

            {{-- Hamburger (HP) --}}
            <div class="relative md:hidden" data-menu>
                <button type="button" data-menu-tombol aria-expanded="false" aria-controls="menu-mobile" aria-label="Buka menu"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-neutral-300 hover:border-neutral-900">
                    <svg data-ikon-buka xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg data-ikon-tutup xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>

                <div id="menu-mobile" data-menu-panel hidden
                     class="absolute right-0 top-full z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-neutral-200 bg-white p-2 shadow-xl">

                    @if ($user)
                        <div class="border-b border-neutral-200 px-4 pb-3 pt-2">
                            <p class="text-xs text-neutral-500">Masuk sebagai</p>
                            <p class="truncate text-sm font-semibold">{{ $user->nama_lengkap }}</p>
                        </div>
                    @endif

                    <nav class="py-1">
                        @foreach ($tautanHp as [$teks, $url])
                            <a href="{{ $url }}" class="flex items-center rounded-xl px-4 py-3 text-sm font-medium text-neutral-800 hover:bg-neutral-100">{{ $teks }}</a>
                        @endforeach
                    </nav>

                    <div class="border-t border-neutral-200 p-2">
                        @if ($user)
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full rounded-full border border-neutral-300 px-4 py-2.5 text-sm font-medium hover:border-neutral-900">Logout</button>
                            </form>
                        @else
                            <div class="grid grid-cols-2 gap-2">
                                <a href="{{ route('login') }}" class="rounded-full border border-neutral-300 px-4 py-2.5 text-center text-sm font-medium hover:border-neutral-900">Login</a>
                                <a href="{{ route('register') }}" class="rounded-full bg-neutral-900 px-4 py-2.5 text-center text-sm font-semibold text-white hover:bg-black">Daftar</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>