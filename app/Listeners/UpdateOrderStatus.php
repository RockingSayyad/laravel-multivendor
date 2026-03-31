<?php
// app/Listeners/UpdateOrderStatus.php

namespace App\Listeners;

use App\Events\PaymentSucceeded;
use Illuminate\Support\Facades\Log;

class UpdateOrderStatus
{
    public function handle(PaymentSucceeded $event): void
    {
        $order   = $event->order;
        $payment = $event->payment;

        Log::info('[PaymentSucceeded] Order status confirmed', [
            'order_id'        => $order->id,
            'payment_id'      => $payment->id,
            'transaction_ref' => $payment->transaction_ref,
            'amount'          => $payment->amount,
            'paid_at'         => $payment->paid_at,
        ]);

        // Hook: notify vendor, update analytics, trigger fulfillment, etc.
    }
}
