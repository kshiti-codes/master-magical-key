@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Manage Coaches</div>

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
        <h2 class="admin-card-title">Coaches</h2>
        <a href="{{ route('admin.coaches.create') }}" class="btn-admin-primary">
            <i class="fas fa-plus"></i> Add New Coach
        </a>
    </div>

    @if($coaches->isEmpty())
        <div class="alert alert-info">
            No coaches found. Add your first coach to get started.
        </div>
    @else
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Session Types</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coaches as $coach)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($coach->profile_image)
                                        <img src="{{ asset($coach->profile_image) }}" alt="{{ $coach->name }}" 
                                             class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle bg-secondary me-2 d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px; color: white;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    {{ $coach->name }}
                                </div>
                            </td>
                            <td>{{ $coach->email }}</td>
                            <td>
                                @if($coach->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                {{ $coach->sessionTypes->count() }}
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('admin.coaches.show', $coach->id) }}" class="btn-admin-secondary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.coaches.edit', $coach->id) }}" class="btn-admin-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn-admin-secondary text-danger" 
                                        onclick="confirmDelete({{ $coach->id }})" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <form id="delete-form-{{ $coach->id }}" 
                                      action="{{ route('admin.coaches.destroy', $coach->id) }}" 
                                      method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this coach?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush