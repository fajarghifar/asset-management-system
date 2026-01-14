<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;

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

    // Search Routes (AJAX)
    Route::get('/ajax/products', [SearchController::class, 'products'])->name('ajax.products');
    Route::get('/ajax/locations', [SearchController::class, 'locations'])->name('ajax.locations');
});

require __DIR__.'/auth.php';
