<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\View\Composers\BuyerSidebarComposer;
use App\View\Composers\SellerSidebarComposer;

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
        // Composer: inject sidebar data ke buyer layout tanpa inline DB query
        View::composer('layouts.buyer', BuyerSidebarComposer::class);

        // Composer: inject sidebar data ke seller layout tanpa inline DB query
        View::composer('layouts.seller', SellerSidebarComposer::class);
    }
}
