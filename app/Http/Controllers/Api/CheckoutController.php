<?php
// app/Http/Controllers/Api/CheckoutController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkoutService) {}

    /**
     * POST /api/checkout
     * Validates stock, splits cart by vendor, creates orders & payments.
     */
    public function checkout(Request $request): JsonResponse
    {
        try {
            $orders = $this->checkoutService->checkout($request->user());

            return response()->json([
                'message'      => 'Checkout successful. ' . $orders->count() . ' order(s) placed.',
                'orders_count' => $orders->count(),
                'orders'       => $orders->map(fn ($order) => [
                    'order_id'   => $order->id,
                    'vendor'     => $order->vendor->name,
                    'total'      => number_format($order->total_amount, 2),
                    'status'     => $order->status,
                    'payment'    => [
                        'status'          => $order->payment->status,
                        'transaction_ref' => $order->payment->transaction_ref,
                        'paid_at'         => $order->payment->paid_at,
                    ],
                    'items'      => $order->items->map(fn ($item) => [
                        'product_name' => $item->product_name,
                        'unit_price'   => $item->unit_price,
                        'quantity'     => $item->quantity,
                        'subtotal'     => $item->subtotal,
                    ]),
                ]),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
