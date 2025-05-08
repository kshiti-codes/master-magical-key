@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Session Type Details</div>

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
    <div class="col-md-8">
        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="admin-card-title">{{ $sessionType->name }}</h2>
                <div>
                    @if($sessionType->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 fw-bold">Description:</div>
                <div class="col-md-8">{{ $sessionType->description ?? 'No description' }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 fw-bold">Duration:</div>
                <div class="col-md-8">{{ $sessionType->duration }} minutes</div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 fw-bold">Price:</div>
                <div class="col-md-8">{{ $sessionType->formatted_price }}</div>
            </div>

            <!-- Coaches offering this session type -->
            <div class="row mb-3">
                <div class="col-md-4 fw-bold">Coaches:</div>
                <div class="col-md-8">
                    @if($coaches->isEmpty())
                        <p>No coaches assigned to this session type</p>
                    @else
                        <ul class="list-group">
                            @foreach($coaches as $coach)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    {{ $coach->name }}
                                    
                                    @if(!$coach->is_active)
                                        <span class="badge bg-warning">Inactive</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <div class="mt-3">
                        <a href="{{ route('admin.session-types.coaches', $sessionType->id) }}" class="btn-admin-primary btn-sm">
                            <i class="fas fa-user-edit"></i> Manage Coaches
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booked Sessions with this session type -->
        <div class="admin-card mt-4">
            <h2 class="admin-card-title">Booked Sessions</h2>

            @if($bookedSessions->isEmpty())
                <div class="alert alert-info">
                    No sessions have been booked with this session type yet.
                </div>
            @else
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Coach</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookedSessions as $session)
                                <tr>
                                    <td>{{ $session->user->name }}</td>
                                    <td>{{ $session->coach->name }}</td>
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

    <div class="col-md-4">
        <div class="admin-card">
            <h2 class="admin-card-title">Actions</h2>

            <div class="d-grid gap-2" style="display: flex; flex-direction: row; gap: 10px; align-items: center;">
                <a href="{{ route('admin.session-types.edit', $sessionType->id) }}" class="btn-admin-primary mb-2">
                    <i class="fas fa-edit"></i> Edit Session Type
                </a>
                
                <a href="{{ route('admin.session-types.coaches', $sessionType->id) }}" class="btn-admin-secondary mb-2">
                    <i class="fas fa-user-edit"></i> Manage Coaches
                </a>
                
                <button type="button" class="btn-admin-secondary text-danger mb-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash-alt"></i> Delete Session Type
                </button>
                
                <a href="{{ route('admin.session-types.index') }}" class="btn-admin-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Session Types
                </a>
            </div>
        </div>

        <div class="admin-card mt-4">
            <h2 class="admin-card-title">Statistics</h2>

            <div class="stat-item mb-3">
                <div class="stat-label">Total Bookings</div>
                <div class="stat-value">{{ $bookedSessions->count() }}</div>
            </div>

            <div class="stat-item mb-3">
                <div class="stat-label">Completed Sessions</div>
                <div class="stat-value">{{ $bookedSessions->where('status', 'completed')->count() }}</div>
            </div>

            <div class="stat-item mb-3">
                <div class="stat-label">Pending Sessions</div>
                <div class="stat-value">{{ $bookedSessions->where('status', 'pending')->count() }}</div>
            </div>

            <div class="stat-item mb-3">
                <div class="stat-label">Assigned Coaches</div>
                <div class="stat-value">{{ $coaches->count() }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Delete Session Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this session type?</p>
                
                @if($bookedSessions->isNotEmpty())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> This session type has {{ $bookedSessions->count() }} bookings. You cannot delete it.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                
                <form action="{{ route('admin.session-types.destroy', $sessionType->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-admin-primary text-danger" {{ $bookedSessions->isNotEmpty() ? 'disabled' : '' }}>
                        Delete Session Type
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .stat-item:last-child {
        border-bottom: none;
    }
    
    .stat-label {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .stat-value {
        font-size: 1.2rem;
        font-weight: bold;
        color: #d8b5ff;
    }
</style>
@endpush