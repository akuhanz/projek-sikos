<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorit = $request->user()
            ->favoriteKos()
            ->where('status_verifikasi', 'terverifikasi')
            ->orderByPivot('created_at', 'desc')
            ->paginate(12)
            ->withQueryString(); // supaya "dari" tidak hilang di halaman 2 dan seterusnya

        // Hanya terima nilai yang dikenal, selain itu dianggap kosong
        $dari = $request->query('dari') === 'kos' ? 'kos' : null;

        return view('kos.favorit', compact('favorit', 'dari'));
    }

    public function toggle(Request $request, Kos $kos)
    {
        $user = $request->user();

        // Kos belum terverifikasi tidak boleh difavoritkan,
        // tapi kalau sudah terlanjur favorit tetap boleh dihapus.
        $sudahFavorit = $user->favoriteKos()->whereKey($kos->getKey())->exists();
        abort_unless($sudahFavorit || $kos->status_verifikasi === 'terverifikasi', 404);

        $hasil = $user->favoriteKos()->toggle($kos->getKey());
        $aktif = count($hasil['attached']) > 0;

        if ($request->expectsJson()) {
            return response()->json(['favorit' => $aktif]);
        }

        return back()->with('status', $aktif ? 'Kos ditambahkan ke favorit.' : 'Kos dihapus dari favorit.');
    }
}