<?php
// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * GET /api/orders
     * List the authenticated customer's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['vendor', 'items', 'payment'])
            ->forCustomer($request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json($orders);
    }

    /**
     * GET /api/orders/{order}
     * Show a single order — policy ensures ownership.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load(['vendor', 'items', 'payment', 'user']);

        return response()->json($order);
    }
}
