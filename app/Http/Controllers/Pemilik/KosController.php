<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\FotoKos;
use App\Models\Kos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class KosController extends Controller
{
    private const MAKS_LUAR  = 3;
    private const MAKS_DALAM = 8;

    public function index(Request $request)
    {
    $this->pastikanPemilik();

    $idUser = Auth::id();

    $filter = $request->query('status');
    if (! in_array($filter, ['terverifikasi', 'menunggu', 'ditolak'], true)) {
        $filter = null;
    }

    // Jumlah kos per status, untuk angka di tab
    $hitung = Kos::where('id_user', $idUser)
        ->selectRaw('status_verifikasi, count(*) as jumlah')
        ->groupBy('status_verifikasi')
        ->pluck('jumlah', 'status_verifikasi');

    $daftarKos = Kos::where('id_user', $idUser)
        ->when($filter, fn ($q) => $q->where('status_verifikasi', $filter))
        ->withCount('difavoritkanOleh as total_favorit')
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('pemilik.kos.index', compact('daftarKos', 'hitung', 'filter'));
    }

    public function create()
    {
        $this->pastikanPemilik();

        return view('pemilik.kos.create');
    }

    public function store(Request $request)
    {
        $this->pastikanPemilik();

        $data = $this->validasi($request);

        $kos = Kos::create(array_merge(
            $this->simpanFoto($request, $this->bersihkan($data)),
            ['id_user' => Auth::id(), 'status_verifikasi' => 'menunggu']
        ));

        $this->simpanGaleri($kos, $request);

        return redirect()
            ->route('pemilik.kos.index')
            ->with('status', 'Kos berhasil ditambahkan dan menunggu verifikasi admin.');
    }

    public function edit(Kos $kos)
    {
        $this->pastikanMilik($kos);

        $kos->load('galeri');

        return view('pemilik.kos.edit', compact('kos'));
    }

    public function update(Request $request, Kos $kos)
    {
        $this->pastikanMilik($kos);

        $data  = $this->validasi($request, $kos);
        $hapus = array_map('intval', $data['hapus_foto'] ?? []);

        $this->periksaBatasFoto($request, $kos, $hapus);

        $kos->update($this->simpanFoto($request, $this->bersihkan($data), $kos));
        $this->hapusGaleri($kos, $hapus);
        $this->simpanGaleri($kos, $request);

        // Kos yang ditolak diajukan ulang setelah diperbaiki
        $diajukanUlang = $kos->status_verifikasi === 'ditolak';
        if ($diajukanUlang) {
            $kos->update(['status_verifikasi' => 'menunggu']);
        }

        return redirect()
            ->route('pemilik.kos.index')
            ->with('status', $diajukanUlang
                ? 'Data kos diperbarui dan diajukan ulang untuk diverifikasi.'
                : 'Data kos berhasil diperbarui.');
    }

    private function validasi(Request $request, ?Kos $kos = null): array
    {
        $gambar = ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

        $data = $request->validate([
            'nama_kos'       => ['required', 'string', 'max:100'],
            'jenis_kos'      => ['required', 'in:putra,putri,campur'],
            'harga'          => ['nullable', 'integer', 'min:0', 'max:100000000'],
            'deskripsi'      => ['nullable', 'string', 'max:2000'],
            'ukuran_kamar'   => ['nullable', 'string', 'max:30'],
            'jumlah_kamar'   => ['nullable', 'integer', 'min:1', 'max:1000'],
            'kamar_tersedia' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'listrik'        => ['nullable', Rule::in(array_keys(Kos::LISTRIK))],
            'fasilitas'      => ['nullable', 'array'],
            'fasilitas.*'    => ['string', Rule::in(array_keys(Kos::FASILITAS))],
            'peraturan'      => ['nullable', 'string', 'max:3000'],
            'foto'           => array_merge([$kos ? 'nullable' : 'required'], $gambar),
            'foto_luar'      => ['nullable', 'array', 'max:' . self::MAKS_LUAR],
            'foto_luar.*'    => $gambar,
            'foto_dalam'     => ['nullable', 'array', 'max:' . self::MAKS_DALAM],
            'foto_dalam.*'   => $gambar,
            'ket_dalam'      => ['nullable', 'array'],
            'ket_dalam.*'    => ['nullable', 'string', 'max:100'],
            'hapus_foto'     => ['nullable', 'array'],
            'hapus_foto.*'   => ['integer'],
            'alamat'         => ['required', 'string', 'max:255'],
            'latitude'       => ['required', 'numeric', 'between:-90,90'],
            'longitude'      => ['required', 'numeric', 'between:-180,180'],
        ], [
            'foto.required'        => 'Foto tampilan luar wajib diunggah.',
            'foto.image'           => 'File harus berupa gambar.',
            'foto.mimes'           => 'Format foto harus JPG, PNG, atau WEBP.',
            'foto.max'             => 'Ukuran foto maksimal 2 MB.',
            'foto_luar.max'        => 'Foto luar tambahan maksimal ' . self::MAKS_LUAR . '.',
            'foto_luar.*.image'    => 'Semua foto luar harus berupa gambar.',
            'foto_luar.*.mimes'    => 'Format foto luar harus JPG, PNG, atau WEBP.',
            'foto_luar.*.max'      => 'Ukuran tiap foto maksimal 2 MB.',
            'foto_dalam.max'       => 'Foto detail maksimal ' . self::MAKS_DALAM . '.',
            'foto_dalam.*.image'   => 'Semua foto detail harus berupa gambar.',
            'foto_dalam.*.mimes'   => 'Format foto detail harus JPG, PNG, atau WEBP.',
            'foto_dalam.*.max'     => 'Ukuran tiap foto maksimal 2 MB.',
            'ket_dalam.*.max'      => 'Keterangan foto maksimal 100 karakter.',
            'harga.integer'        => 'Harga harus berupa angka bulat.',
            'latitude.required'    => 'Pilih lokasi kos pada peta.',
            'longitude.required'   => 'Pilih lokasi kos pada peta.',
        ]);

        if (isset($data['jumlah_kamar'], $data['kamar_tersedia'])
            && $data['kamar_tersedia'] > $data['jumlah_kamar']) {
            throw ValidationException::withMessages([
                'kamar_tersedia' => 'Kamar tersedia tidak boleh melebihi jumlah kamar.',
            ]);
        }

        return $data;
    }

    // Total foto setelah dihapus dan ditambah tidak boleh melebihi batas
    private function periksaBatasFoto(Request $request, Kos $kos, array $hapus): void
    {
        $sisa = fn (string $kategori) => FotoKos::where('id_kos', $kos->id_kos)
            ->where('kategori', $kategori)
            ->whereNotIn('id_foto', $hapus)
            ->count();

        if ($sisa('luar') + count($request->file('foto_luar', [])) > self::MAKS_LUAR) {
            throw ValidationException::withMessages([
                'foto_luar' => 'Total foto luar tambahan maksimal ' . self::MAKS_LUAR . '.',
            ]);
        }

        if ($sisa('dalam') + count($request->file('foto_dalam', [])) > self::MAKS_DALAM) {
            throw ValidationException::withMessages([
                'foto_dalam' => 'Total foto detail maksimal ' . self::MAKS_DALAM . '.',
            ]);
        }
    }

    // Buang field yang bukan kolom tabel kos
    private function bersihkan(array $data): array
    {
        $data['fasilitas'] = array_values($data['fasilitas'] ?? []);

        unset($data['foto_luar'], $data['foto_dalam'], $data['ket_dalam'], $data['hapus_foto']);

        return $data;
    }

    // Foto utama: simpan yang baru, hapus yang lama kalau diganti
    private function simpanFoto(Request $request, array $data, ?Kos $kos = null): array
    {
        unset($data['foto']);

        if ($request->hasFile('foto')) {
            if ($kos?->foto) {
                Storage::disk('public')->delete($kos->foto);
            }
            $data['foto'] = $request->file('foto')->store('kos', 'public');
        }

        return $data;
    }

    private function simpanGaleri(Kos $kos, Request $request): void
    {
        $urutan = (int) FotoKos::where('id_kos', $kos->id_kos)->max('urutan');

        foreach ($request->file('foto_luar', []) as $file) {
            $kos->galeri()->create([
                'path'     => $file->store('kos/galeri', 'public'),
                'kategori' => 'luar',
                'urutan'   => ++$urutan,
            ]);
        }

        $keterangan = $request->input('ket_dalam', []);

        foreach ($request->file('foto_dalam', []) as $i => $file) {
            $kos->galeri()->create([
                'path'       => $file->store('kos/galeri', 'public'),
                'kategori'   => 'dalam',
                'keterangan' => trim((string) ($keterangan[$i] ?? '')) ?: null,
                'urutan'     => ++$urutan,
            ]);
        }
    }

    // Hanya foto milik kos ini yang bisa terhapus
    private function hapusGaleri(Kos $kos, array $ids): void
    {
        if (! $ids) {
            return;
        }

        $fotos = FotoKos::where('id_kos', $kos->id_kos)->whereIn('id_foto', $ids)->get();

        foreach ($fotos as $foto) {
            Storage::disk('public')->delete($foto->path);
            $foto->delete();
        }
    }

    private function pastikanPemilik(): void
    {
        abort_unless(Auth::user()->role === 'pemilik_kos', 403);
    }

    private function pastikanMilik(Kos $kos): void
    {
        $this->pastikanPemilik();
        abort_unless((int) $kos->id_user === (int) Auth::id(), 403);
    }

    // Ubah jumlah kamar tersedia langsung dari dashboard
    public function ubahKamar(Request $request, Kos $kos)
    {
        $this->pastikanMilik($kos);

        $jumlah = filter_var($request->input('kamar_tersedia'), FILTER_VALIDATE_INT);

        if ($jumlah === false || $jumlah < 0 || $jumlah > 1000) {
            return back()->with('galat', 'Isi kamar tersedia dengan angka bulat 0 atau lebih.');
        }

        if ($kos->jumlah_kamar !== null && $jumlah > $kos->jumlah_kamar) {
            return back()->with('galat', 'Kamar tersedia ' . $kos->nama_kos . ' tidak boleh melebihi jumlah kamar (' . $kos->jumlah_kamar . ').');
        }

        $kos->update(['kamar_tersedia' => $jumlah]);

        return redirect(url()->previous() . '#kos-' . $kos->id_kos)
            ->with('status', 'Kamar tersedia ' . $kos->nama_kos . ' diperbarui menjadi ' . $jumlah . '.');
    }

    // Isi nomor telepon pemilik (dipakai tombol WhatsApp di halaman kos)
    public function ubahTelepon(Request $request)
    {
        $this->pastikanPemilik();

        $data = $request->validate([
            'no_telp' => ['required', 'string', 'regex:/^[0-9+\-\s()]{8,20}$/'],
        ], [
            'no_telp.required' => 'Isi nomor telepon.',
            'no_telp.regex'    => 'Nomor telepon harus 8 sampai 20 digit, contoh 081234567890.',
        ]);

        DB::table('users')->where('id_user', Auth::id())->update(['no_telp' => $data['no_telp']]);

        return back()->with('status', 'Nomor telepon disimpan. Tombol WhatsApp sekarang tampil di halaman kosmu.');
    }
}