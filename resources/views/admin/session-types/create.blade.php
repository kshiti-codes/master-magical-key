@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Add New Session Type</div>

@if ($errors->any())
    <div class="admin-alert admin-alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title">Session Type Information</h2>
        <a href="{{ route('admin.session-types.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back to Session Types
        </a>
    </div>

    <form action="{{ route('admin.session-types.store') }}" method="POST" class="admin-form">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="form-text">Inactive session types won't be shown to clients.</div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label for="duration" class="form-label">Duration (minutes)</label>
                    <select class="form-select" id="duration" name="duration" required>
                        <option value="">Select Duration</option>
                        <option value="15" {{ old('duration') == 15 ? 'selected' : '' }}>15 minutes</option>
                        <option value="30" {{ old('duration') == 30 ? 'selected' : '' }}>30 minutes</option>
                        <option value="45" {{ old('duration') == 45 ? 'selected' : '' }}>45 minutes</option>
                        <option value="60" {{ old('duration') == 60 ? 'selected' : '' }}>60 minutes</option>
                        <option value="90" {{ old('duration') == 90 ? 'selected' : '' }}>90 minutes</option>
                        <option value="120" {{ old('duration') == 120 ? 'selected' : '' }}>120 minutes</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}" min="0" step="0.01" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="currency" class="form-label">Currency</label>
                    <select class="form-select" id="currency" name="currency" required>
                        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                        <option value="AUD" {{ old('currency', 'AUD') == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                        <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                        <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                        <option value="CAD" {{ old('currency') == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save"></i> Save Session Type
            </button>
        </div>
    </form>
</div>
@endsection