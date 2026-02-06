<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryController;

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
