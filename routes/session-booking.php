<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SessionBookingController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\SessionTypeController;
use App\Http\Controllers\Admin\AvailabilityController;
use App\Http\Controllers\Admin\BookedSessionController;

// Client routes (protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    // View my sessions
    Route::get('/sessions', [SessionBookingController::class, 'index'])->name('sessions.index');
    
    // Session booking flow
    Route::get('/sessions/book', [SessionBookingController::class, 'create'])->name('sessions.create');
    Route::post('/sessions/coaches', [SessionBookingController::class, 'getCoaches'])->name('sessions.coaches');
    Route::post('/sessions/dates', [SessionBookingController::class, 'getAvailableDates'])->name('sessions.dates');
    Route::post('/sessions/slots', [SessionBookingController::class, 'getAvailableSlots'])->name('sessions.slots');
    Route::post('/sessions/lock', [SessionBookingController::class, 'lockTimeSlot'])->name('sessions.lock');
    Route::post('/sessions/prepare', [SessionBookingController::class, 'prepare'])->name('sessions.prepare');
    Route::get('/sessions/payment/{id?}', [SessionBookingController::class, 'payment'])->name('sessions.payment');
    Route::post('/sessions/process-payment', [SessionBookingController::class, 'processPayment'])->name('sessions.process-payment');
    Route::get('/sessions/store', [SessionBookingController::class, 'store'])->name('sessions.store');
    Route::get('/sessions/cancel-payment', [SessionBookingController::class, 'cancelPayment'])->name('sessions.cancel-payment');
    Route::get('/sessions/confirmation/{id}', [SessionBookingController::class, 'confirmation'])->name('sessions.confirmation');
    
    // View a single session
    Route::get('/sessions/{id}', [SessionBookingController::class, 'show'])->name('sessions.show');
    
    // Join session meeting
    Route::get('/sessions/{id}/join', [SessionBookingController::class, 'joinMeeting'])->name('sessions.join');
});

// Admin routes (protected by auth and admin middleware)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Coaches management
    Route::resource('coaches', CoachController::class);
    
    // Session types management
    Route::resource('session-types', SessionTypeController::class);
    
    // Map coaches to session types
    Route::get('/session-types/{id}/coaches', [SessionTypeController::class, 'manageCoaches'])->name('session-types.coaches');
    Route::post('/session-types/{id}/coaches', [SessionTypeController::class, 'updateCoaches'])->name('session-types.update-coaches');
    
    // Coach availability management
    Route::get('/availabilities', [AvailabilityController::class, 'index'])->name('availabilities.index');
    Route::get('/availabilities/create', [AvailabilityController::class, 'create'])->name('availabilities.create');
    Route::post('/availabilities', [AvailabilityController::class, 'store'])->name('availabilities.store');
    Route::get('/availabilities/{id}/edit', [AvailabilityController::class, 'edit'])->name('availabilities.edit');
    Route::put('/availabilities/{id}', [AvailabilityController::class, 'update'])->name('availabilities.update');
    Route::delete('/availabilities/{id}', [AvailabilityController::class, 'destroy'])->name('availabilities.destroy');
    Route::post('/availabilities/batch/delete', [App\Http\Controllers\Admin\AvailabilityController::class, 'batchDelete'])
        ->name('availabilities.batch.delete');
    
    // Batch availability creation
    Route::get('/availabilities/batch', [AvailabilityController::class, 'batchCreate'])->name('availabilities.batch');
    Route::post('/availabilities/batch', [AvailabilityController::class, 'batchStore'])->name('availabilities.batch.store');
    
    // Booked sessions management
    Route::get('/booked-sessions', [BookedSessionController::class, 'index'])->name('booked-sessions.index');
    Route::get('/booked-sessions/{id}', [BookedSessionController::class, 'show'])->name('booked-sessions.show');
    Route::post('/booked-sessions/{id}/approve', [BookedSessionController::class, 'approve'])->name('booked-sessions.approve');
    Route::post('/booked-sessions/{id}/reject', [BookedSessionController::class, 'reject'])->name('booked-sessions.reject');
    Route::post('/booked-sessions/{id}/cancel', [BookedSessionController::class, 'cancel'])->name('booked-sessions.cancel');
    Route::post('/booked-sessions/{id}/meeting-link', [BookedSessionController::class, 'updateMeetingLink'])->name('booked-sessions.meeting-link');
    Route::post('/booked-sessions/{id}/complete', [BookedSessionController::class, 'markComplete'])->name('booked-sessions.complete');
});