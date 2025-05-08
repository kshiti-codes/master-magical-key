@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Edit Coach</div>

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
        <h2 class="admin-card-title">Edit {{ $coach->name }}</h2>
        <a href="{{ route('admin.coaches.show', $coach->id) }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back to Coach
        </a>
    </div>

    <form action="{{ route('admin.coaches.update', $coach->id) }}" method="POST" enctype="multipart/form-data" class="admin-form">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $coach->name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $coach->email) }}" required>
                </div>

                <div class="mb-3">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea class="form-control" id="bio" name="bio" rows="5">{{ old('bio', $coach->bio) }}</textarea>
                    <div class="form-text">Provide a short professional bio for this coach.</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                    <div class="form-text">Upload a new image or leave blank to keep current image.</div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                            {{ old('is_active', $coach->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="form-text">Inactive coaches won't be shown to clients.</div>
                </div>

                <div class="mb-3 mt-4 text-center">
                    <div class="img-preview d-flex justify-content-center align-items-center mb-3">
                        <img id="preview" src="{{ $coach->profile_image ? asset($coach->profile_image) : asset('images/default-avatar.png') }}" 
                             class="img-fluid rounded-circle" style="width: 150px; height: 150px; object-fit: cover;">
                    </div>
                    @if($coach->profile_image)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                            <label class="form-check-label" for="remove_image">Remove current image</label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Note:</strong> To manage which session types this coach can offer, please go to each session type and add/remove this coach.
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="button" class="btn-admin-secondary text-danger" data-bs-toggle="modal" data-bs-target="#deleteCoachModal">
                <i class="fas fa-trash-alt"></i> Delete Coach
            </button>
            
            <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save"></i> Update Coach
            </button>
        </div>
    </form>
</div>

<!-- Delete Coach Modal -->
<div class="modal fade" id="deleteCoachModal" tabindex="-1" aria-labelledby="deleteCoachModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteCoachModalLabel">Delete Coach</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this coach?</p>
                
                @if($coach->bookedSessions()->exists())
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        This coach has existing bookings. You cannot delete coaches with bookings.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-admin-secondary" data-bs-dismiss="modal">Cancel</button>
                
                <form action="{{ route('admin.coaches.destroy', $coach->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-admin-primary text-danger" 
                            {{ $coach->bookedSessions()->exists() ? 'disabled' : '' }}>
                        Delete Coach
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image preview
        const profileImage = document.getElementById('profile_image');
        const preview = document.getElementById('preview');
        const removeImage = document.getElementById('remove_image');

        profileImage.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    
                    // If there's a remove image checkbox, uncheck it
                    if (removeImage) {
                        removeImage.checked = false;
                    }
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Handle remove image checkbox
        if (removeImage) {
            removeImage.addEventListener('change', function() {
                if (this.checked) {
                    preview.src = '{{ asset('images/default-avatar.png') }}';
                    profileImage.value = ''; // Clear the file input
                } else {
                    preview.src = '{{ $coach->profile_image ? asset($coach->profile_image) : asset('images/default-avatar.png') }}';
                }
            });
        }
    });
</script>
@endpush