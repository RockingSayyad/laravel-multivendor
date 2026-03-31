<?php
// app/Http/Controllers/Api/CartController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    /**
     * GET /api/cart
     * View cart grouped by vendor.
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->cartService->getGroupedCart($request->user());

        return response()->json([
            'cart_id'    => $data['cart']->id,
            'item_count' => $data['item_count'],
            'total'      => number_format($data['total'], 2),
            'vendors'    => $data['vendors']->values()->map(function ($group) {
                return [
                    'vendor'   => $group['vendor'],
                    'subtotal' => number_format($group['subtotal'], 2),
                    'items'    => $group['items']->map(fn ($item) => [
                        'id'         => $item->id,
                        'product_id' => $item->product_id,
                        'product'    => [
                            'id'    => $item->product->id,
                            'name'  => $item->product->name,
                            'price' => $item->product->price,
                            'stock' => $item->product->stock,
                        ],
                        'quantity'   => $item->quantity,
                        'subtotal'   => number_format($item->subtotal(), 2),
                    ]),
                ];
            }),
        ]);
    }

    /**
     * POST /api/cart/add
     */
    public function add(AddToCartRequest $request): JsonResponse
    {
        try {
            $item = $this->cartService->addItem(
                $request->user(),
                $request->validated('product_id'),
                $request->validated('quantity'),
            );

            return response()->json([
                'message' => 'Item added to cart.',
                'item'    => $item->load('product'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * PUT /api/cart/items/{cartItem}
     */
    public function update(UpdateCartItemRequest $request, int $cartItemId): JsonResponse
    {
        try {
            $item = $this->cartService->updateItem(
                $request->user(),
                $cartItemId,
                $request->validated('quantity'),
            );

            return response()->json([
                'message' => 'Cart item updated.',
                'item'    => $item->load('product'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * DELETE /api/cart/items/{cartItem}
     */
    public function removeItem(Request $request, int $cartItemId): JsonResponse
    {
        $this->cartService->removeItem($request->user(), $cartItemId);

        return response()->json(['message' => 'Item removed from cart.']);
    }

    /**
     * DELETE /api/cart
     */
    public function clear(Request $request): JsonResponse
    {
        $this->cartService->clearCart($request->user());

        return response()->json(['message' => 'Cart cleared.']);
    }
}
