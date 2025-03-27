<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SpellController;
// Admin Controllers
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ChapterAdminController;
use App\Http\Controllers\Admin\SpellAdminController;
use App\Http\Controllers\Admin\PurchaseAdminController;
use App\Http\Controllers\Admin\UserAdminController;

Auth::routes();

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'submitContact'])->name('contact.submit');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');

// Chapter routes
Route::get('/chapters', [ChapterController::class, 'index'])->name('chapters.index');
Route::get('/chapters/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');

// Chapter page API endpoints
Route::get('/api/chapters/{chapter}/pages', [ChapterController::class, 'getPages'])
    ->name('api.chapters.pages');

// Spell routes
Route::get('/spells', [SpellController::class, 'index'])->name('spells.index');
Route::get('/spells/{spell}', [SpellController::class, 'show'])->name('spells.show');
Route::get('/spells/{spell}/preview', [SpellController::class, 'preview'])->name('spells.preview');

// Auth routes (already included with Laravel UI)

// Protected routes
Route::middleware(['auth'])->group(function () {
    // User dashboard
    // Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    // Audio API endpoint
    Route::get('/api/chapters/{chapter}/audio', [ChapterController::class, 'getAudio'])
    ->name('api.chapters.audio');

    // Spell download
    Route::get('/spells/{spell}/download', [SpellController::class, 'download'])->name('spells.download');
    
    // Payment routes
    Route::post('/payment/process-cart', [PaymentController::class, 'processCart'])->name('payment.processCart');
    Route::post('/payment/process-spell', [PaymentController::class, 'processSpell'])->name('payment.processSpell');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');

    // Cart routes
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add-spell', [CartController::class, 'addSpell'])->name('cart.addSpell');
    Route::post('/cart/add', [CartController::class, 'addItem'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'updateItem'])->name('cart.update');
    Route::delete('/cart/remove', [CartController::class, 'removeItem'])->name('cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    
    // Reading interface
    Route::get('/read/{chapter}', [ChapterController::class, 'read'])->name('chapters.read');
    Route::post('/read/{chapter}/progress', [ChapterController::class, 'saveProgress'])->name('chapters.progress');

    // Invoice routes
    Route::get('/invoices/{purchase}', [InvoiceController::class, 'view'])->name('invoices.view');
    Route::get('/invoices/{purchase}/download', [InvoiceController::class, 'download'])->name('invoices.download');
});

// Admin routes - all are protected by the 'admin' middleware
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    
    // Chapters management
    Route::get('/chapters/generate-sample', [ChapterAdminController::class, 'generateSampleContent'])->name('chapters.sample');
    Route::post('/chapters/preview', [ChapterAdminController::class, 'preview'])->name('chapters.preview');
    Route::post('/chapters/upload-image', [ChapterAdminController::class, 'uploadImage'])->name('chapters.upload-image');
    Route::post('/chapters/{chapter}/paginate', [ChapterAdminController::class, 'paginate'])->name('chapters.paginate');
    
    // Add explicit route for chapter creation to ensure it's working
    Route::get('/chapters/create', [ChapterAdminController::class, 'create'])->name('chapters.create');
    Route::post('/chapters', [ChapterAdminController::class, 'store'])->name('chapters.store');
    Route::get('/chapters', [ChapterAdminController::class, 'index'])->name('chapters.index');
    Route::get('/chapters/{chapter}/edit', [ChapterAdminController::class, 'edit'])->name('chapters.edit');
    Route::put('/chapters/{chapter}', [ChapterAdminController::class, 'update'])->name('chapters.update');
    Route::delete('/chapters/{chapter}', [ChapterAdminController::class, 'destroy'])->name('chapters.destroy');
    
    
    // New spell routes
    Route::resource('spells', App\Http\Controllers\Admin\SpellAdminController::class);
    Route::get('/spells/{spell}/preview', [App\Http\Controllers\Admin\SpellAdminController::class, 'preview'])->name('spells.preview');
    Route::post('spells/{spell}/upload-pdf', [SpellAdminController::class, 'uploadPdf'])
        ->name('spells.upload-pdf');
    
    // User management
    Route::put('users/{user}/content', [UserAdminController::class, 'updateOwnedContent'])->name('users.update-content');
    Route::resource('users', UserAdminController::class);
    
    // Purchase management
    Route::get('/purchases/data', [PurchaseAdminController::class, 'getData'])->name('purchases.data');
    Route::get('/purchases/export', [PurchaseAdminController::class, 'export'])->name('purchases.export');
    Route::get('/purchases', [PurchaseAdminController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/{purchase}', [PurchaseAdminController::class, 'show'])->name('purchases.show');

    // Reports
    Route::get('/reports/sales', [PurchaseAdminController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/sales/data', [PurchaseAdminController::class, 'salesReportData'])->name('reports.sales.data');
    Route::get('/reports/user-analysis', [PurchaseAdminController::class, 'userAnalysis'])->name('reports.user_analysis');
    Route::get('/reports/user-analysis/data', [PurchaseAdminController::class, 'userAnalysisData'])->name('reports.user_analysis.data');
                   
});