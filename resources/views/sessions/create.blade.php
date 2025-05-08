@extends('layouts.app')

@section('content')
<div class="book-session-container">
    <h1 class="page-title">Book a Session</h1>
    <p class="page-subtitle">Schedule a personalized one-on-one session with our expert coaches</p>

    @if(session('error'))
        <div class="cosmic-alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
        </div>
    @endif

    <!-- Booking Steps Indicator -->
    <div class="booking-steps-container">
        <div class="booking-step active">
            <div class="step-circle">1</div>
            <div class="step-label">Select Session</div>
        </div>
        <div class="booking-step">
            <div class="step-circle">2</div>
            <div class="step-label">Select Coach</div>
        </div>
        <div class="booking-step">
            <div class="step-circle">3</div>
            <div class="step-label">Choose Date & Time</div>
        </div>
    </div>

    <form id="bookingForm" action="{{ route('sessions.prepare') }}" method="POST">
        @csrf
        <input type="hidden" name="session_type_id" id="session_type_id">
        <input type="hidden" name="coach_id" id="coach_id">
        <input type="hidden" name="availability_id" id="availability_id">

        <!-- Step 1: Session Type Selection -->
        <div class="booking-step-content active" id="step1">
            <h2 class="step-title">Select Session</h2>
            
            <div class="session-type-grid">
                @foreach($sessionTypes as $type)
                    <div class="cosmic-card session-type-card" data-session-type-id="{{ $type->id }}">
                        <div class="cosmic-card-body">
                            <h3 class="cosmic-card-title">{{ $type->name }}</h3>
                            <p class="cosmic-card-text">{{ $type->description ?: 'No description available' }}</p>
                            <div class="session-details">
                                <span class="cosmic-badge">{{ $type->duration }} minutes</span>
                                <span class="cosmic-badge price-badge">{{ $type->formatted_price }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="step-navigation">
                <button type="button" class="cosmic-button prev-button" id="step2Prev" disabled>
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                <button type="button" class="cosmic-button next-button" id="step1Next" disabled>
                    Next <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Step 2: Coach Selection -->
        <div class="booking-step-content" id="step2">
            <h2 class="step-title">Select Coach</h2>
            
            <div class="coaches-grid" id="coachesContainer">
                <div class="loading-container">
                    <div class="cosmic-spinner"></div>
                    <p>Loading available coaches...</p>
                </div>
            </div>
            
            <div class="step-navigation">
                <button type="button" class="cosmic-button prev-button" id="step2Prev">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                <button type="button" class="cosmic-button next-button" id="step2Next" disabled>
                    Next <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>

        <!-- Step 3: Combined Date and Time Selection -->
        <div class="booking-step-content" id="step3">
            <h2 class="step-title">Choose Date & Time</h2>
            
            <div class="date-time-selection" id="dateTimeContainer">
                <div class="loading-container">
                    <div class="cosmic-spinner"></div>
                    <p>Loading available sessions...</p>
                </div>
            </div>
            
            <div class="step-navigation">
                <button type="button" class="cosmic-button prev-button" id="step3Prev">
                    <i class="fas fa-arrow-left"></i> Previous
                </button>
                <button type="submit" class="cosmic-button book-button" id="bookButton" disabled>
                    <i class="fas fa-check"></i> Book Session
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    .book-session-container {
        max-width: 1000px;
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
        border-left: 4px solid #dc3545;
        color: #ffa0a0;
        padding: 1rem;
        border-radius: 5px;
        margin-bottom: 2rem;
    }
    
    /* Booking Steps Indicators */
    .booking-steps-container {
        display: flex;
        justify-content: center;
        margin-bottom: 3rem;
        position: relative;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }
    
    .booking-steps-container::before {
        content: "";
        position: absolute;
        top: 2rem;
        left: 10%;
        right: 10%;
        height: 2px;
        background: rgba(255, 255, 255, 0.2);
        z-index: 0;
    }
    
    .booking-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        position: relative;
        z-index: 1;
    }
    
    .step-circle {
        width: 2rem;
        height: 2rem;
        background-color: rgba(30, 30, 60, 0.6);
        border: 2px solid rgba(138, 43, 226, 0.4);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.5rem;
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.5rem;
        font-weight: bold;
        transition: all 0.3s ease;
    }
    
    .booking-step.active .step-circle {
        background-color: rgba(138, 43, 226, 0.7);
        border-color: #fff;
        color: #fff;
        box-shadow: 0 0 15px rgba(138, 43, 226, 0.7);
    }
    
    .step-label {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .booking-step.active .step-label {
        color: #fff;
        font-weight: bold;
    }
    
    /* Step Content */
    .booking-step-content {
        display: none;
        animation: fadeEffect 0.5s;
    }
    
    @keyframes fadeEffect {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .booking-step-content.active {
        display: block;
    }
    
    .step-title {
        font-family: 'Cinzel', serif;
        color: #d8b5ff;
        text-align: center;
        font-size: 1.8rem;
    }
    
    /* Cards Grid */
    .session-type-grid, .coaches-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    /* Card Styling */
    .cosmic-card {
        background: rgba(30, 30, 60, 0.7);
        border: 1px solid rgba(138, 43, 226, 0.4);
        border-radius: 8px;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .cosmic-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(138, 43, 226, 0.3);
        border-color: rgba(138, 43, 226, 0.7);
    }
    
    .cosmic-card.selected {
        background: rgba(138, 43, 226, 0.2);
        border-color: rgba(138, 43, 226, 0.9);
        box-shadow: 0 0 20px rgba(138, 43, 226, 0.5);
    }
    
    .cosmic-card-title {
        color: #fff;
        margin-bottom: 1rem;
        font-size: 1.3rem;
    }
    
    .cosmic-card-text {
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 1.5rem;
        flex-grow: 1;
    }
    
    .session-details {
        display: flex;
        justify-content: space-between;
        margin-top: auto;
    }
    
    .cosmic-badge {
        background: rgba(138, 43, 226, 0.3);
        color: #d8b5ff;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.9rem;
    }
    
    .price-badge {
        background: rgba(0, 128, 128, 0.3);
        color: #a0ffd8;
    }
    
    /* Coach Card */
    .coach-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 50%;
        margin: 0 auto 1rem;
        display: block;
        border: 2px solid rgba(138, 43, 226, 0.5);
    }
    
    /* Date Time Selection */
    .date-time-selection {
        margin-bottom: 2rem;
    }
    
    .date-group {
        margin-bottom: 0.5rem;
    }
    
    .date-header {
        background: rgba(138, 43, 226, 0.2);
        color: #fff;
        padding: 1rem;
        border-radius: 8px 8px 0 0;
        border: 1px solid rgba(138, 43, 226, 0.4);
        border-bottom: none;
        font-family: 'Cinzel', serif;
    }
    
    .time-slots {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 0.5rem;
        padding: 1rem;
        background: rgba(20, 20, 40, 0.5);
        border-radius: 0 0 8px 8px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        border-top: none;
    }
    
    .time-slot {
        padding: 1rem;
        background: rgba(30, 30, 60, 0.7);
        border: 1px solid rgba(138, 43, 226, 0.4);
        border-radius: 5px;
        cursor: pointer;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .time-slot:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(138, 43, 226, 0.3);
        border-color: rgba(138, 43, 226, 0.7);
    }
    
    .time-slot.selected {
        background: rgba(138, 43, 226, 0.2);
        border-color: rgba(138, 43, 226, 0.9);
        box-shadow: 0 0 15px rgba(138, 43, 226, 0.5);
    }
    
    /* Navigation Buttons */
    .step-navigation {
        display: flex;
        justify-content: space-between;
        margin-top: 4rem;
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
    }
    
    .cosmic-button:hover {
        background: rgba(138, 43, 226, 0.8);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(138, 43, 226, 0.4);
    }
    
    .cosmic-button:disabled {
        background: rgba(138, 43, 226, 0.3);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .prev-button {
        background: rgba(255, 255, 255, 0.2);
    }
    
    .prev-button:hover {
        background: rgba(255, 255, 255, 0.3);
    }
    
    .book-button {
        background: rgba(40, 167, 69, 0.7);
    }
    
    .book-button:hover {
        background: rgba(40, 167, 69, 0.9);
        box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
    }
    
    /* Loading Spinner */
    .loading-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        grid-column: 1 / -1;
    }
    
    .cosmic-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid rgba(138, 43, 226, 0.3);
        border-radius: 50%;
        border-top: 4px solid rgba(138, 43, 226, 0.8);
        animation: spin 1s linear infinite;
        margin-bottom: 1rem;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Empty message */
    .empty-message {
        text-align: center;
        padding: 3rem;
        background: rgba(20, 20, 40, 0.5);
        border-radius: 8px;
        border: 1px solid rgba(138, 43, 226, 0.4);
        color: rgba(255, 255, 255, 0.7);
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .booking-steps-container {
            flex-wrap: wrap;
        }
        
        .booking-step {
            flex: 0 0 50%;
            margin-bottom: 1rem;
        }
        
        .step-circle {
            width: 3rem;
            height: 3rem;
            font-size: 1.2rem;
        }
        
        .step-navigation {
            flex-direction: column;
            gap: 1rem;
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
        let currentStep = 1;
        updateStepDisplay(currentStep);
        
        // Step 1: Session Type Selection
        const sessionTypeCards = document.querySelectorAll('.session-type-card');
        const step1Next = document.getElementById('step1Next');
        
        sessionTypeCards.forEach(card => {
            card.addEventListener('click', function() {
                // Clear previous selection
                sessionTypeCards.forEach(c => c.classList.remove('selected'));
                
                // Select this card
                this.classList.add('selected');
                
                // Set the session type ID
                document.getElementById('session_type_id').value = this.dataset.sessionTypeId;
                
                // Enable next button
                step1Next.disabled = false;
            });
        });
        
        step1Next.addEventListener('click', function() {
            goToStep(2);
            loadCoaches();
        });
        
        // Step 2: Coach Selection
        document.getElementById('step2Prev').addEventListener('click', function() {
            goToStep(1);
        });
        
        document.getElementById('step2Next').addEventListener('click', function() {
            goToStep(3);
            loadDateTimeSlots();
        });
        
        // Step 3: Date & Time Selection
        document.getElementById('step3Prev').addEventListener('click', function() {
            goToStep(2);
        });
        
        // Navigate between steps
        function goToStep(step) {
            // Hide current step
            document.getElementById('step' + currentStep).classList.remove('active');
            
            // Show new step
            document.getElementById('step' + step).classList.add('active');
            
            // Update current step
            currentStep = step;
            updateStepDisplay(currentStep);
        }
        
        function updateStepDisplay(step) {
            // Update step indicators
            const steps = document.querySelectorAll('.booking-steps-container .booking-step');
            steps.forEach((s, index) => {
                if (index < step) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        }
        
        // Function to load coaches
        function loadCoaches() {
            const sessionTypeId = document.getElementById('session_type_id').value;
            const coachesContainer = document.getElementById('coachesContainer');
            
            // Show loading
            coachesContainer.innerHTML = `
                <div class="loading-container">
                    <div class="cosmic-spinner"></div>
                    <p>Loading available coaches...</p>
                </div>
            `;
            
            // Make AJAX request
            fetch('{{ route("sessions.coaches") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ session_type_id: sessionTypeId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.coaches && data.coaches.length > 0) {
                    let html = '';
                    
                    data.coaches.forEach(coach => {
                        html += `
                            <div class="cosmic-card coach-card" data-coach-id="${coach.id}">
                                <div class="cosmic-card-body text-center">
                                    <img src="${coach.profile_image || '{{ asset("images/default-avatar.png") }}'}" class="coach-image" alt="${coach.name}">
                                    <h3 class="cosmic-card-title">${coach.name}</h3>
                                    <p class="cosmic-card-text">${coach.bio || 'No bio available'}</p>
                                </div>
                            </div>
                        `;
                    });
                    
                    coachesContainer.innerHTML = html;
                    
                    // Add click event to coach cards
                    document.querySelectorAll('.coach-card').forEach(card => {
                        card.addEventListener('click', function() {
                            // Clear previous selection
                            document.querySelectorAll('.coach-card').forEach(c => c.classList.remove('selected'));
                            
                            // Select this card
                            this.classList.add('selected');
                            
                            // Set the coach ID
                            document.getElementById('coach_id').value = this.dataset.coachId;
                            
                            // Enable next button
                            document.getElementById('step2Next').disabled = false;
                        });
                    });
                } else {
                    coachesContainer.innerHTML = `
                        <div class="cosmic-alert" style="grid-column: 1 / -1;">
                            <i class="fas fa-info-circle"></i> No coaches available for this session type. Please select a different session type.
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                coachesContainer.innerHTML = `
                    <div class="cosmic-alert alert-danger" style="grid-column: 1 / -1;">
                        <i class="fas fa-exclamation-circle"></i> Failed to load coaches. Please try again.
                    </div>
                `;
            });
        }
        
        // Function to load date and time slots in a single view
        function loadDateTimeSlots() {
            const sessionTypeId = document.getElementById('session_type_id').value;
            const coachId = document.getElementById('coach_id').value;
            const dateTimeContainer = document.getElementById('dateTimeContainer');
            
            // Show loading
            dateTimeContainer.innerHTML = `
                <div class="loading-container">
                    <div class="cosmic-spinner"></div>
                    <p>Loading available sessions...</p>
                </div>
            `;
            
            // First get available dates
            fetch('{{ route("sessions.dates") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    session_type_id: sessionTypeId,
                    coach_id: coachId
                })
            })
            .then(response => response.json())
            .then(async data => {
                if (data.dates && data.dates.length > 0) {
                    let html = '';
                    
                    // Process each date and get its time slots
                    for (const date of data.dates) {
                        // Load time slots for this date
                        const slotsResponse = await fetch('{{ route("sessions.slots") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ 
                                session_type_id: sessionTypeId,
                                coach_id: coachId,
                                date: date.date
                            })
                        });
                        
                        const slotsData = await slotsResponse.json();
                        
                        // Only show dates with available slots
                        if (slotsData.slots && slotsData.slots.length > 0) {
                            html += `
                                <div class="date-group">
                                    <div class="date-header">${date.formatted_date}</div>
                                    <div class="time-slots">
                            `;
                            
                            slotsData.slots.forEach(slot => {
                                html += `
                                    <div class="time-slot" data-availability-id="${slot.id}">
                                        ${slot.formatted_time}
                                    </div>
                                `;
                            });
                            
                            html += `
                                    </div>
                                </div>
                            `;
                        }
                    }
                    
                    if (html === '') {
                        dateTimeContainer.innerHTML = `
                            <div class="empty-message">
                                <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                <h3>No Available Sessions</h3>
                                <p>There are no available time slots for this coach. Please try selecting a different coach.</p>
                            </div>
                        `;
                    } else {
                        dateTimeContainer.innerHTML = html;
                        
                        // Add click event to time slots
                        document.querySelectorAll('.time-slot').forEach(slot => {
                            slot.addEventListener('click', function() {
                                // Clear previous selection
                                document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
                                
                                // Select this slot
                                this.classList.add('selected');
                                
                                // Set the availability ID
                                document.getElementById('availability_id').value = this.dataset.availabilityId;
                                
                                // Lock the time slot
                                lockTimeSlot(this.dataset.availabilityId);
                                
                                // Enable book button
                                document.getElementById('bookButton').disabled = false;
                            });
                        });
                    }
                } else {
                    dateTimeContainer.innerHTML = `
                        <div class="empty-message">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <h3>No Available Dates</h3>
                            <p>There are no available dates for this coach. Please try selecting a different coach.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                dateTimeContainer.innerHTML = `
                    <div class="cosmic-alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Failed to load available sessions. Please try again.
                    </div>
                `;
            });
        }
        
        // Function to lock a time slot
        function lockTimeSlot(availabilityId) {
            fetch('{{ route("sessions.lock") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ availability_id: availabilityId })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert(data.message);
                    
                    // Reload time slots if the slot is no longer available
                    loadDateTimeSlots();
                }
            })
            .catch(error => {
                console.error('Error locking time slot:', error);
            });
        }
    });
</script>
@endpush