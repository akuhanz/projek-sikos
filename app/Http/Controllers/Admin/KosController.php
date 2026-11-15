<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\PengajuanSewa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class KosController extends Controller
{
    public function index(Request $request)
    {
        $idPemilik = ctype_digit((string) $request->query('pemilik')) ? (int) $request->query('pemilik') : null;

        // Dari halaman pengguna (filter pemilik) tampilkan semua status, selain itu mulai dari antrean
        $status = $request->query('status', $idPemilik ? 'semua' : 'menunggu');
        $status = in_array($status, ['menunggu', 'terverifikasi', 'ditolak', 'semua'], true) ? $status : 'menunggu';

        $q    = trim((string) $request->query('q', ''));
        $like = '%' . addcslashes($q, '%_\\') . '%';

        $jenis = $request->query('jenis');
        $jenis = in_array($jenis, ['putra', 'putri', 'campur'], true) ? $jenis : null;

        $hitung = Kos::when($idPemilik, fn ($w) => $w->where('id_user', $idPemilik))
            ->selectRaw('status_verifikasi, count(*) as jumlah')
            ->groupBy('status_verifikasi')
            ->pluck('jumlah', 'status_verifikasi');

        $daftar = Kos::with('pemilik:id_user,nama_lengkap')
            ->when($status !== 'semua', fn ($w) => $w->where('status_verifikasi', $status))
            ->when($q !== '', fn ($w) => $w->where(fn ($s) => $s
                ->where('nama_kos', 'like', $like)
                ->orWhere('alamat', 'like', $like)
                ->orWhereHas('pemilik', fn ($p) => $p->where('nama_lengkap', 'like', $like))))
            ->when($jenis, fn ($w) => $w->where('jenis_kos', $jenis))
            ->when($idPemilik, fn ($w) => $w->where('id_user', $idPemilik))
            ->when($status === 'menunggu', fn ($w) => $w->oldest(), fn ($w) => $w->latest())
            ->paginate(15)
            ->withQueryString();

        $namaPemilik = $idPemilik ? User::whereKey($idPemilik)->value('nama_lengkap') : null;

        return view('admin.kos.index', compact('daftar', 'hitung', 'status', 'q', 'jenis', 'idPemilik', 'namaPemilik'));
    }

    public function show(Kos $kos)
    {
        $kos->load(['pemilik', 'galeri']);

        $pengajuanAktif = PengajuanSewa::where('id_kos', $kos->id_kos)
            ->whereIn('status', ['menunggu', 'diterima'])
            ->count();

        $totalFavorit = $kos->difavoritkanOleh()->count();

        return view('admin.kos.show', compact('kos', 'pengajuanAktif', 'totalFavorit'));
    }

    public function verifikasi(Request $request, Kos $kos)
    {
        $data = $request->validate([
            'aksi'               => ['required', Rule::in(['setujui', 'tolak'])],
            'catatan_verifikasi' => ['required_if:aksi,tolak', 'nullable', 'string', 'min:5', 'max:500'],
        ], [
            'catatan_verifikasi.required_if' => 'Tulis alasan penolakan supaya pemilik tahu apa yang harus diperbaiki.',
            'catatan_verifikasi.min'         => 'Alasan penolakan minimal 5 karakter.',
            'catatan_verifikasi.max'         => 'Alasan penolakan maksimal 500 karakter.',
        ]);

        if ($data['aksi'] === 'setujui') {
            if ($kos->status_verifikasi === 'terverifikasi') {
                return back()->with('galat', 'Kos ini sudah tayang di situs.');
            }

            $kos->update([
                'status_verifikasi'  => 'terverifikasi',
                'catatan_verifikasi' => null,
                'diverifikasi_pada'  => now(),
            ]);

            return back()->with('status', $kos->nama_kos . ' disetujui dan sekarang tayang di situs.');
        }

        $dicabut = $kos->status_verifikasi === 'terverifikasi';

        $kos->update([
            'status_verifikasi'  => 'ditolak',
            'catatan_verifikasi' => trim($data['catatan_verifikasi']),
            'diverifikasi_pada'  => now(),
        ]);

        return back()->with('status', $kos->nama_kos . ($dicabut
            ? ' dicabut dari situs. Pemilik bisa memperbaiki dan mengajukan ulang.'
            : ' ditolak. Pemilik bisa memperbaiki dan mengajukan ulang.'));
    }

    public function destroy(Kos $kos)
    {
        $nama   = $kos->nama_kos;
        $berkas = $kos->daftarBerkas();

        // Foto, favorit, dan pengajuan ikut terhapus lewat cascade database
        DB::transaction(fn () => $kos->delete());

        Storage::disk('public')->delete($berkas);

        return redirect()
            ->route('admin.kos.index', ['status' => 'semua'])
            ->with('status', 'Kos ' . $nama . ' dihapus.');
    }
}