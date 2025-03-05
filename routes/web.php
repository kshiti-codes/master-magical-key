<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;

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

// Auth routes (already included with Laravel UI)

// Protected routes
Route::middleware(['auth'])->group(function () {
    // User dashboard
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    
    // Payment routes
    Route::get('/chapters/{chapter}/purchase', [PaymentController::class, 'checkout'])->name('chapters.purchase');
    Route::post('/payment/process', [PaymentController::class, 'process'])->name('payment.process');
    Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
    Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');
    
    // Reading interface
    Route::get('/read/{chapter}', [ChapterController::class, 'read'])->name('chapters.read');
    Route::post('/read/{chapter}/progress', [ChapterController::class, 'saveProgress'])->name('chapters.progress');
});