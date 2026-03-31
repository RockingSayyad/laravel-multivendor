<?php
// app/Services/CheckoutService.php

namespace App\Services;

use App\Events\OrderPlaced;
use App\Events\PaymentSucceeded;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(private CartService $cartService) {}

    /**
     * Process checkout for the given user.
     * Validates stock, splits by vendor, creates orders & payments.
     *
     * @return Collection<Order>  All orders created in this checkout.
     * @throws \Exception
     */
    public function checkout(User $user): Collection
    {
        $cart = Cart::where('user_id', $user->id)
            ->with('items.product.vendor')
            ->first();

        if (!$cart || $cart->isEmpty()) {
            throw new \Exception('Your cart is empty.');
        }

        // Pre-validate all items before touching the DB
        $this->validateStock($cart);

        return DB::transaction(function () use ($user, $cart) {
            $orders = collect();

            // Group cart items by vendor
            $grouped = $cart->items->groupBy(fn ($item) => $item->product->vendor_id);

            foreach ($grouped as $vendorId => $items) {
                $order = $this->createOrderForVendor($user, $vendorId, $items);
                $orders->push($order);
            }

            // Clear cart after successful checkout
            $cart->items()->delete();

            return $orders->load(['items', 'payment', 'vendor']);
        });
    }

    /**
     * Validate that all cart items have sufficient stock.
     *
     * @throws \Exception with a descriptive message listing failed items.
     */
    private function validateStock(Cart $cart): void
    {
        $errors = [];

        foreach ($cart->items as $item) {
            $product = $item->product;

            if (!$product->is_active) {
                $errors[] = "Product '{$product->name}' is no longer available.";
                continue;
            }

            if ($item->quantity > $product->stock) {
                $errors[] = "'{$product->name}': requested {$item->quantity}, only {$product->stock} in stock.";
            }
        }

        if (!empty($errors)) {
            throw new \Exception('Stock validation failed: ' . implode(' | ', $errors));
        }
    }

    /**
     * Create a single order (+ items + payment) for one vendor's items.
     * Uses pessimistic locking on products to prevent overselling.
     */
    private function createOrderForVendor(User $user, int $vendorId, Collection $items): Order
    {
        $total = 0;
        $orderItemData = [];

        foreach ($items as $item) {
            // Pessimistic lock — prevents race conditions on stock
            $product = Product::lockForUpdate()->findOrFail($item->product_id);

            if ($item->quantity > $product->stock) {
                throw new \Exception(
                    "Stock changed for '{$product->name}'. Only {$product->stock} left."
                );
            }

            $subtotal = $product->price * $item->quantity;
            $total   += $subtotal;

            $orderItemData[] = [
                'product_id'   => $product->id,
                'product_name' => $product->name,   // snapshot
                'unit_price'   => $product->price,  // snapshot
                'quantity'     => $item->quantity,
                'subtotal'     => $subtotal,
            ];

            // Deduct stock
            $product->decrement('stock', $item->quantity);
        }

        // Create Order
        $order = Order::create([
            'user_id'      => $user->id,
            'vendor_id'    => $vendorId,
            'total_amount' => $total,
            'status'       => 'processing',
        ]);

        // Create Order Items
        $order->items()->createMany($orderItemData);

        // Simulate payment (always succeeds)
        $payment = Payment::create([
            'order_id'        => $order->id,
            'amount'          => $total,
            'status'          => 'paid',
            'transaction_ref' => 'TXN-' . strtoupper(Str::random(12)),
            'paid_at'         => now(),
        ]);

        // Update order status to completed
        $order->update(['status' => 'completed']);

        // Fire events
        event(new OrderPlaced($order));
        event(new PaymentSucceeded($order, $payment));

        return $order;
    }
}
