<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $semua = Kos::where('id_user', $user->id_user)->get();

        $ringkasan = [
            'total'    => $semua->count(),
            'tayang'   => $semua->where('status_verifikasi', 'terverifikasi')->count(),
            'tersedia' => (int) $semua->sum('kamar_tersedia'),
            'kamar'    => (int) $semua->sum('jumlah_kamar'),
            'favorit'  => DB::table('favorites')->whereIn('id_kos', $semua->pluck('id_kos'))->count(),
        ];

        // Ketersediaan kamar: yang paling penuh tampil duluan, maksimal 5 kos
        $ketersediaan = $semua
            ->filter(fn ($k) => $k->jumlah_kamar)
            ->sortBy(fn ($k) => ($k->kamar_tersedia ?? 0) / $k->jumlah_kamar)
            ->take(5)
            ->values();

        $perhatian = [];

        foreach ($semua as $k) {
            $edit = route('pemilik.kos.edit', $k);

            if ($k->status_verifikasi === 'ditolak') {
                $alasan = $k->catatan_verifikasi
                    ? ' Alasan: ' . $k->catatan_verifikasi
                    : ' Perbaiki datanya, lalu simpan untuk diajukan lagi.';

                $perhatian[] = ['nada' => 'merah', 'teks' => $k->nama_kos . ' ditolak admin.' . $alasan, 'aksi' => 'Perbaiki', 'url' => $edit];
                continue;
            }

            if ($k->status_verifikasi === 'menunggu') {
                $perhatian[] = ['nada' => 'kuning', 'teks' => $k->nama_kos . ' menunggu verifikasi admin. Kos tampil di situs setelah disetujui.'];
            }

            $kurang = [];
            if (! $k->harga) {
                $kurang[] = 'harga';
            }
            if ($k->jumlah_kamar === null) {
                $kurang[] = 'jumlah kamar';
            }
            if ($kurang) {
                $perhatian[] = ['nada' => 'biasa', 'teks' => 'Lengkapi ' . $k->nama_kos . ': ' . implode(' dan ', $kurang) . ' belum diisi.', 'aksi' => 'Lengkapi', 'url' => $edit];
            }

            if ($k->status_verifikasi === 'terverifikasi'
                && $k->jumlah_kamar !== null
                && $k->kamar_tersedia !== null
                && (int) $k->kamar_tersedia === 0) {
                $perhatian[] = ['nada' => 'biasa', 'teks' => $k->nama_kos . ' tampil penuh di situs. Kalau sudah ada kamar kosong, perbarui jumlahnya.', 'aksi' => 'Perbarui', 'url' => route('pemilik.kos.index') . '#kos-' . $k->id_kos];
            }
        }

        $urutan    = ['merah' => 0, 'biasa' => 1, 'kuning' => 2];
        $perhatian = collect($perhatian)->sortBy(fn ($p) => $urutan[$p['nada']])->values()->all();

        $butuhTelepon = blank($user->no_telp);

        return view('pemilik.kos.dashboard', compact('ringkasan', 'ketersediaan', 'perhatian', 'butuhTelepon'));
    }
}