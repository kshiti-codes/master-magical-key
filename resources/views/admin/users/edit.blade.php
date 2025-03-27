@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<h1 class="admin-page-title">Edit User</h1>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title mb-0">User Details</h2>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.users.show', $user) }}" class="btn-admin-secondary">
                <i class="fas fa-eye"></i> View Profile
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn-admin-secondary ms-2">
                <i class="fas fa-arrow-left"></i> Back to Users
            </a>
        </div>
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
    
    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="admin-form">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-6">
                <!-- User Info Column -->
                <div class="mb-3">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Leave blank to keep current password</small>
                </div>
                
                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_admin" id="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}>
                        <label class="form-check-label d-block" for="is_admin" style="margin-left: 1.5rem;">Administrator Access</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="admin-form-actions">
            <button type="submit" class="btn-admin-primary">Update User</button>
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
    
    .user-stats {
        color: rgba(255, 255, 255, 0.8);
    }
    
    .user-stats strong {
        color: white;
    }
</style>
@endpush