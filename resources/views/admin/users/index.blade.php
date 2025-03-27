@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<h1 class="admin-page-title">All Users</h1>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title mb-0">User Management</h2>
        <a href="{{ route('admin.users.create') }}" class="btn-admin-primary">
            <i class="fas fa-plus"></i> Add New User
        </a>
    </div>
    
    <!-- Search and Filters -->
    <div id="user-filters" class="mb-4" style="margin-top: 1rem;">
        <div class="filter-row">
            <div class="filter-group">
                <input type="text" id="search-input" class="admin-form form-control" placeholder="Search by name or email" value="{{ request('search') }}">
            </div>
            
            <div class="filter-group">
                <select id="role-filter" class="admin-form form-control">
                    <option value="">All Roles</option>
                    <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customers</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
                </select>
            </div>
            
            <div class="filter-group d-flex">
                <button type="button" id="apply-filters" class="btn-admin-primary">Apply Filters</button>
                <button type="button" id="reset-filters" class="btn-admin-secondary ms-2">Reset</button>
            </div>
        </div>
    </div>

    <!-- Add a loading overlay -->
    <div id="loading-indicator" style="display: none !important; position: absolute; width: 75%; height: auto; background: rgba(0,0,0,0.7); z-index: 100; display: flex; justify-content: center; align-items: center;">
        <div style="text-align: center; color: white;">
            <p class="mt-3">Loading data...</p>
        </div>
    </div>

    <!-- Users Table -->
    <div id="users-table-container">
        @include('admin.users.partials.users_table')
    </div>
</div>
@endsection

@push('styles')
<style>
    .badge {
        font-size: 0.8rem;
        padding: 5px 10px;
        border-radius: 15px;
    }

    .filter-row {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
        align-items: center;
    }
    
    .filter-group {
        margin-bottom: 0;
        min-width: 200px;
    }
    
    .filter-group:first-child {
        flex: 1;
    }
    
    .text-purple {
        color: #d8b5ff !important;
    }
    
    .spinner-border {
        width: 3rem;
        height: 3rem;
        border: 0.25em solid currentColor;
        border-right-color: transparent;
        animation: spinner-border .75s linear infinite;
    }

    /* Remove default link styling from sortable columns */
    .sortable {
        color: #d8b5ff !important;  /* Match your theme color */
        text-decoration: none !important;
    }

    .sortable:hover {
        color: white !important;
        text-decoration: none !important;
    }

    /* Ensure the sort icons maintain proper styling */
    .sortable i {
        margin-left: 5px;
    }

    
    @keyframes spinner-border {
        to { transform: rotate(360deg); }
    }
    
    .visually-hidden {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }
    
    @media (max-width: 768px) {
        .filter-row {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-group {
            width: 100%;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentSort = "{{ request('sort', 'created_at') }}";
    let currentDirection = "{{ request('direction', 'desc') }}";
    
    // Function to fetch users with AJAX
    function fetchUsers(page = 1) {
        // Show loading indicator
        document.getElementById('loading-indicator').style.display = 'block';
        document.getElementById('users-table-container').style.opacity = '0.5';
        
        // Get filter values
        const search = document.getElementById('search-input').value;
        const role = document.getElementById('role-filter').value;
        
        // Prepare query params
        const params = new URLSearchParams({
            search: search,
            role: role,
            sort: currentSort,
            direction: currentDirection,
            page: page,
            ajax: 1
        });
        
        // Fetch data
        fetch(`{{ route('admin.users.index') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Update table container
            document.getElementById('users-table-container').innerHTML = html;
            document.getElementById('users-table-container').style.opacity = '1';
            
            // Hide loading indicator
            document.getElementById('loading-indicator').style.display = 'none';
            
            // Update browser URL without reloading
            const url = `{{ route('admin.users.index') }}?${params.toString().replace('ajax=1', '')}`;
            window.history.pushState({ path: url }, '', url);
            
            // Re-attach event listeners to new elements
            attachTableEventListeners();
        })
        .catch(error => {
            console.error('Error fetching users:', error);
            document.getElementById('loading-indicator').style.display = 'none';
            document.getElementById('users-table-container').style.opacity = '1';
            alert('Failed to load users. Please try again.');
        });
    }
    
    // Function to handle sorting
    function handleSort(field) {
        if (currentSort === field) {
            // Toggle direction if clicking the same field
            currentDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Default to ascending for new sort field
            currentSort = field;
            currentDirection = 'asc';
        }
        
        fetchUsers();
    }
    
    // Attach event listeners to sortable columns
    function attachTableEventListeners() {
        // Add event listeners for sort headers
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', function(e) {
                e.preventDefault();
                const field = this.getAttribute('data-sort');
                handleSort(field);
            });
        });
        
        // Add event listeners for pagination
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                const url = new URL(href);
                const page = url.searchParams.get('page') || 1;
                fetchUsers(page);
            });
        });
        
        // Add event listeners for delete buttons
        document.querySelectorAll('.delete-user-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this user?')) {
                    const form = this.closest('form');
                    const formData = new FormData(form);
                    
                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            alert(data.message);
                            // Refresh the table
                            fetchUsers();
                        } else {
                            alert(data.message || 'Failed to delete user');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting user:', error);
                        alert('An error occurred while deleting the user');
                    });
                }
            });
        });
    }
    
    // Apply filters button
    document.getElementById('apply-filters').addEventListener('click', function() {
        fetchUsers();
    });
    
    // Reset filters button
    document.getElementById('reset-filters').addEventListener('click', function() {
        document.getElementById('search-input').value = '';
        document.getElementById('role-filter').value = '';
        currentSort = 'created_at';
        currentDirection = 'desc';
        fetchUsers();
    });
    
    // Search input - press Enter to filter
    document.getElementById('search-input').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            fetchUsers();
        }
    });
    
    // Initial setup
    attachTableEventListeners();
});
</script>
@endpush