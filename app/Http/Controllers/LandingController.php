<?php

namespace App\Http\Controllers;

use App\Models\Kos;
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
   public function index()
    {
        /** @var \App\Models\User|null $user */
        $user    = Auth::user();
        $pencari = $user?->role === 'pencari_kos';

        $kosTerbaru = Kos::where('status_verifikasi', 'terverifikasi')
            ->latest()
            ->take(8)
            ->get();

        $totalKos = Kos::where('status_verifikasi', 'terverifikasi')->count();

        // Favorit terbaru, hanya diambil kalau yang login adalah pencari
        $favoritTerbaru = $pencari
            ? $user->favoriteKos()
                ->where('status_verifikasi', 'terverifikasi')
                ->orderByPivot('created_at', 'desc')
                ->take(4)
                ->get()
            : collect();

        $dashboardUrl = null;
        if ($user) {
            $dashboardUrl = match ($user->role) {
                'admin'       => '/admin/dashboard',
                'pemilik_kos' => '/pemilik/dashboard',
                default       => '/pencari/dashboard',
            };
        }

        return view('landing', compact('kosTerbaru', 'totalKos', 'dashboardUrl', 'pencari', 'favoritTerbaru'));
    }
    }