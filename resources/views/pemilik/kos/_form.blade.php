@php
    $input   = 'w-full rounded-lg border border-neutral-300 bg-white px-4 py-3 text-sm outline-none placeholder:text-neutral-400 focus:border-neutral-900 focus:ring-1 focus:ring-neutral-900';
    $label   = 'mb-2 block text-sm font-medium';
    $sub     = 'mb-3 text-sm font-semibold';
    $catatan = 'mt-1.5 text-xs text-neutral-600';
    $berkas  = 'file:mr-3 file:rounded-full file:border-0 file:bg-neutral-100 file:px-4 file:py-1.5 file:text-xs file:font-semibold file:text-neutral-900';
    $chip    = 'rounded-lg border border-neutral-300 px-3 py-2.5 text-center text-[13px] text-neutral-700 transition peer-checked:border-neutral-900 peer-checked:bg-neutral-900 peer-checked:font-medium peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-neutral-900 peer-focus-visible:ring-offset-2';

    $nama      = old('nama_kos', $kos->nama_kos ?? '');
    $jenis     = old('jenis_kos', $kos->jenis_kos ?? '');
    $harga     = old('harga', $kos->harga ?? '');
    $deskripsi = old('deskripsi', $kos->deskripsi ?? '');
    $ukuran    = old('ukuran_kamar', $kos->ukuran_kamar ?? '');
    $jumlah    = old('jumlah_kamar', $kos->jumlah_kamar ?? '');
    $tersedia  = old('kamar_tersedia', $kos->kamar_tersedia ?? '');
    $listrik   = old('listrik', $kos->listrik ?? '');
    $peraturan = old('peraturan', $kos->peraturan ?? '');
    $alamat    = old('alamat', $kos->alamat ?? '');
    $lat       = old('latitude', $kos->latitude ?? '');
    $lng       = old('longitude', $kos->longitude ?? '');

    $fasilitasDipilih = old('fasilitas', $kos->fasilitas ?? []);

    $semuaFoto     = $kos?->galeri ?? collect();
    $fotoLuarLama  = $semuaFoto->where('kategori', 'luar');
    $fotoDalamLama = $semuaFoto->where('kategori', 'dalam');

    // Pesan error pertama yang kuncinya diawali teks tertentu (mendukung kolom array)
    $pesan = function (string $awalan) use ($errors) {
        foreach ($errors->keys() as $kunci) {
            if (\Illuminate\Support\Str::startsWith($kunci, $awalan)) {
                return $errors->first($kunci);
            }
        }

        return null;
    };

    $adaError = fn (array $awalan) => collect($errors->keys())
        ->contains(fn ($k) => \Illuminate\Support\Str::startsWith($k, $awalan));

    // Bagian yang punya error dari server
    $galatBagian = array_values(array_filter([
        $adaError(['nama_kos', 'jenis_kos', 'harga', 'deskripsi']) ? 1 : null,
        $adaError(['ukuran_kamar', 'jumlah_kamar', 'kamar_tersedia', 'listrik', 'fasilitas', 'peraturan']) ? 2 : null,
        $adaError(['foto', 'ket_dalam', 'hapus_foto']) ? 3 : null,
        $adaError(['alamat', 'latitude', 'longitude']) ? 4 : null,
    ]));

    $langkahAwal = $galatBagian[0] ?? 1;

    // [nama bagian, keterangan singkat]
    $langkah = [
        1 => ['Informasi dasar', 'Nama, jenis, harga, dan deskripsi kos.'],
        2 => ['Kamar dan fasilitas', 'Ukuran, jumlah kamar, fasilitas, dan peraturan.'],
        3 => ['Foto', 'Foto luar dan foto detail bagian dalam kos.'],
        4 => ['Alamat dan lokasi', 'Alamat lengkap dan titik kos di peta.'],
        5 => ['Ringkasan', 'Periksa semua data sebelum disimpan.'],
    ];

    $kelompokFasilitas = [
        'Fasilitas kamar'       => \App\Models\Kos::FASILITAS_KAMAR,
        'Fasilitas kamar mandi' => \App\Models\Kos::FASILITAS_KAMAR_MANDI,
        'Fasilitas umum'        => \App\Models\Kos::FASILITAS_UMUM,
    ];

    // Baris ringkasan: kunci => [teks, nomor bagian]
    $barisRingkasan = [
        'nama'      => ['Nama kos', 1],
        'jenis'     => ['Jenis kos', 1],
        'harga'     => ['Harga', 1],
        'deskripsi' => ['Deskripsi', 1],
        'kamar'     => ['Kamar', 2],
        'fasilitas' => ['Fasilitas', 2],
        'peraturan' => ['Peraturan', 2],
        'foto'      => ['Foto', 3],
        'alamat'    => ['Alamat', 4],
        'koordinat' => ['Koordinat', 4],
    ];
@endphp

@push('head')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

<div class="mx-auto max-w-5xl">

    {{-- Judul halaman --}}
    <div class="mb-6">
        <a href="{{ route('pemilik.kos.index') }}" class="inline-flex items-center gap-1.5 text-sm text-neutral-600 hover:text-black">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            Kos saya
        </a>
        <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">{{ $kos ? 'Edit kos' : 'Tambah kos' }}</h1>
        <p class="mt-1 text-sm text-neutral-600">
            {{ $kos ? $kos->nama_kos : 'Isi bagian mana saja lebih dulu. Urutannya bebas.' }}
        </p>
    </div>

    <form id="form-kos" method="POST" action="{{ $action }}" enctype="multipart/form-data" novalidate>
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        <div class="lg:grid lg:grid-cols-[240px_1fr] lg:items-start lg:gap-6">

            {{-- DAFTAR BAGIAN --}}
            <div id="nav-bagian" class="scroll-mt-20 lg:sticky lg:top-6">
                <nav aria-label="Bagian formulir"
                     class="-mx-4 flex gap-2 overflow-x-auto px-4 pb-3 lg:mx-0 lg:flex-col lg:gap-1 lg:overflow-visible lg:px-0 lg:pb-0">
                    @foreach ($langkah as $no => [$namaLangkah, $ket])
                        <button type="button" data-step="{{ $no }}" data-state="opsional" data-aktif="false"
                                aria-controls="bagian-{{ $no }}"
                                class="group flex shrink-0 items-center gap-3 rounded-xl border border-transparent px-3 py-2.5 text-left transition hover:bg-white data-[aktif=true]:border-neutral-900 data-[aktif=true]:bg-white">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-neutral-300 text-sm font-semibold text-neutral-500
                                         group-data-[state=lengkap]:border-neutral-900 group-data-[state=lengkap]:bg-neutral-900 group-data-[state=lengkap]:text-white
                                         group-data-[state=terisi]:border-neutral-900 group-data-[state=terisi]:text-neutral-900
                                         group-data-[state=galat]:border-red-500 group-data-[state=galat]:bg-red-50 group-data-[state=galat]:text-red-600">{{ $no }}</span>
                            <span class="min-w-0">
                                <span class="block whitespace-nowrap text-sm font-medium">{{ $namaLangkah }}</span>
                                <span data-label class="hidden text-xs text-neutral-500 group-data-[state=galat]:text-red-600 lg:block">&nbsp;</span>
                            </span>
                        </button>
                    @endforeach
                </nav>

                <p class="mt-4 hidden px-3 text-xs leading-relaxed text-neutral-600 lg:block">
                    Tanda <span class="text-red-600">*</span> wajib diisi sebelum kos bisa disimpan. Bagian lain boleh dikosongkan dulu.
                </p>
            </div>

            {{-- ISI BAGIAN --}}
            <div class="mt-2 rounded-2xl border border-neutral-200 bg-white p-6 lg:mt-0 lg:p-8">

                {{-- 1: INFORMASI DASAR --}}
                <section id="bagian-1" data-panel="1" class="hidden space-y-5">
                    <header class="mb-2">
                        <h2 class="text-xl font-bold tracking-tight">{{ $langkah[1][0] }}</h2>
                        <p class="mt-1 text-sm text-neutral-600">{{ $langkah[1][1] }}</p>
                    </header>

                    <div>
                        <label for="nama_kos" class="{{ $label }}">Nama kos <span class="text-red-600">*</span></label>
                        <input type="text" id="nama_kos" name="nama_kos" value="{{ $nama }}" maxlength="100"
                               placeholder="Contoh: Kos Melati Putri" class="{{ $input }}">
                        <p data-error="nama_kos" class="mt-1 text-xs text-red-600 {{ $errors->has('nama_kos') ? '' : 'hidden' }}">{{ $errors->first('nama_kos') }}</p>
                    </div>

                    <div>
                        <span class="{{ $label }}">Jenis kos <span class="text-red-600">*</span></span>
                        <div class="grid grid-cols-3 gap-3">
                            @foreach (['putra' => 'Putra', 'putri' => 'Putri', 'campur' => 'Campur'] as $val => $teks)
                                <label class="cursor-pointer">
                                    <input type="radio" name="jenis_kos" value="{{ $val }}" class="peer sr-only"
                                           {{ $jenis === $val ? 'checked' : '' }}>
                                    <div class="{{ $chip }} px-4 py-3 text-sm">{{ $teks }}</div>
                                </label>
                            @endforeach
                        </div>
                        <p data-error="jenis_kos" class="mt-1 text-xs text-red-600 {{ $errors->has('jenis_kos') ? '' : 'hidden' }}">{{ $errors->first('jenis_kos') }}</p>
                    </div>

                    <div>
                        <label for="harga" class="{{ $label }}">Harga per bulan (Rp) <span class="font-normal text-neutral-500">(opsional)</span></label>
                        <input type="number" id="harga" name="harga" value="{{ $harga }}" min="0" step="1000"
                               placeholder="Contoh: 850000" class="{{ $input }}">
                        @if ($pesan('harga'))
                            <p class="mt-1 text-xs text-red-600">{{ $pesan('harga') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="deskripsi" class="{{ $label }}">Deskripsi <span class="font-normal text-neutral-500">(opsional)</span></label>
                        <textarea id="deskripsi" name="deskripsi" rows="5" maxlength="2000"
                                  placeholder="Gambaran umum kos, suasana, jarak ke kampus atau kantor, dan lainnya"
                                  class="{{ $input }}">{{ $deskripsi }}</textarea>
                        @if ($pesan('deskripsi'))
                            <p class="mt-1 text-xs text-red-600">{{ $pesan('deskripsi') }}</p>
                        @endif
                    </div>
                </section>

                {{-- 2: KAMAR DAN FASILITAS --}}
                <section id="bagian-2" data-panel="2" class="hidden space-y-8">
                    <header>
                        <h2 class="text-xl font-bold tracking-tight">{{ $langkah[2][0] }}</h2>
                        <p class="mt-1 text-sm text-neutral-600">{{ $langkah[2][1] }}</p>
                    </header>

                    <div>
                        <h3 class="{{ $sub }}">Spesifikasi kamar</h3>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="ukuran_kamar" class="{{ $label }}">Ukuran kamar</label>
                                <input type="text" id="ukuran_kamar" name="ukuran_kamar" value="{{ $ukuran }}" maxlength="30"
                                       placeholder="Contoh: 3 x 4 m" class="{{ $input }}">
                                @if ($pesan('ukuran_kamar'))
                                    <p class="mt-1 text-xs text-red-600">{{ $pesan('ukuran_kamar') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="listrik" class="{{ $label }}">Listrik</label>
                                <select id="listrik" name="listrik" class="{{ $input }}">
                                    <option value="">Belum diatur</option>
                                    @foreach (\App\Models\Kos::LISTRIK as $v => $t)
                                        <option value="{{ $v }}" {{ $listrik === $v ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                                @if ($pesan('listrik'))
                                    <p class="mt-1 text-xs text-red-600">{{ $pesan('listrik') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="jumlah_kamar" class="{{ $label }}">Jumlah kamar</label>
                                <input type="number" id="jumlah_kamar" name="jumlah_kamar" value="{{ $jumlah }}" min="1"
                                       placeholder="Contoh: 12" class="{{ $input }}">
                                @if ($pesan('jumlah_kamar'))
                                    <p class="mt-1 text-xs text-red-600">{{ $pesan('jumlah_kamar') }}</p>
                                @endif
                            </div>

                            <div>
                                <label for="kamar_tersedia" class="{{ $label }}">Kamar yang masih kosong</label>
                                <input type="number" id="kamar_tersedia" name="kamar_tersedia" value="{{ $tersedia }}" min="0"
                                       placeholder="Contoh: 3" class="{{ $input }}">
                                <p data-error="kamar_tersedia" class="mt-1 text-xs text-red-600 {{ $pesan('kamar_tersedia') ? '' : 'hidden' }}">{{ $pesan('kamar_tersedia') }}</p>
                            </div>
                        </div>
                    </div>

                    @foreach ($kelompokFasilitas as $judulGrup => $daftarGrup)
                        <div>
                            <h3 class="{{ $sub }}">{{ $judulGrup }}</h3>

                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @foreach ($daftarGrup as $key => $teks)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" name="fasilitas[]" value="{{ $key }}" class="peer sr-only"
                                               {{ in_array($key, $fasilitasDipilih) ? 'checked' : '' }}>
                                        <div class="{{ $chip }}">{{ $teks }}</div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @if ($pesan('fasilitas'))
                        <p class="text-xs text-red-600">{{ $pesan('fasilitas') }}</p>
                    @endif

                    <div>
                        <h3 class="{{ $sub }}">Peraturan kos</h3>
                        <label for="peraturan" class="{{ $label }}">Tulis satu aturan per baris <span class="font-normal text-neutral-500">(opsional)</span></label>
                        <textarea id="peraturan" name="peraturan" rows="6" maxlength="3000"
                                  placeholder="Jam malam pukul 22.00&#10;Dilarang merokok di dalam kamar&#10;Tamu lawan jenis tidak boleh masuk kamar"
                                  class="{{ $input }}">{{ $peraturan }}</textarea>
                        @if ($pesan('peraturan'))
                            <p class="mt-1 text-xs text-red-600">{{ $pesan('peraturan') }}</p>
                        @endif
                    </div>
                </section>

                {{-- 3: FOTO --}}
                <section id="bagian-3" data-panel="3" class="hidden space-y-8">
                    <header>
                        <h2 class="text-xl font-bold tracking-tight">{{ $langkah[3][0] }}</h2>
                        <p class="mt-1 text-sm text-neutral-600">{{ $langkah[3][1] }}</p>
                    </header>

                    {{-- Foto utama --}}
                    <div>
                        <h3 class="{{ $sub }}">Foto tampilan luar</h3>

                        <span class="{{ $label }}">Foto utama @unless ($kos)<span class="text-red-600">*</span>@endunless</span>

                        <label for="foto"
                               class="relative flex h-52 cursor-pointer items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-neutral-300 bg-neutral-50 transition hover:border-neutral-900">
                            <img id="foto-preview" src="{{ $kos?->foto_url }}" alt="Pratinjau foto kos"
                                 class="absolute inset-0 h-full w-full object-cover {{ $kos?->foto_url ? '' : 'hidden' }}">

                            <div id="foto-placeholder" class="px-4 text-center text-sm text-neutral-600 {{ $kos?->foto_url ? 'hidden' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-9 w-9 text-neutral-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A1.5 1.5 0 0 0 21.75 19.5v-15A1.5 1.5 0 0 0 20.25 3H3.75A1.5 1.5 0 0 0 2.25 4.5v15A1.5 1.5 0 0 0 3.75 21Z" />
                                </svg>
                                <p class="mt-2 font-medium">Klik untuk memilih foto</p>
                            </div>

                            <input type="file" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" class="sr-only">
                        </label>

                        <p class="{{ $catatan }}">Foto bagian depan kos. Dipakai sebagai gambar utama di daftar kos. JPG, PNG, atau WEBP, maksimal 2 MB.@if ($kos) Klik gambar untuk menggantinya.@endif</p>
                        <p data-error="foto" class="mt-1 text-xs text-red-600 {{ $errors->has('foto') ? '' : 'hidden' }}">{{ $errors->first('foto') }}</p>
                    </div>

                    {{-- Foto luar tambahan --}}
                    <div>
                        <span class="{{ $label }}">Foto luar tambahan <span class="font-normal text-neutral-500">(opsional, maksimal 3)</span></span>

                        @if ($fotoLuarLama->count())
                            <p class="mb-2 text-xs text-neutral-600">Klik foto untuk menandai agar dihapus saat disimpan.</p>
                            <div class="mb-3 grid grid-cols-3 gap-3 sm:grid-cols-4">
                                @foreach ($fotoLuarLama as $g)
                                    <label class="relative block cursor-pointer overflow-hidden rounded-lg">
                                        <img src="{{ $g->url }}" alt="" class="h-20 w-full object-cover">
                                        <input type="checkbox" name="hapus_foto[]" value="{{ $g->id_foto }}" data-lama="luar" class="peer sr-only">
                                        <span class="absolute inset-0 hidden items-center justify-center bg-red-700/80 text-[11px] font-semibold text-white peer-checked:flex">Akan dihapus</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <input type="file" name="foto_luar[]" multiple accept="image/jpeg,image/png,image/webp"
                               class="{{ $input }} {{ $berkas }}">

                        @if ($pesan('foto_luar'))
                            <p class="mt-1 text-xs text-red-600">{{ $pesan('foto_luar') }}</p>
                        @endif
                    </div>

                    {{-- Foto detail dalam kos --}}
                    <div>
                        <h3 class="{{ $sub }}">Foto detail dalam kos</h3>
                        <p class="mb-3 text-[13px] leading-relaxed text-neutral-600">
                            Foto kamar, kamar mandi, dapur, ruang bersama, dan fasilitas lainnya. Beri keterangan pada tiap foto supaya jelas.
                            Maksimal 8 foto, sisa <span id="sisa-dalam" class="font-semibold">8</span>.
                        </p>

                        @if ($fotoDalamLama->count())
                            <p class="mb-2 text-xs text-neutral-600">Klik foto untuk menandai agar dihapus saat disimpan.</p>
                            <div class="mb-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
                                @foreach ($fotoDalamLama as $g)
                                    <label class="block cursor-pointer">
                                        <span class="relative block overflow-hidden rounded-lg">
                                            <img src="{{ $g->url }}" alt="" class="h-20 w-full object-cover">
                                            <input type="checkbox" name="hapus_foto[]" value="{{ $g->id_foto }}" data-lama="dalam" class="peer sr-only">
                                            <span class="absolute inset-0 hidden items-center justify-center bg-red-700/80 text-[11px] font-semibold text-white peer-checked:flex">Akan dihapus</span>
                                        </span>
                                        <span class="mt-1 block truncate text-xs text-neutral-600">{{ $g->keterangan ?: 'Tanpa keterangan' }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif

                        <div id="baris-dalam" class="space-y-3"></div>

                        <button type="button" id="tambah-dalam"
                                class="mt-3 rounded-full border border-neutral-300 px-5 py-2.5 text-[13px] font-semibold transition hover:border-neutral-900 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:border-neutral-300">
                            Tambah foto
                        </button>

                        @if ($pesan('foto_dalam'))
                            <p class="mt-2 text-xs text-red-600">{{ $pesan('foto_dalam') }}</p>
                        @endif
                        @if ($pesan('ket_dalam'))
                            <p class="mt-2 text-xs text-red-600">{{ $pesan('ket_dalam') }}</p>
                        @endif
                    </div>

                    <p data-error="foto_lain" class="hidden text-xs text-red-600"></p>

                    <template id="tpl-dalam">
                        <div data-baris class="flex flex-col gap-3 rounded-xl border border-neutral-200 p-3 sm:flex-row sm:items-center">
                            <img data-prev src="" alt="" class="hidden h-20 w-full rounded-lg object-cover sm:w-28">
                            <div class="min-w-0 flex-1 space-y-2">
                                <input type="file" name="foto_dalam[__i__]" accept="image/jpeg,image/png,image/webp"
                                       class="w-full text-sm {{ $berkas }}">
                                <input type="text" name="ket_dalam[__i__]" maxlength="100"
                                       placeholder="Keterangan, contoh: Kamar mandi dalam" class="{{ $input }}">
                            </div>
                            <button type="button" data-hapus-baris
                                    class="rounded-full border border-red-300 px-4 py-2 text-xs font-semibold text-red-700 hover:bg-red-50">Hapus</button>
                        </div>
                    </template>
                </section>

                {{-- 4: ALAMAT DAN LOKASI --}}
                <section id="bagian-4" data-panel="4" class="hidden space-y-6">
                    <header>
                        <h2 class="text-xl font-bold tracking-tight">{{ $langkah[4][0] }}</h2>
                        <p class="mt-1 text-sm text-neutral-600">{{ $langkah[4][1] }}</p>
                    </header>

                    <div>
                        <label for="alamat" class="{{ $label }}">Alamat lengkap <span class="text-red-600">*</span></label>
                        <input type="text" id="alamat" name="alamat" value="{{ $alamat }}" maxlength="255"
                               placeholder="Nama jalan, nomor, kelurahan, kecamatan, kota" class="{{ $input }}">
                        <p data-error="alamat" class="mt-1 text-xs text-red-600 {{ $errors->has('alamat') ? '' : 'hidden' }}">{{ $errors->first('alamat') }}</p>
                    </div>

                    <div>
                        <h3 class="{{ $sub }}">Titik lokasi di peta <span class="text-red-600">*</span></h3>

                        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-xs text-neutral-600">Klik peta untuk menaruh pin, lalu geser pin untuk menyesuaikan.</p>
                            <button type="button" id="btn-lokasi"
                                    class="inline-flex items-center gap-2 rounded-full border border-neutral-300 px-4 py-2 text-xs font-semibold hover:border-neutral-900 disabled:opacity-50">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2m0 14v2M3 12h2m14 0h2M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z" />
                                </svg>
                                <span id="btn-lokasi-teks">Gunakan lokasi saya</span>
                            </button>
                        </div>

                        <div class="isolate overflow-hidden rounded-xl ring-1 ring-neutral-200">
                            <div id="map-kos" class="h-[340px] w-full"></div>
                        </div>

                        <p id="lokasi-status" class="mt-2 text-xs text-neutral-600">Lokasi belum dipilih.</p>
                        <p data-error="latitude" class="mt-1 text-xs text-red-600 {{ $errors->has('latitude') || $errors->has('longitude') ? '' : 'hidden' }}">{{ $errors->first('latitude') ?: $errors->first('longitude') }}</p>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="latitude" class="{{ $label }}">Latitude</label>
                                <input type="text" id="latitude" name="latitude" value="{{ $lat }}" readonly class="{{ $input }} bg-neutral-50">
                            </div>
                            <div>
                                <label for="longitude" class="{{ $label }}">Longitude</label>
                                <input type="text" id="longitude" name="longitude" value="{{ $lng }}" readonly class="{{ $input }} bg-neutral-50">
                            </div>
                        </div>
                    </div>
                </section>

                {{-- 5: RINGKASAN --}}
                <section id="bagian-5" data-panel="5" class="hidden">
                    <header class="mb-6">
                        <h2 class="text-xl font-bold tracking-tight">{{ $langkah[5][0] }}</h2>
                        <p class="mt-1 text-sm text-neutral-600">{{ $langkah[5][1] }}</p>
                    </header>

                    <dl class="divide-y divide-neutral-200 rounded-xl border border-neutral-200">
                        @foreach ($barisRingkasan as $kunci => [$teks, $bagian])
                            <div class="grid gap-1 px-4 py-3 sm:grid-cols-[130px_1fr_auto] sm:items-start sm:gap-4">
                                <dt class="text-sm text-neutral-600">{{ $teks }}</dt>
                                <dd data-ringkas="{{ $kunci }}" class="whitespace-pre-line break-words text-sm font-medium">-</dd>
                                <button type="button" data-ke="{{ $bagian }}"
                                        class="justify-self-start text-[13px] font-semibold underline underline-offset-4 hover:text-black sm:justify-self-end">Ubah</button>
                            </div>
                        @endforeach
                    </dl>

                    @unless ($kos)
                        <p class="mt-4 rounded-lg bg-neutral-100 px-4 py-3 text-xs text-neutral-700">
                            Setelah disimpan, kos diperiksa admin lebih dulu sebelum tampil di halaman publik.
                        </p>
                    @endunless

                    @if ($kos && $kos->status_verifikasi === 'ditolak')
                        <p class="mt-4 rounded-lg bg-neutral-100 px-4 py-3 text-xs text-neutral-700">
                            Kos ini sebelumnya ditolak. Menyimpan perubahan akan mengajukannya kembali untuk diverifikasi.
                        </p>
                    @endif
                </section>
            </div>
        </div>

        {{-- BAR SIMPAN (selalu terlihat) --}}
        <div class="sticky bottom-4 z-20 mt-6 rounded-2xl border border-neutral-200 bg-white p-3 shadow-sm">
            <p id="galat-simpan" role="alert" class="hidden px-2 pb-3 text-sm text-red-700"></p>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-4 px-2">
                    <a href="{{ route('pemilik.kos.index') }}" class="text-sm text-neutral-600 hover:text-black">Batal</a>
                    <span id="hint-kotor" class="hidden text-xs text-neutral-600">Ada perubahan yang belum disimpan</span>
                </div>

                <button type="submit" id="btn-submit"
                        class="rounded-full bg-neutral-900 px-8 py-3 text-sm font-semibold text-white hover:bg-black disabled:opacity-60">
                    {{ $kos ? 'Simpan perubahan' : 'Simpan kos' }}
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const form = document.getElementById('form-kos');
    const panels = [...form.querySelectorAll('[data-panel]')];
    const tabs = [...form.querySelectorAll('[data-step]')];
    const navEl = document.getElementById('nav-bagian');
    const btnSubmit = document.getElementById('btn-submit');
    const labelSimpan = btnSubmit.textContent.trim();
    const galatBox = document.getElementById('galat-simpan');
    const hintKotor = document.getElementById('hint-kotor');
    const latEl = document.getElementById('latitude');
    const lngEl = document.getElementById('longitude');
    const statusEl = document.getElementById('lokasi-status');
    const jenisLabel = { putra: 'Putra', putri: 'Putri', campur: 'Campur' };
    const NAMA_BAGIAN = @json(collect($langkah)->map(fn ($l) => $l[0])->all());
    const BATAS_BYTE = 2 * 1024 * 1024;
    const WAJIB = ['nama', 'jenis', 'foto', 'alamat', 'koordinat'];
    const galat = new Set(@json($galatBagian));

    let kotor = false;
    let mengirim = false;

    const val = name => (form.elements[name]?.value ?? '').trim();

    function setError(name, msg) {
        const p = form.querySelector('[data-error="' + name + '"]');
        if (!p) return;
        p.textContent = msg || '';
        p.classList.toggle('hidden', !msg);
    }

    function tandaiKotor() {
        kotor = true;
        hintKotor.classList.remove('hidden');
    }

    /* ---------- STATUS TIAP BAGIAN ---------- */
    const TEKS_STATUS = {
        lengkap: 'Lengkap',
        wajib: 'Wajib diisi',
        terisi: 'Terisi',
        opsional: 'Opsional',
        galat: 'Perlu diperbaiki',
        periksa: 'Periksa sebelum simpan'
    };

    const status = {
        1: () => (val('nama_kos') && val('jenis_kos')) ? 'lengkap' : 'wajib',
        2: () => (['ukuran_kamar', 'jumlah_kamar', 'kamar_tersedia', 'listrik', 'peraturan'].some(n => val(n))
                  || form.querySelector('input[name="fasilitas[]"]:checked')) ? 'terisi' : 'opsional',
        3: () => (fotoEl.files.length || punyaFotoLama) ? 'lengkap' : 'wajib',
        4: () => (val('alamat') && val('latitude') && val('longitude')) ? 'lengkap' : 'wajib',
        5: () => 'periksa'
    };

    function renderTabs() {
        tabs.forEach(t => {
            const n = Number(t.dataset.step);
            const s = galat.has(n) ? 'galat' : status[n]();
            t.dataset.state = s;
            t.querySelector('[data-label]').textContent = TEKS_STATUS[s];
        });
    }

    /* ---------- FOTO UTAMA ---------- */
    const fotoEl = document.getElementById('foto');
    const fotoPrev = document.getElementById('foto-preview');
    const fotoHold = document.getElementById('foto-placeholder');
    const punyaFotoLama = @json((bool) ($kos?->foto));

    fotoEl.addEventListener('change', () => {
        const f = fotoEl.files[0];
        if (!f) return;

        if (f.size > BATAS_BYTE) {
            fotoEl.value = '';
            setError('foto', 'Ukuran foto maksimal 2 MB.');
            return;
        }

        setError('foto', '');
        fotoPrev.src = URL.createObjectURL(f);
        fotoPrev.classList.remove('hidden');
        fotoHold.classList.add('hidden');
    });

    /* ---------- FOTO LUAR TAMBAHAN DAN FOTO DETAIL ---------- */
    const MAKS_LUAR = 3;
    const MAKS_DALAM = 8;
    const barisDalam = document.getElementById('baris-dalam');
    const tplDalam = document.getElementById('tpl-dalam').innerHTML;
    const btnDalam = document.getElementById('tambah-dalam');
    const sisaDalamEl = document.getElementById('sisa-dalam');
    let idxDalam = 0;

    const jumlahLama = tipe => document.querySelectorAll('input[data-lama="' + tipe + '"]').length;
    const jumlahDihapus = tipe => document.querySelectorAll('input[data-lama="' + tipe + '"]:checked').length;

    const sisaLuar = () => MAKS_LUAR - (jumlahLama('luar') - jumlahDihapus('luar'));
    const sisaDalam = () => MAKS_DALAM - (jumlahLama('dalam') - jumlahDihapus('dalam')) - barisDalam.children.length;

    function aturTombolDalam() {
        const sisa = Math.max(sisaDalam(), 0);
        btnDalam.disabled = sisa <= 0;
        sisaDalamEl.textContent = sisa;
    }

    btnDalam.addEventListener('click', () => {
        if (sisaDalam() <= 0) return;
        barisDalam.insertAdjacentHTML('beforeend', tplDalam.replaceAll('__i__', idxDalam++));
        aturTombolDalam();
        tandaiKotor();
    });

    form.addEventListener('click', e => {
        const hapus = e.target.closest('[data-hapus-baris]');
        if (!hapus) return;

        hapus.closest('[data-baris]').remove();
        setError('foto_lain', '');
        aturTombolDalam();
        tandaiKotor();
    });

    form.addEventListener('change', e => {
        const el = e.target;

        if (el.matches('input[data-lama]')) aturTombolDalam();

        if (el.type !== 'file' || el === fotoEl) return;

        if ([...el.files].some(f => f.size > BATAS_BYTE)) {
            el.value = '';
            setError('foto_lain', 'Ukuran tiap foto maksimal 2 MB.');
            return;
        }

        if (el.name === 'foto_luar[]' && el.files.length > Math.max(sisaLuar(), 0)) {
            el.value = '';
            setError('foto_lain', 'Foto luar tambahan maksimal ' + MAKS_LUAR + ' (sudah termasuk foto yang tersimpan).');
            return;
        }

        setError('foto_lain', '');

        const prev = el.closest('[data-baris]')?.querySelector('[data-prev]');
        if (prev && el.files[0]) {
            prev.src = URL.createObjectURL(el.files[0]);
            prev.classList.remove('hidden');
        }
    });

    aturTombolDalam();

    /* ---------- PETA ---------- */
    const punyaKoordinat = latEl.value !== '' && lngEl.value !== '';

    const map = L.map('map-kos').setView(
        punyaKoordinat ? [parseFloat(latEl.value), parseFloat(lngEl.value)] : [-2.5, 118],
        punyaKoordinat ? 16 : 5
    );

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    let marker = null;

    function setLokasi(lat, lng, zoom, tandai = true) {
        lat = Number(lat);
        lng = Number(lng);

        latEl.value = lat.toFixed(8);
        lngEl.value = lng.toFixed(8);

        if (!marker) {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', () => {
                const p = marker.getLatLng();
                setLokasi(p.lat, p.lng, false);
            });
        } else {
            marker.setLatLng([lat, lng]);
        }

        if (zoom) map.setView([lat, lng], Math.max(map.getZoom(), 17));

        statusEl.textContent = 'Lokasi dipilih: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
        setError('latitude', '');

        if (tandai) tandaiKotor();
        if (galat.has(4)) periksa(4);
        renderTabs();
    }

    map.on('click', e => setLokasi(e.latlng.lat, e.latlng.lng, false));

    if (punyaKoordinat) setLokasi(latEl.value, lngEl.value, false, false);

    const btnLokasi = document.getElementById('btn-lokasi');
    const btnLokasiTeks = document.getElementById('btn-lokasi-teks');

    btnLokasi.addEventListener('click', () => {
        if (!navigator.geolocation) {
            alert('Browser kamu tidak mendukung pengambilan lokasi.');
            return;
        }

        btnLokasi.disabled = true;
        btnLokasiTeks.textContent = 'Mencari lokasi...';

        navigator.geolocation.getCurrentPosition(
            pos => {
                setLokasi(pos.coords.latitude, pos.coords.longitude, true);
                btnLokasi.disabled = false;
                btnLokasiTeks.textContent = 'Gunakan lokasi saya';
            },
            err => {
                const pesan = {
                    1: 'Izin lokasi ditolak. Aktifkan izin lokasi di browser, atau pilih titik langsung di peta.',
                    2: 'Lokasi tidak bisa ditentukan. Pilih titik langsung di peta.',
                    3: 'Pencarian lokasi terlalu lama. Coba lagi atau pilih titik di peta.'
                };
                alert(pesan[err.code] || 'Gagal mengambil lokasi.');
                btnLokasi.disabled = false;
                btnLokasiTeks.textContent = 'Gunakan lokasi saya';
            },
            { enableHighAccuracy: true, timeout: 10000 }
        );
    });

    /* ---------- VALIDASI PER BAGIAN ---------- */
    function periksa(n) {
        let ok = true;

        const perlu = (name, msg) => {
            if (!val(name)) { setError(name, msg); ok = false; }
            else setError(name, '');
        };

        if (n === 1) {
            perlu('nama_kos', 'Nama kos wajib diisi.');
            perlu('jenis_kos', 'Pilih jenis kos.');
        }

        if (n === 2) {
            const jumlah = parseInt(val('jumlah_kamar'), 10);
            const tersedia = parseInt(val('kamar_tersedia'), 10);

            if (!isNaN(jumlah) && !isNaN(tersedia) && tersedia > jumlah) {
                setError('kamar_tersedia', 'Kamar yang kosong tidak boleh melebihi jumlah kamar.');
                ok = false;
            } else {
                setError('kamar_tersedia', '');
            }
        }

        if (n === 3) {
            if (!fotoEl.files.length && !punyaFotoLama) {
                setError('foto', 'Foto tampilan luar wajib diunggah.');
                ok = false;
            } else {
                setError('foto', '');
            }
        }

        if (n === 4) {
            perlu('alamat', 'Alamat wajib diisi.');

            if (!val('latitude') || !val('longitude')) {
                setError('latitude', 'Pilih lokasi kos pada peta.');
                ok = false;
            } else {
                setError('latitude', '');
            }
        }

        if (ok) galat.delete(n); else galat.add(n);

        return ok;
    }

    /* ---------- RINGKASAN ---------- */
    function isiRingkasan() {
        const set = (k, v) => {
            const el = form.querySelector('[data-ringkas="' + k + '"]');
            const kosong = !v;
            el.textContent = v || (WAJIB.includes(k) ? 'Belum diisi' : '-');
            el.classList.toggle('text-red-600', kosong && WAJIB.includes(k));
        };

        const rupiah = n => 'Rp ' + new Intl.NumberFormat('id-ID').format(n);

        set('nama', val('nama_kos'));
        set('jenis', jenisLabel[val('jenis_kos')]);
        set('harga', val('harga') ? rupiah(val('harga')) + ' / bulan' : '');

        set('kamar', [
            val('ukuran_kamar') && 'Ukuran ' + val('ukuran_kamar'),
            val('jumlah_kamar') && val('jumlah_kamar') + ' kamar',
            val('kamar_tersedia') && val('kamar_tersedia') + ' kosong'
        ].filter(Boolean).join(', '));

        const nFasilitas = form.querySelectorAll('input[name="fasilitas[]"]:checked').length;
        set('fasilitas', nFasilitas ? nFasilitas + ' fasilitas dipilih' : '');

        const nAturan = val('peraturan').split(/\r?\n/).filter(s => s.trim()).length;
        set('peraturan', nAturan ? nAturan + ' aturan' : '');

        const luarBaru = form.querySelector('input[name="foto_luar[]"]').files.length;
        const dalamBaru = [...barisDalam.querySelectorAll('input[type="file"]')].filter(i => i.files.length).length;
        const utama = fotoEl.files[0]?.name || (punyaFotoLama ? 'Foto utama dipertahankan' : '');

        set('foto', utama ? utama + (luarBaru || dalamBaru ? ' (+' + luarBaru + ' foto luar, +' + dalamBaru + ' foto detail)' : '') : '');

        set('alamat', val('alamat'));
        set('koordinat', val('latitude') ? val('latitude') + ', ' + val('longitude') : '');
        set('deskripsi', val('deskripsi'));
    }

    /* ---------- BUKA BAGIAN MANA SAJA ---------- */
    const hemat = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const layarKecil = () => window.matchMedia('(max-width: 1023px)').matches;

    function show(n, gulir = true) {
        panels.forEach(p => p.classList.toggle('hidden', Number(p.dataset.panel) !== n));

        tabs.forEach(t => {
            const aktif = Number(t.dataset.step) === n;
            t.dataset.aktif = aktif ? 'true' : 'false';
            if (aktif) t.setAttribute('aria-current', 'step');
            else t.removeAttribute('aria-current');
        });

        if (n === 5) isiRingkasan();

        // Peta di panel tersembunyi perlu dihitung ulang ukurannya
        if (n === 4) {
            setTimeout(() => {
                map.invalidateSize();
                if (marker) map.setView(marker.getLatLng(), Math.max(map.getZoom(), 16));
            }, 50);
        }

        history.replaceState(null, '', '#bagian-' + n);

        if (gulir && layarKecil()) {
            tabs[n - 1].scrollIntoView({ block: 'nearest', inline: 'center' });
            navEl.scrollIntoView({ behavior: hemat ? 'auto' : 'smooth', block: 'start' });
        }
    }

    form.addEventListener('click', e => {
        const tujuan = e.target.closest('[data-ke], [data-step]');
        if (tujuan) show(Number(tujuan.dataset.ke || tujuan.dataset.step));
    });

    /* ---------- PESAN SAAT BELUM BISA DISIMPAN ---------- */
    function tampilGalat(daftar) {
        galatBox.textContent = 'Belum bisa disimpan. Lengkapi ';

        daftar.forEach((n, i) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.dataset.ke = n;
            b.className = 'font-semibold underline underline-offset-4';
            b.textContent = 'bagian ' + n + ' (' + NAMA_BAGIAN[n].toLowerCase() + ')';

            galatBox.append(i === 0 ? '' : (i === daftar.length - 1 ? ' dan ' : ', '), b);
        });

        galatBox.append('.');
        galatBox.classList.remove('hidden');
    }

    /* ---------- EVENT FORM ---------- */
    ['input', 'change'].forEach(jenis => {
        form.addEventListener(jenis, e => {
            const panel = e.target.closest('[data-panel]');
            if (panel) {
                const n = Number(panel.dataset.panel);
                if (galat.has(n)) periksa(n);
                tandaiKotor();
            }
            renderTabs();
        });
    });

    // Enter tidak boleh mengirim form secara tidak sengaja
    form.addEventListener('keydown', e => {
        if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') e.preventDefault();
    });

    form.addEventListener('submit', e => {
        const bermasalah = [1, 2, 3, 4].filter(n => !periksa(n));
        renderTabs();

        if (bermasalah.length) {
            e.preventDefault();
            tampilGalat(bermasalah);
            show(bermasalah[0]);
            return;
        }

        galatBox.classList.add('hidden');
        mengirim = true;
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Menyimpan...';
    });

    window.addEventListener('beforeunload', e => {
        if (kotor && !mengirim) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    window.addEventListener('pageshow', e => {
        if (e.persisted) {
            mengirim = false;
            btnSubmit.disabled = false;
            btnSubmit.textContent = labelSimpan;
        }
    });

    /* ---------- MULAI ---------- */
    const dariHash = (location.hash.match(/^#bagian-([1-5])$/) || [])[1];

    renderTabs();
    show(dariHash ? Number(dariHash) : @json($langkahAwal), false);
</script>
@endpush