@extends('layouts.app')

@push('styles')
<style>
    .thankyou-container {
        max-width: 800px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .thankyou-card {
        background: rgba(10, 10, 30, 0.8);
        border-radius: 15px;
        padding: 40px 30px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        text-align: center;
    }
    
    .success-icon {
        font-size: 5rem;
        color: #4BB543;
        margin-bottom: 20px;
        animation: pulse 2s ease-in-out;
    }
    
    @keyframes pulse {
        0% { transform: scale(0.8); opacity: 0; }
        50% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .thankyou-title {
        color: #fff;
        font-family: 'Cinzel', serif;
        margin-bottom: 20px;
        font-size: 2.5rem;
        letter-spacing: 2px;
        text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
    }
    
    .thankyou-subtitle {
        color: #d8b5ff;
        font-size: 1.5rem;
        margin-bottom: 30px;
    }
    
    .subscription-details {
        background: rgba(10, 10, 30, 0.5);
        padding: 25px;
        border-radius: 10px;
        margin-bottom: 30px;
        text-align: left;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .detail-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .detail-label {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .detail-value {
        color: white;
        font-weight: 500;
    }
    
    .subscription-status {
        display: inline-block;
        padding: 5px 15px;
        border-radius: 30px;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .status-active {
        background: rgba(0, 128, 0, 0.2);
        color: #a0ffa0;
        border: 1px solid rgba(0, 128, 0, 0.4);
    }
    
    .status-lifetime {
        background: rgba(138, 43, 226, 0.2);
        color: #d8b5ff;
        border: 1px solid rgba(138, 43, 226, 0.4);
    }
    
    .thankyou-message {
        color: rgba(255, 255, 255, 0.9);
        line-height: 1.6;
        margin-bottom: 30px;
    }
    
    .next-steps {
        margin-bottom: 40px;
    }
    
    .next-steps-title {
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        font-size: 1.4rem;
        margin-bottom: 20px;
    }
    
    .steps-list {
        text-align: left;
        max-width: 500px;
        margin: 0 auto;
    }
    
    .step-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    
    .step-number {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(138, 43, 226, 0.3);
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .step-text {
        color: rgba(255, 255, 255, 0.9);
    }
    
    .action-buttons {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 30px;
    }
    
    .btn-action {
        background: linear-gradient(to right, #4b0082, #9400d3);
        border: none;
        color: white;
        padding: 12px 25px;
        border-radius: 30px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-action:hover {
        background: linear-gradient(to right, #9400d3, #4b0082);
        transform: translateY(-3px);
        box-shadow: 0 7px 14px rgba(75, 0, 130, 0.4);
        color: white;
    }
    
    .btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    @media (max-width: 767px) {
        .thankyou-title {
            font-size: 2rem;
        }
        
        .thankyou-subtitle {
            font-size: 1.3rem;
        }
        
        .action-buttons {
            flex-direction: column;
            gap: 15px;
        }
        
        .btn-action {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="thankyou-container">
    <div class="thankyou-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="thankyou-title">Thank You!</h1>
        
        <p class="thankyou-subtitle">
            Your subscription has been successfully activated
        </p>
        
        <div class="subscription-details">
            <div class="detail-row">
                <div class="detail-label">Subscription Plan:</div>
                <div class="detail-value">{{ $subscription->plan->name }}</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Amount Paid:</div>
                <div class="detail-value">${{ number_format($subscription->amount_paid, 2) }} {{ $subscription->plan->currency }}</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">Start Date:</div>
                <div class="detail-value">{{ $subscription->start_date->format('F j, Y') }}</div>
            </div>
            
            @if(!$subscription->plan->is_lifetime && $subscription->next_billing_date)
                <div class="detail-row">
                    <div class="detail-label">Next Billing Date:</div>
                    <div class="detail-value">{{ $subscription->next_billing_date->format('F j, Y') }}</div>
                </div>
            @endif
            
            <div class="detail-row">
                <div class="detail-label">Status:</div>
                <div class="detail-value">
                    @if($subscription->plan->is_lifetime)
                        <span class="subscription-status status-lifetime">Lifetime Access</span>
                    @else
                        <span class="subscription-status status-active">Active</span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="thankyou-message">
            @if($subscription->plan->is_lifetime)
                <p>Congratulations on becoming a lifetime member! You now have unlimited access to all content, including chapters, spells, and training videos. Your journey to mastery begins now.</p>
            @else
                <p>Your subscription is now active! You have access to all chapters and spells for the duration of your subscription. Training videos can be purchased separately.</p>
            @endif
        </div>
        
        <div class="next-steps">
            <h2 class="next-steps-title">Your Next Steps</h2>
            
            <div class="steps-list">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-text">Explore the chapters and begin your mystical journey.</div>
                </div>
                
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-text">Download spells to enhance your practice and understanding.</div>
                </div>
                
                @if($subscription->plan->is_lifetime)
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-text">Watch training videos to deepen your connection with the material.</div>
                    </div>
                @else
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-text">Browse available training videos to supplement your learning.</div>
                    </div>
                @endif
                
                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-text">Connect with the wisdom and apply it to your daily practice.</div>
                </div>
            </div>
        </div>
        
        <div class="action-buttons">
            <a href="{{ route('home') }}" class="btn-action">Open Digital Book</a>
            @if($subscription->plan->is_lifetime)
                <a href="{{ route('videos.index') }}" class="btn-action">Explore Training Videos</a>
            @else
                <a href="{{ route('subscription.manage') }}" class="btn-action btn-secondary">Manage Subscription</a>
            @endif
        </div>
    </div>
</div>
@endsection