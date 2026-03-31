<?php
// app/Http/Controllers/Admin/OrderController.php


namespace App\Http\Controllers\Admin;
use Illuminate\Routing\Controller as BaseController;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrderController extends BaseController
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            Gate::authorize('admin');
            return $next($request);
        });
    }

    /**
     * GET /api/admin/orders
     * List all orders with optional filters: vendor_id, customer_id, status.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'vendor_id'   => 'sometimes|integer|exists:vendors,id',
            'customer_id' => 'sometimes|integer|exists:users,id',
            'status'      => 'sometimes|in:pending,processing,completed,cancelled',
            'per_page'    => 'sometimes|integer|min:1|max:100',
        ]);

        $query = Order::with(['user', 'vendor', 'payment'])
            ->withCount('items');

        if ($request->filled('vendor_id')) {
            $query->forVendor($request->integer('vendor_id'));
        }

        if ($request->filled('customer_id')) {
            $query->forCustomer($request->integer('customer_id'));
        }

        if ($request->filled('status')) {
            $query->withStatus($request->input('status'));
        }

        $orders = $query->latest()->paginate($request->integer('per_page', 20));

        return response()->json($orders);
    }

    /**
     * GET /api/admin/orders/{order}
     * Full order detail: items, quantities, totals, payment, customer, vendor.
     */
    public function show(Order $order): JsonResponse
    {
        $order->load([
            'user',
            'vendor',
            'items.product',
            'payment',
        ]);

        return response()->json([
            'order'    => $order,
            'summary'  => [
                'item_count'   => $order->items->sum('quantity'),
                'total_amount' => $order->total_amount,
                'payment_status' => $order->payment?->status ?? 'none',
                'transaction_ref' => $order->payment?->transaction_ref,
            ],
        ]);
    }

    /**
     * PATCH /api/admin/orders/{order}/status
     * Manually update order status.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $order->update(['status' => $data['status']]);

        return response()->json([
            'message' => 'Order status updated.',
            'order'   => $order->fresh(),
        ]);
    }
}
