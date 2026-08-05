<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Batasi panjang default string index agar kompatibel dengan MySQL/MariaDB
        // lama (max key length 1000 bytes pada utf8mb4).
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();
    }
}
