@props(['items' => [], 'lebar' => 'max-w-7xl'])

<nav aria-label="Breadcrumb" class="bg-white">
    <ol class="mx-auto flex {{ $lebar }} flex-wrap items-center gap-x-2 gap-y-1 px-4 py-3.5 text-sm">
        @foreach ($items as $i => $item)
            @php
                // Tiap item: [teks, url]. Item tanpa url adalah halaman yang sedang dibuka.
                [$teks, $url] = $item + [1 => null];
            @endphp

            <li class="flex items-center gap-2">
                @if ($i > 0)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                @endif

                @if ($url && ! $loop->last)
                    <a href="{{ $url }}" class="text-neutral-500 transition hover:text-neutral-900 hover:underline">{{ $teks }}</a>
                @else
                    <span aria-current="page" class="break-words font-semibold text-neutral-900">{{ $teks }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>