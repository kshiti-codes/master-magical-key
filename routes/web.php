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
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TrainingVideoController;
use App\Http\Controllers\PayPalWebhookController;
use App\Http\Controllers\SubscriptionPlanController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProductController;
// Admin Controllers
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ChapterAdminController;
use App\Http\Controllers\Admin\SpellAdminController;
use App\Http\Controllers\Admin\PurchaseAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Admin\SubscriptionPlanAdminController;
use App\Http\Controllers\Admin\EmailCampaignController;
use App\Http\Controllers\Admin\TrainingVideoAdminController;

Auth::routes();

// Public routes
// Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/framework', [HomeController::class, 'framework'])->name('framework');
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

// Subscription routes (public)
Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
Route::get('/subscriptions/{plan}', [SubscriptionController::class, 'show'])->name('subscriptions.show');

// Training video routes (public)
Route::get('/videos', [TrainingVideoController::class, 'index'])->name('videos.index');
Route::get('/videos/{video}', [TrainingVideoController::class, 'show'])->name('videos.show');

//webhook for PayPal
Route::post('api/webhooks/paypal', [PayPalWebhookController::class, 'handleWebhook'])
    ->name('paypal.webhook')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
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
    Route::post('/cart/apply-promo', [CartController::class, 'applyPromoCode'])->name('cart.applyPromo');
    Route::delete('/cart/remove-promo', [CartController::class, 'removePromoCode'])->name('cart.removePromo');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::get('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    
    // Reading interface
    Route::get('/read/{chapter}', [ChapterController::class, 'read'])->name('chapters.read');
    Route::post('/read/{chapter}/progress', [ChapterController::class, 'saveProgress'])->name('chapters.progress');

    // Invoice routes
    Route::get('/invoices/{purchase}', [InvoiceController::class, 'view'])->name('invoices.view');
    Route::get('/invoices/{purchase}/download', [InvoiceController::class, 'download'])->name('invoices.download');

    // Subscription routes 
    Route::post('/subscriptions/{plan}/purchase', [SubscriptionController::class, 'purchase'])->name('subscriptions.purchase');
    Route::post('subscriptions/{subscription}/extend', [SubscriptionController::class, 'extend'])->name('subscriptions.extend');
    Route::get('subscription/extend/success', [SubscriptionController::class, 'extendSuccess'])->name('subscription.extend.success');
    Route::get('subscription/extend/cancel', [SubscriptionController::class, 'extendCancel'])->name('subscription.extend.cancel');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::get('/subscriptions/thankyou/{subscription}', [SubscriptionController::class, 'thankyou'])->name('subscriptions.thankyou');
    Route::get('/subscription/manage', [SubscriptionController::class, 'manage'])->name('subscription.manage');
    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancelSubscription'])->name('subscriptions.cancel-subscription');
    
    // Training video routes
    Route::post('/cart/add-video', [CartController::class, 'addVideo'])->name('cart.addVideo');
    Route::get('/videos/{video}/download', [TrainingVideoController::class, 'download'])->name('videos.download');
    Route::post('/videos/purchase', [TrainingVideoController::class, 'purchase'])->name('videos.purchase');
    Route::post('/videos/add-to-cart', [TrainingVideoController::class, 'addToCart'])->name('videos.add-to-cart');
    Route::get('/videos/purchase/success', [TrainingVideoController::class, 'purchaseSuccess'])->name('videos.purchase.success');
    Route::get('/videos/purchase/cancel', [TrainingVideoController::class, 'purchaseCancel'])->name('videos.purchase.cancel');
    Route::get('/videos/{video}/watch', [TrainingVideoController::class, 'watch'])->name('videos.watch');

    // Contact routes
    Route::get('/contact', [App\Http\Controllers\ContactController::class, 'index'])->name('contact');
    Route::post('/contact', [App\Http\Controllers\ContactController::class, 'submit'])->name('contact.submit');
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

    // Promo codes
    Route::get('/promo-codes', [App\Http\Controllers\Admin\PromoCodeAdminController::class, 'index'])->name('promo-codes.index');
    Route::get('/promo-codes/create', [App\Http\Controllers\Admin\PromoCodeAdminController::class, 'create'])->name('promo-codes.create');
    Route::post('/promo-codes', [App\Http\Controllers\Admin\PromoCodeAdminController::class, 'store'])->name('promo-codes.store');
    Route::delete('/promo-codes/{promoCode}', [App\Http\Controllers\Admin\PromoCodeAdminController::class, 'destroy'])->name('promo-codes.destroy');
    Route::post('/promo-codes/{promoCode}/toggle', [App\Http\Controllers\Admin\PromoCodeAdminController::class, 'toggle'])->name('promo-codes.toggle');
    
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
                   
    //subscription management
    Route::get('/subscriptions', [SubscriptionPlanAdminController::class, 'index'])->name('subscriptions.index');
    Route::get('/subscriptions/create', [SubscriptionPlanAdminController::class, 'create'])->name('subscriptions.create');
    Route::post('/subscriptions', [SubscriptionPlanAdminController::class, 'store'])->name('subscriptions.store');
    Route::get('/subscriptions/{plan}', [SubscriptionPlanAdminController::class, 'show'])->name('subscriptions.show');
    Route::get('/subscriptions/{plan}/edit', [SubscriptionPlanAdminController::class, 'edit'])->name('subscriptions.edit');
    Route::put('/subscriptions/{plan}', [SubscriptionPlanAdminController::class, 'update'])->name('subscriptions.update');
    Route::delete('/subscriptions/{plan}', [SubscriptionPlanAdminController::class, 'destroy'])->name('subscriptions.destroy');
    Route::post('/subscriptions/{plan}/toggle-status', [SubscriptionPlanAdminController::class, 'toggleStatus'])->name('subscriptions.toggle-status');
    Route::get('/subscriptions-analytics', [SubscriptionPlanAdminController::class, 'analytics'])->name('subscriptions.analytics');

    //marketing email campaigns
    Route::get('/email-campaigns', [EmailCampaignController::class, 'index'])->name('email-campaigns.index');
    Route::get('/email-campaigns/create', [EmailCampaignController::class, 'create'])->name('email-campaigns.create');
    Route::post('/email-campaigns', [EmailCampaignController::class, 'store'])->name('email-campaigns.store');
    Route::get('/email-campaigns/{emailCampaign}', [EmailCampaignController::class, 'show'])->name('email-campaigns.show');
    Route::get('/email-campaigns/{emailCampaign}/edit', [EmailCampaignController::class, 'edit'])->name('email-campaigns.edit');
    Route::put('/email-campaigns/{emailCampaign}', [EmailCampaignController::class, 'update'])->name('email-campaigns.update');
    Route::get('/email-campaigns/{emailCampaign}/send', [EmailCampaignController::class, 'showSendConfirmation'])->name('email-campaigns.send-confirmation');
    Route::post('/email-campaigns/{emailCampaign}/send', [EmailCampaignController::class, 'send'])->name('email-campaigns.send');
    
    // Gmail API configuration routes
    Route::get('/email-campaigns/gmail/config', [EmailCampaignController::class, 'configureGmail'])
        ->name('email-campaigns.configure-gmail');
    Route::get('/email-campaigns/gmail/callback', [EmailCampaignController::class, 'handleGmailCallback'])
        ->name('email-campaigns.gmail-callback');

    // Training video management
    Route::get('/videos', [TrainingVideoAdminController::class, 'index'])->name('videos.index');
    Route::get('/videos/create', [TrainingVideoAdminController::class, 'create'])->name('videos.create');
    Route::post('/videos', [TrainingVideoAdminController::class, 'store'])->name('videos.store');
    Route::get('/videos/{video}', [TrainingVideoAdminController::class, 'show'])->name('videos.show');
    Route::get('/videos/{video}/edit', [TrainingVideoAdminController::class, 'edit'])->name('videos.edit');
    Route::put('/videos/{video}', [TrainingVideoAdminController::class, 'update'])->name('videos.update');
    Route::delete('/videos/{video}', [TrainingVideoAdminController::class, 'destroy'])->name('videos.destroy');
    Route::post('/videos/{video}/toggle-status', [TrainingVideoAdminController::class, 'toggleStatus'])->name('videos.toggle-status');
});

require __DIR__.'/session-booking.php';
require __DIR__.'/products.php';
