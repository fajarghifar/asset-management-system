<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LoanController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::view('locations', 'locations.index')->name('locations.index');
    Route::view('categories', 'categories.index')->name('categories.index');
    Route::view('products', 'products.index')->name('products.index');
    Route::view('stocks', 'stocks.index')->name('stocks.index');
    Route::resource('assets', AssetController::class);

    // Loan Management
    Route::resource('loans', LoanController::class);
    Route::post('/loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');
    Route::patch('/loans/{loan}/reject', [LoanController::class, 'reject'])->name('loans.reject'); // Patch implies modification
    Route::patch('/loans/{loan}/restore', [LoanController::class, 'restore'])->name('loans.restore');
    Route::post('/loans/{loan}/return', [LoanController::class, 'returnItems'])->name('loans.return');

    // Search Routes (AJAX)
    Route::get('/ajax/products', [SearchController::class, 'products'])->name('ajax.products');
    Route::get('/ajax/locations', [SearchController::class, 'locations'])->name('ajax.locations');
    Route::get('/ajax/assets', [SearchController::class, 'assets'])->name('ajax.assets');
    Route::get('/ajax/stocks', [SearchController::class, 'stocks'])->name('ajax.stocks');
    Route::get('/ajax/unified', [SearchController::class, 'unified'])->name('ajax.unified');
});

require __DIR__.'/auth.php';
