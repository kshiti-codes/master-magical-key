<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Product Routes
|--------------------------------------------------------------------------
|
| Routes for product browsing, purchasing, and downloading
|
*/
Route::get('/', [ProductController::class, 'index'])->name('products');
// Public Product Routes (Customer-Facing)
Route::prefix('products')->name('products.')->group(function () {
    // Browse products
    
    Route::get('/{product:slug}', [ProductController::class, 'show'])->name('show');
    
    // Add to cart (requires authentication)
    Route::middleware('auth')->group(function () {
        Route::post('/{product:slug}/add-to-cart', [ProductController::class, 'addToCart'])->name('add-to-cart');
    });
});

// My Products (requires authentication)
Route::middleware('auth')->group(function () {
    Route::get('/my-products', [ProductController::class, 'myProducts'])->name('products.my-products');
    
    // Download purchased products
    Route::get('/products/{product:slug}/download-pdf', [ProductController::class, 'downloadPdf'])->name('products.download-pdf');
    Route::get('/products/{product:slug}/download-audio', [ProductController::class, 'downloadAudio'])->name('products.download-audio');
});

// Admin Product Management (requires admin authentication)
Route::middleware(['auth', 'admin'])->prefix('admin/products')->name('admin.products.')->group(function () {
    // List all products
    Route::get('/', [ProductController::class, 'adminIndex'])->name('index');
    
    // Create new product
    Route::get('/create', [ProductController::class, 'create'])->name('create');
    Route::post('/', [ProductController::class, 'store'])->name('store');
    
    // Edit product
    Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
    Route::put('/{product}', [ProductController::class, 'update'])->name('update');
    
    // Delete product
    Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    
    // Toggle active status
    Route::post('/{product}/toggle-active', [ProductController::class, 'toggleActive'])->name('toggle-active');
});

