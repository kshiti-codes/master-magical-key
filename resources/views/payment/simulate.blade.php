@extends('layouts.app')

@push('styles')
<link href="{{ asset('css/components/payment.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="payment-container">
    <div class="payment-simulator">
        <h1 class="simulator-title">PayPal Payment Simulation</h1>
        
        <div class="simulator-card">
            <div class="simulator-header">
                <img src="{{ asset('images/paypal-logo.png') }}" alt="PayPal" class="paypal-logo">
                <h2>Complete Your Payment</h2>
            </div>
            
            <div class="payment-details">
                <p class="payment-to">Payment to: Master Magical Key to the Universe</p>
                <p class="payment-for">Chapter {{ $chapter->id }}: {{ $chapter->title }}</p>
                <p class="payment-amount">${{ number_format($paymentIntent['amount'], 2) }} {{ $paymentIntent['currency'] }}</p>
            </div>
            
            <div class="payment-actions">
                <form action="{{ route('payment.complete') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-approve">Approve Payment</button>
                </form>
                
                <a href="{{ route('payment.cancel') }}" class="btn-cancel">Cancel</a>
            </div>
        </div>
        
        <div class="simulator-note">
            <p>This is a payment simulation for demonstration purposes.</p>
            <p>In a real environment, you would be redirected to PayPal's payment page.</p>
        </div>
    </div>
</div>
@endsection