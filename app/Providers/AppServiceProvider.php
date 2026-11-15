<?php

namespace App\Providers;

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
        View::composer('layouts.pemilik', function ($view) {
            $view->with('pengajuanBaru', Auth::check()
                ? PengajuanSewa::untukPemilik(Auth::id())->where('status', 'menunggu')->count()
                : 0);
        });
    }
}