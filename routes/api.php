<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LocationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // API Routes
});

// Public API Routes (for now, or protect as needed)
Route::prefix('categories')->name('api.categories.')->group(function () {
    Route::get('/search', [CategoryController::class, 'search'])->name('search');
});

Route::prefix('products')->name('api.products.')->group(function () {
    Route::get('/search', [ProductController::class, 'search'])->name('search');
});

Route::prefix('locations')->name('api.locations.')->group(function () {
    Route::get('/search', [LocationController::class, 'search'])->name('search');
});
