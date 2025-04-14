@extends('layouts.app')

@push('styles')
<style>
    .manage-subscription-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
    }
    
    .manage-title {
        text-align: center;
        color: #fff;
        font-family: 'Cinzel', serif;
        margin-bottom: 30px;
        font-size: 2.2rem;
        letter-spacing: 2px;
        text-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
    }
    
    .subscription-card {
        background: rgba(10, 10, 30, 0.8);
        border-radius: 15px;
        padding: 30px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        margin-bottom: 30px;
    }
    
    .subscription-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .subscription-name {
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        font-size: 1.5rem;
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
    
    .status-canceled {
        background: rgba(255, 0, 0, 0.2);
        color: #ffa0a0;
        border: 1px solid rgba(255, 0, 0, 0.4);
    }
    
    .subscription-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .detail-item {
        background: rgba(10, 10, 30, 0.5);
        padding: 15px;
        border-radius: 10px;
    }
    
    .detail-label {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .detail-value {
        color: white;
        font-size: 1.1rem;
    }
    
    .subscription-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
    }
    
    .btn-action {
        padding: 8px 20px;
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: linear-gradient(to right, #4b0082, #9400d3);
        border: none;
        color: white;
    }
    
    .btn-primary:hover {
        background: linear-gradient(to right, #9400d3, #4b0082);
        transform: translateY(-3px);
        box-shadow: 0 7px 14px rgba(75, 0, 130, 0.4);
        color: white;
    }
    
    .btn-danger {
        background: rgba(255, 0, 0, 0.2);
        border: 1px solid rgba(255, 0, 0, 0.4);
        color: #ffa0a0;
    }
    
    .btn-danger:hover {
        background: rgba(255, 0, 0, 0.3);
        color: white;
    }
    
    .lifetime-note {
        margin-top: 20px;
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.6);
        font-style: italic;
        text-align: center;
    }
    
    .no-subscriptions {
        text-align: center;
        padding: 50px 20px;
        background: rgba(10, 10, 30, 0.5);
        border-radius: 10px;
        color: rgba(255, 255, 255, 0.8);
    }
    
    .cancel-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    
    .cancel-modal.active {
        display: flex;
    }
    
    .cancel-modal-content {
        background: rgba(20, 20, 40, 0.95);
        border-radius: 15px;
        padding: 30px;
        max-width: 500px;
        width: 100%;
        border: 1px solid rgba(138, 43, 226, 0.5);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }
    
    .modal-title {
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        font-size: 1.5rem;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .modal-message {
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 30px;
        line-height: 1.6;
    }
    
    .modal-actions {
        display: flex;
        justify-content: center;
        gap: 20px;
    }
    
    .secondary-action {
        margin-top: 30px;
        text-align: center;
    }
    
    .btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
    }
    
    .btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .subscription-info {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
        margin-top: 20px;
        line-height: 1.5;
    }
    
    @media (max-width: 767px) {
        .manage-title {
            font-size: 1.8rem;
        }
        
        .subscription-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .subscription-details {
            grid-template-columns: 1fr;
        }
        
        .subscription-actions {
            justify-content: center;
        }
        
        .modal-actions {
            flex-direction: column;
            gap: 15px;
        }
        
        .modal-actions .btn-action {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle opening cancel modal
        const cancelBtns = document.querySelectorAll('.btn-cancel-subscription');
        const cancelModal = document.getElementById('cancelModal');
        const modalCancelForm = document.getElementById('modalCancelForm');
        
        cancelBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const subscriptionId = this.getAttribute('data-subscription-id');
                modalCancelForm.action = `/subscriptions/${subscriptionId}/cancel`;
                cancelModal.classList.add('active');
                document.body.style.overflow = 'hidden';
            });
        });
        
        // Handle closing modal
        const closeBtns = document.querySelectorAll('.btn-close-modal');
        closeBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                cancelModal.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
        
        // Also close modal when clicking outside
        cancelModal.addEventListener('click', function(e) {
            if (e.target === cancelModal) {
                cancelModal.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    });
</script>
@endpush

@section('content')
<div class="manage-subscription-container">
    <h1 class="manage-title">Manage Your Subscription</h1>
    
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
    
    @if(count($subscriptions) > 0)
        @foreach($subscriptions as $subscription)
            <div class="subscription-card">
                <div class="subscription-header">
                    <div class="subscription-name">{{ $subscription->plan->name }}</div>
                    <div>
                        <span class="subscription-status {{ $subscription->status === 'active' ? ($subscription->plan->is_lifetime ? 'status-lifetime' : 'status-active') : 'status-canceled' }}">
                            {{ $subscription->status_text }}
                        </span>
                    </div>
                </div>
                
                <div class="subscription-details">
                    <div class="detail-item">
                        <div class="detail-label">Amount Paid</div>
                        <div class="detail-value">${{ number_format($subscription->amount_paid, 2) }} {{ $subscription->plan->currency }}</div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label">Start Date</div>
                        <div class="detail-value">{{ $subscription->start_date->format('F j, Y') }}</div>
                    </div>
                    
                    @if($subscription->plan->is_lifetime)
                        <div class="detail-item">
                            <div class="detail-label">Type</div>
                            <div class="detail-value">Lifetime Access</div>
                        </div>
                    @elseif($subscription->status === 'active')
                        <div class="detail-item">
                            <div class="detail-label">Next Billing Date</div>
                            <div class="detail-value">{{ $subscription->next_billing_date ? $subscription->next_billing_date->format('F j, Y') : 'N/A' }}</div>
                        </div>
                    @else
                        <div class="detail-item">
                            <div class="detail-label">End Date</div>
                            <div class="detail-value">{{ $subscription->end_date ? $subscription->end_date->format('F j, Y') : 'N/A' }}</div>
                        </div>
                    @endif
                    
                    <div class="detail-item">
                        <div class="detail-label">Billing</div>
                        <div class="detail-value">{{ $subscription->plan->billing_description }}</div>
                    </div>
                </div>
                
                <div class="subscription-actions">
                    @if($subscription->status === 'active' && !$subscription->plan->is_lifetime)
                        <button class="btn-action btn-danger btn-cancel-subscription" data-subscription-id="{{ $subscription->id }}">
                            Cancel Subscription
                        </button>
                    @endif
                    
                    @if($subscription->plan->is_lifetime)
                        <a href="{{ route('videos.index') }}" class="btn-action btn-primary">Access Training Videos</a>
                    @elseif($subscription->status === 'active')
                        <a href="{{ route('chapters.index') }}" class="btn-action btn-primary">Browse Chapters</a>
                    @elseif($subscription->status === 'canceled')
                        <a href="{{ route('subscriptions.index') }}" class="btn-action btn-primary">View Subscription Plans</a>
                    @endif
                </div>
                
                @if($subscription->plan->is_lifetime)
                    <div class="lifetime-note">
                        Your lifetime subscription gives you permanent access to all content, including chapters, spells, and training videos.
                    </div>
                @elseif($subscription->status === 'canceled')
                    <div class="subscription-info">
                        Your subscription has been canceled. You will continue to have access until {{ $subscription->end_date->format('F j, Y') }}.
                    </div>
                @else
                    <div class="subscription-info">
                        You can cancel your subscription at any time. If you cancel, you'll still have access until the end of your current billing period.
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div class="no-subscriptions">
            <h3>No Subscriptions Found</h3>
            <p>You don't have any active subscriptions at the moment.</p>
            <a href="{{ route('subscriptions.index') }}" class="btn-action btn-primary" style="display: inline-block; margin-top: 20px;">
                View Subscription Plans
            </a>
        </div>
    @endif
</div>

<!-- Cancel Confirmation Modal -->
<div class="cancel-modal" id="cancelModal">
    <div class="cancel-modal-content">
        <h2 class="modal-title">Cancel Subscription</h2>
        
        <div class="modal-message">
            <p>Are you sure you want to cancel your subscription?</p>
            <p>You will continue to have access until the end of your current billing period.</p>
        </div>
        
        <div class="modal-actions">
            <form method="POST" id="modalCancelForm">
                @csrf
                <button type="submit" class="btn-action btn-danger">
                    Yes, Cancel My Subscription
                </button>
            </form>
            
            <button class="btn-action btn-secondary btn-close-modal">
                No, Keep My Subscription
            </button>
        </div>
        
        <div class="secondary-action">
            <a href="{{ route('home') }}" class="btn-close-modal" style="color: rgba(255, 255, 255, 0.6); text-decoration: none; font-size: 0.9rem;">
                Return to Homepage
            </a>
        </div>
    </div>
</div>
@endsection