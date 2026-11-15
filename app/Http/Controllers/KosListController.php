<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KosListController extends Controller
{
    private const PER_HALAMAN = 20;

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $jenis = $request->query('jenis');
        $jenis = in_array($jenis, ['putra', 'putri', 'campur'], true) ? $jenis : null;

        $min  = $this->angka($request->query('harga_min'));
        $maks = $this->angka($request->query('harga_maks'));

        // Kalau terbalik (min lebih besar dari maks), tukar saja
        if ($min !== null && $maks !== null && $min > $maks) {
            [$min, $maks] = [$maks, $min];
        }

        $daftarKos = Kos::query()
            ->where('status_verifikasi', 'terverifikasi')
            ->when($q !== '', fn ($w) => $w->where(
                fn ($s) => $s->where('nama_kos', 'like', "%{$q}%")
                             ->orWhere('alamat', 'like', "%{$q}%")
            ))
            ->when($jenis, fn ($w) => $w->where('jenis_kos', $jenis))
            ->when($min !== null, fn ($w) => $w->where('harga', '>=', $min))
            ->when($maks !== null, fn ($w) => $w->where('harga', '<=', $maks))
            ->latest()
            ->paginate(self::PER_HALAMAN)
            ->withQueryString()
            ->fragment('daftar-kos');

        $filterAktif = $q !== '' || $jenis || $min !== null || $maks !== null;

        $dashboardUrl = null;
        if (Auth::check()) {
            $dashboardUrl = match (Auth::user()->role) {
                'admin'       => '/admin/dashboard',
                'pemilik_kos' => '/pemilik/dashboard',
                default       => '/pencari/dashboard',
            };
        }

        return view('kos.index', compact('daftarKos', 'dashboardUrl', 'q', 'jenis', 'min', 'maks', 'filterAktif'));
    }

    // Hanya terima angka bulat positif, selain itu dianggap kosong
    private function angka(mixed $nilai): ?int
    {
        if (! is_string($nilai) || ! ctype_digit($nilai) || strlen($nilai) > 9) {
            return null;
        }

        return (int) $nilai;
    }
}