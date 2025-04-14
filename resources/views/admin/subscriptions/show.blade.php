@extends('layouts.admin')

@section('title', 'Subscription Plan Details')

@section('content')
<h1 class="admin-page-title">Subscription Plan Details</h1>

<div class="d-flex justify-content-end mb-4">
    <a href="{{ route('admin.subscriptions.edit', $plan) }}" class="btn-admin-primary me-2">
        <i class="fas fa-edit"></i> Edit Plan
    </a>
    <a href="{{ route('admin.subscriptions.index') }}" class="btn-admin-secondary" style="margin-left: 1rem;">
        <i class="fas fa-arrow-left"></i> Back to Plans
    </a>
</div>

<div class="row">
    <!-- Plan Details Card -->
    <div class="col-md-6 mb-4">
        <div class="admin-card">
            <h2 class="admin-card-title">Plan Information</h2>
            
            <table class="table">
                <tr>
                    <th style="width: 30%; background: rgba(30, 30, 70, 0.4); border-bottom: 1px solid rgba(138, 43, 226, 0.3);">Name</th>
                    <td style="border-bottom: 1px solid rgba(138, 43, 226, 0.3);">{{ $plan->name }}</td>
                </tr>
                <tr>
                    <th style="background: rgba(30, 30, 70, 0.4); border-bottom: 1px solid rgba(138, 43, 226, 0.3);">Type</th>
                    <td style="border-bottom: 1px solid rgba(138, 43, 226, 0.3);">
                        @if($plan->is_lifetime)
                            <span class="badge" style="background: rgba(138, 43, 226, 0.7);">Lifetime</span>
                        @else
                            <span class="badge" style="background: rgba(65, 105, 225, 0.7);">{{ ucfirst($plan->billing_interval) }}ly</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th style="background: rgba(30, 30, 70, 0.4); border-bottom: 1px solid rgba(138, 43, 226, 0.3);">Price</th>
                    <td style="border-bottom: 1px solid rgba(138, 43, 226, 0.3);">${{ number_format($plan->price, 2) }} {{ $plan->currency }}</td>
                </tr>
                <tr>
                    <th style="background: rgba(30, 30, 70, 0.4); border-bottom: 1px solid rgba(138, 43, 226, 0.3);">Status</th>
                    <td style="border-bottom: 1px solid rgba(138, 43, 226, 0.3);">
                        <span class="badge" style="background: {{ $plan->is_active ? 'rgba(40, 167, 69, 0.7)' : 'rgba(220, 53, 69, 0.7)' }};">
                            {{ $plan->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th style="background: rgba(30, 30, 70, 0.4); border-bottom: 1px solid rgba(138, 43, 226, 0.3);">Availability</th>
                    <td style="border-bottom: 1px solid rgba(138, 43, 226, 0.3);">
                        @if($plan->available_until)
                            Available until {{ $plan->available_until->format('F j, Y') }}
                            @if($plan->available_until->isPast())
                                <span class="badge" style="background: rgba(220, 53, 69, 0.7);">Expired</span>
                            @endif
                        @else
                            Always available
                        @endif
                    </td>
                </tr>
                <tr>
                    <th style="background: rgba(30, 30, 70, 0.4); border-bottom: 1px solid rgba(138, 43, 226, 0.3);">Date Created</th>
                    <td style="border-bottom: 1px solid rgba(138, 43, 226, 0.3);">{{ $plan->created_at->format('F j, Y') }}</td>
                </tr>
                <tr>
                    <th style="background: rgba(30, 30, 70, 0.4);">Last Updated</th>
                    <td>{{ $plan->updated_at->format('F j, Y') }}</td>
                </tr>
            </table>
            
            <div class="mt-4">
                <h3 class="admin-card-title">Description</h3>
                <div class="p-3" style="background: rgba(10, 10, 30, 0.4); border-radius: 5px; color: rgba(255, 255, 255, 0.9);">
                    {{ $plan->description }}
                </div>
            </div>
        </div>
    </div>
    
    <!-- Plan Stats Card -->
    <div class="col-md-6 mb-4">
        <div class="admin-card">
            <h2 class="admin-card-title">Plan Statistics</h2>
            
            <div class="row" style="display: flex;gap:1rem; flex-wrap: wrap;">
                <div class="col-md-6 mb-3">
                    <div class="p-3" style="background: rgba(10, 10, 30, 0.4); border-radius: 8px; text-align: center;padding: 20px;">
                        <div style="font-size: 2.5rem; font-weight: bold; color: #d8b5ff;">{{ $stats['active_subscribers'] }}</div>
                        <div>Active Subscribers</div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="p-3" style="background: rgba(10, 10, 30, 0.4); border-radius: 8px; text-align: center;padding: 20px;">
                        <div style="font-size: 2.5rem; font-weight: bold; color: #d8b5ff;">{{ $stats['total_subscribers'] }}</div>
                        <div>Total Subscribers</div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="p-3" style="background: rgba(10, 10, 30, 0.4); border-radius: 8px; text-align: center;padding: 20px;">
                        <div style="font-size: 2.5rem; font-weight: bold; color: #d8b5ff;">{{ $stats['canceled_subscribers'] }}</div>
                        <div>Canceled Subscribers</div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="p-3" style="background: rgba(10, 10, 30, 0.4); border-radius: 8px; text-align: center;padding: 20px;">
                        <div style="font-size: 2.5rem; font-weight: bold; color: #d8b5ff;">${{ number_format($stats['total_revenue'], 2) }}</div>
                        <div>Total Revenue</div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <h3 style="font-size: 1.2rem; color: #d8b5ff; margin-bottom: 15px; border-bottom: 1px solid rgba(138, 43, 226, 0.3); padding-bottom: 8px;">Actions</h3>
                
                <div class="d-flex flex-wrap gap-2">
                    <form action="{{ route('admin.subscriptions.toggle-status', $plan) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-admin-secondary">
                            <i class="fas {{ $plan->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                            {{ $plan->is_active ? 'Deactivate Plan' : 'Activate Plan' }}
                        </button>
                    </form>
                    
                    @if($stats['total_subscribers'] === 0)
                        <form action="{{ route('admin.subscriptions.destroy', $plan) }}" method="POST" id="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-admin-secondary text-danger">
                                <i class="fas fa-trash"></i> Delete Plan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Subscribers List -->
<div class="admin-card">
    <h2 class="admin-card-title">Active Subscribers</h2>
    
    @if(count($subscribers) > 0)
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Start Date</th>
                        <th>Next Billing</th>
                        <th>Amount Paid</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subscribers as $subscription)
                        <tr>
                            <td>{{ $subscription->user->name }}</td>
                            <td>{{ $subscription->user->email }}</td>
                            <td>{{ $subscription->start_date->format('M d, Y') }}</td>
                            <td>
                                @if($plan->is_lifetime)
                                    <span class="badge" style="background: rgba(138, 43, 226, 0.7);">Lifetime</span>
                                @elseif($subscription->next_billing_date)
                                    {{ $subscription->next_billing_date->format('M d, Y') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>${{ number_format($subscription->amount_paid, 2) }}</td>
                            <td>
                                <span class="badge" style="background: {{ $subscription->status === 'active' ? 'rgba(40, 167, 69, 0.7)' : 'rgba(220, 53, 69, 0.7)' }};">
                                    {{ ucfirst($subscription->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $subscribers->links() }}
        </div>
    @else
        <div class="text-center py-4" style="background: rgba(10, 10, 30, 0.4); border-radius: 5px; margin-top: 15px;">
            <p style="color: rgba(255, 255, 255, 0.7);">No active subscribers for this plan.</p>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle delete confirmation
        const deleteForm = document.getElementById('delete-form');
        if (deleteForm) {
            deleteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to delete this subscription plan? This action cannot be undone.')) {
                    this.submit();
                }
            });
        }

        // Paginated table rows hover effect
        const tableRows = document.querySelectorAll('.admin-table tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(138, 43, 226, 0.1)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
</script>
@endpush