@extends('layouts.admin')

@section('title', 'Spells Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="admin-page-title">Spells Management</h1>
    <a href="{{ route('admin.spells.create') }}" class="btn btn-admin-primary">
        <i class="fas fa-plus"></i> Create New Spell
    </a>
</div>

<div class="admin-card">
    <h2 class="admin-card-title">All Spells</h2>
    
    @if($spells->isEmpty())
        <div class="text-center py-4">
            <i class="fas fa-magic fa-3x mb-3" style="color: rgba(138, 43, 226, 0.4);"></i>
            <p>No spells have been created yet.</p>
            <a href="{{ route('admin.spells.create') }}" class="btn btn-admin-primary mt-3">Create Your First Spell</a>
        </div>
    @else
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th>Related Chapters</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($spells as $spell)
                        <tr>
                            <td>{{ $spell->id }}</td>
                            <td>{{ $spell->title }}</td>
                            <td>${{ number_format($spell->price, 2) }} {{ $spell->currency }}</td>
                            <td>{{ $spell->order }}</td>
                            <td>
                                @if($spell->is_published)
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-warning">Draft</span>
                                @endif
                            </td>
                            <td>{{ $spell->chapters->count() }}</td>
                            <td class="actions-cell">
                                <a href="{{ route('admin.spells.edit', $spell) }}" class="btn btn-admin-secondary btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.spells.preview', $spell) }}" class="btn btn-admin-secondary btn-sm" title="Preview" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.spells.destroy', $spell) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this spell?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-admin-secondary btn-sm" style="background-color: #dc3545; border-color: #dc3545; height: 100%;" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
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