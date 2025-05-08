@extends('layouts.app')

@section('content')
<div class="container mt-4" style="max-width: 800px; align-items: center; margin: auto; margin-top: 50px;">
    <div class="session-header">
        <h1 class="session-title">Session Details</h1>
        <a href="{{ route('sessions.index') }}" class="back-button">
            <i class="fas fa-arrow-left"></i> Back to Sessions
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="session-card">
        <div class="session-card-header">
            <div class="session-status">
                {!! $bookedSession->status_badge !!}
            </div>
            
            <div class="session-datetime">
                <div class="session-date">
                    <i class="fas fa-calendar-alt"></i> {{ $bookedSession->session_time->format('l, F j, Y') }}
                </div>
                <div class="session-time">
                    <i class="fas fa-clock"></i> {{ $bookedSession->session_time->format('g:i A') }} - 
                    {{ $bookedSession->session_time->copy()->addMinutes($bookedSession->duration)->format('g:i A') }}
                </div>
            </div>
        </div>

        <div class="session-card-body">
            <div class="session-detail-table-container">
                <table class="session-detail-table">
                    <tbody>
                        <tr>
                            <th>Session Type</th>
                            <td>{{ $bookedSession->sessionType->name }}</td>
                        </tr>
                        <tr>
                            <th>Coach</th>
                            <td>{{ $bookedSession->coach->name }}</td>
                        </tr>
                        <tr>
                            <th>Duration</th>
                            <td>{{ $bookedSession->duration }} minutes</td>
                        </tr>
                        <tr>
                            <th>Payment</th>
                            <td>${{ number_format($bookedSession->amount_paid, 2) }} {{ $bookedSession->sessionType->currency }}</td>
                        </tr>
                        <tr>
                            <th>Transaction ID</th>
                            <td>{{ $bookedSession->transaction_id ?? 'N/A' }}</td>
                        </tr>
                        @if($bookedSession->meeting_link && $bookedSession->canJoinNow())
                        <tr class="meeting-link-row">
                            <th>Meeting Link</th>
                            <td>
                                <a href="{{ $bookedSession->meeting_link }}" target="_blank" class="join-link">
                                    <i class="fas fa-video"></i> Join Session
                                </a>
                                <div class="small text-muted">
                                    Click to join your session with {{ $bookedSession->coach->name }}
                                </div>
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

            <div class="session-actions">
                @if($bookedSession->status === 'confirmed' && $bookedSession->session_time->isFuture())
                    <div class="countdown-container">
                        <div class="countdown-label">Session starts in:</div>
                        <div class="countdown" id="sessionCountdown" data-session-time="{{ $bookedSession->session_time->toIso8601String() }}">
                            <div class="countdown-item">
                                <div class="countdown-number" id="countdown-days">0</div>
                                <div class="countdown-text">Days</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-number" id="countdown-hours">0</div>
                                <div class="countdown-text">Hours</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-number" id="countdown-minutes">0</div>
                                <div class="countdown-text">Minutes</div>
                            </div>
                            <div class="countdown-item">
                                <div class="countdown-number" id="countdown-seconds">0</div>
                                <div class="countdown-text">Seconds</div>
                            </div>
                        </div>
                    </div>

                    @if($bookedSession->canJoinNow() && $bookedSession->meeting_link)
                        <a href="{{ route('sessions.join', $bookedSession->id) }}" class="btn-join-session" target="_blank">
                            <i class="fas fa-video"></i> Join Session Now
                        </a>
                    @elseif($bookedSession->meeting_link)
                        <div class="join-info">
                            <i class="fas fa-info-circle"></i> You can join this session 10 minutes before the scheduled time.
                            <br>Click the link below to join:
                            <a href="{{ $bookedSession->meeting_link }}" target="_blank" class="join-link">
                                <i class="fas fa-video"></i> Meeting Link
                            </a>
                        </div>
                    @else
                        <div class="join-info">
                            <i class="fas fa-info-circle"></i> The meeting link will be available closer to the session time.
                        </div>
                    @endif
                @elseif($bookedSession->status === 'completed')
                    <div class="session-completed-message">
                        <i class="fas fa-check-circle"></i> This session has been completed. Thank you for attending!
                    </div>
                @elseif($bookedSession->status === 'cancelled')
                    <div class="session-cancelled-message">
                        <i class="fas fa-ban"></i> This session was cancelled.
                    </div>
                @elseif($bookedSession->status === 'pending')
                    <div class="session-pending-message">
                        <i class="fas fa-hourglass-half"></i> This session is pending confirmation from the coach.
                    </div>
                @endif
            </div>
        </div>

        <div class="session-card-footer">
            <div class="coach-info">
                <div class="coach-image">
                    @if($bookedSession->coach->profile_image)
                        <img src="{{ asset($bookedSession->coach->profile_image) }}" alt="{{ $bookedSession->coach->name }}">
                    @else
                        <div class="coach-image-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                </div>
                <div class="coach-details">
                    <div class="coach-name">{{ $bookedSession->coach->name }}</div>
                    <div class="coach-bio">{{ Str::limit($bookedSession->coach->bio, 100) ?? 'No bio available' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="session-note mt-4">
        <div class="note-icon"><i class="fas fa-info-circle"></i></div>
        <div class="note-content">
            <h4>Important Notes</h4>
            <ul>
                <li>You'll be able to join the session 10 minutes before the scheduled time.</li>
                <li>Please ensure your microphone and camera are working before the session.</li>
                <li>If you need to reschedule, please contact support as soon as possible.</li>
                <li>Client-initiated cancellations are not eligible for refunds.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Session Detail Styles */
    .session-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }
    
    .session-title {
        color: #fff;
        font-family: 'Cinzel', serif;
        font-size: 2rem;
        margin-bottom: 0;
    }
    
    .back-button {
        display: inline-block;
        padding: 8px 16px;
        background: rgba(30, 30, 60, 0.7);
        border: 1px solid rgba(138, 43, 226, 0.4);
        border-radius: 30px;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .back-button:hover {
        background: rgba(138, 43, 226, 0.3);
        transform: translateY(-2px);
        color: white;
    }
    
    .session-card {
        background: rgba(15, 15, 35, 0.8);
        border-radius: 15px;
        margin-bottom: 30px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        border: 1px solid rgba(138, 43, 226, 0.4);
    }
    
    .session-card-header {
        background: rgba(10, 10, 30, 0.9);
        padding: 20px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.4);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .session-status .badge {
        font-size: 1rem;
        padding: 8px 16px;
        border-radius: 30px;
    }
    
    .session-datetime {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }
    
    .session-date, .session-time {
        color: rgba(255, 255, 255, 0.9);
    }
    
    .session-date {
        font-size: 1.1rem;
        font-weight: 500;
        margin-bottom: 5px;
    }
    
    .session-time {
        font-size: 1rem;
    }
    
    .session-card-body {
        padding: 25px;
    }
    
    .session-detail-table-container {
        margin-bottom: 30px;
    }
    
    .session-detail-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .session-detail-table th,
    .session-detail-table td {
        padding: 12px 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .session-detail-table th {
        width: 30%;
        color: #d8b5ff;
        font-weight: 500;
        vertical-align: top;
        background-color: rgba(15, 15, 35, 0.4);
    }
    
    .session-detail-table tr:last-child th,
    .session-detail-table tr:last-child td {
        border-bottom: none;
    }
    
    .meeting-link-row {
        background-color: rgba(138, 43, 226, 0.1);
    }
    
    .join-link {
        display: inline-block;
        padding: 8px 15px;
        background: rgba(0, 128, 0, 0.3);
        color: #a0ffa0;
        border: 1px solid rgba(0, 128, 0, 0.5);
        border-radius: 5px;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .join-link:hover {
        background: rgba(0, 128, 0, 0.5);
        color: white;
    }
    
    .session-actions {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        background: rgba(10, 10, 30, 0.5);
        border-radius: 10px;
    }
    
    .countdown-container {
        margin-bottom: 25px;
        text-align: center;
    }
    
    .countdown-label {
        font-size: 1.1rem;
        color: #d8b5ff;
        margin-bottom: 10px;
    }
    
    .countdown {
        display: flex;
        gap: 15px;
        justify-content: center;
    }
    
    .countdown-item {
        text-align: center;
    }
    
    .countdown-number {
        font-size: 2rem;
        font-weight: 700;
        color: white;
        background: rgba(138, 43, 226, 0.3);
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin-bottom: 5px;
    }
    
    .countdown-text {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.7);
    }
    
    .btn-join-session {
        display: inline-block;
        padding: 12px 30px;
        background: linear-gradient(to right, #4b0082, #9400d3);
        color: white;
        border-radius: 30px;
        text-decoration: none;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        border: none;
    }
    
    .btn-join-session:hover {
        transform: translateY(-3px);
        box-shadow: 0 7px 15px rgba(148, 0, 211, 0.4);
        color: white;
    }
    
    .join-info, 
    .session-completed-message, 
    .session-cancelled-message,
    .session-pending-message {
        padding: 15px;
        border-radius: 8px;
        text-align: center;
    }
    
    .join-info {
        background: rgba(23, 162, 184, 0.2);
        color: #a0e0ff;
    }
    
    .session-completed-message {
        background: rgba(40, 167, 69, 0.2);
        color: #a0ffa0;
    }
    
    .session-cancelled-message {
        background: rgba(220, 53, 69, 0.2);
        color: #ffa0a0;
    }
    
    .session-pending-message {
        background: rgba(255, 193, 7, 0.2);
        color: #ffe0a0;
    }
    
    .session-card-footer {
        background: rgba(10, 10, 30, 0.5);
        padding: 20px;
        border-top: 1px solid rgba(138, 43, 226, 0.4);
    }
    
    .coach-info {
        display: flex;
        align-items: center;
    }
    
    .coach-image {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 20px;
        border: 2px solid rgba(138, 43, 226, 0.5);
    }
    
    .coach-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .coach-image-placeholder {
        width: 100%;
        height: 100%;
        background: rgba(30, 30, 60, 0.5);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .coach-details {
        flex: 1;
    }
    
    .coach-name {
        font-size: 1.2rem;
        font-weight: 500;
        color: #d8b5ff;
        margin-bottom: 5px;
    }
    
    .coach-bio {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
    }
    
    .session-note {
        background: rgba(15, 15, 35, 0.7);
        border-radius: 10px;
        padding: 20px;
        display: flex;
        border-left: 4px solid #d8b5ff;
    }
    
    .note-icon {
        font-size: 2rem;
        color: #d8b5ff;
        margin-right: 20px;
    }
    
    .note-content h4 {
        color: #d8b5ff;
        margin-bottom: 10px;
    }
    
    .note-content ul {
        padding-left: 20px;
        margin-bottom: 0;
        color: rgba(255, 255, 255, 0.8);
    }
    
    .note-content ul li {
        margin-bottom: 8px;
    }
    
    .note-content ul li:last-child {
        margin-bottom: 0;
    }
    
    @media (max-width: 768px) {
        .session-card-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .session-datetime {
            align-items: flex-start;
            margin-top: 10px;
        }
        
        .countdown {
            flex-wrap: wrap;
        }
        
        .countdown-number {
            width: 50px;
            height: 50px;
            font-size: 1.5rem;
        }
        
        .session-note {
            flex-direction: column;
        }
        
        .note-icon {
            margin-bottom: 10px;
            margin-right: 0;
            text-align: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const countdownElement = document.getElementById('sessionCountdown');
        
        if (countdownElement) {
            const sessionTime = new Date(countdownElement.dataset.sessionTime);
            
            // Update countdown every second
            const countdownInterval = setInterval(function() {
                const now = new Date();
                const distance = sessionTime - now;
                
                // If session time has passed, stop countdown
                if (distance < 0) {
                    clearInterval(countdownInterval);
                    document.getElementById('countdown-days').innerText = '0';
                    document.getElementById('countdown-hours').innerText = '0';
                    document.getElementById('countdown-minutes').innerText = '0';
                    document.getElementById('countdown-seconds').innerText = '0';
                    
                    // Reload page to show join button if needed
                    location.reload();
                    return;
                }
                
                // Calculate days, hours, minutes, seconds
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                // Update elements
                document.getElementById('countdown-days').innerText = days;
                document.getElementById('countdown-hours').innerText = hours;
                document.getElementById('countdown-minutes').innerText = minutes;
                document.getElementById('countdown-seconds').innerText = seconds;
                
                // If we're 10 minutes or less from the session, reload page to show join button
                if (distance <= 10 * 60 * 1000 && distance > 9.95 * 60 * 1000) {
                    location.reload();
                }
            }, 1000);
        }
    });
</script>
@endpush