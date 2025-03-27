@extends('layouts.admin')

@section('title', 'Edit Spell')

@push('styles')
<style>
    .chapter-selection {
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 5px;
        padding: 15px;
        margin-bottom: 20px;
        background: rgba(10, 10, 30, 0.3);
    }
    
    .chapter-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        padding: 8px;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .chapter-item:hover {
        background: rgba(138, 43, 226, 0.1);
    }
    
    .chapter-info {
        margin-left: 10px;
        flex: 1;
    }
    
    .chapter-title {
        font-weight: 500;
        color: #d8b5ff;
    }
    
    .chapter-free-option {
        margin-left: 15px;
    }
    
    .select-all-chapters {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.2);
    }
    
    .file-upload-box {
        border: 2px dashed rgba(138, 43, 226, 0.4);
        border-radius: 5px;
        padding: 30px;
        text-align: center;
        background: rgba(10, 10, 30, 0.2);
        margin-bottom: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .file-upload-box:hover {
        border-color: rgba(138, 43, 226, 0.8);
        background: rgba(10, 10, 30, 0.4);
    }
    
    .file-upload-icon {
        font-size: 2.5rem;
        color: rgba(138, 43, 226, 0.6);
        margin-bottom: 15px;
    }
    
    .file-upload-text {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .file-name-preview {
        margin-top: 10px;
        font-size: 0.9rem;
        display: none;
    }
    
    .current-pdf-info {
        background: rgba(43, 138, 62, 0.1);
        border: 1px solid rgba(43, 138, 62, 0.2);
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 15px;
    }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center mb-3">
    <a href="{{ route('admin.spells.index') }}" class="btn btn-admin-secondary mr-3">
        <i class="fas fa-arrow-left"></i> Back to Spells
    </a>
    <h1 class="admin-page-title mb-0">Edit Spell: {{ $spell->title }}</h1>
</div>

<div class="admin-card">
    <form action="{{ route('admin.spells.update', $spell) }}" method="POST" enctype="multipart/form-data" class="admin-form">
        @csrf
        @method('PUT')
        
        @if($errors->any())
            <div class="admin-alert admin-alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="title">Spell Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $spell->title) }}" required>
                </div>
                
                <div class="mb-3">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="5" required>{{ old('description', $spell->description) }}</textarea>
                    <div class="form-text text-muted">Provide a detailed description of the spell and its benefits.</div>
                </div>
                
                <div class="admin-form-group">
                    <label for="pdf_file">Spell PDF File</label>
                    <input type="file" class="admin-form-control" id="pdf_file" name="pdf_file" accept=".pdf" @if(!isset($spell)) required @endif>
                    
                    @if(isset($spell) && $spell->pdf_path)
                        <div class="mt-2" style="margin-top:1rem;">
                            <span class="text-success">
                                <i class="fas fa-check-circle"></i> Current PDF file: {{ basename($spell->pdf_path) }}
                            </span>
                            <a href="{{ asset($spell->pdf_path) }}" target="_blank" class="btn btn-admin-secondary btn-sm ml-2">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    @endif
                    
                    <small class="form-text text-muted">
                        Upload a PDF file (max 10MB). @if(isset($spell)) Leave empty to keep the current file. @endif
                    </small>
                    
                    @error('pdf_file')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="price">Price</label>
                            <input type="number" name="price" id="price" class="form-control" value="{{ old('price', $spell->price) }}" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="currency">Currency</label>
                            <input type="text" name="currency" id="currency" class="form-control" value="{{ old('currency', $spell->currency) }}" maxlength="3" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="order">Display Order</label>
                            <input type="number" name="order" id="order" class="form-control" value="{{ old('order', $spell->order) }}" min="1" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3 mt-4">
                            <div class="form-check">
                                <input type="checkbox" name="is_published" id="is_published" class="form-check-input" value="1" {{ old('is_published', $spell->is_published) ? 'checked' : '' }}>
                                <label for="is_published" class="form-check-label">Published</label>
                            </div>
                            <div class="form-text text-muted">If checked, this spell will be visible on the site.</div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label>Related Chapters</label>
                    <div class="chapter-selection">
                        <div class="select-all-chapters">
                            <div class="form-check">
                                <input type="checkbox" id="select-all" class="form-check-input">
                                <label for="select-all" class="form-check-label">Select All Chapters</label>
                            </div>
                        </div>
                        
                        @if($chapters->isEmpty())
                            <p class="text-muted">No chapters available. Please create chapters first.</p>
                        @else
                            @foreach($chapters as $chapter)
                                @if(!$chapter->is_free)
                                    <div class="chapter-item">
                                        <div class="form-check">
                                            <input type="checkbox" name="related_chapters[]" id="chapter-{{ $chapter->id }}" 
                                                value="{{ $chapter->id }}" class="form-check-input chapter-checkbox"
                                                {{ in_array($chapter->id, old('related_chapters', $relatedChapterIds)) ? 'checked' : '' }}>
                                            <label for="chapter-{{ $chapter->id }}" class="form-check-label"></label>
                                        </div>
                                        
                                        <div class="chapter-info">
                                            <div class="chapter-title">Chapter {{ $chapter->order }}: {{ $chapter->title }}</div>
                                        </div>
                                        
                                        <div class="chapter-free-option">
                                            <div class="form-check">
                                                <input type="checkbox" name="free_with_chapters[]" id="free-{{ $chapter->id }}" 
                                                    value="{{ $chapter->id }}" class="form-check-input free-checkbox"
                                                    {{ in_array($chapter->id, old('free_with_chapters', $freeChapterIds)) ? 'checked' : '' }}
                                                    {{ !in_array($chapter->id, old('related_chapters', $relatedChapterIds)) ? 'disabled' : '' }}>
                                                <label for="free-{{ $chapter->id }}" class="form-check-label">Free with this chapter</label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                    <div class="form-text text-muted" style="margin-bottom: 1rem;">Select which chapters this spell is related to and if it's provided free with any of them.</div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('admin.spells.index') }}" class="btn btn-admin-secondary">Cancel</a>
            <button type="submit" class="btn btn-admin-primary">
                <i class="fas fa-save"></i> Update Spell
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // PDF File upload
        const pdfUploadBox = document.getElementById('pdfUploadBox');
        const pdfFileInput = document.getElementById('pdf_file');
        const pdfFileName = document.getElementById('pdfFileName');
        
        pdfUploadBox.addEventListener('click', function() {
            pdfFileInput.click();
        });
        
        pdfFileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                pdfFileName.textContent = this.files[0].name;
                pdfFileName.style.display = 'block';
                
                // Change the icon and color to indicate file selected
                const icon = pdfUploadBox.querySelector('.file-upload-icon i');
                icon.className = 'fas fa-check-circle';
                icon.style.color = '#4BB543';
            }
        });
        
        // Chapter selection functionality
        const selectAllCheckbox = document.getElementById('select-all');
        const chapterCheckboxes = document.querySelectorAll('.chapter-checkbox');
        const freeCheckboxes = document.querySelectorAll('.free-checkbox');
        
        // Check if all chapters are already selected
        function updateSelectAllState() {
            const allSelected = Array.from(chapterCheckboxes).every(checkbox => checkbox.checked);
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = allSelected;
            }
        }
        
        // Initialize select all state
        updateSelectAllState();
        
        // Select all checkbox functionality
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                chapterCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                    
                    // Find the corresponding free checkbox and update its disabled state
                    const chapterId = checkbox.value;
                    const freeCheckbox = document.getElementById('free-' + chapterId);
                    if (freeCheckbox) {
                        freeCheckbox.disabled = !this.checked;
                    }
                });
            });
        }
        
        // Individual chapter checkbox functionality
        chapterCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const chapterId = this.value;
                const freeCheckbox = document.getElementById('free-' + chapterId);
                
                if (freeCheckbox) {
                    freeCheckbox.disabled = !this.checked;
                    if (!this.checked) {
                        freeCheckbox.checked = false;
                    }
                }
                
                // Update select all checkbox state
                updateSelectAllState();
            });
        });
    });
</script>
@endpush