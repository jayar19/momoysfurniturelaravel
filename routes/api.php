<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\BrandApiController;
use App\Http\Controllers\Api\HealthApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\PaymentApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\QueryApiController;
use App\Http\Controllers\Api\TestimonialApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthApiController::class);

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/logout', [AuthApiController::class, 'logout']);
    Route::get('/me', [AuthApiController::class, 'me']);
    Route::put('/profile', [AuthApiController::class, 'profile']);
});

Route::apiResource('products', ProductApiController::class);
Route::apiResource('brands', BrandApiController::class);
Route::apiResource('queries', QueryApiController::class);
Route::apiResource('testimonials', TestimonialApiController::class);
Route::apiResource('users', UserApiController::class)->only(['index', 'show', 'destroy']);

Route::prefix('orders')->group(function (): void {
    Route::get('/user/{userId}', [OrderApiController::class, 'forUser']);
    Route::get('/{order}/chat', [OrderApiController::class, 'chat']);
    Route::post('/{order}/chat', [OrderApiController::class, 'sendChat']);
    Route::put('/{order}/location', [OrderApiController::class, 'updateLocation']);
});
Route::apiResource('orders', OrderApiController::class)->only(['index', 'store', 'show', 'update']);

Route::prefix('payments')->group(function (): void {
    Route::post('/paymongo/checkout-session', [PaymentApiController::class, 'checkoutSession']);
    Route::post('/paymongo/checkout-session/{sessionId}/sync', [PaymentApiController::class, 'syncCheckoutSession']);
    Route::post('/paymongo/webhook', [PaymentApiController::class, 'webhook']);
    Route::post('/down-payment', [PaymentApiController::class, 'downPayment']);
    Route::post('/remaining-balance', [PaymentApiController::class, 'remainingBalance']);
    Route::get('/user/{userId}', [PaymentApiController::class, 'forUser']);
});
