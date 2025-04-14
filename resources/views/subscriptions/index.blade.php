@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/components/subscriptions.css') }}">
@endpush

@section('content')
<div class="subscription-container">
    <h1 class="subscription-title">Subscription Plans</h1>
    
    <p class="subscription-subtitle">Choose the right plan for your mystical journey</p>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    @if(session('info'))
        <div class="alert alert-info">
            {{ session('info') }}
        </div>
    @endif
    
    <!-- Active Subscription (if any) -->
    @auth
        @if($hasLifetimeSubscription)
            <div class="current-subscription">
                <h2 class="current-subscription-title">Your Current Subscription</h2>
                
                <div class="subscription-info">
                    <div class="subscription-info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="subscription-status status-lifetime">Lifetime Access</span>
                        </div>
                    </div>
                    
                    <div class="subscription-info-item">
                        <div class="info-label">Plan</div>
                        <div class="info-value">Early Lifetime Access</div>
                    </div>
                    
                    <div class="subscription-info-item">
                        <div class="info-label">Benefits</div>
                        <div class="info-value">Full access to all content including chapters, spells, and training videos</div>
                    </div>
                </div>
                
                <div class="subscription-actions">
                    <a href="{{ route('subscription.manage') }}" class="btn-manage">Manage Subscription</a>
                </div>
            </div>
        @elseif(count($userSubscriptions) > 0)
            @php
                $activeSubscription = $userSubscriptions->where('status', 'active')->first();
            @endphp
            
            @if($activeSubscription)
                <div class="current-subscription">
                    <h2 class="current-subscription-title">Your Current Subscription</h2>
                    
                    <div class="subscription-info">
                        <div class="subscription-info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="subscription-status status-active">{{ $activeSubscription->status_text }}</span>
                            </div>
                        </div>
                        
                        <div class="subscription-info-item">
                            <div class="info-label">Plan</div>
                            <div class="info-value">{{ $activeSubscription->plan->name }}</div>
                        </div>
                        
                        @if($activeSubscription->next_billing_date)
                            <div class="subscription-info-item">
                                <div class="info-label">Next Billing Date</div>
                                <div class="info-value">{{ $activeSubscription->next_billing_date->format('F j, Y') }}</div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="subscription-actions">
                        <a href="{{ route('subscription.manage') }}" class="btn-manage">Manage Subscription</a>
                    </div>
                </div>
            @endif
        @endif
    @endauth
    
    <!-- Subscription Plans -->
    <div class="subscription-cards">
        @foreach($plans as $plan)
            <div class="subscription-card">
                @if($plan->is_lifetime)
                    <div class="premium-badge">Limited Time Offer</div>
                @endif
                
                <h2 class="subscription-card-title">{{ $plan->name }}</h2>
                
                <div class="subscription-price">{{ $plan->formatted_price }}</div>
                <div class="subscription-billing">{{ $plan->billing_description }}</div>
                
                <div class="subscription-features">
                    @if($plan->is_lifetime)
                        <div class="subscription-feature">
                            <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                            <div>Lifetime access to all chapters and spells</div>
                        </div>
                        <div class="subscription-feature">
                            <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                            <div>Free access to all training videos</div>
                        </div>
                        <div class="subscription-feature">
                            <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                            <div>Access to all future content updates</div>
                        </div>
                        <div class="subscription-feature">
                            <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                            <div>No recurring payments ever</div>
                        </div>
                    @else
                        <div class="subscription-feature">
                            <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                            <div>Access to all chapters and spells</div>
                        </div>
                        <div class="subscription-feature">
                            <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                            <div>Purchase training videos separately</div>
                        </div>
                        <div class="subscription-feature">
                            <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
                            <div>Cancel anytime</div>
                        </div>
                    @endif
                </div>
                
                <div class="subscription-action">
                    @auth
                        @if($hasLifetimeSubscription)
                            <button class="btn-subscribe disabled" disabled>Already Subscribed</button>
                        @else
                            <a href="{{ route('subscriptions.show', $plan) }}" class="btn-subscribe">Subscribe Now</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn-subscribe">Login to Subscribe</a>
                    @endauth
                </div>
                
                @if($plan->available_until)
                    <div class="subscription-limited">
                        <i class="fas fa-clock"></i> Available until {{ $plan->available_until->format('F j, Y') }}
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection