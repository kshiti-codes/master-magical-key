@extends('layouts.admin')

@section('title', 'Create New User')

@section('content')
<h1 class="admin-page-title">Create New User</h1>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title mb-0">User Details</h2>
        <a href="{{ route('admin.users.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>
    
    @if ($errors->any())
    <div class="admin-alert admin-alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <form action="{{ route('admin.users.store') }}" method="POST" class="admin-form">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Must be at least 8 characters and contain letters, numbers, and symbols</small>
            </div>
            
            <div class="col-md-6 mb-3">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="is_admin" id="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}>
                <label class="form-check-label d-block" for="is_admin" style="margin-left: 1.5rem;">Administrator Access</label>
                <small class="text-muted d-block" style="margin-left: 1.5rem;">Administrators have full access to manage content, users, and settings.</small>
            </div>
        </div>
        
        <div class="admin-form-actions">
            <button type="submit" class="btn-admin-primary">Create User</button>
            <a href="{{ route('admin.users.index') }}" class="btn-admin-secondary ms-2">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .invalid-feedback {
        display: block;
        color: #ff7373;
        margin-top: 5px;
    }
    
    .admin-form-actions {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .text-muted {
        color: rgba(255, 255, 255, 0.5) !important;
    }
</style>
@endpush