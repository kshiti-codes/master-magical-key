@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Session Details</div>

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
                <h2 class="admin-card-title">Session Information</h2>
                <div>{!! $bookedSession->status_badge !!}</div>
            </div>

            <table class="admin-detail-table">
                <tbody>
                    <tr>
                        <th>Client</th>
                        <td>
                            {{ $bookedSession->user->name }}<br>
                            <small class="text-muted">{{ $bookedSession->user->email }}</small>
                        </td>
                    </tr>
                    <tr>
                        <th>Coach</th>
                        <td>{{ $bookedSession->coach->name }}</td>
                    </tr>
                    <tr>
                        <th>Session Type</th>
                        <td>{{ $bookedSession->sessionType->name }}</td>
                    </tr>
                    <tr>
                        <th>Date & Time</th>
                        <td>{{ $bookedSession->formatted_session_time }}</td>
                    </tr>
                    <tr>
                        <th>Duration</th>
                        <td>{{ $bookedSession->duration }} minutes</td>
                    </tr>
                    <tr>
                        <th>Payment</th>
                        <td>${{ number_format($bookedSession->amount_paid, 2) }} {{ $bookedSession->sessionType->currency }}</td>
                    </tr>
                    @if($bookedSession->transaction_id)
                    <tr>
                        <th>Transaction ID</th>
                        <td>{{ $bookedSession->transaction_id }}</td>
                    </tr>
                    @endif
                    @if($bookedSession->meeting_link)
                    <tr>
                        <th>Meeting Link</th>
                        <td>
                            <a href="{{ $bookedSession->meeting_link }}" target="_blank">{{ $bookedSession->meeting_link }}</a>
                        </td>
                    </tr>
                    @endif
                    @if($bookedSession->status === 'cancelled' && $bookedSession->cancellation_reason)
                    <tr>
                        <th>Cancellation Reason</th>
                        <td>{{ $bookedSession->cancellation_reason }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-md-4">
        <div class="admin-card mb-4">
            <h2 class="admin-card-title">Actions</h2>

            @if($bookedSession->status === 'pending')
                <form action="{{ route('admin.booked-sessions.approve', $bookedSession->id) }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn-admin-primary w-100">
                        <i class="fas fa-check"></i> Approve Session
                    </button>
                </form>

                <button type="button" class="btn-admin-secondary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times"></i> Reject Session
                </button>
            @endif

            @if($bookedSession->status === 'confirmed')
                <button type="button" class="btn-admin-secondary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#meetingLinkModal">
                    <i class="fas fa-link"></i> Set Meeting Link
                </button>

                <button type="button" class="btn-admin-secondary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="fas fa-ban"></i> Cancel Session
                </button>

                @if($bookedSession->session_time < now())
                    <form action="{{ route('admin.booked-sessions.complete', $bookedSession->id) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn-admin-primary w-100">
                            <i class="fas fa-check-double"></i> Mark as Completed
                        </button>
                    </form>
                @endif
            @endif

            <a href="{{ route('admin.booked-sessions.index') }}" class="btn-admin-secondary w-100">
                <i class="fas fa-arrow-left"></i> Back to Sessions
            </a>
        </div>

        <div class="admin-card">
            <h2 class="admin-card-title">Timeline</h2>
            <ul class="timeline">
                <li>
                    <div class="timeline-badge bg-success"><i class="fas fa-calendar-plus"></i></div>
                    <div class="timeline-panel">
                        <div class="timeline-heading">
                            <h5 class="timeline-title">Session Booked</h5>
                            <p><small class="text-muted">{{ $bookedSession->created_at->format('F j, Y g:i A') }}</small></p>
                        </div>
                    </div>
                </li>

                @if($bookedSession->status !== 'pending')
                <li>
                    <div class="timeline-badge {{ $bookedSession->status === 'cancelled' ? 'bg-danger' : 'bg-primary' }}">
                        <i class="fas {{ $bookedSession->status === 'cancelled' ? 'fa-ban' : 'fa-check' }}"></i>
                    </div>
                    <div class="timeline-panel">
                        <div class="timeline-heading">
                            <h5 class="timeline-title">
                                {{ $bookedSession->status === 'cancelled' ? 'Session Cancelled' : 'Session Confirmed' }}
                            </h5>
                            <p><small class="text-muted">{{ $bookedSession->updated_at->format('F j, Y g:i A') }}</small></p>
                        </div>
                    </div>
                </li>
                @endif

                @if($bookedSession->status === 'completed')
                <li>
                    <div class="timeline-badge bg-success"><i class="fas fa-check-double"></i></div>
                    <div class="timeline-panel">
                        <div class="timeline-heading">
                            <h5 class="timeline-title">Session Completed</h5>
                            <p><small class="text-muted">{{ $bookedSession->updated_at->format('F j, Y g:i A') }}</small></p>
                        </div>
                    </div>
                </li>
                @endif
            </ul>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Reject Session</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.booked-sessions.reject', $bookedSession->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to reject this session?</p>
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Reason for Rejection</label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-admin-primary">Reject Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelModalLabel">Cancel Session</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.booked-sessions.cancel', $bookedSession->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Cancelling a confirmed session may require a refund process.
                    </div>
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Reason for Cancellation</label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn-admin-primary">Cancel Session</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Meeting Link Modal -->
<div class="modal fade" id="meetingLinkModal" tabindex="-1" aria-labelledby="meetingLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="meetingLinkModalLabel">Set Meeting Link</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.booked-sessions.meeting-link', $bookedSession->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="meeting_link" class="form-label">Meeting URL</label>
                        <input type="url" class="form-control" id="meeting_link" name="meeting_link" 
                               value="{{ $bookedSession->meeting_link }}" required>
                        <div class="form-text">
                            Enter the Zoom, Google Meet, or other video conferencing link for this session
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-admin-primary">Save Meeting Link</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding: 20px 0;
        list-style: none;
        max-width: 1200px;
    }

    .timeline:before {
        content: " ";
        position: absolute;
        top: 0;
        bottom: 0;
        left: 18px;
        width: 2px;
        background-color: rgba(138, 43, 226, 0.3);
    }

    .timeline > li {
        margin-bottom: 20px;
        position: relative;
    }

    .timeline > li:before,
    .timeline > li:after {
        content: " ";
        display: table;
    }

    .timeline > li:after {
        clear: both;
    }

    .timeline > li > .timeline-panel {
        width: calc(100% - 50px);
        float: right;
        padding: 10px;
    }

    .timeline > li > .timeline-badge {
        color: #fff;
        width: 36px;
        height: 36px;
        line-height: 36px;
        font-size: 1em;
        text-align: center;
        position: absolute;
        top: 0;
        left: 0;
        border-radius: 50%;
        z-index: 1;
    }

    .timeline-title {
        margin-top: 0;
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        font-size: 1rem;
    }
</style>
@endpush