@extends('layouts.admin')

@section('title', 'Create Subscription Plan')

@section('content')
<h1 class="admin-page-title">Create Subscription Plan</h1>

<div class="admin-card">
    <form action="{{ route('admin.subscriptions.store') }}" method="POST" class="admin-form">
        @csrf
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Plan Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" min="0" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required>
                @error('price')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-3 mb-3">
                <label for="currency" class="form-label">Currency</label>
                <select class="form-control @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                    <option value="AUD" {{ old('currency') == 'AUD' ? 'selected' : '' }}>AUD</option>
                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                    <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP</option>
                    <option value="CAD" {{ old('currency') == 'CAD' ? 'selected' : '' }}>CAD</option>
                </select>
                @error('currency')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row">
            <div class="col-12 mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Describe the benefits of this subscription plan. This will be displayed to customers.</small>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_lifetime" name="is_lifetime" {{ old('is_lifetime') ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_lifetime">Lifetime Plan (One-time payment)</label>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active (Available for purchase)</label>
                </div>
            </div>
        </div>
        
        <div class="row" id="billingIntervalRow">
            <div class="col-md-6 mb-3">
                <label for="billing_interval" class="form-label">Billing Interval</label>
                <select class="form-control @error('billing_interval') is-invalid @enderror" id="billing_interval" name="billing_interval">
                    <option value="month" {{ old('billing_interval') == 'month' ? 'selected' : '' }}>Monthly</option>
                    <option value="year" {{ old('billing_interval') == 'year' ? 'selected' : '' }}>Yearly</option>
                </select>
                @error('billing_interval')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="available_until" class="form-label">Available Until (Optional)</label>
                <input type="date" class="form-control @error('available_until') is-invalid @enderror" id="available_until" name="available_until" value="{{ old('available_until') }}">
                @error('available_until')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">If set, the plan will not be available for purchase after this date.</small>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-12">
                <button type="submit" class="btn-admin-primary">Create Subscription Plan</button>
                <a href="{{ route('admin.subscriptions.index') }}" class="btn-admin-secondary ms-2">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle lifetime toggle
        const isLifetimeCheckbox = document.getElementById('is_lifetime');
        const billingIntervalRow = document.getElementById('billingIntervalRow');
        
        function toggleBillingInterval() {
            if (isLifetimeCheckbox.checked) {
                billingIntervalRow.style.display = 'none';
            } else {
                billingIntervalRow.style.display = 'flex';
            }
        }
        
        // Initial state
        toggleBillingInterval();
        
        // Add change listener
        isLifetimeCheckbox.addEventListener('change', toggleBillingInterval);
    });
</script>
@endpush