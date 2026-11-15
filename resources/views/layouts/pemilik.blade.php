<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Sikos</title>
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-neutral-100 text-neutral-900 antialiased">

@php
    // [teks, url, aktif?, ikon]
    $menu = [
        ['Ringkasan', route('pemilik.dashboard'), request()->routeIs('pemilik.dashboard'),
            'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
        ['Kos saya', route('pemilik.kos.index'), request()->routeIs('pemilik.kos.index', 'pemilik.kos.edit'),
            'm2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
        ['Tambah kos', route('pemilik.kos.create'), request()->routeIs('pemilik.kos.create'),
            'M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
    ];
@endphp

<div class="min-h-screen lg:grid lg:grid-cols-[256px_1fr]">

    {{-- SIDEBAR --}}
    <aside id="sidebar" data-terbuka="false"
           class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-neutral-950 text-white transition-transform duration-200 motion-reduce:transition-none data-[terbuka=true]:translate-x-0 lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:w-auto lg:translate-x-0">

        <div class="flex h-16 shrink-0 items-center justify-between px-5">
            <a href="{{ route('pemilik.dashboard') }}" class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-sm font-bold text-neutral-950">S</div>
                <span class="text-lg font-bold">Sikos</span>
            </a>

            <button type="button" data-tutup-sidebar aria-label="Tutup menu"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-neutral-400 hover:bg-white/10 hover:text-white lg:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4" aria-label="Menu pemilik">
            @foreach ($menu as [$teks, $url, $aktif, $ikon])
                <a href="{{ $url }}" @if ($aktif) aria-current="page" @endif
                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                          {{ $aktif ? 'bg-white text-neutral-950' : 'text-neutral-300 hover:bg-white/10 hover:text-white' }}">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $ikon }}" />
                    </svg>
                    {{ $teks }}
                </a>
            @endforeach
        </nav>

        <div class="shrink-0 border-t border-white/10 p-3">
            <div class="px-3 pb-3 pt-2">
                <p class="truncate text-sm font-semibold">{{ Auth::user()->nama_lengkap }}</p>
                <p class="text-xs text-neutral-400">Pemilik kos</p>
            </div>

            <a href="{{ route('home') }}" target="_blank" rel="noopener"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-neutral-300 hover:bg-white/10 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
                Lihat situs
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-neutral-300 hover:bg-white/10 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Lapisan gelap di belakang drawer (HP) --}}
    <div id="lapis-sidebar" data-tutup-sidebar class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden"></div>

    {{-- KONTEN --}}
    <div class="min-w-0">

        {{-- Bar atas (HP) --}}
        <header class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-neutral-200 bg-white px-4 lg:hidden">
            <button type="button" data-buka-sidebar aria-label="Buka menu"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-neutral-300 hover:border-neutral-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <span class="text-base font-bold">Sikos</span>
        </header>

        <main class="px-4 py-6 sm:px-8 lg:px-10 lg:py-10">
            <div class="mx-auto max-w-5xl">
                @if (session('status'))
                    <div class="mb-6 rounded-xl border border-neutral-300 bg-white px-4 py-3 text-sm font-medium">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('galat'))
                    <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800" role="alert">
                        {{ session('galat') }}
                    </div>
                @endif
            </div>

            @yield('content')
        </main>
    </div>
</div>

<script>
    (function () {
        var sidebar = document.getElementById('sidebar');
        var lapis   = document.getElementById('lapis-sidebar');

        function atur(buka) {
            sidebar.dataset.terbuka = buka ? 'true' : 'false';
            lapis.classList.toggle('hidden', !buka);
            document.body.classList.toggle('overflow-hidden', buka);
        }

        document.querySelectorAll('[data-buka-sidebar]').forEach(function (b) {
            b.addEventListener('click', function () { atur(true); });
        });
        document.querySelectorAll('[data-tutup-sidebar]').forEach(function (b) {
            b.addEventListener('click', function () { atur(false); });
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') atur(false);
        });
    })();
</script>

@stack('scripts')
</body>
</html>