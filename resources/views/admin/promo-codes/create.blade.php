@extends('layouts.admin')

@section('title', 'Create Promo Code')

@section('content')
<div class="admin-header">
    <h1 class="admin-page-title">Create Promo Code</h1>
    <a href="{{ route('admin.promo-codes.index') }}" class="btn-admin-secondary">
        <i class="fas fa-arrow-left"></i> Back to Promo Codes
    </a>
</div>

@if($errors->any())
    <div class="admin-alert admin-alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Please correct the following errors:</strong>
        <ul style="margin:0.5rem 0 0 0; padding-left:1.5rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.promo-codes.store') }}" method="POST">
    @csrf

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Promo Code Details</h2>
        </div>

        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label for="code" class="admin-form-label required">Code</label>
                <input type="text" class="admin-form-input" id="code" name="code"
                       value="{{ old('code') }}" placeholder="e.g. SAVE20" required>
                <small class="admin-form-help">Will be converted to uppercase automatically</small>
            </div>

            <div class="admin-form-group">
                <label for="discount_type" class="admin-form-label required">Discount Type</label>
                <select class="admin-form-select" id="discount_type" name="discount_type" required>
                    <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount ($)</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="discount_value" class="admin-form-label required">Discount Value</label>
                <input type="number" step="0.01" min="0.01" class="admin-form-input" id="discount_value" name="discount_value"
                       value="{{ old('discount_value') }}" placeholder="20" required>
                <small class="admin-form-help" id="discount_help">e.g. 20 for 20% off</small>
            </div>

            <div class="admin-form-group">
                <label for="min_order_amount" class="admin-form-label">Minimum Order Amount ($)</label>
                <input type="number" step="0.01" min="0" class="admin-form-input" id="min_order_amount" name="min_order_amount"
                       value="{{ old('min_order_amount') }}" placeholder="0.00">
                <small class="admin-form-help">Leave empty for no minimum</small>
            </div>

            <div class="admin-form-group">
                <label for="max_uses" class="admin-form-label">Maximum Uses</label>
                <input type="number" min="1" class="admin-form-input" id="max_uses" name="max_uses"
                       value="{{ old('max_uses') }}" placeholder="100">
                <small class="admin-form-help">Leave empty for unlimited uses</small>
            </div>

            <div class="admin-form-group">
                <label for="expires_at" class="admin-form-label">Expiry Date</label>
                <input type="date" class="admin-form-input" id="expires_at" name="expires_at"
                       value="{{ old('expires_at') }}">
                <small class="admin-form-help">Leave empty for no expiry</small>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Status</label>
                <div style="padding-top:8px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; color:rgba(255,255,255,0.8);">
                        <input type="checkbox" name="is_active" checked
                               style="width:18px; height:18px; accent-color:#9400d3;">
                        Active
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-form-actions">
        <button type="submit" class="btn-admin-primary">
            <i class="fas fa-save"></i> Create Promo Code
        </button>
        <a href="{{ route('admin.promo-codes.index') }}" class="btn-admin-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.getElementById('discount_type').addEventListener('change', function() {
        const help = document.getElementById('discount_help');
        help.textContent = this.value === 'percentage' ? 'e.g. 20 for 20% off' : 'e.g. 10 for $10.00 off';
    });
</script>
@endpush