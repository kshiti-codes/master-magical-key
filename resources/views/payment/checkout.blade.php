@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/payment.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="payment-container">
    <div class="checkout-card">
        <h1 class="checkout-title">Purchase Chapter</h1>
        
        <div class="chapter-details">
            <h2 class="chapter-title">Chapter {{ $chapter->id }}: {{ $chapter->title }}</h2>
            <p class="chapter-description">{{ $chapter->description }}</p>
            
            <div class="price-details">
                <p class="price">${{ number_format($chapter->price, 2) }} {{ $chapter->currency ?? 'AUD' }}</p>
            </div>
        </div>
        
        <div class="payment-options">
            <form action="{{ route('payment.process') }}" method="POST">
                @csrf
                <input type="hidden" name="chapter_id" value="{{ $chapter->id }}">
                
                <button type="submit" class="btn-paypal">
                    <img src="https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png" alt="Check out with PayPal">
                </button>
            </form>
            
            <div class="payment-note">
                <p>You will be redirected to PayPal to complete your payment.</p>
                <p>After payment, you'll have immediate access to this chapter.</p>
            </div>
        </div>
        
        <div class="back-link">
            <a href="{{ route('chapters.index') }}" class="mystic-link">
                <i class="fas fa-arrow-left"></i> Back to Chapters
            </a>
        </div>
    </div>
</div>
@endsection