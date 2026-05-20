<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CourierController;
use Illuminate\Support\Facades\Route;

// Auth (sin middleware)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/logout',   [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me',        [AuthController::class, 'me'])->middleware('auth:sanctum');
});

// Catálogo público (sin auth)
Route::get('/vendors',                           [VendorController::class, 'index']);
Route::get('/vendors/{vendor}',                  [VendorController::class, 'show']);
Route::get('/vendors/{vendor}/products',         [ProductController::class, 'byVendor']);
Route::get('/vendors/{vendor}/bundles',          [VendorController::class, 'bundles']);
Route::get('/vendors/{vendor}/categories',       [VendorController::class, 'categories']);
Route::get('/products/{product}',                [ProductController::class, 'show']);

// Rutas autenticadas
Route::middleware('auth:sanctum')->group(function () {
    // Orders
    Route::get('/orders',                   [OrderController::class, 'index']);
    Route::post('/orders',                  [OrderController::class, 'store']);
    Route::get('/orders/{order}',           [OrderController::class, 'show']);
    Route::patch('/orders/{order}/status',  [OrderController::class, 'updateStatus']);
    Route::post('/orders/{order}/cancel',   [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/accept',   [OrderController::class, 'accept']);

    // Payments
    Route::post('/payments/{order}/process', [PaymentController::class, 'process']);
    Route::post('/payments/{payment}/refund', [PaymentController::class, 'refund']);

    // Discounts
    Route::get('/discounts/validate/{code}', function (\Illuminate\Http\Request $request, string $code) {
        $discount = \App\Models\Discount::where('code', $code)->first();
        if (!$discount) {
            return response()->json(['valid' => false, 'error' => 'Discount not found.'], 404);
        }
        $isValid = now() >= $discount->valid_from
            && now() <= $discount->valid_to
            && ($discount->max_uses === null || $discount->current_uses < $discount->max_uses);
        return response()->json(['valid' => $isValid, 'discount' => $discount]);
    });

    // Customer profile
    Route::get('/customers/profile',   [CustomerController::class, 'profile']);
    Route::patch('/customers/profile', [CustomerController::class, 'updateProfile']);

    // Courier
    Route::get('/couriers/available',              [CourierController::class, 'available']);
    Route::patch('/couriers/{courier}/location',   [CourierController::class, 'updateLocation']);
    Route::patch('/couriers/{courier}/availability', [CourierController::class, 'updateAvailability']);

    // Vendors (management)
    Route::post('/vendors',          [VendorController::class, 'store']);
    Route::put('/vendors/{vendor}',  [VendorController::class, 'update']);
    Route::get('/vendors/{vendor}/orders', [VendorController::class, 'orders']);

    // Products (management)
    Route::post('/products',           [ProductController::class, 'store']);
    Route::put('/products/{product}',  [ProductController::class, 'update']);
});
