<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Auth Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Recommendations
    Route::get('/recommendations', [App\Http\Controllers\Api\RecommendationController::class, 'index']);
});

// Banner Tracking (Public)
Route::post('/banners/impression', [App\Http\Controllers\Api\BannerTrackingController::class, 'impression']);
Route::post('/banners/click', [App\Http\Controllers\Api\BannerTrackingController::class, 'click']);
