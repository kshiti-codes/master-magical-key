@extends('layouts.admin')

@section('title', 'Subscription Plans')

@section('content')
<h1 class="admin-page-title">Subscription Plans</h1>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div style="float:right;">
        <a href="{{ route('admin.subscriptions.analytics') }}" class="btn-admin-secondary me-2">
            <i class="fas fa-chart-line"></i> Analytics
        </a>
    </div>
    <a href="{{ route('admin.subscriptions.create') }}" class="btn-admin-primary">
        <i class="fas fa-plus"></i> Add Subscription Plan
    </a>
</div>

<div class="admin-card">
    @if(count($plans) > 0)
    <table class="admin-table">
        <thead>
            <tr>
                <th width="25%">Name</th>
                <th>Type</th>
                <th>Price</th>
                <th>Status</th>
                <th>Subscribers</th>
                <th>Availability</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($plans as $plan)
            <tr>
                <td>{{ $plan->name }}</td>
                <td>
                    @if($plan->is_lifetime)
                        <span class="badge bg-purple">Lifetime</span>
                    @else
                        <span class="badge bg-blue">{{ ucfirst($plan->billing_interval) }}ly</span>
                    @endif
                </td>
                <td>${{ number_format($plan->price, 2) }} {{ $plan->currency }}</td>
                <td>
                    <span class="badge {{ $plan->is_active ? 'bg-success' : 'bg-danger' }}">
                        {{ $plan->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('admin.subscriptions.show', $plan) }}">
                        {{ $plan->active_subscribers_count }} active
                    </a>
                </td>
                <td>
                    @if($plan->available_until)
                        Until {{ $plan->available_until->format('M d, Y') }}
                    @else
                        Always
                    @endif
                </td>
                <td class="actions-cell">
                    <a href="{{ route('admin.subscriptions.show', $plan) }}" class="btn-admin-secondary btn-sm" title="View Details">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.subscriptions.edit', $plan) }}" class="btn-admin-secondary btn-sm" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.subscriptions.toggle-status', $plan) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-admin-secondary btn-sm" title="{{ $plan->is_active ? 'Deactivate' : 'Activate' }}" style="height: 100%;">
                            <i class="fas {{ $plan->is_active ? 'fa-toggle-off' : 'fa-toggle-on' }}"></i>
                        </button>
                    </form>
                    <form action="{{ route('admin.subscriptions.destroy', $plan) }}" method="POST" class="d-inline delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-admin-secondary btn-sm text-danger" title="Delete" style="background-color: #dc3545; border-color: #dc3545; height: 100%;" title="Delete Video">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="text-center py-4">
        <p>No subscription plans found.</p>
        <a href="{{ route('admin.subscriptions.create') }}" class="btn-admin-primary">
            <i class="fas fa-plus"></i> Create First Subscription Plan
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle delete confirmation
        const deleteForms = document.querySelectorAll('.delete-form');
        
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to delete this subscription plan? This action cannot be undone.')) {
                    this.submit();
                }
            });
        });
    });
</script>
@endpush