@extends('layouts.app')

@section('content')
<div class="confirmation-container">
    <div class="confirmation-card">
        <div class="success-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        
        <h1 class="page-title">Booking Confirmed</h1>
        
        <p class="confirmation-message">
            Thank you for your booking! Your session with {{ $bookedSession->coach->name }} has been confirmed.
        </p>
        
        <div class="session-details">
            <div class="details-header">Session Information</div>
            <div class="details-grid">
                <div class="detail-item">
                    <div class="session-datetime">
                        <div class="date-badge">
                            <div class="month">{{ $bookedSession->session_time->format('M') }}</div>
                            <div class="day">{{ $bookedSession->session_time->format('d') }}</div>
                            <div class="year">{{ $bookedSession->session_time->format('Y') }}</div>
                        </div>
                        <div class="time-details">
                            <div class="weekday">{{ $bookedSession->session_time->format('l') }}</div>
                            <div class="time-range">
                                <i class="far fa-clock"></i>
                                {{ $bookedSession->session_time->format('g:i A') }} - 
                                {{ $bookedSession->session_time->copy()->addMinutes($bookedSession->duration)->format('g:i A') }}
                            </div>
                            <div class="duration">
                                <i class="fas fa-hourglass-half"></i> {{ $bookedSession->duration }} minutes
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Session Type:</div>
                    <div class="detail-value">{{ $bookedSession->sessionType->name }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Coach:</div>
                    <div class="detail-value">{{ $bookedSession->coach->name }}</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">{!! $bookedSession->status_badge !!}</div>
                </div>
            </div>
        </div>
        
        <div class="confirmation-alert">
            <i class="fas fa-envelope"></i>
            A confirmation email has been sent to your registered email address with session details.
            You will receive the meeting link before your session begins.
        </div>
        
        <div class="next-steps">
            <h2 class="steps-title">Your Next Steps</h2>
            
            <div class="steps-list">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Review your email</h3>
                        <p>Check your inbox for confirmation details and receipt.</p>
                    </div>
                </div>
                
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Prepare for your session</h3>
                        <p>Find a quiet space with a stable internet connection.</p>
                    </div>
                </div>
                
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Add to calendar</h3>
                        <p>Don't forget to add this appointment to your calendar.</p>
                    </div>
                </div>
                
                <div class="step-item">
                    <div class="step-number">4</div>
                    <div class="step-content">
                        <h3>Join the session</h3>
                        <p>You can join up to 10 minutes before your scheduled time.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="confirmation-actions">
            <a href="{{ route('sessions.show', $bookedSession->id) }}" class="cosmic-button primary-button">
                <i class="fas fa-eye"></i> View Session Details
            </a>
            <a href="{{ route('sessions.index') }}" class="cosmic-button secondary-button">
                <i class="fas fa-list"></i> My Sessions
            </a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .confirmation-container {
        max-width: 800px;
        margin: 3rem auto;
        padding: 0 1rem;
    }
    
    .confirmation-card {
        background: rgba(30, 30, 60, 0.7);
        border: 1px solid rgba(138, 43, 226, 0.4);
        border-radius: 15px;
        padding: 3rem 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        text-align: center;
    }
    
    .success-icon {
        font-size: 5rem;
        color: #28a745;
        margin-bottom: 1.5rem;
        animation: pulse 2s ease-in-out;
    }
    
    @keyframes pulse {
        0% { transform: scale(0.8); opacity: 0; }
        50% { transform: scale(1.2); opacity: 1; }
        100% { transform: scale(1); opacity: 1; }
    }
    
    .page-title {
        font-family: 'Cinzel', serif;
        color: #fff;
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
        text-shadow: 0 0 10px rgba(138, 43, 226, 0.8);
    }
    
    .confirmation-message {
        color: rgba(255, 255, 255, 0.9);
        font-size: 1.2rem;
        margin-bottom: 2rem;
    }
    
    .session-details {
        background: rgba(20, 20, 40, 0.5);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        text-align: left;
    }
    
    .details-header {
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }
    
    .details-grid {
        display: grid;
        gap: 1.5rem;
    }
    
    .detail-item {
        display: flex;
        flex-direction: column;
    }
    
    .detail-label {
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .detail-value {
        color: #fff;
    }
    
    /* Session datetime */
    .session-datetime {
        display: flex;
        align-items: center;
    }
    
    .date-badge {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 80px;
        height: 90px;
        background: rgba(138, 43, 226, 0.2);
        border: 2px solid rgba(138, 43, 226, 0.5);
        border-radius: 8px;
        margin-right: 1.5rem;
    }
    
    .month {
        color: #d8b5ff;
        font-size: 0.9rem;
        text-transform: uppercase;
        font-weight: bold;
    }
    
    .day {
        color: #fff;
        font-size: 2rem;
        font-weight: bold;
        line-height: 1;
        margin: 0.3rem 0;
    }
    
    .year {
        color: rgba(255, 255, 255, 0.6);
        font-size: 0.8rem;
    }
    
    .time-details {
        display: flex;
        flex-direction: column;
    }
    
    .weekday {
        color: #fff;
        font-size: 1.3rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .time-range {
        color: #d8b5ff;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
    }
    
    .duration {
        color: rgba(255, 255, 255, 0.7);
    }
    
    /* Status badge */
    .badge {
        display: inline-block;
        padding: 0.5em 0.8em;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    
    .bg-success {
        background-color: rgba(40, 167, 69, 0.2);
        color: #a0ffa0;
        border: 1px solid rgba(40, 167, 69, 0.4);
    }
    
    /* Confirmation alert */
    .confirmation-alert {
        background: rgba(23, 162, 184, 0.2);
        color: #a0e5ff;
        border: 1px solid rgba(23, 162, 184, 0.4);
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    
    .confirmation-alert i {
        font-size: 1.5rem;
        margin-right: 1rem;
    }
    
    /* Next steps */
    .next-steps {
        margin-bottom: 2.5rem;
    }
    
    .steps-title {
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        font-size: 1.5rem;
        margin-bottom: 1.5rem;
        text-align: center;
    }
    
    .steps-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .step-item {
        display: flex;
        align-items: flex-start;
        text-align: left;
    }
    
    .step-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        background: rgba(138, 43, 226, 0.2);
        border: 2px solid rgba(138, 43, 226, 0.5);
        border-radius: 50%;
        color: #fff;
        font-weight: bold;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .step-content h3 {
        color: #fff;
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }
    
    .step-content p {
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 0;
    }
    
    /* Action buttons */
    .confirmation-actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
    }
    
    .cosmic-button {
        padding: 1rem 1.5rem;
        border-radius: 30px;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
    }
    
    .primary-button {
        background: rgba(138, 43, 226, 0.7);
        color: white;
    }
    
    .primary-button:hover {
        background: rgba(138, 43, 226, 0.9);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(138, 43, 226, 0.4);
        color: white;
        text-decoration: none;
    }
    
    .secondary-button {
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .secondary-button:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        text-decoration: none;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .confirmation-actions {
            flex-direction: column;
            gap: 0.8rem;
        }
        
        .cosmic-button {
            width: 100%;
            justify-content: center;
        }
        
        .session-datetime {
            flex-direction: column;
            align-items: center;
        }
        
        .date-badge {
            margin-right: 0;
            margin-bottom: 1rem;
        }
        
        .time-details {
            text-align: center;
        }
    }
</style>
@endpush