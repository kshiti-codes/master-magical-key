@extends('layouts.admin')

@section('title', 'Create Email Campaign')

@push('styles')
<style>
    .variable-badge {
        display: inline-block;
        padding: 5px 10px;
        margin: 5px;
        background: rgba(138, 43, 226, 0.2);
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 5px;
        color: #d8b5ff;
        cursor: pointer;
    }
    
    .variables-container {
        margin-bottom: 15px;
        padding: 10px;
        background: rgba(10, 10, 30, 0.3);
        border-radius: 5px;
    }
    
    /* Custom Editor Styles */
    .editor-wrapper {
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 5px;
        overflow: hidden;
        background: rgba(30, 30, 60, 0.4);
        margin-bottom: 20px;
        transition: border-color 0.3s ease;
    }
    
    .editor-wrapper:focus-within {
        border-color: rgba(138, 43, 226, 0.7);
        box-shadow: 0 0 10px rgba(138, 43, 226, 0.3);
    }
    
    .editor-toolbar {
        padding: 10px;
        background: rgba(20, 20, 40, 0.6);
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .toolbar-btn {
        background: rgba(60, 60, 100, 0.6);
        border: 1px solid rgba(138, 43, 226, 0.3);
        color: #d8b5ff;
        border-radius: 4px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .toolbar-btn:hover {
        background: rgba(80, 80, 120, 0.6);
    }
    
    .toolbar-btn.active {
        background: rgba(138, 43, 226, 0.4);
        box-shadow: 0 0 5px rgba(138, 43, 226, 0.5) inset;
    }
    
    .toolbar-separator {
        width: 1px;
        height: 36px;
        background: rgba(138, 43, 226, 0.3);
        margin: 0 5px;
    }
    
    .toolbar-select {
        background: rgba(60, 60, 100, 0.6);
        border: 1px solid rgba(138, 43, 226, 0.3);
        color: #d8b5ff;
        border-radius: 4px;
        height: 36px;
        padding: 0 10px;
    }
    
    .editor-content {
        padding: 15px;
        min-height: 300px;
        color: white;
        outline: none;
    }
    
    .editor-content:focus {
        outline: none;
    }
    
    .segment-category {
        font-weight: bold;
        color: #d8b5ff;
        padding: 5px 12px;
        background: rgba(138, 43, 226, 0.2);
    }
    
    .segment-option {
        padding-left: 20px;
    }
    
    /* Modal styles */
    .editor-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .editor-modal-content {
        background: rgba(30, 30, 60, 0.95);
        border: 1px solid rgba(138, 43, 226, 0.5);
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        padding: 20px;
        box-shadow: 0 0 30px rgba(138, 43, 226, 0.3);
    }
    
    .modal-title {
        color: #d8b5ff;
        font-family: 'Cinzel', serif;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .modal-form-group {
        margin-bottom: 15px;
    }
    
    .modal-label {
        display: block;
        margin-bottom: 5px;
        color: white;
    }
    
    .modal-input {
        width: 100%;
        padding: 8px 12px;
        background: rgba(20, 20, 40, 0.6);
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 4px;
        color: white;
    }
    
    .modal-select {
        width: 100%;
        padding: 8px 12px;
        background: rgba(20, 20, 40, 0.6);
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 4px;
        color: white;
    }
    
    .modal-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }
    
    .modal-btn {
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .modal-btn-cancel {
        background: rgba(60, 60, 100, 0.6);
        border: 1px solid rgba(138, 43, 226, 0.3);
        color: #d8b5ff;
    }
    
    .modal-btn-insert {
        background: rgba(138, 43, 226, 0.6);
        border: 1px solid rgba(138, 43, 226, 0.8);
        color: white;
    }
    
    /* Email button styles */
    .email-button {
        display: inline-block;
        padding: 10px 20px;
        margin: 10px 0;
        background: linear-gradient(to right, #4b0082, #9400d3);
        color: white;
        text-decoration: none;
        border-radius: 5px;
        text-align: center;
        cursor: pointer;
    }
    
    /* Image preview */
    .image-preview {
        max-width: 100%;
        max-height: 150px;
        margin-top: 10px;
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 4px;
        display: none;
    }
    
    /* File input styles */
    .file-input-wrapper {
        position: relative;
        overflow: hidden;
        display: inline-block;
        cursor: pointer;
    }
    
    .file-input-button {
        background: rgba(60, 60, 100, 0.6);
        border: 1px solid rgba(138, 43, 226, 0.3);
        color: #d8b5ff;
        border-radius: 4px;
        padding: 8px 15px;
        cursor: pointer;
    }
    
    .file-input {
        position: absolute;
        top: 0;
        left: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .file-name {
        margin-left: 10px;
        color: white;
    }

    #campaignForm {
        margin-top: 20px;
    }
</style>
@endpush

@section('content')
<h1 class="admin-page-title">Create Email Campaign</h1>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title mb-0">New Campaign</h2>
        <a href="{{ route('admin.email-campaigns.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back to Campaigns
        </a>
    </div>

    <form action="{{ route('admin.email-campaigns.store') }}" method="POST" class="admin-form" id="campaignForm" enctype="multipart/form-data">
        @csrf
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="name">Campaign Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="subject">Email Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control" value="{{ old('subject') }}" required>
                    @error('subject')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
        
        <div class="mb-4">
            <label for="segment">Recipient Segment</label>
            <select name="segment" id="segment" class="form-control">
                <option value="">All Users</option>
                
                <optgroup label="Purchase History">
                    <option value="has_purchases" {{ old('segment') === 'has_purchases' ? 'selected' : '' }}>
                        Users Who Made Purchases
                    </option>
                    <option value="no_purchases" {{ old('segment') === 'no_purchases' ? 'selected' : '' }}>
                        Users With No Purchases
                    </option>
                    <option value="free_content_only" {{ old('segment') === 'free_content_only' ? 'selected' : '' }}>
                        Users With Only Free Content
                    </option>
                </optgroup>
                
                <optgroup label="Subscription Status">
                    <option value="active_subscribers" {{ old('segment') === 'active_subscribers' ? 'selected' : '' }}>
                        Active Subscribers
                    </option>
                    <option value="expired_subscribers" {{ old('segment') === 'expired_subscribers' ? 'selected' : '' }}>
                        Expired Subscribers
                    </option>
                    <option value="lifetime_subscribers" {{ old('segment') === 'lifetime_subscribers' ? 'selected' : '' }}>
                        Lifetime Subscribers
                    </option>
                    <option value="non_subscribers" {{ old('segment') === 'non_subscribers' ? 'selected' : '' }}>
                        Non-Subscribers
                    </option>
                </optgroup>
                
                <optgroup label="Content Ownership">
                    <option value="chapter_owners" {{ old('segment') === 'chapter_owners' ? 'selected' : '' }}>
                        Chapter Owners
                    </option>
                    <option value="spell_owners" {{ old('segment') === 'spell_owners' ? 'selected' : '' }}>
                        Spell Owners
                    </option>
                </optgroup>
            </select>
            
            @error('segment')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
        </div>
        
        <div class="mb-2">
            <label for="emailContent">Email Content</label>
            
            <!-- File upload for images -->
            <div id="imageUploads" style="display: none;">
                <!-- Hidden image uploads will be added here -->
            </div>
            
            <!-- Hidden textarea to store HTML content -->
            <textarea name="content" id="hiddenContent" style="display: none;">{{ old('content') }}</textarea>
            
            @error('content')
                <div class="text-danger mt-1">{{ $message }}</div>
            @enderror
            
            <!-- Custom Editor -->
            <div class="editor-wrapper">
                <div class="editor-toolbar">
                    <select class="toolbar-select paragraph-format">
                        <option value="p">Paragraph</option>
                        <option value="h1">Heading 1</option>
                        <option value="h2">Heading 2</option>
                        <option value="h3">Heading 3</option>
                        <option value="h4">Heading 4</option>
                    </select>
                    
                    <div class="toolbar-separator"></div>
                    
                    <button type="button" class="toolbar-btn" data-command="bold" title="Bold">
                        <i class="fas fa-bold"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="italic" title="Italic">
                        <i class="fas fa-italic"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="underline" title="Underline">
                        <i class="fas fa-underline"></i>
                    </button>
                    
                    <div class="toolbar-separator"></div>
                    
                    <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List">
                        <i class="fas fa-list-ul"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered List">
                        <i class="fas fa-list-ol"></i>
                    </button>
                    
                    <div class="toolbar-separator"></div>
                    
                    <button type="button" class="toolbar-btn" data-command="justifyLeft" title="Align Left">
                        <i class="fas fa-align-left"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="justifyCenter" title="Align Center">
                        <i class="fas fa-align-center"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="justifyRight" title="Align Right">
                        <i class="fas fa-align-right"></i>
                    </button>
                    
                    <div class="toolbar-separator"></div>
                    
                    <button type="button" class="toolbar-btn" data-command="createLink" title="Insert Link">
                        <i class="fas fa-link"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="unlink" title="Remove Link">
                        <i class="fas fa-unlink"></i>
                    </button>
                    
                    <div class="toolbar-separator"></div>
                    
                    <!-- <button type="button" class="toolbar-btn" id="insertImageBtn" title="Insert Image">
                        <i class="fas fa-image"></i>
                    </button>
                    <button type="button" class="toolbar-btn" id="insertButtonBtn" title="Insert Button">
                        <i class="fas fa-mouse-pointer"></i>
                    </button> 
                    
                    <div class="toolbar-separator"></div>-->
                    
                    <button type="button" class="toolbar-btn" data-command="undo" title="Undo">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="redo" title="Redo">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>
                <div class="editor-content" id="emailEditor" contenteditable="true"></div>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save"></i> Save Campaign
            </button>
        </div>
    </form>
</div>

<!-- Image Modal -->
<div class="editor-modal" id="imageModal">
    <div class="editor-modal-content">
        <h3 class="modal-title">Insert Image</h3>
        
        <div class="modal-form-group">
            <label class="modal-label">Choose Image</label>
            <div class="file-input-wrapper">
                <button type="button" class="file-input-button">Select Image</button>
                <input type="file" id="imageFile" class="file-input" accept="image/*">
                <span class="file-name" id="imageName"></span>
            </div>
            <img id="imagePreview" class="image-preview">
        </div>
        
        <div class="modal-form-group">
            <label class="modal-label" for="imageAlt">Alt Text</label>
            <input type="text" id="imageAlt" class="modal-input" placeholder="Describe the image">
        </div>
        
        <div class="modal-form-group">
            <label class="modal-label" for="imageAlign">Alignment</label>
            <select id="imageAlign" class="modal-select">
                <option value="none">None</option>
                <option value="left">Left</option>
                <option value="center">Center</option>
                <option value="right">Right</option>
            </select>
        </div>
        
        <div class="modal-buttons">
            <button type="button" class="modal-btn modal-btn-cancel" id="cancelImageBtn">Cancel</button>
            <button type="button" class="modal-btn modal-btn-insert" id="insertImageConfirmBtn">Insert Image</button>
        </div>
    </div>
</div>

<!-- Button Modal -->
<div class="editor-modal" id="buttonModal">
    <div class="editor-modal-content">
        <h3 class="modal-title">Insert Button</h3>
        
        <div class="modal-form-group">
            <label class="modal-label" for="buttonText">Button Text</label>
            <input type="text" id="buttonText" class="modal-input" placeholder="Click Here">
        </div>
        
        <div class="modal-form-group">
            <label class="modal-label" for="buttonUrl">Button URL</label>
            <input type="text" id="buttonUrl" class="modal-input" placeholder="https://example.com">
        </div>
        
        <div class="modal-form-group">
            <label class="modal-label" for="buttonAlign">Alignment</label>
            <select id="buttonAlign" class="modal-select">
                <option value="left">Left</option>
                <option value="center">Center</option>
                <option value="right">Right</option>
            </select>
        </div>
        
        <div class="modal-buttons">
            <button type="button" class="modal-btn modal-btn-cancel" id="cancelButtonBtn">Cancel</button>
            <button type="button" class="modal-btn modal-btn-insert" id="insertButtonConfirmBtn">Insert Button</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editor = document.getElementById('emailEditor');
        const hiddenContent = document.getElementById('hiddenContent');
        const form = document.getElementById('campaignForm');
        const toolbarButtons = document.querySelectorAll('.toolbar-btn[data-command]');
        const paragraphFormat = document.querySelector('.paragraph-format');
        const imageUploads = document.getElementById('imageUploads');
        let imageCounter = 0;
        
        // Modal elements
        const imageModal = document.getElementById('imageModal');
        const buttonModal = document.getElementById('buttonModal');
        const insertImageBtn = document.getElementById('insertImageBtn');
        const insertButtonBtn = document.getElementById('insertButtonBtn');
        const cancelImageBtn = document.getElementById('cancelImageBtn');
        const insertImageConfirmBtn = document.getElementById('insertImageConfirmBtn');
        const cancelButtonBtn = document.getElementById('cancelButtonBtn');
        const insertButtonConfirmBtn = document.getElementById('insertButtonConfirmBtn');
        
        // Image upload elements
        const imageFile = document.getElementById('imageFile');
        const imageName = document.getElementById('imageName');
        const imagePreview = document.getElementById('imagePreview');
        
        // Initialize editor with existing content if any
        if (hiddenContent.value) {
            editor.innerHTML = hiddenContent.value;
        }
        
        // Save editor content to hidden field before form submission
        form.addEventListener('submit', function() {
            hiddenContent.value = editor.innerHTML;
        });
        
        // Update format on selection change
        function updateToolbarState() {
            toolbarButtons.forEach(button => {
                const command = button.getAttribute('data-command');
                if (document.queryCommandState(command)) {
                    button.classList.add('active');
                } else {
                    button.classList.remove('active');
                }
            });
        }
        
        // Handle toolbar button clicks
        toolbarButtons.forEach(button => {
            button.addEventListener('click', function() {
                const command = this.getAttribute('data-command');
                
                if (command === 'createLink') {
                    const url = prompt('Enter the link URL');
                    if (url) {
                        document.execCommand(command, false, url);
                    }
                } else {
                    document.execCommand(command, false, null);
                }
                
                updateToolbarState();
                editor.focus();
            });
        });
        
        // Handle paragraph formatting
        paragraphFormat.addEventListener('change', function() {
            document.execCommand('formatBlock', false, this.value);
            editor.focus();
        });
        
        // Update toolbar state when selection changes
        editor.addEventListener('mouseup', updateToolbarState);
        editor.addEventListener('keyup', updateToolbarState);
        
        // Variable insertion
        document.querySelectorAll('.variable-badge').forEach(function(badge) {
            badge.addEventListener('click', function() {
                const variable = this.getAttribute('data-variable');
                
                // Insert at cursor position
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    const textNode = document.createTextNode(variable);
                    range.deleteContents();
                    range.insertNode(textNode);
                    
                    // Move cursor to end of inserted text
                    range.setStartAfter(textNode);
                    range.setEndAfter(textNode);
                    selection.removeAllRanges();
                    selection.addRange(range);
                } else {
                    // If no selection, append to end
                    editor.innerHTML += variable;
                }
                
                editor.focus();
            });
        });
        
        // Image button click
        insertImageBtn.addEventListener('click', function() {
            imageModal.style.display = 'flex';
        });
        
        // Button button click
        insertButtonBtn.addEventListener('click', function() {
            buttonModal.style.display = 'flex';
        });
        
        // Cancel image modal
        cancelImageBtn.addEventListener('click', function() {
            imageModal.style.display = 'none';
            imageFile.value = '';
            imageName.textContent = '';
            imagePreview.style.display = 'none';
            imagePreview.src = '';
        });
        
        // Cancel button modal
        cancelButtonBtn.addEventListener('click', function() {
            buttonModal.style.display = 'none';
            document.getElementById('buttonText').value = '';
            document.getElementById('buttonUrl').value = '';
            document.getElementById('buttonAlign').value = 'left';
        });
        
        // Handle image file selection
        imageFile.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                imageName.textContent = file.name;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
        
        // Insert image confirmation
        insertImageConfirmBtn.addEventListener('click', function() {
            if (!imageFile.files || !imageFile.files[0]) {
                alert('Please select an image file');
                return;
            }
            
            const file = imageFile.files[0];
            const alt = document.getElementById('imageAlt').value || 'Image';
            const align = document.getElementById('imageAlign').value;
            
            // Create a unique ID for this image
            const imageId = 'email-image-' + imageCounter++;
            
            // Create a hidden input for the file
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'file';
            hiddenInput.name = 'email_images[]';
            hiddenInput.style.display = 'none';
            hiddenInput.id = imageId + '-upload';
            
            // Clone the selected file to the hidden input
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            hiddenInput.files = dataTransfer.files;
            
            // Add to form
            imageUploads.appendChild(hiddenInput);
            
            // Create image placeholder in editor
            const imageHTML = `<div class="image-container" ${align !== 'none' ? `style="text-align: ${align};"` : ''}><img src="${imagePreview.src}" alt="${alt}" data-image-id="${imageId}" style="max-width: 100%;"></div>`;
            
            // Insert at cursor position
            insertContentAtCursor(imageHTML);
            
            // Close modal and reset
            imageModal.style.display = 'none';
            imageFile.value = '';
            imageName.textContent = '';
            imagePreview.style.display = 'none';
            document.getElementById('imageAlt').value = '';
            document.getElementById('imageAlign').value = 'none';
        });
        
        // Insert button confirmation
        insertButtonConfirmBtn.addEventListener('click', function() {
            const text = document.getElementById('buttonText').value || 'Click Here';
            const url = document.getElementById('buttonUrl').value || '#';
            const align = document.getElementById('buttonAlign').value;
            
            // Create button HTML
            const buttonHTML = `<div style="text-align: ${align};"><a href="${url}" class="email-button">${text}</a></div>`;
            
            // Insert at cursor position
            insertContentAtCursor(buttonHTML);
            
            // Close modal and reset
            buttonModal.style.display = 'none';
            document.getElementById('buttonText').value = '';
            document.getElementById('buttonUrl').value = '';
            document.getElementById('buttonAlign').value = 'left';
        });
        
        // Helper function to insert content at cursor position
        function insertContentAtCursor(html) {
            // Make sure the editor has focus
            editor.focus();
            
            // Insert at current selection
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                
                // Create a temporary div to hold our HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = html;
                
                // Insert all nodes from the temp div
                const fragment = document.createDocumentFragment();
                while (tempDiv.firstChild) {
                    fragment.appendChild(tempDiv.firstChild);
                }
                
                range.deleteContents();
                range.insertNode(fragment);
                
                // Move cursor to end of inserted content
                range.collapse(false);
                selection.removeAllRanges();
                selection.addRange(range);
            } else {
                // If no selection, append to end
                editor.innerHTML += html;
            }
        }
    });
</script>
@endpush