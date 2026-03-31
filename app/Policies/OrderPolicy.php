<?php
// app/Policies/OrderPolicy.php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Admins can view any order.
     * Customers can only view their own orders.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $order->user_id;
    }

    /**
     * Only admins can update order status.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}
