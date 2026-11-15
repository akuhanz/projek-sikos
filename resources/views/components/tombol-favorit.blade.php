@props(['kos', 'ukuran' => 'kecil', 'hapusKartu' => false])

@php
    $user  = auth()->user();
    $besar = $ukuran === 'besar';
    $kelas = 'group/fav flex items-center justify-center rounded-full border border-neutral-200 bg-white/95 shadow-sm backdrop-blur transition hover:scale-105 hover:border-neutral-900 '
           . ($besar ? 'h-11 w-11' : 'h-9 w-9');
    $ikon  = ($besar ? 'h-5 w-5' : 'h-[18px] w-[18px]')
           . ' fill-none stroke-neutral-900 transition group-data-[aktif=true]/fav:fill-rose-500 group-data-[aktif=true]/fav:stroke-rose-500';
    $path  = 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z';
@endphp

@if (! $user)
    <a href="{{ route('login') }}" title="Masuk untuk menyimpan kos favorit" aria-label="Masuk untuk menyimpan kos favorit"
       class="{{ $kelas }}">
        <svg xmlns="http://www.w3.org/2000/svg" class="{{ $ikon }}" viewBox="0 0 24 24" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
        </svg>
    </a>
@elseif ($user->role === 'pencari_kos')
    @php $aktif = $user->sudahFavorit((int) $kos->id_kos); @endphp
    <form action="{{ route('favorites.toggle', $kos) }}" method="POST" data-favorit
          @if ($hapusKartu) data-hapus-kartu @endif>
        @csrf
        <button type="submit" data-aktif="{{ $aktif ? 'true' : 'false' }}" aria-pressed="{{ $aktif ? 'true' : 'false' }}"
                aria-label="{{ $aktif ? 'Hapus dari favorit' : 'Simpan ke favorit' }}"
                title="{{ $aktif ? 'Hapus dari favorit' : 'Simpan ke favorit' }}"
                class="{{ $kelas }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="{{ $ikon }}" viewBox="0 0 24 24" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $path }}" />
            </svg>
        </button>
    </form>
@endif