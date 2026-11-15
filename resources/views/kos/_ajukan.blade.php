@php
    $pengguna   = auth()->user();
    $penuh      = ($tersedia ?? null) === 0;
    $kelasInput = 'h-12 w-full rounded-lg border border-neutral-300 bg-white px-3 text-sm outline-none focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900';
    $kelasLabel = 'mb-1 block text-xs font-medium text-neutral-600';
@endphp

<div id="ajukan" class="scroll-mt-40 rounded-2xl border border-neutral-300 bg-white p-5 shadow-sm">

    {{-- Harga --}}
    @if ($kos->harga)
        <p class="text-3xl font-bold tracking-tight">
            Rp {{ number_format($kos->harga, 0, ',', '.') }}
            <span class="text-sm font-normal text-neutral-600">/ bulan</span>
        </p>
    @else
        <p class="text-xl font-bold">Harga belum dicantumkan</p>
        <p class="mt-1 text-sm text-neutral-600">Ajukan sewa untuk membahas harga dengan pemilik.</p>
    @endif

    @if (session('status'))
        <p class="mt-4 rounded-lg border border-neutral-300 bg-neutral-50 px-4 py-3 text-sm font-medium" role="status">{{ session('status') }}</p>
    @endif

    @if (session('galat'))
        <p class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800" role="alert">{{ session('galat') }}</p>
    @endif

    @if (! $terverifikasi)
        <p class="mt-4 rounded-lg bg-neutral-100 px-4 py-3 text-sm text-neutral-700">Pengajuan sewa dibuka setelah kos ini diverifikasi admin.</p>

    @elseif (! $pengguna)
        <a href="{{ route('kos.ajukan', $kos) }}"
           class="mt-4 flex h-12 items-center justify-center rounded-full bg-neutral-900 px-5 text-sm font-semibold text-white hover:bg-black">
            Ajukan Sewa
        </a>
        <p class="mt-3 text-center text-xs text-neutral-600">
            Login dulu untuk mengajukan sewa. Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold underline underline-offset-2">Daftar</a>
        </p>

    @elseif ($pengguna->role !== 'pencari_kos')
        <p class="mt-4 rounded-lg bg-neutral-100 px-4 py-3 text-sm text-neutral-700">Pengajuan sewa hanya bisa dibuat dengan akun pencari kos.</p>

    @elseif ($pengajuan?->status === 'diterima')
        @php $linkWa = $pengajuan->linkWa(); @endphp

        <div class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 p-4">
            <p class="text-sm font-semibold text-emerald-900">Pengajuan sewamu diterima</p>
            <p class="mt-1 text-[13px] text-emerald-950">
                Mulai {{ $pengajuan->tanggalMasukIndo() }} &middot; {{ $pengajuan->labelDurasi() }} &middot; {{ $pengajuan->jumlah_penghuni }} penghuni
            </p>

            @if ($pengajuan->catatan_pemilik)
                <p class="mt-3 text-xs font-medium text-emerald-900">Pesan dari pemilik</p>
                <p class="mt-0.5 whitespace-pre-line break-words text-sm text-emerald-950">{{ $pengajuan->catatan_pemilik }}</p>
            @endif

            @if ($linkWa)
                <p class="mt-3 text-[13px] text-emerald-950">
                    Lanjutkan dengan {{ $kos->pemilik?->nama_lengkap ?? 'pemilik' }} untuk jadwal survei, pembayaran, dan serah terima kamar.
                </p>
                <a href="{{ $linkWa }}" target="_blank" rel="noopener"
                   class="mt-3 flex h-12 items-center justify-center rounded-full bg-neutral-900 px-5 text-sm font-semibold text-white hover:bg-black">
                    Hubungi via WhatsApp
                </a>
                <p class="mt-2 text-center text-xs text-emerald-900">+{{ $pengajuan->nomorPemilik() }}</p>
            @else
                <p class="mt-3 rounded-lg bg-white px-3 py-2 text-[13px] text-neutral-700">Pemilik belum mencantumkan nomor telepon. Coba lagi nanti.</p>
            @endif
        </div>

    @elseif ($pengajuan?->status === 'menunggu')
        <div class="mt-4 rounded-xl border border-amber-300 bg-amber-50 p-4">
            <p class="text-sm font-semibold text-amber-900">Pengajuan menunggu respons pemilik</p>
            <p class="mt-1 text-[13px] text-amber-950">
                Mulai {{ $pengajuan->tanggalMasukIndo() }} &middot; {{ $pengajuan->labelDurasi() }} &middot; {{ $pengajuan->jumlah_penghuni }} penghuni
            </p>
            <p class="mt-2 text-xs text-amber-900">Kontak pemilik tampil di sini setelah pengajuanmu diterima.</p>

            <div class="mt-3 flex flex-wrap items-center gap-3">
                <form action="{{ route('pengajuan.batal', $pengajuan) }}" method="POST"
                      onsubmit="return confirm('Batalkan pengajuan sewa ini?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="rounded-full border border-amber-700 px-4 py-2 text-[13px] font-semibold text-amber-900 hover:bg-amber-100">
                        Batalkan pengajuan
                    </button>
                </form>
                <a href="{{ route('pengajuan.index') }}" class="text-[13px] font-semibold underline underline-offset-2">Semua pengajuanku</a>
            </div>
        </div>

    @elseif ($penuh)
        <p class="mt-4 rounded-lg bg-neutral-100 px-4 py-3 text-sm font-medium text-neutral-800">
            Kamar di kos ini sedang penuh, jadi pengajuan sewa belum dibuka. Simpan ke favorit untuk dicek lagi nanti.
        </p>

    @else
        @if ($pengajuan && in_array($pengajuan->status, ['ditolak', 'dibatalkan'], true))
            <div class="mt-4 rounded-lg border border-neutral-300 bg-neutral-50 px-4 py-3 text-[13px] text-neutral-800">
                Pengajuan sebelumnya {{ $pengajuan->status === 'ditolak' ? 'ditolak' : 'dibatalkan' }}.
                @if ($pengajuan->status === 'ditolak' && $pengajuan->catatan_pemilik)
                    Alasan: {{ $pengajuan->catatan_pemilik }}
                @endif
                Kamu bisa mengajukan lagi.
            </div>
        @endif

        <form action="{{ route('pengajuan.store', $kos) }}" method="POST" class="mt-4">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="tanggal_masuk" class="{{ $kelasLabel }}">Mulai kos</label>
                    <input type="date" id="tanggal_masuk" name="tanggal_masuk" value="{{ old('tanggal_masuk') }}"
                           min="{{ now()->toDateString() }}" max="{{ now()->addYear()->toDateString() }}" required
                           class="{{ $kelasInput }}">
                </div>

                <div>
                    <label for="durasi_bulan" class="{{ $kelasLabel }}">Lama sewa</label>
                    <select id="durasi_bulan" name="durasi_bulan" required class="{{ $kelasInput }}">
                        @foreach (\App\Models\PengajuanSewa::DURASI as $bulan => $teks)
                            <option value="{{ $bulan }}" {{ (int) old('durasi_bulan', 6) === $bulan ? 'selected' : '' }}>{{ $teks }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <label for="jumlah_penghuni" class="{{ $kelasLabel }}">Jumlah penghuni</label>
                <input type="number" id="jumlah_penghuni" name="jumlah_penghuni" min="1" max="10"
                       value="{{ old('jumlah_penghuni', 1) }}" required class="{{ $kelasInput }}">
            </div>

            @foreach (['tanggal_masuk', 'durasi_bulan', 'jumlah_penghuni'] as $kolom)
                @error($kolom)
                    <p class="mt-1.5 text-xs text-red-600" role="alert">{{ $message }}</p>
                @enderror
            @endforeach

            <button type="submit"
                    class="mt-4 flex h-12 w-full items-center justify-center rounded-full bg-neutral-900 px-5 text-sm font-semibold text-white hover:bg-black">
                Ajukan Sewa
            </button>

            <p class="mt-3 text-center text-xs text-neutral-600">Kontak pemilik tampil setelah pengajuanmu diterima.</p>
        </form>
    @endif
</div>

@if ($errors->any() || session('status') || session('galat'))
    <script>document.getElementById('ajukan')?.scrollIntoView({ block: 'center' });</script>
@endif