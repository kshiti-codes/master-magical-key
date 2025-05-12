@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Add New Availability</div>

@if ($errors->any())
    <div class="admin-alert admin-alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title">Availability Information</h2>
        <a href="{{ route('admin.availabilities.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back to Availabilities
        </a>
    </div>

    <form action="{{ route('admin.availabilities.store') }}" method="POST" class="admin-form">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="coach_id" class="form-label">Coach</label>
                    <select class="form-select" id="coach_id" name="coach_id" {{ !auth()->user()->is_admin ? 'disabled' : '' }} required>
                        @if(auth()->user()->is_admin)
                            <option value="">Select Coach</option>
                        @endif
                        @foreach($coaches as $coach)
                            <option value="{{ $coach->id }}" {{ old('coach_id') == $coach->id ? 'selected' : (!auth()->user()->is_admin ? 'selected' : '') }}>
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
                    <label for="date" class="form-label">Date</label>
                    <input type="date" class="form-control" id="date" name="date" 
                           value="{{ old('date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required>
                </div>
            </div>

            <div class="col-md-6">
                <div class="mb-3">
                    <label for="start_time" class="form-label">Start Time</label>
                    <input type="time" class="form-control" id="start_time" name="start_time" 
                           value="{{ old('start_time') }}" required>
                </div>

                <div class="mb-3">
                    <label for="end_time" class="form-label">End Time</label>
                    <input type="time" class="form-control" id="end_time" name="end_time" 
                           value="{{ old('end_time') }}" required>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available</option>
                        <!-- <option value="unavailable" {{ old('status') == 'unavailable' ? 'selected' : '' }}>Unavailable</option> -->
                    </select>
                </div>
            </div>
        </div>

        <div class="alert alert-info mt-3">
            <i class="fas fa-info-circle"></i>
            Ensure that the time slot doesn't overlap with existing availabilities for this coach.
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save"></i> Save Availability
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.querySelector('form');
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        
        form.addEventListener('submit', function(e) {
            const startTime = startTimeInput.value;
            const endTime = endTimeInput.value;
            
            if (startTime >= endTime) {
                e.preventDefault();
                alert('End time must be after start time');
            }
        });
    });
</script>
@endpush