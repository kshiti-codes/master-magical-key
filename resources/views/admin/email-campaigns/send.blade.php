@extends('layouts.admin')

@section('title', 'Send Email Campaign')

@push('styles')
<style>
    .email-preview {
        border: 1px solid rgba(138, 43, 226, 0.3);
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        background: rgba(10, 10, 30, 0.4);
        max-height: 400px;
        overflow-y: auto;
    }
    
    .preview-header {
        background: rgba(5, 5, 20, 0.5);
        padding: 15px;
        margin: -20px -20px 20px -20px;
        border-radius: 8px 8px 0 0;
        border-bottom: 1px solid rgba(138, 43, 226, 0.3);
    }
    
    .preview-subject {
        font-size: 1.2rem;
        color: #d8b5ff;
        margin-bottom: 5px;
    }
    
    .preview-metadata {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.6);
    }
    
    .confirmation-warning {
        background: rgba(255, 193, 7, 0.1);
        border-left: 4px solid #ffc107;
        padding: 15px;
        margin: 20px 0;
        color: #ffe0a0;
    }
</style>
@endpush

@section('content')
<h1 class="admin-page-title">Send Email Campaign</h1>

<div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="admin-card-title mb-0">Send Campaign: {{ $campaign->name }}</h2>
        <a href="{{ route('admin.email-campaigns.index') }}" class="btn-admin-secondary">
            <i class="fas fa-arrow-left"></i> Back to Campaigns
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="mb-4">
                <h3 style="color: #d8b5ff; font-size: 1.2rem; margin-bottom: 15px;">Campaign Details</h3>
                
                <div class="mb-3">
                    <label style="color: rgba(255, 255, 255, 0.6);">Campaign Name:</label>
                    <div>{{ $campaign->name }}</div>
                </div>
                
                <div class="mb-3">
                    <label style="color: rgba(255, 255, 255, 0.6);">Subject:</label>
                    <div>{{ $campaign->subject }}</div>
                </div>
                
                <div class="mb-3">
                    <label style="color: rgba(255, 255, 255, 0.6);">Recipient Segment:</label>
                    <div>{{ $campaign->segment_name }}</div>
                </div>
                
                <div class="mb-3">
                    <label style="color: rgba(255, 255, 255, 0.6);">Estimated Recipients:</label>
                    <div>{{ $recipientCount }} users</div>
                </div>
            </div>
            
            <div class="confirmation-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Warning:</strong> This action cannot be undone. The email will be sent to {{ $recipientCount }} recipients.
            </div>
            
            <form action="{{ route('admin.email-campaigns.send', $campaign) }}" method="POST">
                @csrf
                
                <div class="mt-4 d-flex gap-3">
                    <button type="submit" class="btn-admin-primary">
                        <i class="fas fa-paper-plane"></i> Send Campaign Now
                    </button>
                    
                    <a href="{{ route('admin.email-campaigns.edit', $campaign) }}" class="btn-admin-secondary">
                        <i class="fas fa-edit"></i> Edit First
                    </a>
                </div>
            </form>
        </div>
        
        <div class="col-lg-6">
            <h3 style="color: #d8b5ff; font-size: 1.2rem; margin-bottom: 15px;">Email Preview</h3>
            
            <div class="email-preview">
                <div class="preview-header">
                    <div class="preview-subject">{{ $campaign->subject }}</div>
                    <div class="preview-metadata">
                        From: Master Magical Key &lt;noreply@mastermagicalkey.com&gt;<br>
                        To: [Recipient]
                    </div>
                </div>
                
                <div>
                    {!! $campaign->content !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection