<?php

namespace App\Providers;

use App\Models\Configration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

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
        // Keep index key lengths within the MySQL 5.6 / utf8mb4 limit.
        Schema::defaultStringLength(191);

        try {
            DB::connection()->getPdo();

            View::share('briefs', Configration::find(1));
        } catch (Throwable $e) {
            // The database is not reachable yet (e.g. during the very first install
            // or while running `composer install` on a fresh machine); the shared
            // configuration is simply unavailable in that case.
        }
    }
}
