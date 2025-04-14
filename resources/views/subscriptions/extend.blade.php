<!-- resources/views/subscriptions/extend.blade.php -->
@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/subscriptions.css') }}">
@endpush

@section('content')
<div class="subscription-detail-container">
    <a href="{{ route('subscription.manage') }}" class="back-link"><i class="fas fa-arrow-left"></i> Back to Subscriptions</a>
    
    <div class="subscription-detail-card">
        <h1 class="subscription-title">Extend Your Subscription</h1>
        
        <div style="background: rgba(10, 10, 30, 0.5); padding: 20px; border-radius: 10px; margin-bottom: 30px;">
            <p>You already have an active subscription that expires on 
            <strong>{{ $existingSubscription->end_date->format('F j, Y') }}</strong>.</p>
            
            <p>Instead of starting a new subscription today, we can extend your 
            current subscription by another billing period from your current end date.</p>
        </div>
        
        <div style="background: rgba(10, 10, 30, 0.6); padding: 25px; border-radius: 10px; margin-bottom: 30px;">
            <h3 style="color: #d8b5ff; margin-bottom: 20px; font-family: 'Cinzel', serif;">Subscription Details</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <div style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">Plan</div>
                    <div style="color: white; font-size: 1.1rem;">{{ $plan->name }}</div>
                </div>
                
                <div>
                    <div style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">Current End Date</div>
                    <div style="color: white; font-size: 1.1rem;">{{ $existingSubscription->end_date->format('F j, Y') }}</div>
                </div>
                
                <div>
                    <div style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">New End Date After Extension</div>
                    <div style="color: white; font-size: 1.1rem;">
                        @if($plan->billing_interval === 'month')
                            {{ $existingSubscription->end_date->copy()->addMonth()->format('F j, Y') }}
                        @elseif($plan->billing_interval === 'year')
                            {{ $existingSubscription->end_date->copy()->addYear()->format('F j, Y') }}
                        @endif
                    </div>
                </div>
                
                <div>
                    <div style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">Price</div>
                    <div style="color: #d8b5ff; font-size: 1.2rem; font-weight: 500;">{{ $plan->formatted_price }}</div>
                </div>
            </div>
        </div>
        
        <form method="POST" action="{{ route('subscriptions.extend', $existingSubscription) }}">
            @csrf
            <div style="margin-bottom: 25px;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="confirm_extension" id="confirm_extension" required 
                           style="width: 20px; height: 20px; margin-right: 10px;">
                    <span>I confirm that I want to extend my subscription by one billing period.</span>
                </label>
            </div>
            
            <div style="display: flex; justify-content: center; gap: 20px;">
                <button type="submit" class="btn-subscribe">
                    Extend Subscription
                </button>
                <a href="{{ route('subscription.manage') }}" 
                   style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.3); 
                          color: white; padding: 15px 30px; border-radius: 30px; text-decoration: none; 
                          transition: all 0.3s ease; display: inline-block;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection