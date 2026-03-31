<?php
// app/Services/CartService.php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get or create the cart for the authenticated user.
     */
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::firstOrCreate(['user_id' => $user->id]);
    }

    /**
     * Return cart grouped by vendor for display.
     */
    public function getGroupedCart(User $user): array
    {
        $cart = $this->getOrCreateCart($user);

        $grouped = $cart->groupedByVendor();

        return [
            'cart'        => $cart,
            'vendors'     => $grouped,
            'total'       => $grouped->sum('subtotal'),
            'item_count'  => $cart->items()->sum('quantity'),
        ];
    }

    /**
     * Add a product to cart or increment quantity.
     *
     * @throws \Exception
     */
    public function addItem(User $user, int $productId, int $quantity): CartItem
    {
        $product = Product::where('is_active', true)->findOrFail($productId);

        $cart = $this->getOrCreateCart($user);

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        $currentQty  = $existingItem ? $existingItem->quantity : 0;
        $requestedQty = $currentQty + $quantity;

        if ($requestedQty > $product->stock) {
            throw new \Exception(
                "Insufficient stock. Available: {$product->stock}, Already in cart: {$currentQty}."
            );
        }

        if ($existingItem) {
            $existingItem->update(['quantity' => $requestedQty]);
            return $existingItem->fresh();
        }

        return CartItem::create([
            'cart_id'    => $cart->id,
            'product_id' => $productId,
            'quantity'   => $quantity,
        ]);
    }

    /**
     * Update the quantity of an existing cart item.
     *
     * @throws \Exception
     */
    public function updateItem(User $user, int $cartItemId, int $quantity): CartItem
    {
        $cart = $this->getOrCreateCart($user);
        $item = CartItem::where('cart_id', $cart->id)->findOrFail($cartItemId);

        if ($quantity <= 0) {
            $this->removeItem($user, $cartItemId);
            return $item;
        }

        $product = $item->product;

        if ($quantity > $product->stock) {
            throw new \Exception(
                "Insufficient stock. Only {$product->stock} units available."
            );
        }

        $item->update(['quantity' => $quantity]);

        return $item->fresh();
    }

    /**
     * Remove a specific item from the cart.
     */
    public function removeItem(User $user, int $cartItemId): void
    {
        $cart = $this->getOrCreateCart($user);
        CartItem::where('cart_id', $cart->id)->findOrFail($cartItemId)->delete();
    }

    /**
     * Empty the entire cart.
     */
    public function clearCart(User $user): void
    {
        $cart = $this->getOrCreateCart($user);
        $cart->items()->delete();
    }
}
