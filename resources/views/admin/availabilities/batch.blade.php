@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Batch Create Availabilities</div>

@if ($errors->any())
    <div class="admin-alert admin-alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('error'))
    <div class="admin-alert admin-alert-danger">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title">Create Multiple Availability Slots</h2>
        <a href="{{ route('admin.availabilities.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back to Availabilities
        </a>
    </div>

    <form action="{{ route('admin.availabilities.batch.store') }}" method="POST" id="batchAvailabilityForm" class="admin-form">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="coach_id" class="form-label">Coach</label>
                    <select name="coach_id" id="coach_id" class="form-select" {{ !auth()->user()->is_admin ? 'disabled' : '' }} required>
                        @if(auth()->user()->is_admin)
                            <option value="">Select Coach</option>
                        @endif
                        @foreach($coaches as $coach)
                            <option value="{{ $coach->id }}" {{ !auth()->user()->is_admin ? 'selected' : '' }}>
                                {{ $coach->name }}{{ !auth()->user()->is_admin ? ' (You)' : '' }}
                            </option>
                        @endforeach
                    </select>
                    
                    @if(!auth()->user()->is_admin && isset($coaches[0]))
                        <!-- Add a hidden input to ensure the coach_id is submitted even when the select is disabled -->
                        <input type="hidden" name="coach_id" value="{{ $coaches[0]->id }}">
                    @endif
                </div>

                <div class="mb-3">
                    <label for="date_range" class="form-label">Date Range</label>
                    <input type="text" class="form-control" id="date_range" name="date_range" 
                           placeholder="Select date range" required>
                    <div class="form-text">
                        This will create availabilities for the selected days within this date range
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Days of Week</label>
                    <div class="row mt-2">
                        <div class="col-md-4 col-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="days[]" id="day-0" value="0">
                                <label class="form-check-label" for="day-0">Sunday</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="days[]" id="day-1" value="1">
                                <label class="form-check-label" for="day-1">Monday</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="days[]" id="day-2" value="2">
                                <label class="form-check-label" for="day-2">Tuesday</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="days[]" id="day-3" value="3">
                                <label class="form-check-label" for="day-3">Wednesday</label>
                            </div>
                        </div>
                        <div class="col-md-4 col-6">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="days[]" id="day-4" value="4">
                                <label class="form-check-label" for="day-4">Thursday</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="days[]" id="day-5" value="5">
                                <label class="form-check-label" for="day-5">Friday</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="days[]" id="day-6" value="6">
                                <label class="form-check-label" for="day-6">Saturday</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Time Slots</label>
                    <div class="form-text mb-2">
                        Add multiple time slots for the selected days
                    </div>

                    <div id="timeSlots">
                        <div class="time-slot-row mb-3 row">
                            <div class="col-5">
                                <label class="form-label small">Start Time</label>
                                <input type="time" name="time_slots[0][start_time]" class="form-control" required>
                            </div>
                            <div class="col-5">
                                <label class="form-label small">End Time</label>
                                <input type="time" name="time_slots[0][end_time]" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i>
            This will create availability slots for each day and time combination you've selected.
            Any overlapping slots with existing availabilities will be skipped.
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save"></i> Create Availabilities
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .daterangepicker {
        background-color: #1e1e3c;
        border: 1px solid rgba(138, 43, 226, 0.3);
        color: white;
    }
    .daterangepicker .calendar-table {
        background-color: #1e1e3c;
        border: 1px solid rgba(138, 43, 226, 0.3);
    }
    .daterangepicker td.available:hover, .daterangepicker th.available:hover {
        background-color: rgba(138, 43, 226, 0.3);
    }
    .daterangepicker td.active, .daterangepicker td.active:hover {
        background-color: rgba(138, 43, 226, 0.7);
    }
    .daterangepicker .drp-buttons {
        border-top: 1px solid rgba(138, 43, 226, 0.3);
    }
    .daterangepicker .drp-selected {
        color: white;
    }
    .daterangepicker .calendar-table .next span, .daterangepicker .calendar-table .prev span {
        border-color: white;
    }
    .daterangepicker td.off, .daterangepicker td.off.in-range, .daterangepicker td.off.start-date, .daterangepicker td.off.end-date {
        background-color: #0f0f25;
        color: #666;
    }
    
    /* Improved styling for time slot rows */
    .time-slot-row {
        background-color: rgba(15, 15, 35, 0.4);
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px !important;
    }
    
    /* Style for the add button */
    #addTimeSlot {
        background: rgba(138, 43, 226, 0.2);
        border: 1px solid rgba(138, 43, 226, 0.3);
        color: white;
        transition: all 0.3s ease;
    }
    
    #addTimeSlot:hover {
        background: rgba(138, 43, 226, 0.4);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date range picker
        $('#date_range').daterangepicker({
            opens: 'left',
            minDate: moment(),
            locale: {
                format: 'MM/DD/YYYY',
                separator: ' - '
            }
        });
        
        // Time slots management
        let timeSlotCount = 1;
        
        $('#addTimeSlot').on('click', function() {
            const newSlot = `
                <div class="time-slot-row mb-3 row">
                    <div class="col-5">
                        <label class="form-label small">Start Time</label>
                        <input type="time" name="time_slots[${timeSlotCount}][start_time]" class="form-control" required>
                    </div>
                    <div class="col-5">
                        <label class="form-label small">End Time</label>
                        <input type="time" name="time_slots[${timeSlotCount}][end_time]" class="form-control" required>
                    </div>
                    <div class="col-2 d-flex align-items-end">
                        <button type="button" class="btn-admin-secondary text-danger remove-time-slot">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            
            $('#timeSlots').append(newSlot);
            timeSlotCount++;
            
            // Enable the first remove button if we have more than one slot
            if ($('.time-slot-row').length > 1) {
                $('.remove-time-slot').prop('disabled', false);
            }
        });
        
        // Handle removal of time slots using event delegation
        $(document).on('click', '.remove-time-slot', function() {
            $(this).closest('.time-slot-row').remove();
            
            // If only one slot remains, disable its remove button
            if ($('.time-slot-row').length === 1) {
                $('.remove-time-slot').prop('disabled', true);
            }
        });
        
        // Form validation
        $('#batchAvailabilityForm').on('submit', function(e) {
            let isValid = true;
            
            // Check if at least one day is selected
            if (!$('input[name="days[]"]:checked').length) {
                alert('Please select at least one day of the week');
                isValid = false;
            }
            
            // Check if each time slot has valid times (end time > start time)
            $('.time-slot-row').each(function() {
                const startTimeInput = $(this).find('input[name$="[start_time]"]');
                const endTimeInput = $(this).find('input[name$="[end_time]"]');
                
                const startTime = startTimeInput.val();
                const endTime = endTimeInput.val();
                
                if (startTime && endTime && startTime >= endTime) {
                    alert('End time must be after start time for each time slot');
                    startTimeInput.focus();
                    isValid = false;
                    return false; // Break the each loop
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
</script>
@endpush