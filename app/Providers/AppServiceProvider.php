<?php

namespace App\Providers;

use App\Models\Kos;
use App\Models\PengajuanSewa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Lencana pengajuan baru di sidebar pemilik
        View::composer('layouts.pemilik', function ($view) {
            $view->with('pengajuanBaru', Auth::check()
                ? PengajuanSewa::untukPemilik(Auth::id())->where('status', 'menunggu')->count()
                : 0);
        });

        // Lencana kos yang menunggu verifikasi di sidebar admin
        View::composer('layouts.admin', function ($view) {
            $view->with('kosMenunggu', Auth::check()
                ? Kos::where('status_verifikasi', 'menunggu')->count()
                : 0);
        });
    }
}