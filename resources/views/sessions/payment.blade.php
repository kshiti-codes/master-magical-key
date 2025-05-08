@extends('layouts.app')

@section('content')
<div class="payment-container">
    <div class="back-link-container">
        <a href="{{ route('sessions.index') }}" class="cosmic-link">
            <i class="fas fa-arrow-left"></i> Back to Sessions
        </a>
    </div>
    
    <h1 class="page-title">Complete Your Booking</h1>
    <p class="page-subtitle">Review your session details and proceed to payment</p>

    @if(session('error'))
        <div class="cosmic-alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="payment-grid">
        <div class="booking-details">
            <div class="cosmic-card">
                <div class="cosmic-card-header">
                    <h2 class="cosmic-card-title">Booking Details</h2>
                </div>
                <div class="cosmic-card-body">
                    <div class="session-datetime">
                        <div class="date-badge">
                            <div class="month">{{ $bookedSession->session_time->format('M') }}</div>
                            <div class="day">{{ $bookedSession->session_time->format('d') }}</div>
                            <div class="year">{{ $bookedSession->session_time->format('Y') }}</div>
                        </div>
                        <div class="time-details">
                            <div class="weekday">{{ $bookedSession->session_time->format('l') }}</div>
                            <div class="time-range">
                                <i class="far fa-clock"></i>
                                {{ $bookedSession->session_time->format('g:i A') }} - 
                                {{ $bookedSession->session_time->copy()->addMinutes($bookedSession->duration)->format('g:i A') }}
                            </div>
                            <div class="duration">
                                <i class="fas fa-hourglass-half"></i> {{ $bookedSession->duration }} minutes
                            </div>
                        </div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">Session Type:</div>
                        <div class="info-value">{{ $bookedSession->sessionType->name }}</div>
                    </div>
                    
                    <div class="info-group">
                        <div class="info-label">Coach:</div>
                        <div class="info-value">
                            <div class="coach-info">
                                @if($bookedSession->coach->profile_image)
                                    <img src="{{ asset($bookedSession->coach->profile_image) }}" 
                                        alt="{{ $bookedSession->coach->name }}" class="coach-image-small">
                                @else
                                    <div class="coach-placeholder-small">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                                <span>{{ $bookedSession->coach->name }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-group total-price">
                        <div class="info-label">Price:</div>
                        <div class="info-value price">${{ number_format($bookedSession->amount_paid, 2) }} {{ $bookedSession->sessionType->currency }}</div>
                    </div>
                </div>
            </div>

            <div class="cosmic-card">
                <div class="cosmic-card-header">
                    <h2 class="cosmic-card-title">Important Notes</h2>
                </div>
                <div class="cosmic-card-body">
                    <div class="guidelines">
                        <div class="guideline-item">
                            <div class="guideline-icon info">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div class="guideline-content">
                                <p>You will receive a meeting link after your payment is confirmed.</p>
                            </div>
                        </div>
                        
                        <div class="guideline-item">
                            <div class="guideline-icon info">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="guideline-content">
                                <p>You can join the session up to 10 minutes before the scheduled time.</p>
                            </div>
                        </div>
                        
                        <div class="guideline-item">
                            <div class="guideline-icon warning">
                                <i class="fas fa-undo"></i>
                            </div>
                            <div class="guideline-content">
                                <p>In case of coach cancellation, you'll be eligible for a refund.</p>
                            </div>
                        </div>
                        
                        <div class="guideline-item">
                            <div class="guideline-icon danger">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div class="guideline-content">
                                <p>Client cancellations are not eligible for refunds.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="payment-sidebar">
            <div class="cosmic-card payment-card">
                <div class="cosmic-card-header payment-header">
                    <h2 class="cosmic-card-title">Complete Payment</h2>
                </div>
                <div class="cosmic-card-body">
                    <div class="payment-summary">
                        @php
                            $subtotal = $bookedSession->amount_paid / 1.1;
                            $tax = $bookedSession->amount_paid - $subtotal;
                        @endphp
                        <div class="summary-item">
                            <span class="summary-label">Session Fee:</span>
                            <span class="summary-value">${{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">GST (10%):</span>
                            <span class="summary-value">${{ number_format($tax, 2) }}</span>
                        </div>
                        <div class="summary-divider"></div>
                        <div class="summary-item total">
                            <span class="summary-label">Total:</span>
                            <span class="summary-value">${{ number_format($bookedSession->amount_paid, 2) }} {{ $bookedSession->sessionType->currency }}</span>
                        </div>
                    </div>

                    <form action="{{ route('sessions.process-payment') }}" method="POST">
                        @csrf
                        
                        <div class="payment-secure">
                            <i class="fas fa-lock"></i>
                            Your payment is safe and secure
                        </div>
                        
                        <button type="submit" class="cosmic-button payment-button">
                            <i class="fab fa-paypal"></i> Pay with PayPal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .payment-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    
    .back-link-container {
        margin-bottom: 1.5rem;
    }
    
    .cosmic-link {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .cosmic-link:hover {
        color: #d8b5ff;
        text-decoration: none;
    }
    
    .page-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        text-align: center;
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        text-shadow: 0 0 10px rgba(138, 43, 226, 0.8);
    }
    
    .page-subtitle {
        color: rgba(255, 255, 255, 0.7);
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .cosmic-alert {
        background: rgba(30, 30, 60, 0.7);
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 2rem;
    }
    
    .alert-danger {
        border-left: 4px solid #dc3545;
        color: #ffa0a0;
    }
    
    /* Grid layout */
    .payment-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }
    
    /* Card styling */
    .cosmic-card {
        background: rgba(30, 30, 60, 0.7);
        border: 1px solid rgba(138, 43, 226, 0.4);
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .cosmic-card-header {
        padding: 1.5rem;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .cosmic-card-title {
        color: #d8b5ff;
        margin: 0;
        font-size: 1.3rem;
        font-family: 'Cinzel', serif;
    }
    
    .cosmic-card-body {
        padding: 1.5rem;
    }
    
    /* Session datetime section */
    .session-datetime {
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: rgba(20, 20, 40, 0.5);
        border-radius: 8px;
    }
    
    .date-badge {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 80px;
        height: 90px;
        background: rgba(138, 43, 226, 0.2);
        border: 2px solid rgba(138, 43, 226, 0.5);
        border-radius: 8px;
        margin-right: 1.5rem;
    }
    
    .month {
        color: #d8b5ff;
        font-size: 0.9rem;
        text-transform: uppercase;
        font-weight: bold;
    }
    
    .day {
        color: #fff;
        font-size: 2rem;
        font-weight: bold;
        line-height: 1;
        margin: 0.3rem 0;
    }
    
    .year {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.8rem;
    }
    
    .time-details {
        display: flex;
        flex-direction: column;
    }
    
    .weekday {
        color: #fff;
        font-size: 1.3rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .time-range {
        color: #d8b5ff;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    
    .duration {
        color: rgba(255, 255, 255, 0.7);
    }
    
    /* Info groups */
    .info-group {
        display: flex;
        margin-bottom: 1.5rem;
    }
    
    .info-label {
        color: rgba(255, 255, 255, 0.7);
        width: 150px;
        font-weight: 500;
    }
    
    .info-value {
        color: #fff;
        flex: 1;
    }
    
    .total-price {
        margin-top: 2rem;
        background: rgba(20, 20, 40, 0.5);
        padding: 1rem;
        border-radius: 8px;
    }
    
    .price {
        color: #d8b5ff;
        font-size: 1.3rem;
        font-weight: bold;
    }
    
    .coach-info {
        display: flex;
        align-items: center;
    }
    
    .coach-image-small {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 1rem;
        border: 2px solid rgba(138, 43, 226, 0.5);
    }
    
    .coach-placeholder-small {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(108, 117, 125, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: rgba(255, 255, 255, 0.7);
    }
    
    /* Guidelines */
    .guidelines {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .guideline-item {
        display: flex;
        align-items: center;
    }
    
    .guideline-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .guideline-icon.info {
        background: rgba(23, 162, 184, 0.2);
        color: #a0e5ff;
    }
    
    .guideline-icon.warning {
        background: rgba(255, 193, 7, 0.2);
        color: #ffe0a0;
    }
    
    .guideline-icon.danger {
        background: rgba(220, 53, 69, 0.2);
        color: #ffa0a0;
    }
    
    .guideline-content p {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 0;
    }
    
    /* Payment card */
    .payment-card {
        position: sticky;
        top: 2rem;
    }
    
    .payment-header {
        background: rgba(138, 43, 226, 0.2);
    }
    
    .payment-summary {
        margin-bottom: 2rem;
    }
    
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 0.8rem 0;
    }
    
    .summary-label {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .summary-value {
        color: #fff;
        font-weight: 500;
    }
    
    .summary-divider {
        height: 1px;
        background: rgba(138, 43, 226, 0.3);
        margin: 0.5rem 0;
    }
    
    .summary-item.total {
        font-size: 1.2rem;
    }
    
    .summary-item.total .summary-value {
        color: #d8b5ff;
        font-weight: bold;
    }
    
    .payment-secure {
        text-align: center;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 1.5rem;
    }
    
    .payment-secure i {
        color: #28a745;
        margin-right: 0.5rem;
    }
    
    .payment-button {
        background: rgba(0, 123, 255, 0.7);
        padding: 1rem;
        font-size: 1.1rem;
        font-weight: bold;
        margin-bottom: 1rem;
    }
    
    .payment-button:hover {
        background: rgba(0, 123, 255, 0.9);
        box-shadow: 0 5px 15px rgba(0, 123, 255, 0.4);
    }
    
    .payment-methods {
        display: flex;
        justify-content: center;
        gap: 1rem;
        color: rgba(255, 255, 255, 0.5);
        font-size: 1.5rem;
    }
    
    /* Buttons */
    .cosmic-button {
        background: rgba(138, 43, 226, 0.6);
        color: white;
        border: none;
        padding: 0.8rem 1rem;
        border-radius: 30px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        text-decoration: none;
        width: 100%;
    }
    
    .cosmic-button:hover {
        background: rgba(138, 43, 226, 0.8);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(138, 43, 226, 0.4);
        color: white;
        text-decoration: none;
    }
    
    /* Responsive adjustments */
    @media (max-width: 991px) {
        .payment-grid {
            grid-template-columns: 1fr;
        }
        
        .payment-sidebar {
            order: -1;
        }
        
        .payment-card {
            position: static;
        }
    }
    
    @media (max-width: 768px) {
        .session-datetime {
            flex-direction: column;
            text-align: center;
        }
        
        .date-badge {
            margin-right: 0;
            margin-bottom: 1rem;
        }
        
        .info-group {
            flex-direction: column;
        }
        
        .info-label {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>
@endpush