<?php
// routes/api.php

use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// Products & Vendors (publicly browsable)
Route::get('/products',         [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/vendors',          [ProductController::class, 'vendors']);

/*
|--------------------------------------------------------------------------
| Authenticated Customer Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // Cart
    Route::prefix('cart')->group(function () {
        Route::get('/',              [CartController::class, 'index']);
        Route::post('/add',          [CartController::class, 'add']);
        Route::put('/items/{id}',    [CartController::class, 'update']);
        Route::delete('/items/{id}', [CartController::class, 'removeItem']);
        Route::delete('/',           [CartController::class, 'clear']);
    });

    // Checkout
    Route::post('/checkout', [CheckoutController::class, 'checkout']);

    // Customer orders
    Route::get('/orders',      [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);

    /*
    |--------------------------------------------------------------------------
    | Admin Routes  (Gate::authorize('admin') applied per controller)
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {
        Route::get('/orders',                      [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}',              [AdminOrderController::class, 'show']);
        Route::patch('/orders/{order}/status',     [AdminOrderController::class, 'updateStatus']);
    });
});
