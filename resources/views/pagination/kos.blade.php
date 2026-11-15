@if ($paginator->hasPages())
    <nav class="flex flex-wrap items-center justify-center gap-1.5" aria-label="Navigasi halaman">

        {{-- Sebelumnya --}}
        @if ($paginator->onFirstPage())
            <span class="flex h-10 min-w-10 cursor-not-allowed items-center justify-center rounded-full border border-brand-200 px-3 text-sm text-brand-900/30">&larr;</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               class="flex h-10 min-w-10 items-center justify-center rounded-full border border-brand-200 px-3 text-sm font-medium hover:border-brand-600 hover:text-brand-600">&larr;</a>
        @endif

        {{-- Nomor halaman --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-2 text-brand-900/40">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                              class="flex h-10 min-w-10 items-center justify-center rounded-full bg-brand-600 px-3 text-sm font-semibold text-white shadow-lg shadow-brand-600/30">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}"
                           class="flex h-10 min-w-10 items-center justify-center rounded-full border border-brand-200 px-3 text-sm font-medium hover:border-brand-600 hover:text-brand-600">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Berikutnya --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               class="flex h-10 min-w-10 items-center justify-center rounded-full border border-brand-200 px-3 text-sm font-medium hover:border-brand-600 hover:text-brand-600">&rarr;</a>
        @else
            <span class="flex h-10 min-w-10 cursor-not-allowed items-center justify-center rounded-full border border-brand-200 px-3 text-sm text-brand-900/30">&rarr;</span>
        @endif
    </nav>
@endif