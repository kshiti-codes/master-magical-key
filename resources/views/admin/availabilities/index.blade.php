@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Manage Coach Availabilities</div>

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
<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title">Availabilities</h2>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="{{ route('admin.availabilities.batch') }}" class="btn-admin-secondary me-2">
                <i class="fas fa-calendar-plus"></i> Batch Create
            </a>
            <a href="{{ route('admin.availabilities.create') }}" class="btn-admin-primary">
                <i class="fas fa-plus"></i> Add Availability
            </a>
        </div>
    </div>

    <!-- Filter form -->
    <div class="mb-4">
        <form action="{{ route('admin.availabilities.index') }}" method="GET" class="filter-section">
            <div class="filter-group">
                <label for="coach_id" class="form-label">Coach</label>
                <select name="coach_id" id="coach_id" class="form-select">
                    <option value="">All Coaches</option>
                    @foreach($coaches as $coach)
                        <option value="{{ $coach->id }}" {{ $selectedCoachId == $coach->id ? 'selected' : '' }}>
                            {{ $coach->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="{{ $selectedDate }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-admin-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('admin.availabilities.index') }}" class="btn-admin-secondary">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    @if($availabilities->isEmpty())
        <div class="alert alert-info">
            No availabilities found. Add availability slots to allow clients to book sessions.
        </div>
    @else
        <form id="batchDeleteForm" action="{{ route('admin.availabilities.batch.delete') }}" method="POST">
            @csrf
            <div class="batch-actions-container mb-4">
                <div class="d-flex align-items-center flex-wrap">
                    <button type="button" class="btn-admin-secondary me-2 mb-2" id="toggleAllCheckboxes">
                        <i class="fas fa-check-square"></i> Toggle All
                    </button>
                    <button type="button" class="btn-admin-secondary text-danger me-2 mb-2" id="batchDeleteBtn">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                    <div class="ms-3 selected-counter mb-2">
                        <span id="selectedCount">0</span> items selected
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input class="form-check-input select-all-checkbox" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th>Coach</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($availabilities as $availability)
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input availability-checkbox" type="checkbox" 
                                            name="selected_ids[]" value="{{ $availability->id }}"
                                            {{ $availability->status === 'booked' ? 'disabled' : '' }}>
                                    </div>
                                </td>
                                <td>{{ $availability->coach->name }}</td>
                                <td>{{ $availability->date->format('M d, Y') }}</td>
                                <td>{{ $availability->time_range }}</td>
                                <td>
                                    @if($availability->status === 'available')
                                        <span class="badge bg-success">Available</span>
                                    @elseif($availability->status === 'booked')
                                        <span class="badge bg-primary">Booked</span>
                                    @else
                                        <span class="badge bg-secondary">Unavailable</span>
                                    @endif
                                </td>
                                <td class="actions-cell">
                                    @if($availability->status !== 'booked')
                                        <a href="{{ route('admin.availabilities.edit', $availability->id) }}" class="btn-admin-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn-admin-secondary text-danger" 
                                                onclick="confirmDelete({{ $availability->id }})" title="Delete">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                        <form id="delete-form-{{ $availability->id }}" 
                                            action="{{ route('admin.availabilities.destroy', $availability->id) }}" 
                                            method="POST" class="d-none">
                                            @csrf
                                        </form>
                                    @else
                                        <span class="text-muted">Booked</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>

        <div class="mt-4">
            {{ $availabilities->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .filter-section {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            align-items: flex-end;
    }

    .filter-group {
        flex: 1;
        min-width: 200px;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
    }

    .batch-actions-container {
        background-color: rgba(15, 15, 35, 0.4);
        border-radius: 8px;
        padding: 12px 15px;
        border: 1px solid rgba(138, 43, 226, 0.2);
    }

    .selected-counter {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.9rem;
    }

    #selectedCount {
        color: #d8b5ff;
        font-weight: bold;
    }

    @media (max-width: 768px) {
        .filter-section {
            flex-direction: column;
        }
        
        .filter-group {
            width: 100%;
        }
        
        .filter-actions {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Individual delete
    window.confirmDelete = function(id) {
        if (confirm('Are you sure you want to delete this availability?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
    
    // Batch delete functionality
    const selectAll = document.getElementById('selectAll');
    const toggleAllBtn = document.getElementById('toggleAllCheckboxes');
    const batchDeleteBtn = document.getElementById('batchDeleteBtn');
    const confirmBatchDeleteBtn = document.getElementById('confirmBatchDelete');
    const batchDeleteForm = document.getElementById('batchDeleteForm');
    const selectedCountElement = document.getElementById('selectedCount');
    
    // Function to get all available checkboxes (not disabled)
    function getAllCheckboxes() {
        return document.querySelectorAll('.availability-checkbox:not(:disabled)');
    }
    
    // Function to update selected count
    function updateSelectedCount() {
        const selectedCount = document.querySelectorAll('.availability-checkbox:checked').length;
        if (selectedCountElement) {
            selectedCountElement.textContent = selectedCount;
        }
        
        // Disable batch delete button if no items selected
        if (batchDeleteBtn) {
            batchDeleteBtn.disabled = selectedCount === 0;
        }
        
        // Update header checkbox state
        if (selectAll) {
            const checkboxes = getAllCheckboxes();
            if (checkboxes.length > 0) {
                selectAll.checked = [...checkboxes].every(cb => cb.checked);
                selectAll.indeterminate = !selectAll.checked && [...checkboxes].some(cb => cb.checked);
            } else {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
        }
    }
    
    // Select all checkbox in header
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = getAllCheckboxes();
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Toggle all button
    if (toggleAllBtn) {
        toggleAllBtn.addEventListener('click', function() {
            const checkboxes = getAllCheckboxes();
            const allChecked = checkboxes.length > 0 && [...checkboxes].every(cb => cb.checked);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
            
            if (selectAll) {
                selectAll.checked = !allChecked;
                selectAll.indeterminate = false;
            }
            
            updateSelectedCount();
        });
    }
    
    // Update count when individual checkboxes change
    document.addEventListener('change', function(e) {
        if (e.target && e.target.classList.contains('availability-checkbox')) {
            updateSelectedCount();
        }
    });
    
    // Show confirmation modal when batch delete button is clicked
    if (batchDeleteBtn) {
        batchDeleteBtn.addEventListener('click', function() {
            const selectedCount = document.querySelectorAll('.availability-checkbox:checked').length;
            
            if (selectedCount > 0) {
                if (confirm('Are you sure you want to delete ' + selectedCount + ' selected availability slots?')) {
                    batchDeleteForm.submit();
                }
            } else {
                alert('Please select at least one availability to delete.');
            }
        });
    }
    
    // Submit the form when confirmed in modal
    if (confirmBatchDeleteBtn) {
        confirmBatchDeleteBtn.addEventListener('click', function() {
            batchDeleteForm.submit();
        });
    }
    
    // Initial count update
    updateSelectedCount();
});
</script>
@endpush