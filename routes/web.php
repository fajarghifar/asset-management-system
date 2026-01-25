<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KitController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::view('locations', 'locations.index')->name('locations.index');
    Route::view('categories', 'categories.index')->name('categories.index');
    Route::view('products', 'products.index')->name('products.index');
    Route::get('/products/import', [App\Http\Controllers\ProductImportController::class, 'create'])->name('products.import');
    Route::post('/products/import', [App\Http\Controllers\ProductImportController::class, 'store'])->name('products.import.store');
    Route::view('stocks', 'stocks.index')->name('stocks.index');
    Route::get('/stocks/import', [App\Http\Controllers\ConsumableStockImportController::class, 'create'])->name('stocks.import');
    Route::post('/stocks/import', [App\Http\Controllers\ConsumableStockImportController::class, 'store'])->name('stocks.import.store');
    Route::get('/assets/import', [App\Http\Controllers\AssetImportController::class, 'create'])->name('assets.import');
    Route::post('/assets/import', [App\Http\Controllers\AssetImportController::class, 'store'])->name('assets.import.store');
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

    // Search Routes (AJAX)
    Route::get('/ajax/products', [SearchController::class, 'products'])->name('ajax.products');
    Route::get('/ajax/locations', [SearchController::class, 'locations'])->name('ajax.locations');
    Route::get('/ajax/kits', [SearchController::class, 'kits'])->name('ajax.kits');
    Route::get('/ajax/assets', [SearchController::class, 'assets'])->name('ajax.assets');
    Route::get('/ajax/stocks', [SearchController::class, 'stocks'])->name('ajax.stocks');
    Route::get('/ajax/unified', [SearchController::class, 'unified'])->name('ajax.unified');
    Route::get('/ajax/product-locations', [SearchController::class, 'productLocations'])->name('ajax.product-locations');
});

require __DIR__.'/auth.php';
