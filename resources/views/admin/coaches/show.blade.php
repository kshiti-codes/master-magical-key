@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Coach Details</div>

@if(session('success'))
    <div class="admin-alert admin-alert-success">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="admin-alert admin-alert-danger">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="admin-card">
            <div class="text-center mb-4">
                @if($coach->profile_image)
                    <img src="{{ asset($coach->profile_image) }}" alt="{{ $coach->name }}" 
                         class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                @else
                    <div class="rounded-circle bg-secondary mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         style="width: 150px; height: 150px; color: white;">
                        <i class="fas fa-user fa-4x"></i>
                    </div>
                @endif
                
                <h2 class="admin-card-title mb-0">{{ $coach->name }}</h2>
                <div class="text-muted">{{ $coach->email }}</div>
                
                <div class="mt-3">
                    @if($coach->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>
            
            @if($coach->bio)
                <div class="bio-section mt-3">
                    <h3 class="section-heading">Bio</h3>
                    <p>{{ $coach->bio }}</p>
                </div>
            @endif
            
            <div class="action-buttons mt-4">
                <a href="{{ route('admin.coaches.edit', $coach->id) }}" class="btn-admin-primary w-100 mb-2">
                    <i class="fas fa-edit"></i> Edit Coach
                </a>
                <a href="{{ route('admin.availabilities.batch') }}?coach_id={{ $coach->id }}" class="btn-admin-secondary w-100 mb-2">
                    <i class="fas fa-calendar-plus"></i> Add Availability
                </a>
                <a href="{{ route('admin.coaches.index') }}" class="btn-admin-secondary w-100">
                    <i class="fas fa-arrow-left"></i> Back to Coaches
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="admin-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="admin-card-title">Session Types</h2>
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.session-types.index') }}" class="btn-admin-secondary btn-sm">
                        <i class="fas fa-cog"></i> Manage Session Types
                    </a>
                @endif
            </div>
            
            @if($sessionTypes->isEmpty())
                <div class="alert alert-info">
                    No session types are assigned to this coach yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Duration</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessionTypes as $sessionType)
                                <tr>
                                    <td>{{ $sessionType->name }}</td>
                                    <td>{{ $sessionType->duration }} minutes</td>
                                    <td>{{ $sessionType->formatted_price }}</td>
                                    <td>
                                        @if($sessionType->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        <div class="admin-card mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="admin-card-title">Upcoming Sessions</h2>
                <a href="{{ route('admin.booked-sessions.index') }}?coach_id={{ $coach->id }}" class="btn-admin-secondary btn-sm">
                    <i class="fas fa-external-link-alt"></i> View All
                </a>
            </div>
            
            @if($upcomingSessions->isEmpty())
                <div class="alert alert-info">
                    No upcoming sessions scheduled for this coach.
                </div>
            @else
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Session Type</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingSessions as $session)
                                <tr>
                                    <td>{{ $session->user->name }}</td>
                                    <td>{{ $session->sessionType->name }}</td>
                                    <td>{{ $session->formatted_session_time }}</td>
                                    <td>{!! $session->status_badge !!}</td>
                                    <td>
                                        <a href="{{ route('admin.booked-sessions.show', $session->id) }}" class="btn-admin-secondary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="admin-card-title">Past Sessions</h2>
                <a href="{{ route('admin.booked-sessions.index') }}?coach_id={{ $coach->id }}&status=completed" class="btn-admin-secondary btn-sm">
                    <i class="fas fa-external-link-alt"></i> View All
                </a>
            </div>
            
            @if($pastSessions->isEmpty())
                <div class="alert alert-info">
                    No past sessions for this coach.
                </div>
            @else
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Session Type</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pastSessions->take(5) as $session)
                                <tr>
                                    <td>{{ $session->user->name }}</td>
                                    <td>{{ $session->sessionType->name }}</td>
                                    <td>{{ $session->formatted_session_time }}</td>
                                    <td>{!! $session->status_badge !!}</td>
                                    <td>
                                        <a href="{{ route('admin.booked-sessions.show', $session->id) }}" class="btn-admin-secondary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .section-heading {
        font-size: 1.1rem;
        color: #d8b5ff;
        margin-bottom: 10px;
        font-weight: normal;
    }
    
    .bio-section {
        background: rgba(15, 15, 35, 0.4);
        padding: 15px;
        border-radius: 5px;
        color: rgba(255, 255, 255, 0.8);
    }
</style>
@endpush
