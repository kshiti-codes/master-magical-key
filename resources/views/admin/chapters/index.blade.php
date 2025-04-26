@extends('layouts.admin')

@section('title', 'Manage Chapters')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="admin-page-title">Manage Chapters</h1>
        <a href="{{ route('admin.chapters.create') }}" class="btn btn-admin-primary">
            <i class="fas fa-plus-circle"></i> Create New Chapter
        </a>
    </div>
    
    <div class="admin-card">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="5%">Order</th>
                        <th width="30%">Title</th>
                        <th width="10%">Price</th>
                        <th width="10%">Status</th>
                        <th width="10%">Pages</th>
                        <th width="10%">Audio</th>
                        <th width="20%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($chapters as $chapter)
                        <tr>
                            <td>{{ $chapter->id }}</td>
                            <td>{{ $chapter->order }}</td>
                            <td>{{ $chapter->title }}</td>
                            <td>
                                @if($chapter->isFree())
                                    <span class="badge bg-success">Free</span>
                                @else
                                    ${{ number_format($chapter->price, 2) }} {{ $chapter->currency }}
                                @endif
                            </td>
                            <td>
                                @if($chapter->is_published)
                                    <span class="badge bg-success">Published</span>
                                @else
                                    <span class="badge bg-warning">Draft</span>
                                @endif
                            </td>
                            <td>
                                {{ $chapter->pages->count() }} pages
                            </td>
                            <td>
                                @if($chapter->has_audio)
                                    <span class="badge bg-info">Available</span>
                                @else
                                    <span class="badge bg-secondary">None</span>
                                @endif
                            </td>
                            <td class="actions-cell">
                                <a href="{{ route('admin.chapters.edit', $chapter) }}" class="btn btn-sm btn-admin-secondary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form action="{{ route('admin.chapters.paginate', $chapter) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-admin-secondary" title="Paginate" style="height: 100%;">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.chapters.destroy', $chapter) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-admin-secondary" style="background-color: #dc3545; border-color: #dc3545;height: 100%;" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No chapters found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Confirmation for delete
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('.delete-form');
        
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to delete this chapter? This action cannot be undone.')) {
                    this.submit();
                }
            });
        });
    });
</script>
@endpush