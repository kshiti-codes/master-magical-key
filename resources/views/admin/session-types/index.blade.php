@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Manage Session Types</div>

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
        <h2 class="admin-card-title">Session Types</h2>
        <a href="{{ route('admin.session-types.create') }}" class="btn-admin-primary">
            <i class="fas fa-plus"></i> Add New Session Type
        </a>
    </div>

    @if($sessionTypes->isEmpty())
        <div class="alert alert-info">
            No session types found. Add your first session type to get started.
        </div>
    @else
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Coaches</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessionTypes as $sessionType)
                        <tr>
                            <td>{{ $sessionType->name }}</td>
                            <td>{{ $sessionType->duration }} minutes</td>
                            <td>{{ $sessionType->formatted_price }}</td>
                            <td>
                                @if($sessionType->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                {{ $sessionType->coaches->count() }}
                                <a href="{{ route('admin.session-types.coaches', $sessionType->id) }}" class="btn-admin-secondary btn-sm ms-2">
                                    <i class="fas fa-user-edit"></i> Manage
                                </a>
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('admin.session-types.show', $sessionType->id) }}" class="btn-admin-secondary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.session-types.edit', $sessionType->id) }}" class="btn-admin-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn-admin-secondary text-danger" 
                                        onclick="confirmDelete({{ $sessionType->id }})" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                <form id="delete-form-{{ $sessionType->id }}" 
                                      action="{{ route('admin.session-types.destroy', $sessionType->id) }}" 
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
        if (confirm('Are you sure you want to delete this session type?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endpush