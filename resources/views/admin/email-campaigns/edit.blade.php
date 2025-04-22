@extends('layouts.admin')

@section('title', 'Edit Email Campaign')

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
    

    #campaignForm {
        margin-top: 20px;
    }
</style>
@endpush

@section('content')
<h1 class="admin-page-title">Edit Email Campaign</h1>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title mb-0">Edit Campaign: {{ $campaign->name }}</h2>
        <div>
            <a href="{{ route('admin.email-campaigns.send-confirmation', $campaign) }}" class="btn-admin-primary me-2">
                <i class="fas fa-paper-plane"></i> Send
            </a>
            <a href="{{ route('admin.email-campaigns.index') }}" class="btn-admin-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <form action="{{ route('admin.email-campaigns.update', $campaign) }}" method="POST" class="admin-form" id="campaignForm">
        @csrf
        @method('PUT')
        
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="name">Campaign Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $campaign->name) }}" required>
                    @error('name')
                        <div class="text-danger mt-1">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="subject">Email Subject</label>
                    <input type="text" name="subject" id="subject" class="form-control" value="{{ old('subject', $campaign->subject) }}" required>
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
                    <option value="has_purchases" {{ old('segment', $campaign->segment_conditions) === 'has_purchases' ? 'selected' : '' }}>
                        Users Who Made Purchases
                    </option>
                    <option value="no_purchases" {{ old('segment', $campaign->segment_conditions) === 'no_purchases' ? 'selected' : '' }}>
                        Users With No Purchases
                    </option>
                    <option value="free_content_only" {{ old('segment', $campaign->segment_conditions) === 'free_content_only' ? 'selected' : '' }}>
                        Users With Only Free Content
                    </option>
                </optgroup>
                
                <optgroup label="Subscription Status">
                    <option value="active_subscribers" {{ old('segment', $campaign->segment_conditions) === 'active_subscribers' ? 'selected' : '' }}>
                        Active Subscribers
                    </option>
                    <option value="expired_subscribers" {{ old('segment', $campaign->segment_conditions) === 'expired_subscribers' ? 'selected' : '' }}>
                        Expired Subscribers
                    </option>
                    <option value="lifetime_subscribers" {{ old('segment', $campaign->segment_conditions) === 'lifetime_subscribers' ? 'selected' : '' }}>
                        Lifetime Subscribers
                    </option>
                    <option value="non_subscribers" {{ old('segment', $campaign->segment_conditions) === 'non_subscribers' ? 'selected' : '' }}>
                        Non-Subscribers
                    </option>
                </optgroup>
                
                <optgroup label="Content Ownership">
                    <option value="chapter_owners" {{ old('segment', $campaign->segment_conditions) === 'chapter_owners' ? 'selected' : '' }}>
                        Chapter Owners
                    </option>
                    <option value="spell_owners" {{ old('segment', $campaign->segment_conditions) === 'spell_owners' ? 'selected' : '' }}>
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
            
            <!-- Hidden textarea to store HTML content -->
            <textarea name="content" id="hiddenContent" style="display: none;">{{ old('content', $campaign->content) }}</textarea>
            
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
                    
                    <!-- <button type="button" class="toolbar-btn" data-command="createLink" title="Insert Link">
                        <i class="fas fa-link"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="unlink" title="Remove Link">
                        <i class="fas fa-unlink"></i>
                    </button>
                    
                    <div class="toolbar-separator"></div> -->
                    
                    <button type="button" class="toolbar-btn" data-command="undo" title="Undo">
                        <i class="fas fa-undo"></i>
                    </button>
                    <button type="button" class="toolbar-btn" data-command="redo" title="Redo">
                        <i class="fas fa-redo"></i>
                    </button>
                </div>
                <div class="editor-content" id="emailEditor" contenteditable="true"></div>
            </div>
            <p>
                <small style="color: rgba(255, 255, 255, 0.6);">
                    Use @{{name}} to insert the user's name, @{{email}} for their email, and @{{first_name}} for their first name.
                    <br>
                    <strong>Note:</strong> Avoid using HTML tags directly in the content area. Use the toolbar for formatting.
                    <br>
                </small>
            </p>
        </div>
        
        <div class="mt-4 text-center">
            <button type="submit" class="btn-admin-primary">
                <i class="fas fa-save"></i> Update Campaign
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const editor = document.getElementById('emailEditor');
        const hiddenContent = document.getElementById('hiddenContent');
        const form = document.getElementById('campaignForm');
        const toolbarButtons = document.querySelectorAll('.toolbar-btn');
        const paragraphFormat = document.querySelector('.paragraph-format');
        
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
    });
</script>
@endpush