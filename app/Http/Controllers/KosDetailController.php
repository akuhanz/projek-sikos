<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use App\Models\PengajuanSewa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KosDetailController extends Controller
{
    public function show(Request $request, Kos $kos)
    {
        $user = Auth::user(); // null kalau belum login

        // Kos yang belum terverifikasi hanya boleh dilihat pemiliknya dan admin
        $boleh = $kos->status_verifikasi === 'terverifikasi'
            || ($user && ($user->role === 'admin' || (int) $kos->id_user === (int) $user->id_user));

        abort_unless($boleh, 404);

        $kos->load(['pemilik', 'galeri']);

        // Filter tipe dari halaman daftar, dipakai untuk breadcrumb.
        // Hanya dipakai kalau sama dengan tipe kos ini, supaya URL buatan tidak menyesatkan.
        $dariJenis = $request->query('jenis') === $kos->jenis_kos ? $kos->jenis_kos : null;

        // Tampilan luar: foto utama + foto luar tambahan
        $fotoLuar = collect([$kos->foto_url])
            ->merge($kos->galeri->where('kategori', 'luar')->map(fn ($g) => $g->url))
            ->filter()
            ->values();

        // Foto detail dalam kos, lengkap dengan keterangan
        $fotoDalam = $kos->galeri
            ->where('kategori', 'dalam')
            ->map(fn ($g) => ['url' => $g->url, 'ket' => $g->keterangan])
            ->values();

        // Peraturan: satu aturan per baris
        $peraturan = collect(preg_split('/\R/u', (string) $kos->peraturan) ?: [])
            ->map(fn ($baris) => trim($baris))
            ->filter()
            ->values();

        // Pengajuan milik pencari yang sedang login: untuk kos ini, dan yang masih aktif di kos lain
        $pengajuan = null;
        $aktifLain = null;

        if ($user?->role === 'pencari_kos') {
            $milikku = PengajuanSewa::with('kos:id_kos,nama_kos')
                ->where('id_user', $user->id_user)
                ->where(fn ($q) => $q->where('id_kos', $kos->id_kos)->orWhereIn('status', ['menunggu', 'diterima']))
                ->orderByDesc('id_pengajuan')
                ->get();

            // Yang terbaru untuk kos ini
            $pengajuan = $milikku->first(fn ($p) => (int) $p->id_kos === (int) $kos->id_kos);

            // Yang masih aktif di kos lain (menghalangi pengajuan baru)
            $aktifLain = $milikku->first(fn ($p) => (int) $p->id_kos !== (int) $kos->id_kos
                && in_array($p->status, ['menunggu', 'diterima'], true));

            // Pemilik sudah dimuat di $kos, jadi tidak perlu query lagi
            $pengajuan?->setRelation('kos', $kos);
        }

        $serupa = Kos::where('status_verifikasi', 'terverifikasi')
            ->where('id_kos', '!=', $kos->id_kos)
            ->where('jenis_kos', $kos->jenis_kos)
            ->latest()
            ->take(4)
            ->get();

        // Hanya ada kalau sudah login
        $dashboardUrl = $user ? match ($user->role) {
            'admin'       => '/admin/dashboard',
            'pemilik_kos' => '/pemilik/dashboard',
            default       => '/pencari/dashboard',
        } : null;

        return view('kos.show', compact(
            'kos', 'fotoLuar', 'fotoDalam', 'peraturan', 'pengajuan', 'aktifLain', 'serupa', 'dashboardUrl', 'dariJenis'
        ));
    }

    // Tombol "Ajukan Sewa" untuk tamu: diarahkan ke login lalu kembali ke formnya
    public function ajukan(Kos $kos)
    {
        abort_unless($kos->status_verifikasi === 'terverifikasi', 404);

        $tujuan = route('kos.show', $kos) . '#ajukan';

        if (! Auth::check()) {
            session(['url.intended' => $tujuan]);

            return redirect()->route('login')
                ->with('status', 'Silakan login dulu untuk mengajukan sewa.');
        }

        return redirect()->to($tujuan);
    }
}