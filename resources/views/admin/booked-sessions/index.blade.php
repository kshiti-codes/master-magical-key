@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Manage Booked Sessions</div>

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

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title">Booked Sessions</h2>
    </div>

    <!-- Filters -->
    <div class="mb-4">
        <form action="{{ route('admin.booked-sessions.index') }}" method="GET" class="row g-3" style="display: flex; align-items: center; flex-direction: row; gap: 10px;">
            <div class="col-md-4">
                <label for="coach_id" class="form-label">Coach</label>
                <select name="coach_id" id="coach_id" class="form-select">
                    <option value="">All Coaches</option>
                    @foreach($coaches as $coach)
                        <option value="{{ $coach->id }}" {{ $coachId == $coach->id ? 'selected' : '' }}>
                            {{ $coach->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn-admin-primary me-2">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.booked-sessions.index') }}" class="btn-admin-secondary">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    @if($bookedSessions->isEmpty())
        <div class="alert alert-info">
            No booked sessions found.
        </div>
    @else
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Coach</th>
                        <th>Session Type</th>
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
                            <td>{{ $session->sessionType->name }} ({{ $session->duration }} min)</td>
                            <td>{{ $session->formatted_session_time }}</td>
                            <td>{!! $session->status_badge !!}</td>
                            <td class="actions-cell">
                                <a href="{{ route('admin.booked-sessions.show', $session->id) }}" class="btn-admin-secondary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $bookedSessions->links() }}
        </div>
    @endif
</div>
@endsection