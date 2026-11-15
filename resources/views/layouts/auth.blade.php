<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Sikos</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-neutral-900 antialiased">

<div class="grid min-h-screen lg:grid-cols-[5fr_6fr]">

    {{-- PANEL KIRI (hanya di layar besar) --}}
    <aside class="hidden flex-col justify-between bg-neutral-950 p-12 text-white lg:flex">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-sm font-bold text-neutral-950">S</div>
            <span class="text-lg font-bold">Sikos</span>
        </a>

        {{-- Denah kamar kos: pintu satu-satunya garis putih --}}
        <svg viewBox="0 0 424 330" class="mx-auto w-full max-w-md fill-none stroke-neutral-600"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <g transform="translate(0 30)">
                {{-- Dinding, ada celah untuk pintu di kanan bawah --}}
                <path d="M270 280H20V20H380V280H350" stroke-width="3" />

                {{-- Jendela --}}
                <rect x="150" y="15" width="100" height="10" class="fill-neutral-950" />
                <path d="M150 20H250" stroke-width="1" />

                {{-- Pintu --}}
                <path d="M350 280V200" class="stroke-white" stroke-width="3" />
                <path d="M350 200A80 80 0 0 0 270 280" class="stroke-white" stroke-width="1.5" stroke-dasharray="3 6" />

                {{-- Kasur --}}
                <rect x="36" y="44" width="90" height="200" rx="4" />
                <rect x="46" y="54" width="70" height="34" rx="8" />
                <path d="M36 130H126" />

                {{-- Lemari --}}
                <rect x="270" y="34" width="100" height="50" />
                <path d="M320 34V84" />

                {{-- Meja dan kursi --}}
                <rect x="320" y="100" width="50" height="92" />
                <rect x="282" y="132" width="26" height="26" rx="13" />

                {{-- Ukuran kamar --}}
                <path d="M20 -10H380M20 -16V-4M380 -16V-4" stroke-width="1" />
                <text x="200" y="-18" text-anchor="middle" font-size="12" class="fill-neutral-500 stroke-none">4 m</text>
                <path d="M404 20V280M398 20H410M398 280H410" stroke-width="1" />
                <text transform="translate(418 150) rotate(90)" text-anchor="middle" font-size="12" class="fill-neutral-500 stroke-none">3 m</text>
            </g>
        </svg>

        <div>
            <h2 class="max-w-md text-4xl font-bold leading-tight tracking-tight">
                @yield('panel-judul', 'Cari kos yang pas, tanpa keliling kota.')
            </h2>
            <p class="mt-4 max-w-sm leading-7 text-neutral-400">
                @yield('panel-teks', 'Bandingkan harga dan fasilitas, lalu hubungi pemilik langsung.')
            </p>
        </div>
    </aside>

    {{-- KOLOM FORM --}}
    <div class="flex min-h-screen flex-col lg:min-h-0">
        <header class="flex items-center justify-between px-6 py-5 sm:px-10">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 lg:hidden">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-neutral-900 text-sm font-bold text-white">S</div>
                <span class="text-lg font-bold">Sikos</span>
            </a>

            <a href="{{ route('home') }}" class="ml-auto inline-flex items-center gap-1.5 text-sm font-medium text-neutral-600 hover:text-black">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                </svg>
                Kembali ke beranda
            </a>
        </header>

        <main class="flex flex-1 items-center justify-center px-6 pb-12 pt-4 sm:px-10">
            <div class="w-full max-w-md">
                @yield('content')
            </div>
        </main>
    </div>
</div>

<script>
    // Tombol tampilkan / sembunyikan password
    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input  = document.getElementById(btn.dataset.togglePassword);
            var tampil = input.type === 'password';

            input.type = tampil ? 'text' : 'password';
            btn.setAttribute('aria-pressed', tampil ? 'true' : 'false');
            btn.setAttribute('aria-label', tampil ? 'Sembunyikan password' : 'Tampilkan password');
            btn.querySelector('[data-mata]').classList.toggle('hidden', tampil);
            btn.querySelector('[data-mata-coret]').classList.toggle('hidden', !tampil);
        });
    });
</script>
</body>
</html>