<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title') - Sikos @else Sikos - Cari Kos Lebih Mudah @endif</title>
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="@yield('body-class', 'bg-white text-neutral-900 antialiased')">

    @yield('latar')

    @include('partials.navbar')

    @hasSection('lebar')
        <main class="mx-auto @yield('lebar') px-4 py-8">
            @if (session('status'))
                <div class="mx-auto mb-6 max-w-5xl rounded-xl border border-neutral-300 bg-neutral-50 px-4 py-3 text-sm font-medium">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </main>
    @else
        @yield('content')
    @endif

    @stack('scripts')
</body>
</html>