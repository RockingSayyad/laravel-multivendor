<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // CartService is shared (singleton) - same instance across a request lifecycle
        $this->app->singleton(CartService::class);

        // CheckoutService depends on CartService
        $this->app->singleton(CheckoutService::class, function ($app) {
            return new CheckoutService($app->make(CartService::class));
        });
    }

    public function boot(): void
    {
        //
    }
}
