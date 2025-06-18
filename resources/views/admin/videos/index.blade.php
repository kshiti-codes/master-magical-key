@extends('layouts.admin')

@section('title', 'Training Videos')

@section('content')
<h1 class="admin-page-title">Manage Training Videos</h1>


<a href="{{ route('admin.videos.create') }}" class="btn-admin-primary">
    <i class="fas fa-plus"></i> Add New Video
</a>

<div class="admin-card">
    @if (count($videos) > 0)
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Duration</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($videos as $video)
                <tr>
                    <td>{{ $video->order_sequence }}</td>
                    <td>{{ $video->title }}</td>
                    <td>{{ $video->getFormattedDurationAttribute() }}</td>
                    <td>
                        @if($video->isFreeForUser())
                            <span class="badge bg-success">Free</span>
                        @else
                            {{ $video->getFormattedPriceAttribute() }}
                        @endif
                    </td>
                    <td>
                        @if($video->is_published)
                            <span class="badge bg-success">Published</span>
                        @else
                            <span class="badge bg-warning">Draft</span>  
                        @endif
                    </td>
                    <td class="actions-cell">
                        <a href="{{ route('admin.videos.show', $video) }}" class="btn-admin-secondary" title="View Details">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.videos.edit', $video) }}" class="btn-admin-secondary" title="Edit Video">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.videos.toggle-status', $video) }}" method="POST" style="display: inline;" class="toggle-status-form">
                            @csrf
                            <button type="submit" class="btn-admin-secondary" title="{{ $video->is_published ? 'Unpublish' : 'Publish' }}" style="height: 100%;">
                                <i class="fas {{ $video->is_published ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.videos.destroy', $video) }}" method="POST" style="display: inline;" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-admin-secondary" style="background-color: #dc3545; border-color: #dc3545; height: 100%;" title="Delete Video">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-4">
        <i class="fas fa-video fa-3x mb-3" style="color: rgba(138, 43, 226, 0.3);margin-top:1rem;"></i>
        <p>No training videos have been added yet.</p>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add confirm dialog to delete buttons
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (confirm('Are you sure you want to delete this video? This action cannot be undone.')) {
                    this.submit();
                }
            });
        });
    });
</script>
@endpush