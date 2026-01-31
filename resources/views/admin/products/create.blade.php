@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
<div class="admin-header">
    <h1 class="admin-page-title">Create New Product</h1>
    <a href="{{ route('admin.products.index') }}" class="btn-admin-secondary">
        <i class="fas fa-arrow-left"></i> Back to Products
    </a>
</div>

@if($errors->any())
    <div class="admin-alert admin-alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <strong>Please correct the following errors:</strong>
        <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Basic Information</h2>
        </div>

        <div class="admin-form-grid">
            <div class="admin-form-group full-width">
                <label for="title" class="admin-form-label required">Product Title</label>
                <input type="text" class="admin-form-input" id="title" name="title" value="{{ old('title') }}" required>
                <small class="admin-form-help">The name of your product as it will appear to customers</small>
            </div>

            <div class="admin-form-group full-width">
                <label for="description" class="admin-form-label">Description</label>
                <textarea class="admin-form-textarea" id="description" name="description" rows="5">{{ old('description') }}</textarea>
                <small class="admin-form-help">Detailed description of the product</small>
            </div>

            <div class="admin-form-group">
                <label for="type" class="admin-form-label required">Product Type</label>
                <select class="admin-form-select" id="type" name="type" required>
                    <option value="digital_download" {{ old('type') == 'digital_download' ? 'selected' : '' }}>Digital Download</option>
                    <option value="course" {{ old('type') == 'course' ? 'selected' : '' }}>Course</option>
                    <option value="session" {{ old('type') == 'session' ? 'selected' : '' }}>Session</option>
                    <option value="subscription" {{ old('type') == 'subscription' ? 'selected' : '' }}>Subscription</option>
                    <option value="video" {{ old('type') == 'video' ? 'selected' : '' }}>Video</option>
                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="sku" class="admin-form-label">SKU</label>
                <input type="text" class="admin-form-input" id="sku" name="sku" value="{{ old('sku') }}">
                <small class="admin-form-help">Product identifier (optional)</small>
            </div>

            <div class="admin-form-group">
                <label for="price" class="admin-form-label required">Price</label>
                <input type="number" class="admin-form-input" id="price" name="price" value="{{ old('price') }}" step="0.01" min="0" required>
            </div>

            <div class="admin-form-group">
                <label for="currency" class="admin-form-label required">Currency</label>
                <select class="admin-form-select" id="currency" name="currency" required>
                    <option value="AUD" {{ old('currency', 'AUD') == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar</option>
                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                    <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                </select>
            </div>

            <div class="admin-form-group">
                <label for="slug" class="admin-form-label">URL Slug</label>
                <input type="text" class="admin-form-input" id="slug" name="slug" value="{{ old('slug') }}">
                <small class="admin-form-help">Leave blank to auto-generate from title</small>
            </div>

            <div class="admin-form-group">
                <label class="admin-form-label">Status</label>
                <div class="admin-form-checkbox">
                    <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    <label for="is_active">Active (visible to customers)</label>
                </div>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Product Media</h2>
        </div>

        <div class="admin-form-grid">
            <div class="admin-form-group full-width">
                <label for="image" class="admin-form-label">Product Image</label>
                <input type="file" class="admin-form-file" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                <small class="admin-form-help">JPEG, PNG, GIF, or WebP. Max 10MB</small>
            </div>

            <div class="admin-form-group full-width">
                <label for="pdf_file" class="admin-form-label">PDF File</label>
                <input type="file" class="admin-form-file" id="pdf_file" name="pdf_file" accept="application/pdf">
                <small class="admin-form-help">Downloadable PDF file. Max 50MB</small>
            </div>

            <div class="admin-form-group full-width">
                <label for="audio_file" class="admin-form-label">Audio File</label>
                <input type="file" class="admin-form-file" id="audio_file" name="audio_file" accept="audio/mpeg,audio/wav,audio/x-m4a,audio/ogg">
                <small class="admin-form-help">MP3, WAV, M4A, or OGG. Max 100MB</small>
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card-header">
            <h2 class="admin-card-title">Pop-up Content</h2>
        </div>

        <div class="admin-form-group full-width">
            <label for="popup_text" class="admin-form-label">Pop-up Text</label>
            <textarea class="admin-form-textarea" id="popup_text" name="popup_text" rows="8">{{ old('popup_text') }}</textarea>
            <small class="admin-form-help">Text to display in pop-up when product is accessed (e.g., usage instructions, terms of use)</small>
        </div>
    </div>

    <div class="admin-form-actions">
        <button type="submit" class="btn-admin-primary">
            <i class="fas fa-save"></i> Create Product
        </button>
        <a href="{{ route('admin.products.index') }}" class="btn-admin-secondary">
            <i class="fas fa-times"></i> Cancel
        </a>
    </div>
</form>

@endsection

@push('styles')
<style>
    .admin-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .admin-form-group {
        display: flex;
        flex-direction: column;
    }

    .admin-form-group.full-width {
        grid-column: 1 / -1;
    }

    .admin-form-label {
        color: #d8b5ff;
        font-weight: 500;
        margin-bottom: 0.5rem;
        display: block;
    }

    .admin-form-label.required::after {
        content: " *";
        color: rgba(239, 68, 68, 0.8);
    }

    .admin-form-input,
    .admin-form-select,
    .admin-form-textarea {
        padding: 0.75rem;
        background: rgba(10, 10, 30, 0.5);
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 5px;
        color: white;
        font-size: 1rem;
    }

    .admin-form-input:focus,
    .admin-form-select:focus,
    .admin-form-textarea:focus {
        outline: none;
        border-color: rgba(138, 43, 226, 0.6);
    }

    .admin-form-textarea {
        resize: vertical;
        font-family: inherit;
    }

    .admin-form-file {
        padding: 0.5rem;
        background: rgba(10, 10, 30, 0.5);
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 5px;
        color: white;
        font-size: 0.9rem;
    }

    .admin-form-file::file-selector-button {
        padding: 0.5rem 1rem;
        background: rgba(138, 43, 226, 0.3);
        border: none;
        border-radius: 3px;
        color: white;
        cursor: pointer;
        margin-right: 1rem;
    }

    .admin-form-file::file-selector-button:hover {
        background: rgba(138, 43, 226, 0.5);
    }

    .admin-form-help {
        color: rgba(255, 255, 255, 0.5);
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .admin-form-checkbox {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem;
    }

    .admin-form-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .admin-form-checkbox label {
        color: white;
        cursor: pointer;
        margin: 0;
    }

    .admin-form-actions {
        display: flex;
        gap: 1rem;
        padding: 1.5rem;
        background: rgba(10, 10, 30, 0.3);
        border-radius: 10px;
        margin-top: 1.5rem;
    }

    @media (max-width: 768px) {
        .admin-form-grid {
            grid-template-columns: 1fr;
        }
        
        .admin-form-actions {
            flex-direction: column;
        }
        
        .admin-form-actions .btn-admin-primary,
        .admin-form-actions .btn-admin-secondary {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-generate slug from title
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');
        
        if (titleInput && slugInput) {
            titleInput.addEventListener('input', function() {
                if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                    const slug = this.value
                        .toLowerCase()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');
                    slugInput.value = slug;
                    slugInput.dataset.autoGenerated = 'true';
                }
            });
            
            slugInput.addEventListener('input', function() {
                if (this.value) {
                    this.dataset.autoGenerated = 'false';
                }
            });
        }
    });
</script>
@endpush