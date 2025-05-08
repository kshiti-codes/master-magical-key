@extends('layouts.admin')

@section('content')
<div class="admin-page-title">Add New Coach</div>

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
        <h2 class="admin-card-title">Coach Information</h2>
        <a href="{{ route('admin.coaches.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back to Coaches
        </a>
    </div>

    <form action="{{ route('admin.coaches.store') }}" method="POST" enctype="multipart/form-data" class="admin-form">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                    <label for="bio" class="form-label">Bio</label>
                    <textarea class="form-control" id="bio" name="bio" rows="5">{{ old('bio') }}</textarea>
                    <div class="form-text">Provide a short professional bio for this coach.</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="mb-3">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                    <div class="form-text">Recommended size: 300x300 pixels</div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Active</label>
                    </div>
                    <div class="form-text">Inactive coaches won't be shown to clients.</div>
                </div>

                <div class="mb-3 mt-4 text-center">
                    <div class="img-preview d-flex justify-content-center align-items-center mb-3">
                        <img id="preview" src="{{ asset('images/default-avatar.png') }}" class="img-fluid rounded-circle" 
                             style="max-width: 150px; max-height: 150px; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save"></i> Save Coach
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Image preview
        const profileImage = document.getElementById('profile_image');
        const preview = document.getElementById('preview');

        profileImage.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '{{ asset('images/default-avatar.png') }}';
            }
        });
    });
</script>
@endpush