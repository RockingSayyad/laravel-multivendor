<?php
// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * List active products with optional vendor filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('vendor')
            ->where('is_active', true);

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $products = $query->latest()->paginate(20);

        return response()->json($products);
    }

    /**
     * GET /api/products/{product}
     */
    public function show(Product $product): JsonResponse
    {
        if (!$product->is_active) {
            return response()->json(['message' => 'Product not found.'], 404);
        }

        return response()->json($product->load('vendor'));
    }

    /**
     * GET /api/vendors
     * List all active vendors.
     */
    public function vendors(): JsonResponse
    {
        $vendors = Vendor::where('is_active', true)
            ->withCount('products')
            ->get();

        return response()->json($vendors);
    }
}
