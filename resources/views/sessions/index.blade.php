@extends('layouts.app')

@section('content')
<div class="sessions-container">
    <h1 class="page-title">My Sessions</h1>
    <p class="page-subtitle">Manage your upcoming and past coaching sessions</p>

    @if(session('success'))
        <div class="cosmic-alert alert-success">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="cosmic-alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <div class="action-button-container">
        <a href="{{ route('sessions.create') }}" class="cosmic-button create-button">
            <i class="fas fa-plus"></i> Book New Session
        </a>
    </div>

    <div class="tabs-container">
        <div class="tab-buttons">
            <button class="tab-button active" data-tab="upcoming">
                <i class="fas fa-calendar-alt"></i> Upcoming Sessions
                @if(count($upcomingSessions) > 0)
                    <span class="tab-badge">{{ count($upcomingSessions) }}</span>
                @endif
            </button>
            <button class="tab-button" data-tab="past">
                <i class="fas fa-history"></i> Past Sessions
            </button>
        </div>

        <div class="tab-content active" id="upcoming-tab">
            @if($upcomingSessions->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>No upcoming sessions</h3>
                    <p>Book your first session with one of our expert coaches</p>
                    <a href="{{ route('sessions.create') }}" class="cosmic-button">
                        <i class="fas fa-plus"></i> Book a Session
                    </a>
                </div>
            @else
                <div class="sessions-grid">
                    @foreach($upcomingSessions as $session)
                        <div class="cosmic-card session-card">
                            <div class="session-header">
                                <div class="session-date">
                                    <div class="date-circle">
                                        <span class="month">{{ $session->session_time->format('M') }}</span>
                                        <span class="day">{{ $session->session_time->format('d') }}</span>
                                    </div>
                                    <div class="date-time">
                                        <span class="weekday">{{ $session->session_time->format('l') }}</span>
                                        <span class="time">{{ $session->session_time->format('g:i A') }}</span>
                                    </div>
                                </div>
                                <div class="session-status">
                                    {!! $session->status_badge !!}
                                </div>
                            </div>
                            <div class="session-details">
                                <div class="coach-info">
                                    @if($session->coach->profile_image)
                                        <img src="{{ asset($session->coach->profile_image) }}" 
                                            alt="{{ $session->coach->name }}" class="coach-image">
                                    @else
                                        <div class="coach-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <div class="coach-name">{{ $session->coach->name }}</div>
                                </div>
                                <div class="session-type">
                                    <span class="label">Session:</span>
                                    <span class="value">{{ $session->sessionType->name }}</span>
                                </div>
                                <div class="session-duration">
                                    <span class="label">Duration:</span>
                                    <span class="value">{{ $session->duration }} minutes</span>
                                </div>
                            </div>
                            <div class="session-actions">
                                <a href="{{ route('sessions.show', $session->id) }}" class="cosmic-button view-button">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                
                                @if($session->canJoinNow() && $session->meeting_link)
                                    <a href="{{ route('sessions.join', $session->id) }}" class="cosmic-button join-button" target="_blank">
                                        <i class="fas fa-video"></i> Join
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="tab-content" id="past-tab">
            @if($pastSessions->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3>No past sessions</h3>
                    <p>Your session history will appear here</p>
                </div>
            @else
                <div class="sessions-grid">
                    @foreach($pastSessions as $session)
                        <div class="cosmic-card session-card {{ $session->status === 'cancelled' ? 'cancelled' : '' }}">
                            <div class="session-header">
                                <div class="session-date">
                                    <div class="date-circle past">
                                        <span class="month">{{ $session->session_time->format('M') }}</span>
                                        <span class="day">{{ $session->session_time->format('d') }}</span>
                                    </div>
                                    <div class="date-time">
                                        <span class="weekday">{{ $session->session_time->format('l') }}</span>
                                        <span class="time">{{ $session->session_time->format('g:i A') }}</span>
                                    </div>
                                </div>
                                <div class="session-status">
                                    {!! $session->status_badge !!}
                                </div>
                            </div>
                            <div class="session-details">
                                <div class="coach-info">
                                    @if($session->coach->profile_image)
                                        <img src="{{ asset($session->coach->profile_image) }}" 
                                            alt="{{ $session->coach->name }}" class="coach-image">
                                    @else
                                        <div class="coach-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <div class="coach-name">{{ $session->coach->name }}</div>
                                </div>
                                <div class="session-type">
                                    <span class="label">Session:</span>
                                    <span class="value">{{ $session->sessionType->name }}</span>
                                </div>
                                <div class="session-duration">
                                    <span class="label">Duration:</span>
                                    <span class="value">{{ $session->duration }} minutes</span>
                                </div>
                            </div>
                            <div class="session-actions">
                                <a href="{{ route('sessions.show', $session->id) }}" class="cosmic-button view-button">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .sessions-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }
    
    .page-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        text-align: center;
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        text-shadow: 0 0 10px rgba(138, 43, 226, 0.8);
    }
    
    .page-subtitle {
        color: rgba(255, 255, 255, 0.7);
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .cosmic-alert {
        background: rgba(30, 30, 60, 0.7);
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 2rem;
    }
    
    .alert-success {
        border-left: 4px solid #28a745;
        color: #a0ffa0;
    }
    
    .alert-danger {
        border-left: 4px solid #dc3545;
        color: #ffa0a0;
    }
    
    .action-button-container {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 2rem;
    }
    
    .cosmic-button {
        background: rgba(138, 43, 226, 0.6);
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 30px;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }
    
    .cosmic-button:hover {
        background: rgba(138, 43, 226, 0.8);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(138, 43, 226, 0.4);
        color: white;
        text-decoration: none;
    }
    
    .create-button {
        font-weight: bold;
    }
    
    .view-button {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .view-button:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .join-button {
        background: rgba(40, 167, 69, 0.7);
    }
    
    .join-button:hover {
        background: rgba(40, 167, 69, 0.9);
    }
    
    /* Tabs */
    .tabs-container {
        background: rgba(30, 30, 60, 0.4);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .tab-buttons {
        display: flex;
        background: rgba(30, 30, 60, 0.7);
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .tab-button {
        background: transparent;
        border: none;
        color: rgba(255, 255, 255, 0.7);
        padding: 1rem 2rem;
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
        position: relative;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .tab-button:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: transparent;
        transition: all 0.3s ease;
    }
    
    .tab-button.active {
        color: #fff;
        background: rgba(138, 43, 226, 0.1);
    }
    
    .tab-button.active:after {
        background: rgba(138, 43, 226, 0.8);
    }
    
    .tab-badge {
        background: rgba(138, 43, 226, 0.7);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
    }
    
    .tab-content {
        display: none;
        padding: 2rem;
    }
    
    .tab-content.active {
        display: block;
    }
    
    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }
    
    .empty-icon {
        font-size: 3rem;
        color: rgba(138, 43, 226, 0.4);
        margin-bottom: 1rem;
    }
    
    .empty-state h3 {
        color: #fff;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 1.5rem;
    }
    
    /* Session cards */
    .sessions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .session-card {
        background: rgba(30, 30, 60, 0.7);
        border: 1px solid rgba(138, 43, 226, 0.4);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .session-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        border-color: rgba(138, 43, 226, 0.7);
    }
    
    .session-card.cancelled {
        background: rgba(30, 30, 45, 0.7);
        border-color: rgba(220, 53, 69, 0.4);
    }
    
    .session-header {
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .session-date {
        display: flex;
        align-items: center;
    }
    
    .date-circle {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(138, 43, 226, 0.2);
        border: 2px solid rgba(138, 43, 226, 0.5);
        margin-right: 1rem;
    }
    
    .date-circle.past {
        background: rgba(108, 117, 125, 0.2);
        border-color: rgba(108, 117, 125, 0.5);
    }
    
    .month {
        font-size: 0.8rem;
        color: #d8b5ff;
        text-transform: uppercase;
        font-weight: bold;
    }
    
    .day {
        font-size: 1.5rem;
        font-weight: bold;
        color: #fff;
    }
    
    .date-time {
        display: flex;
        flex-direction: column;
    }
    
    .weekday {
        color: #fff;
        font-weight: bold;
    }
    
    .time {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .session-status .badge {
        display: inline-block;
        padding: 0.5em 0.8em;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .session-status .bg-success {
        background-color: rgba(40, 167, 69, 0.2);
        color: #a0ffa0;
        border: 1px solid rgba(40, 167, 69, 0.4);
    }
    
    .session-status .bg-warning {
        background-color: rgba(255, 193, 7, 0.2);
        color: #ffe0a0;
        border: 1px solid rgba(255, 193, 7, 0.4);
    }
    
    .session-status .bg-danger {
        background-color: rgba(220, 53, 69, 0.2);
        color: #ffa0a0;
        border: 1px solid rgba(220, 53, 69, 0.4);
    }
    
    .session-status .bg-primary {
        background-color: rgba(0, 123, 255, 0.2);
        color: #a0d4ff;
        border: 1px solid rgba(0, 123, 255, 0.4);
    }
    
    .session-details {
        padding: 1.5rem;
    }
    
    .coach-info {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .coach-image {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        margin-right: 1rem;
        border: 2px solid rgba(138, 43, 226, 0.5);
    }
    
    .coach-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(108, 117, 125, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: rgba(255, 255, 255, 0.7);
    }
    
    .coach-name {
        color: #fff;
        font-weight: 500;
    }
    
    .session-type, .session-duration {
        margin-bottom: 0.5rem;
        display: flex;
    }
    
    .label {
        color: rgba(255, 255, 255, 0.7);
        width: 100px;
    }
    
    .value {
        color: #fff;
    }
    
    .session-actions {
        padding: 1.5rem;
        display: flex;
        gap: 1rem;
        border-top: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .sessions-grid {
            grid-template-columns: 1fr;
        }
        
        .tab-buttons {
            flex-direction: column;
        }
        
        .session-actions {
            flex-direction: column;
        }
        
        .cosmic-button {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching functionality
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons and tab contents
                tabButtons.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked button and its corresponding tab content
                button.classList.add('active');
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });
    });
</script>
@endpush