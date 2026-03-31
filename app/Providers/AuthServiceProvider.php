<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\Order;
use App\Policies\OrderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Order::class => OrderPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        /**
         * Gate: restrict access to admin-only routes.
         * Usage: Gate::authorize('admin') or $this->authorize('admin')
         */
        Gate::define('admin', function ($user) {
            return $user->isAdmin();
        });
    }
}
