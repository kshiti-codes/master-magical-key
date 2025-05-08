@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Manage Coaches for {{ $sessionType->name }}</div>

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
        <h2 class="admin-card-title">Assign Coaches</h2>
        <a href="{{ route('admin.session-types.show', $sessionType->id) }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back to Session Type
        </a>
    </div>

    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle"></i>
        Select which coaches can offer this session type. Clients will be able to choose from these coaches when booking.
    </div>

    <form action="{{ route('admin.session-types.update-coaches', $sessionType->id) }}" method="POST">
        @csrf

        <div class="row">
            @if($coaches->isEmpty())
                <div class="col-12">
                    <div class="alert alert-warning">
                        No coaches are available. Please add coaches first.
                    </div>
                </div>
            @else
                @foreach($coaches as $coach)
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="coach_ids[]" 
                                               id="coach-{{ $coach->id }}" value="{{ $coach->id }}"
                                               {{ in_array($coach->id, $assignedCoachIds) ? 'checked' : '' }}>
                                    </div>
                                    
                                    <div class="ms-3 d-flex align-items-center">
                                        @if($coach->profile_image)
                                            <img src="{{ asset($coach->profile_image) }}" alt="{{ $coach->name }}" 
                                                class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="rounded-circle bg-secondary me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px; color: white;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        @endif
                                        
                                        <div>
                                            <label for="coach-{{ $coach->id }}" class="fw-bold mb-0">{{ $coach->name }}</label>
                                            <div class="small text-muted">{{ $coach->email }}</div>
                                            
                                            @if(!$coach->is_active)
                                                <span class="badge bg-warning">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn-admin-secondary" id="toggleAll">
                <i class="fas fa-check-square"></i> Toggle All
            </button>
            
            <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save"></i> Save Assignments
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleAllBtn = document.getElementById('toggleAll');
        const checkboxes = document.querySelectorAll('input[name="coach_ids[]"]');
        
        toggleAllBtn.addEventListener('click', function() {
            const allChecked = [...checkboxes].every(cb => cb.checked);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
        });
    });
</script>
@endpush