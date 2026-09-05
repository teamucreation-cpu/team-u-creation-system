<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
        // The app only loads Bootstrap CSS (no Tailwind), but paginate()->links()
        // renders Laravel's default Tailwind pagination view unless told
        // otherwise — that markup has no matching styles here, so it shows up
        // unstyled. Render Bootstrap 5 pagination markup everywhere instead.
        Paginator::useBootstrapFive();
    }
}
