<?php
// app/Listeners/SendOrderConfirmationEmail.php

namespace App\Listeners;

use App\Events\OrderPlaced;
use Illuminate\Support\Facades\Log;

class SendOrderConfirmationEmail
{
    public function handle(OrderPlaced $event): void
    {
        $order    = $event->order;
        $customer = $order->user;
        $vendor   = $order->vendor;

        // In production: Mail::to($customer)->send(new OrderConfirmationMail($order));
        Log::info('[OrderPlaced] Confirmation email (mock)', [
            'order_id'       => $order->id,
            'customer_email' => $customer->email,
            'vendor'         => $vendor->name,
            'total'          => $order->total_amount,
            'status'         => $order->status,
        ]);
    }
}
