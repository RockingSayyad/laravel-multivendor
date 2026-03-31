<?php
// app/Providers/EventServiceProvider.php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\PaymentSucceeded;
use App\Listeners\SendOrderConfirmationEmail;
use App\Listeners\UpdateOrderStatus;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderPlaced::class => [
            SendOrderConfirmationEmail::class,
        ],
        PaymentSucceeded::class => [
            UpdateOrderStatus::class,
        ],
    ];

    public function boot(): void {}
}
