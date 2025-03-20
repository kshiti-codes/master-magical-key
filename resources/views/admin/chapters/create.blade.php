@extends('layouts.admin')

@section('title', 'Create Chapter')

@push('styles')
<link href="{{ asset('css/admin/admin-chapters-styles.css') }}" rel="stylesheet">
<link href="{{ asset('css/admin/admin-modal-styles.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="admin-page-title">Create New Chapter</h1>
        <a href="{{ route('admin.chapters.index') }}" class="btn btn-admin-secondary" style="margin-bottom: 1rem;">
            <i class="fas fa-arrow-left"></i> Back to Chapters
        </a>
    </div>
    
    <div class="admin-card">
        <form action="{{ route('admin.chapters.store') }}" method="POST" enctype="multipart/form-data" class="admin-form">
            @csrf
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title">Chapter Title</label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="order">Display Order</label>
                        <input type="number" class="form-control @error('order') is-invalid @enderror" id="order" name="order" value="{{ old('order') }}" min="1" required>
                        @error('order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="price">Price</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', '11.11') }}" min="0" style="width: 30%;" required>
                            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency">
                                <option value="AUD" {{ old('currency') == 'AUD' ? 'selected' : '' }}>AUD</option>
                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR</option>
                            </select>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description">Chapter Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" {{ old('is_published') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_published">Publish this chapter</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_free" name="is_free" {{ old('is_free') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_free">Free chapter</label>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="audio_file">Chapter Audio (Optional)</label>
                <input type="file" class="form-control @error('audio_file') is-invalid @enderror" id="audio_file" name="audio_file" accept="audio/mp3,audio/wav,audio/ogg">
                <small class="form-text text-muted">Upload an MP3, WAV, or OGG file (max 50MB)</small>
                @error('audio_file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-3">
                <label for="content">Chapter Content</label>
                <div class="mb-2">
                    <button type="button" id="preview-btn" class="btn btn-admin-secondary btn-sm">
                        <i class="fas fa-eye"></i> Preview Formatting
                    </button>
                    <button type="button" id="insert-image-btn" class="btn btn-admin-secondary btn-sm">
                        <i class="fas fa-image"></i> Insert Image
                    </button>
                    <button type="button" id="sample-content-btn" class="btn btn-admin-secondary btn-sm">
                        <i class="fas fa-list"></i> Sample Content
                    </button>
                </div>
                <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="15" required>{{ old('content') }}</textarea>
                @error('content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                    Use Markdown formatting: **bold**, *italic*, # Heading, ## Subheading, > Quote, ![Image](url)
                </small>
            </div>
            
            <div id="content-preview" class="p-3 mb-3 d-none" style="border: 1px solid rgba(138, 43, 226, 0.3); border-radius: 5px; background: rgba(10, 10, 30, 0.4);">
                <h5>Content Preview</h5>
                <div id="preview-content"></div>
            </div>
            
            <div class="mb-3">
                <label for="preview_content">Preview Content (Optional)</label>
                <textarea class="form-control @error('preview_content') is-invalid @enderror" id="preview_content" name="preview_content" rows="3">{{ old('preview_content') }}</textarea>
                <small class="form-text text-muted">A short preview of the chapter shown to users before purchasing</small>
                @error('preview_content')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="mb-4">
                <label>Include Free Spells</label>
                <div class="row">
                    @foreach($spells as $spell)
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="free_spells[]" value="{{ $spell->id }}" id="spell_{{ $spell->id }}" 
                                    {{ (is_array(old('free_spells')) && in_array($spell->id, old('free_spells'))) ? 'checked' : '' }}>
                                <label class="form-check-label" for="spell_{{ $spell->id }}">
                                    {{ $spell->title }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <small class="form-text text-muted">Select spells that will be included free with this chapter</small>
            </div>
            
            <div class="text-end">
                <button type="submit" class="btn btn-admin-primary">
                    <i class="fas fa-save"></i> Create Chapter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Image Upload Modal -->
<div class="modal fade" id="imageUploadModal" tabindex="-1" aria-labelledby="imageUploadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-light">
            <div class="modal-header">
                <h5 class="modal-title" id="imageUploadModalLabel">Upload Image</h5>
            </div>
            <div class="modal-body">
                <form id="imageUploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="image" class="form-label">Select Image</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label for="alt_text" class="form-label">Alt Text</label>
                        <input type="text" class="form-control" id="alt_text" name="alt_text" placeholder="Describe the image">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image Attributes (Optional)</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">Width</span>
                                    <input type="number" class="form-control" id="img_width" name="width" placeholder="e.g., 500">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text">Height</span>
                                    <input type="number" class="form-control" id="img_height" name="height" placeholder="e.g., 300">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="img_class" class="form-label">CSS Class</label>
                        <select class="form-select" id="img_class" name="class">
                            <option value="center">Centered</option>
                            <option value="left">Float Left</option>
                            <option value="right">Float Right</option>
                            <option value="large">Full Width</option>
                        </select>
                    </div>
                </form>
                <div class="alert alert-info d-none" id="uploadStatus"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="uploadImageBtn">Upload & Insert</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Debug form submission
    document.addEventListener('DOMContentLoaded', function() {
        const chapterForm = document.querySelector('form.admin-form');
        if (chapterForm) {
            chapterForm.addEventListener('submit', function(e) {
                console.log('Form submission attempted');
                
                // Check for empty required fields
                const requiredFields = chapterForm.querySelectorAll('[required]');
                let missingFields = [];
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        missingFields.push(field.name || 'unnamed field');
                        console.error(`Missing required field: ${field.name || 'unnamed field'}`);
                        field.classList.add('is-invalid');
                    }
                });
                
                if (missingFields.length > 0) {
                    e.preventDefault();
                    alert(`Please fill in all required fields: ${missingFields.join(', ')}`);
                    return;
                }
                
                // Ensure we have a valid form action
                const formAction = chapterForm.getAttribute('action');
                if (!formAction) {
                    console.error('Form action is missing');
                    e.preventDefault();
                    alert('Form action is missing. Please contact the administrator.');
                    return;
                }
                
                // Show submission indicator
                const submitBtn = chapterForm.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
                    submitBtn.disabled = true;
                }
                
                console.log('Form submission proceeding');
                // Let the form submit normally
            });
        } else {
            console.error('Chapter form not found on page');
        }
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const previewBtn = document.getElementById('preview-btn');
        const contentTextarea = document.getElementById('content');
        const contentPreview = document.getElementById('content-preview');
        const previewContent = document.getElementById('preview-content');
        const insertImageBtn = document.getElementById('insert-image-btn');
        const sampleContentBtn = document.getElementById('sample-content-btn');
        
        // Handle content preview
        previewBtn.addEventListener('click', function() {
            const content = contentTextarea.value;
            
            if (content.trim() === '') {
                alert('Please enter some content to preview');
                return;
            }
            
            // Show loading state
            previewContent.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Generating preview...</div>';
            contentPreview.classList.remove('d-none');
            
            // Send to server for formatting
            fetch('{{ route('admin.chapters.preview') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ content: content })
            })
            .then(response => response.json())
            .then(data => {
                previewContent.innerHTML = data.formatted_content;
            })
            .catch(error => {
                previewContent.innerHTML = '<div class="alert alert-danger">Error generating preview</div>';
                console.error('Error:', error);
            });
        });
        
        // Handle image upload
        const imageModal = new bootstrap.Modal(document.getElementById('imageUploadModal'));
        
        insertImageBtn.addEventListener('click', function() {
            imageModal.show();
        });
        
        document.getElementById('uploadImageBtn').addEventListener('click', function() {
            const formData = new FormData(document.getElementById('imageUploadForm'));
            const uploadStatus = document.getElementById('uploadStatus');
            
            // Validate form
            if (!formData.get('image')) {
                uploadStatus.textContent = 'Please select an image';
                uploadStatus.classList.remove('d-none');
                return;
            }
            
            // Show loading
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            uploadStatus.textContent = 'Uploading image...';
            uploadStatus.classList.remove('d-none');
            
            // Upload image
            fetch('{{ route('admin.chapters.upload-image') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Insert markdown at cursor position
                const altText = document.getElementById('alt_text').value || 'Image';
                let markdown = `![${altText}](${data.url}`;
                
                // Add attributes if provided
                const width = document.getElementById('img_width').value;
                const height = document.getElementById('img_height').value;
                const cssClass = document.getElementById('img_class').value;
                
                if (width || height || cssClass) {
                    markdown += '|';
                    
                    if (width) markdown += `width=${width}|`;
                    if (height) markdown += `height=${height}|`;
                    if (cssClass) markdown += `class=${cssClass}|`;
                    
                    // Remove trailing pipe
                    markdown = markdown.slice(0, -1);
                }
                
                markdown += ')';
                
                // Insert at cursor position
                const textarea = document.getElementById('content');
                const cursorPos = textarea.selectionStart;
                const textBefore = textarea.value.substring(0, cursorPos);
                const textAfter = textarea.value.substring(cursorPos);
                
                textarea.value = textBefore + markdown + textAfter;
                
                // Close modal
                imageModal.hide();
                
                // Reset form
                document.getElementById('imageUploadForm').reset();
                this.disabled = false;
                this.innerHTML = 'Upload & Insert';
                uploadStatus.classList.add('d-none');
            })
            .catch(error => {
                uploadStatus.textContent = 'Error uploading image';
                uploadStatus.classList.add('alert-danger');
                uploadStatus.classList.remove('alert-info');
                this.disabled = false;
                this.innerHTML = 'Upload & Insert';
                console.error('Error:', error);
            });
        });
        
        // Handle sample content
        sampleContentBtn.addEventListener('click', function() {
            fetch('{{ route('admin.chapters.sample') }}')
                .then(response => response.json())
                .then(data => {
                    contentTextarea.value = data.sample_content;
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        });
    });
</script>
@endpush