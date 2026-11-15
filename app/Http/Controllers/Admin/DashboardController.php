<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kos;
use App\Models\PengajuanSewa;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $hitungKos = Kos::selectRaw('status_verifikasi, count(*) as jumlah')
            ->groupBy('status_verifikasi')->pluck('jumlah', 'status_verifikasi');

        $hitungPengguna = User::selectRaw('role, count(*) as jumlah')
            ->groupBy('role')->pluck('jumlah', 'role');

        $hitungPengajuan = PengajuanSewa::selectRaw('status, count(*) as jumlah')
            ->groupBy('status')->pluck('jumlah', 'status');

        // Antrean verifikasi: yang paling lama menunggu tampil duluan
        $antrean = Kos::with('pemilik:id_user,nama_lengkap')
            ->where('status_verifikasi', 'menunggu')
            ->oldest()
            ->take(5)
            ->get();

        $penggunaBaru = User::where('role', '!=', 'admin')->latest()->take(5)->get();

        $pengajuanTerbaru = PengajuanSewa::with(['kos:id_kos,nama_kos', 'pencari:id_user,nama_lengkap'])
            ->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'hitungKos', 'hitungPengguna', 'hitungPengajuan', 'antrean', 'penggunaBaru', 'pengajuanTerbaru'
        ));
    }
}