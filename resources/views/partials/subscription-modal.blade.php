<!-- resources/views/partials/subscription-modal.blade.php -->
<div id="subscriptionModal" class="subscription-modal">
    <div class="subscription-modal-content">
        <button class="subscription-modal-close">&times;</button>
        <div class="subscription-modal-header">
            <div class="subscription-modal-icon">
                <i class="fas fa-crown"></i>
            </div>
            <h2>Unlock Complete Access</h2>
        </div>
        <div class="subscription-modal-body">
            <p>Choose a subscription plan for unlimited access to all content:</p>
            
            <div class="subscription-plans">
                @forelse($subscriptionPlans ?? [] as $plan)
                    <div class="plan-card {{ $plan->is_lifetime ? 'plan-lifetime' : '' }}">
                        <h3 class="plan-title">{{ $plan->name }}</h3>
                        <div class="plan-price">
                            <span class="price-amount">${{ number_format($plan->price, 2) }}</span>
                            <span class="price-period">{{ $plan->is_lifetime ? 'one-time' : '/'.$plan->billing_interval }}</span>
                        </div>
                        <div class="plan-description">{{ Str::limit($plan->description, 60) }}</div>
                        <a href="{{ route('subscriptions.show', $plan->id) }}" class="btn-subscribe">Choose Plan</a>
                    </div>
                @empty
                    <div class="no-plans">
                        <p>No subscription plans are currently available.</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="subscription-modal-footer">
            <label class="dont-show-again">
                <input type="checkbox" id="dontShowAgain"> Don't show this again
            </label>
        </div>
    </div>
</div>