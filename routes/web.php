<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KitController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\AssetImportController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\LocationImportController;
use App\Http\Controllers\Api\LoanItemSearchController;
use App\Http\Controllers\ConsumableStockImportController;

Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // --- DASHBOARD & PROFILE ---
    Route::view('locations', 'locations.index')->name('locations.index');
    Route::view('users', 'users.index')->name('users.index');
    Route::view('categories', 'categories.index')->name('categories.index');
    Route::view('products', 'products.index')->name('products.index');
    Route::view('stocks', 'stocks.index')->name('stocks.index');

    // --- IMPORTS ---
    Route::prefix('locations/import')->name('locations.import')->group(function () {
        Route::get('/template', [LocationImportController::class, 'downloadTemplate'])->name('.template');
        Route::get('/', [LocationImportController::class, 'create']);
        Route::post('/', [LocationImportController::class, 'store'])->name('.store');
    });

    Route::prefix('products/import')->name('products.import')->group(function () {
        Route::get('/template', [ProductImportController::class, 'downloadTemplate'])->name('.template');
        Route::get('/', [ProductImportController::class, 'create']);
        Route::post('/', [ProductImportController::class, 'store'])->name('.store');
    });

    Route::prefix('stocks/import')->name('stocks.import')->group(function () {
        Route::get('/template', [ConsumableStockImportController::class, 'downloadTemplate'])->name('.template');
        Route::get('/', [ConsumableStockImportController::class, 'create']);
        Route::post('/', [ConsumableStockImportController::class, 'store'])->name('.store');
    });

    Route::prefix('assets/import')->name('assets.import')->group(function () {
        Route::get('/template', [AssetImportController::class, 'downloadTemplate'])->name('.template');
        Route::get('/', [AssetImportController::class, 'create']);
        Route::post('/', [AssetImportController::class, 'store'])->name('.store');
    });

    // --- MODULES ---
    Route::resource('assets', AssetController::class);

    // Loan Management
    Route::resource('loans', LoanController::class);
    Route::post('/loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');
    Route::patch('/loans/{loan}/reject', [LoanController::class, 'reject'])->name('loans.reject');
    Route::patch('/loans/{loan}/restore', [LoanController::class, 'restore'])->name('loans.restore');
    Route::post('/loans/{loan}/return', [LoanController::class, 'returnItems'])->name('loans.return');

    // Kit Routes
    Route::resource('kits', KitController::class);
    Route::get('/kits/{kit}/resolve', [KitController::class, 'resolve'])->name('kits.resolve');

    // --- AJAX ---
    Route::prefix('ajax')->group(function () {
        Route::prefix('categories')->name('ajax.categories.')->group(function () {
            Route::post('/search', [CategoryController::class, 'search'])->name('search');
        });
        Route::prefix('products')->name('ajax.products.')->group(function () {
            Route::post('/search', [ProductController::class, 'search'])->name('search');
            Route::post('/search/assets', [ProductController::class, 'searchAssets'])->name('assets.search');
            Route::post('/search/consumables', [ProductController::class, 'searchConsumables'])->name('consumables.search');
        });
        Route::prefix('locations')->name('ajax.locations.')->group(function () {
            Route::post('/search', [LocationController::class, 'search'])->name('search');
        });
        Route::post('/loans/items/search', LoanItemSearchController::class)->name('ajax.loans.items.search');
    });
});

require __DIR__.'/auth.php';
