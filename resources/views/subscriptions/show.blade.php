@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/subscriptions.css') }}">
@endpush

@section('content')
<div class="subscription-detail-container">
    <a href="{{ route('subscriptions.index') }}" class="back-link"><i class="fas fa-arrow-left"></i> Back to Plans</a>
    
    <div class="subscription-detail-card">
        <h1 class="subscription-title">{{ $plan->name }}</h1>
        
        <div class="subscription-price-section">
            <div class="subscription-price">{{ $plan->formatted_price }} *</div>
            <div class="subscription-billing">{{ $plan->billing_description }}</div>
        </div>
        
        <div class="subscription-description">
            {{ $plan->description }}
        </div>
        
        <div class="subscription-benefits">
            <h2 class="benefits-title">What's Included</h2>
            
            <div class="benefits-list">
                @if($plan->is_lifetime)
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-book"></i></div>
                        <div class="benefit-text">Unlimited access to all chapters and magical content, forever.</div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-magic"></i></div>
                        <div class="benefit-text">All spells included with full download rights.</div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-video"></i></div>
                        <div class="benefit-text">Free access to all training videos, including future releases.</div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-gem"></i></div>
                        <div class="benefit-text">All future updates and additions at no extra cost.</div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-infinity"></i></div>
                        <div class="benefit-text">One-time payment, lifetime access with no recurring charges ever.</div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-clock"></i></div>
                        <div class="benefit-text">Limited time offer - available only for the first three months.</div>
                    </div>
                @else
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-book"></i></div>
                        <div class="benefit-text">Access to all chapters for the duration of your subscription.</div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-magic"></i></div>
                        <div class="benefit-text">All spells included with full download rights.</div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-video"></i></div>
                        <div class="benefit-text">Training videos available for separate purchase.</div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-calendar-alt"></i></div>
                        <div class="benefit-text">Monthly billing with the flexibility to cancel anytime.</div>
                    </div>
                    
                    <div class="benefit-item">
                        <div class="benefit-icon"><i class="fas fa-unlock"></i></div>
                        <div class="benefit-text">Access to new content as it's released during your subscription period.</div>
                    </div>
                @endif
            </div>
        </div>
        
        <div class="subscription-action">
            <form action="{{ route('subscriptions.purchase', $plan) }}" method="POST">
                @csrf
                <button type="submit" class="btn-subscribe">Subscribe Now</button>
            </form>
        </div>
        
        <div class="subscription-footnote">
            @if($plan->is_lifetime)
                Early lifetime subscription is available until {{ $plan->available_until->format('F j, Y') }}.
                After this date, only monthly subscriptions will be available.
            @else
                You can cancel your subscription at any time through your account settings.
                Access will continue until the end of your current billing period.
            @endif
            <br>
            * Taxes and fees may apply based on your location.
        </div>
    </div>
</div>
@endsection