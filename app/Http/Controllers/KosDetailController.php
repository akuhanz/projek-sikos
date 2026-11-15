<?php

namespace App\Http\Controllers;

use App\Models\Kos;
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

        // Nomor WhatsApp: 08xxxx diubah menjadi 628xxxx
        $wa = null;
        $telp = preg_replace('/\D+/', '', (string) $kos->pemilik?->no_telp);
        if ($telp !== '') {
            $wa = str_starts_with($telp, '0') ? '62' . substr($telp, 1) : $telp;
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
            'kos', 'fotoLuar', 'fotoDalam', 'peraturan', 'wa', 'serupa', 'dashboardUrl', 'dariJenis'
        ));
    }
    public function hubungi(Kos $kos)
    {
        $user = Auth::user();

        // Aturan lihat sama seperti halaman detail
        $boleh = $kos->status_verifikasi === 'terverifikasi'
            || ($user && ($user->role === 'admin' || (int) $kos->id_user === (int) $user->id_user));
        abort_unless($boleh, 404);

        // Belum login: simpan halaman detail sebagai tujuan setelah login
        if (! $user) {
            session(['url.intended' => route('kos.show', $kos)]);

            return redirect()->route('login')
                ->with('status', 'Silakan login dulu untuk menghubungi pemilik kos.');
        }

        // 08xxxx diubah menjadi 628xxxx
        $telp = preg_replace('/\D+/', '', (string) $kos->pemilik?->no_telp);
        abort_if($telp === '', 404);
        $wa = str_starts_with($telp, '0') ? '62' . substr($telp, 1) : $telp;

        $pesan = 'Halo, saya tertarik dengan ' . $kos->nama_kos . '. Apakah masih ada kamar kosong?';

        return redirect()->away('https://wa.me/' . $wa . '?text=' . rawurlencode($pesan));
    }
}